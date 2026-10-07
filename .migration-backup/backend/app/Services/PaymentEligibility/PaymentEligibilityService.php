<?php
declare(strict_types=1);

namespace App\Services\PaymentEligibility;

use App\Helpers\EnvironmentPolicy;
use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PlatformPaymentConfig;
use App\Models\Shop;
use App\Models\ShopPayment;
use App\Models\Wallet;
use App\Services\PaymentService\StripeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Shared discovery/configuration/initiation decision. All output is safe
 * metadata. Credential readiness queries return booleans, not stored values.
 */
final class PaymentEligibilityService
{
    private const READY = ['cash', 'wallet', 'mtn', 'stripe', 'paypal', 'flutter-wave', 'paystack'];

    public function decision(Payment $payment, array $context, bool $configuration = false): array
    {
        if (in_array($payment->tag, ProviderState::TAGS, true)) {
            return (new ProviderState)->decision($payment, $context, $configuration);
        }
        $tag = (string) $payment->tag;
        $internal = in_array($tag, ['cash', 'wallet'], true);
        $vendorConfigurable = in_array($tag, Payment::SHOP_CREDENTIAL_TAGS, true);
        $capabilityCurrencies = config("payment_eligibility.vendor_direct_currencies.$tag", []);
        if (config('app.env') === 'testing' ||
            (config('development.enabled') === true && in_array(config('development.payments.mode'), ['test', 'sandbox'], true))) {
            $capabilityCurrencies = array_merge($capabilityCurrencies,
                config("payment_eligibility.vendor_direct_sandbox_currencies.$tag", []));
        }
        $capabilityCurrencySupported = in_array(strtoupper((string) $context['transaction_currency']), $capabilityCurrencies, true);
        $ready = in_array($tag, self::READY, true);
        $transactionTypes = $ready ? ['booking', 'product'] : [];
        $collectionModes = $tag === 'cash' ? ['offline'] : ($tag === 'wallet' ? ['internal']
            : ($vendorConfigurable ? ['vendor_direct', 'platform'] : ['platform']));
        $environment = $internal || EnvironmentPolicy::paymentProviderEnabled($tag);
        $mode = $tag === 'cash' ? 'offline' : ($tag === 'wallet' ? 'internal' : $context['collection_mode']);
        $countryPermitted = (bool) $context['valid'];
        foreach ($context['country_ids'] as $countryId) {
            if ($internal && config('payment_eligibility.internal_country_policy', 'legacy_compatibility') === 'legacy_compatibility') {
                continue; // Explicitly documented compatibility, not an accidental hidden bypass.
            }
            $countryPermitted = $countryPermitted && DB::table('country_payments')
                ->where('country_id', $countryId)->where('payment_id', $payment->id)->where('active', true)->exists();
        }
        $configured = $internal;
        $enabled = true;
        $currencySupported = $internal ? !empty($context['transaction_currency']) : null;
        if ($vendorConfigurable && $context['valid'] && $countryPermitted && count($context['shop_ids']) === 1) {
            $query = $mode === 'platform' && !$configuration
                ? PlatformPaymentConfig::query()->where('country_id', $context['country_ids'][0])
                : ShopPayment::query()->where('shop_id', $context['shop_ids'][0]);
            $query->where('payment_id', $payment->id);
            $enabled = (clone $query)->where('status', true)->exists();
            $configured = (clone $query)->whereNotNull($tag === 'mtn' ? 'api_key' : 'merchant_key')
                ->where($tag === 'mtn' ? 'api_key' : 'merchant_key', '!=', '')
                ->when($tag === 'mtn', fn ($q) => $q->whereNotNull('subscription_key')
                    ->where('subscription_key', '!=', '')->whereNotNull('api_user')->where('api_user', '!=', '')
                    ->whereNotNull('target_environment')->where('target_environment', '!=', ''))
                ->exists();
            // Currency is non-secret routing metadata; compare in SQL.
            $currencySupported = $capabilityCurrencySupported &&
                (clone $query)->whereRaw('UPPER(currency) = ?', [strtoupper($context['transaction_currency'])])->exists();
        } elseif (!$internal && $context['valid'] && $countryPermitted) {
            $configured = $vendorConfigurable ? false : $this->globalConfigured($payment);
            $supported = config("payment_eligibility.currencies.$tag");
            $currencySupported = is_array($supported)
                ? in_array(strtoupper((string) $context['transaction_currency']), $supported, true) : null;
            if ($tag === 'stripe' && !StripeService::supportsCurrency($context['transaction_currency'])) {
                $currencySupported = false;
            }
        }
        if ($tag === 'wallet' && $context['valid'] && $countryPermitted) {
            $actorId = auth('sanctum')->id();
            $configured = $actorId && Wallet::query()->where('user_id', $actorId)
                ->where('currency_id', $context['transaction_currency_id'])->exists();
        }
        $reasons = [];
        if (!$context['valid']) $reasons[] = $context['reason'];
        if (!$payment->active) $reasons[] = 'globally_disabled';
        if (!$ready) $reasons[] = 'integration_unavailable';
        if (!$environment) $reasons[] = 'environment_disabled';
        if (!$countryPermitted) $reasons[] = 'country_policy_denied';
        if (!in_array($context['transaction_type'], $transactionTypes, true)) $reasons[] = 'transaction_type_unsupported';
        if (!in_array($mode, $collectionModes, true)) {
            $reasons[] = 'collection_mode_unsupported';
        }
        if (!$internal && count($context['shop_ids']) !== 1) $reasons[] = 'multi_shop_collection_unsupported';
        if (!$configured) $reasons[] = 'collector_not_configured';
        if (!$enabled) $reasons[] = 'collector_disabled';
        if ($currencySupported !== true) $reasons[] = $currencySupported === null ? 'currency_support_unknown' : 'currency_unsupported';
        if ($configuration && $vendorConfigurable && $context['collection_mode'] !== 'vendor_direct') {
            $reasons[] = 'vendor_direct_required';
        }
        // Registration precedes credentials; it is NOT permission to charge.
        $availableForConfiguration = $configuration && $vendorConfigurable && $context['valid']
            && $payment->active && $ready && $environment && $countryPermitted
            && $context['collection_mode'] === 'vendor_direct'
            && in_array($context['transaction_type'], $transactionTypes, true)
            && in_array($mode, $collectionModes, true)
            && count($context['shop_ids']) === 1
            && $capabilityCurrencySupported;
        return [
            'id' => (int) $payment->id, 'tag' => $tag, 'globally_active' => (bool) $payment->active,
            'integration_ready' => $ready, 'vendor_configurable' => $vendorConfigurable,
            'transaction_types' => $transactionTypes, 'collection_modes' => $collectionModes,
            'country_permitted' => $countryPermitted, 'currency_supported' => $currencySupported,
            'supported_currencies' => $internal ? ['authoritative_transaction_currency']
                : ($vendorConfigurable ? ['collector_configured_currency'] : config("payment_eligibility.currencies.$tag")),
            'collection_mode' => $mode, 'configured' => (bool) $configured,
            'eligible' => $reasons === [], 'available_for_configuration' => (bool) $availableForConfiguration,
            'reasons' => array_values(array_unique($reasons)),
        ];
    }

