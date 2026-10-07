<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use App\Models\Booking;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PaymentProcess;
use App\Models\ShopPayment;
use App\Models\Transaction;
use App\Services\PaymentService\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Called only inside existing authenticated initiation/verified settlement paths. */
final class ProviderContributionAdapter
{
    public function prepare(string $key, array $data, Payment $payment, array $routing, BaseService $base): array
    {
        if (($key === 'booking_id' && isset($data['tips'])) || !empty($data['tips']) || isset($data['extra_time'])) {
            throw new \DomainException('A distinct trusted supplement quote is required before supplement funding.');
        }
        return DB::transaction(function () use ($key, $data, $payment, $routing, $base): array {
            if ($key === 'cart_id') {
                $allocations = (new CartAllocationFactory)->prepare(Cart::findOrFail($data[$key]), $data);
            } else {
                $booking = Booking::with('children')->findOrFail($data[$key]);
                $allocations = [];
                foreach (collect([$booking])->merge($booking->children) as $target) {
                    $a = (new NativePaymentAccounting)->allocation($target);
                    $allocations[(int) $target->id] = (int) $a->id;
                }
            }
            $type = $key === 'cart_id' ? Cart::class : Booking::class;
            $config = $base->resolveGatewayConfig(['model_type' => $type,'model_id' => $data[$key]] + $routing, (int) $payment->id);
            if (!$config) {
                // Native global-payload providers use PaymentPayload rather than
                // GatewayConfig; capture only its row identity, never its payload.
                $config = PaymentPayload::query()->where('payment_id', $payment->id)->first();
            }
            $configurationId = $config?->getKey();
            $revision = $config instanceof PaymentPayload ? $config->revision_id : $config?->updated_at?->toIso8601String();
            if (!$config || !$configurationId || !$revision) {
                throw new \DomainException('Resolved original configuration identity is unavailable.');
            }
            $direct = $config instanceof ShopPayment;
            $mode = $direct ? 'vendor_direct' : 'platform';
            if ($direct !== !(bool) $routing['collect_via_platform']) {
                throw new \DomainException('Resolved credential owner disagrees with immutable routing.');
            }
            $rows = DB::table('commerce_payment_allocations')->whereIn('id', array_values($allocations))->orderBy('id')->get();
            if ($rows->pluck('checkout_key')->unique()->count() !== 1) {
                throw new \DomainException('Shared Booking funding must belong to one original checkout.');
            }
            foreach ($rows as $a) {
                (new AllocationWriter)->lock((int) $a->id);
                if ($a->finalized_at !== null || !in_array($a->state, ['committed','funding'], true)
                    || DB::table('payment_collection_contexts')->where('allocation_id', $a->id)
                        ->where('funding_slot','selected_method')->whereIn('state',['committed','pending','confirmed','review_required'])->exists()) {
                    throw new \DomainException('Original funding is terminal or unresolved; do not initiate another charge.');
                }
            }
            $weights = $rows->mapWithKeys(fn ($a) => [$a->id => (int) $a->gross_amount])->all();
            $gross = ExactMoney::sum(array_values($weights));
            if (ExactMoney::units((string) ($data['from_wallet_price'] ?? '0')) > 0) {
                foreach ($rows as $a) NativePaymentAccounting::assertWalletCurrency((int) $a->currency_id,
                    $a->payer_user_id === null ? null : (int)$a->payer_user_id);
            }
            if ($key === 'cart_id') {
                $native = (new \App\Repositories\CartRepository\CartRepository)->calculateByCartId((int) $data[$key], $data);
                if (!data_get($native,'status') || ExactMoney::units((string) data_get($native,'data.total_price')) !== $gross) {
                    throw new \DomainException('Native Cart charge and immutable Shop quotes do not reconcile.');
                }
            } else {
                $nativeGross = ExactMoney::sum(collect([$booking])->merge($booking->children)
                    ->map(fn ($b) => ExactMoney::units((string) $b->rate_total_price))->all());
                if ($nativeGross !== $gross) {
                    throw new \DomainException('Native Booking changed after original commitment.');
                }
            }
            $walletAmount = ExactMoney::units((string) ($data['from_wallet_price'] ?? '0'));
            if ($walletAmount >= $gross) {
                throw new \DomainException('Electronic checkout needs a positive remainder after Wallet.');
            }
            $walletShares = ExactMoney::partition($walletAmount, $weights);
            $event = (string) Str::uuid(); $walletEvent = (string) Str::uuid(); $history = (string) Str::uuid();
            $selected = []; $walletContexts = [];
            $wallet = $walletAmount === 0 ? null : DB::table('wallets')->where('user_id', $rows->first()->payer_user_id)->first();
            $walletMethod = $walletAmount === 0 ? null : Payment::where('tag','wallet')->first();
            if ($walletAmount > 0 && (!$wallet || !$walletMethod)) {
                throw new \DomainException('Verified original Wallet contribution is unavailable.');
            }
            foreach ($rows as $a) {
                $shared = ['currency_id' => (int) $a->currency_id,'currency_code' => $a->currency_code,'money_scale' => (int) $a->money_scale];
                if ($walletShares[$a->id] > 0) {
                    $walletContexts[] = (new AllocationWriter)->stage((int) $a->id, $shared + [
                        'funding_key' => 'wallet_contribution:'.$walletEvent, 'funding_slot' => 'wallet_contribution',
                        'funding_event_key' => $walletEvent,'collection_mode' => 'internal','custody_type' => 'platform',
                        'expected_collector_type' => 'platform','expected_collector_id' => null,
                        'credential_owner_type' => 'none','credential_owner_id' => null,
                        'payment_id' => (int) $walletMethod->id,'provider_tag' => null,
                        'wallet_id' => (int) $wallet->id,'wallet_history_reference' => $history,
                        'amount' => $walletShares[$a->id],'receipt_total_amount' => $walletAmount,
                    ]);
                }
                $remainder = (int) $a->gross_amount - $walletShares[$a->id];
                if ($remainder > 0) {
                    $selected[] = (new AllocationWriter)->stage((int) $a->id, $shared + [
                        'funding_key' => 'selected_method:'.$event, 'funding_slot' => 'selected_method','funding_event_key' => $event,
                        'collection_mode' => $mode,'custody_type' => $direct ? 'vendor' : 'platform',
                        'expected_collector_type' => $direct ? 'shop' : 'platform','expected_collector_id' => $direct ? (int) $a->shop_id : null,
                        'credential_owner_type' => $direct ? 'shop' : 'platform','credential_owner_id' => $direct ? (int) $a->shop_id : null,
                        'payment_id' => (int) $payment->id,'provider_tag' => $payment->tag,
                        'configuration_source' => $direct ? 'shop' : ($config instanceof PaymentPayload ? 'global_payload' : 'platform_country'),
                         'configuration_reference' => (string) $configurationId,'configuration_revision' => $revision,
                        'merchant_binding_reference' => null, 'amount' => $remainder, 'receipt_total_amount' => $gross - $walletAmount,
                    ]);
                }
            }
            // Persist ambiguous/in-flight state BEFORE any external request.
            (new AllocationWriter)->pending($selected);
            if ($key === 'booking_id') {
                foreach (DB::table('payment_collection_contexts')->whereIn('id',$selected)->get() as $context) {
                    $a = DB::table('commerce_payment_allocations')->find($context->allocation_id);
                    Booking::findOrFail($a->payable_id)->createTransaction([
                        'payment_sys_id' => (int) $payment->id,
                        'price' => ExactMoney::decimal((int)$context->amount,(int)$context->money_scale),
                        'status' => Transaction::STATUS_PROGRESS,'user_id' => (int)$a->payer_user_id,
                        'status_description' => 'Original Booking payment pending verification',
                    ]);
                }
            }
            return ['accounting_context_ids' => $selected,'accounting_wallet_context_ids' => $walletContexts,
                'accounting_funding_event' => $event,'accounting_expected_minor' => $gross - $walletAmount];
        }, 3);
    }