    public function methods(array $context): Collection
    {
        return Payment::query()->get()->filter(fn (Payment $payment) => $this->decision($payment, $context)['eligible'])->values();
    }

    public function vendorPolicy(Shop $shop, ?int $locationType = null): array
    {
        $context = (new PaymentContextFactory)->shop($shop, $locationType);
        return ['context' => $context, 'collection' => $this->collectionPolicy($shop, $locationType),
            'methods' => Payment::query()->get()
            ->map(function (Payment $payment) use ($context) {
                $decision = $this->decision($payment, $context);
                $directContext = array_merge($context, ['collection_mode' => 'vendor_direct']);
                $decision['available_for_configuration'] = $this->decision($payment, $directContext, true)['available_for_configuration'];
                $direct = $this->decision($payment, $directContext);
                $decision['vendor_direct_ready'] = $direct['runtime_ready'] ?? $direct['eligible'];
                $decision['vendor_direct'] = $direct;
                return $decision;
            })->values()->all()];
    }

    /** Choice capability is not charge readiness and never activates a rail. */
    public function collectionPolicy(Shop $shop, ?int $locationType = null): array
    {
        $factory = new PaymentContextFactory;
        $contexts = $locationType === null
            ? $shop->locations()->whereIn('type', [1, 2])->distinct()->pluck('type')
                ->map(fn ($type) => $factory->shop($shop, (int) $type))->values()
            : collect([$factory->shop($shop, $locationType)]);
        $currencies = $contexts->filter(fn ($context) => $context['valid'])
            ->pluck('transaction_currency')->unique();
        $currencyConflict = $currencies->count() > 1;
        $available = $contexts->isNotEmpty();
        $ready = $available;
        $directMethods = [];
        foreach ($contexts as $context) {
            $context['collection_mode'] = 'vendor_direct';
            $methods = Payment::query()->get()->filter(fn (Payment $payment) =>
                $this->decision($payment, $context, true)['available_for_configuration']
                // The native MTN merchant row has one exact charge currency;
                // a shared toggle cannot promise readiness in two currencies.
                && !($currencyConflict && in_array($payment->tag, ['mtn', 'orange'], true)));
            $available = $available && $context['valid'] && $methods->isNotEmpty();
            $ready = $ready && $methods->contains(function (Payment $payment) use ($context) {
                $decision = $this->decision($payment, $context);
                return $decision['runtime_ready'] ?? $decision['eligible'];
            });
            $directMethods = array_merge($directMethods, $methods->pluck('id')->map(fn ($id) => (int) $id)->all());
        }
        return [
            'mode' => $shop->collect_via_platform ? 'platform' : 'vendor_direct',
            'scope' => 'shop',
            'applies_to' => ['product', 'booking'],
            'default_mode' => 'platform',
            'platform_available' => true,
            'vendor_direct_available' => $available,
            'vendor_direct_state' => !$available ? 'unavailable' : ($ready ? 'ready' : 'setup_required'),
            'vendor_direct_unavailable_reason' => $available ? null : ($currencyConflict
                ? 'shop_wide_merchant_currency_conflict' : 'no_supported_gateway_for_every_business_context'),
            'vendor_direct_method_ids' => array_values(array_unique($directMethods)),
            'can_manage' => (bool) auth('sanctum')->user()?->hasShopPermission((int) $shop->id, 'payments.gateways.manage'),
            'payout_methods_implemented' => false,
        ];
    }