    /** Only after BaseService::matchesVerifiedIntent has accepted the proof. */
    public function complete(PaymentProcess $process, string $status, array $verification): void
    {
        $ids = $process->data['accounting_context_ids'] ?? [];
        if (!$ids) {
            throw new \DomainException('Legacy provider intent has no original accounting authority.');
        }
        $contexts = DB::table('payment_collection_contexts')->whereIn('id', $ids)->orderBy('id')->get();
        if ($contexts->count() !== count($ids)) {
            throw new \DomainException('Original provider contexts are missing.');
        }
        $attempt = !empty($process->data['generic_attempt_id']) && MerchantRevisions::installed()
            ? DB::table('electronic_collection_attempts')->find($process->data['generic_attempt_id']) : null;
        $reference = (string) ($attempt?->provider_reference ?? $process->data['payment_reference'] ?? $process->id);
        foreach ($contexts as $context) {
            if ((int) $context->payment_id !== (int) $verification['payment_id']
                || strtoupper($context->currency_code) !== strtoupper($verification['currency'])
                || (int) $context->receipt_total_amount !== (int) $verification['amount_minor']) {
                throw new \DomainException('Authenticated provider result does not match original funding.');
            }
        }
        (new AllocationWriter)->pending($ids, $reference);
        if ($status === Transaction::STATUS_PAID) {
            foreach ($contexts as $context) {
                $a = DB::table('commerce_payment_allocations')->find($context->allocation_id);
                if ($a->payable_id !== null) {
                    $type = $a->payable_type === 'booking' ? Booking::class : \App\Models\Order::class;
                    $transaction = DB::table('transactions')->where('payable_type', $type)->where('payable_id', $a->payable_id)
                        ->where('payment_sys_id', $context->payment_id)->first();
                    if ($transaction) (new AllocationWriter)->linkTransaction((int) $transaction->id,(int) $a->id,(int) $context->id);
                }
            }
            (new AllocationWriter)->confirm($ids, 'provider-receipt:'.$reference, (int) $verification['amount_minor']);
        } elseif (in_array($status, [Transaction::STATUS_REJECTED,Transaction::STATUS_CANCELED], true)) {
            foreach ($contexts as $context) (new AllocationWriter)->reject((int) $context->id, true);
            foreach ($contexts->pluck('allocation_id')->unique() as $id) {
                $a = DB::table('commerce_payment_allocations')->find($id);
                if ($a->origin_type === 'cart' && !DB::table('payment_collection_contexts')->where('allocation_id',$id)
                    ->whereNotIn('state',['rejected','canceled'])->exists()) {
                    (new AllocationWriter)->cancel((int) $id);
                }
            }
        }
    }
}