    public function assertEligible(int $paymentId, array $context): void
    {
        $payment = Payment::query()->find($paymentId);
        $decision = $payment ? $this->decision($payment, $context) : null;
        if (!$decision || !$decision['eligible']) {
            if ($decision && in_array('country_policy_denied', $decision['reasons'], true)) {
                throw new RuntimeException('Payment method is unavailable in this checkout country');
            }
            throw new RuntimeException('Payment method unavailable: ' . implode(', ', $decision['reasons'] ?? ['payment_not_found']));
        }
    }

    /** Guard the native internal-method cart checkout before order/wallet writes. */
    public function assertCartCheckout(array $input): void
    {
        $context = (new PaymentContextFactory)->target('cart_id', (int) $input['cart_id'],
            isset($input['currency_id']) ? (int) $input['currency_id'] : null);
        if (isset($input['payment_id'])) {
            $this->assertEligible((int) $input['payment_id'], $context);
        }
        if ((float) ($input['from_wallet_price'] ?? 0) > 0) {
            $wallet = Payment::query()->where('tag', 'wallet')->first();
            if (!$wallet) throw new RuntimeException('Wallet payment method is unavailable');
            $this->assertEligible((int) $wallet->id, $context);
        }
    }

    public function catalogReady(Payment $payment): bool
    {
        if (in_array($payment->tag, ProviderState::TAGS, true) && config('app.env') !== 'testing') return false;
        return $payment->active && in_array($payment->tag, self::READY, true)
            && (in_array($payment->tag, ['cash', 'wallet'], true)
                || EnvironmentPolicy::paymentProviderEnabled($payment->tag));
    }

    private function globalConfigured(Payment $payment): bool
    {
        $keys = match ($payment->tag) {
            'stripe' => ['stripe_sk', 'stripe_webhook_secret'],
            'flutter-wave' => ['flw_sk', 'flw_webhook_secret_hash', 'flw_account_id'],
            'paystack' => ['paystack_sk'],
            default => [],
        };
        if ($payment->tag === 'paypal') {
            foreach (['sandbox', 'live'] as $mode) {
                $query = PaymentPayload::query()->where('payment_id', $payment->id)->where('payload->paypal_mode', $mode);
                foreach (["paypal_{$mode}_client_id", "paypal_{$mode}_client_secret"] as $key) {
                    $query->whereNotNull("payload->$key")->where("payload->$key", '!=', '');
                }
                foreach (['merchant_id', 'webhook_id'] as $key) {
                    $query->where(function ($q) use ($mode, $key) {
                        $q->where(function ($q) use ($mode, $key) {
                            $q->whereNotNull("payload->paypal_{$mode}_$key")->where("payload->paypal_{$mode}_$key", '!=', '');
                        })->orWhere(function ($q) use ($key) {
                            $q->whereNotNull("payload->paypal_$key")->where("payload->paypal_$key", '!=', '');
                        });
                    });
                }
                if ($query->exists()) return true;
            }
            return false;
        }
        if (!$keys) return false;
        $query = PaymentPayload::query()->where('payment_id', $payment->id);
        foreach ($keys as $key) $query->whereNotNull("payload->$key")->where("payload->$key", '!=', '');
        return $query->exists();
    }
}