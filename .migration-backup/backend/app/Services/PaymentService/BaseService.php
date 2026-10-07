<?php
declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Helpers\GetShop;
use App\Helpers\ResponseError;
use App\Models\AdsPackage;
use App\Models\Booking;
use App\Models\BookingExtraTime;
use App\Models\AuctionUser;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Country;
use App\Models\Currency;
use App\Models\GiftCart;
use App\Models\MemberShip;
use App\Models\Order;
use App\Models\ParcelOrder;
use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Payout;
use App\Models\PlatformPaymentConfig;
use App\Models\Shop;
use App\Models\ShopAdsPackage;
use App\Models\ShopLocation;
use App\Models\ShopPayment;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\UserGiftCart;
use App\Models\UserMemberShip;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Repositories\CartRepository\CartRepository;
use App\Services\CoreService;
use App\Services\PaymentService\Contracts\GatewayConfig;
use App\Services\OrderService\OrderService;
use App\Services\TransactionService\TransactionService;
use App\Services\UserServices\UserWalletService;
use App\Services\WalletHistoryService\WalletHistoryService;
use App\Traits\Notification;
use App\Services\PaymentService\Verification\DecimalAmount;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Log;
use Throwable;

class BaseService extends CoreService
{
    use Notification;

    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param $token
     * @param $status
     * @param string|null $secondToken
     * @return array
     */
    public function afterHook($token, $status, ?string $secondToken = null, ?array $verification = null): array
    {
        try {
            return DB::transaction(function () use ($token, $status, $secondToken, $verification): array {
            $paymentProcess = PaymentProcess::with(['model', 'user'])->lockForUpdate()->find($token);

            if (empty($paymentProcess)) {
                $paymentProcess = PaymentProcess::with(['model', 'user'])->lockForUpdate()->find($secondToken);
            }
            if (!$paymentProcess && \App\Services\PaymentAccounting\MerchantRevisions::installed()) {
                $paymentProcess = \App\Services\PaymentAccounting\DurableCollections::process((string)$token)
                    ?? \App\Services\PaymentAccounting\DurableCollections::process((string)$secondToken);
            }

            if (empty($paymentProcess)) {
                return [
                    'status'  => false,
                    'message' => 'Payment intent not found',
                ];
            }

            /** @var PaymentProcess $paymentProcess */
            // Only a verified provider result (or an authenticated provider
            // status lookup) may change a payment state. Never use callback
            // fields supplied by the browser as proof of settlement.
            if (!in_array($status, [Transaction::STATUS_PAID, Transaction::STATUS_CANCELED, Transaction::STATUS_REJECTED, Transaction::STATUS_PROGRESS], true)) {
                return ['status' => false, 'message' => 'Unsupported payment status'];
            }

            if (!$this->matchesVerifiedIntent($paymentProcess, $status, $verification)) {
                Log::warning('Rejected unverified or mismatched payment settlement', [
                    'payment_process_id' => $paymentProcess->id,
                    'payment_id' => data_get($paymentProcess->data, 'payment_id'),
                ]);
                return [
                    'status'  => false,
                    'message' => 'Payment verification failed',
                ];
            }

            $oldStatus = data_get($paymentProcess->data, 'status');
            if ($oldStatus === $status || ($oldStatus === Transaction::STATUS_PAID && $status !== Transaction::STATUS_PAID)) {
                return ['status' => true, 'message' => 'already settled'];
            }
            if ($status === Transaction::STATUS_PAID && $this->hasPaidAnotherIntentForPayable($paymentProcess)) {
                return ['status' => false, 'message' => 'This payment target has already been settled'];
            }
            if (in_array($paymentProcess->model_type, [Cart::class, Booking::class], true)
                && \App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
                (new \App\Services\PaymentAccounting\ProviderContributionAdapter)->complete($paymentProcess, $status, $verification);
                (new \App\Services\PaymentAccounting\DurableCollections)->finalized($paymentProcess, $status, $verification);
            }

            $paymentId = $paymentProcess->data['payment_id'];
            $data = $paymentProcess->data;
            $data['status'] = $status;

            $paymentProcess->update([
                'data' => $data
            ]);

            $paymentProcess->loadMissing([
                'model.transaction' => fn($q) => $q->where('payment_sys_id', $paymentId)
            ]);

            if ($paymentProcess->model_type === GiftCart::class && $status === Transaction::STATUS_PAID) {

                /** @var GiftCart $model */
                $model = $paymentProcess->model;

                // A distinct payment-process reference is a distinct
                // purchase, even when the same user buys this product again
                // within seconds. Replays are stopped by the terminal
                // payment-process status check above.
                $userGiftCart = UserGiftCart::create([
                    'gift_cart_id'   => $paymentProcess->model_id,
                    'user_id'        => $paymentProcess->user_id,
                    'price'          => $model->price,
                    'expired_at'     => date('Y-m-d H:i:s', strtotime("+$model->time")),
                ]);

                $userGiftCart->createTransaction([
                    'price'          => $model->price,
                    'payment_sys_id' => data_get($paymentProcess->data, 'payment_id'),
                    'user_id'        => $paymentProcess->user_id,
                    'payment_trx_id' => $token,
                    'status'         => $status,
                ]);

                return [
                    'status'  => true,
                    'message' => 'gift success',
                ];
            }

            if ($paymentProcess->model_type === MemberShip::class && $status === Transaction::STATUS_PAID) {

                /** @var MemberShip $model */
                $model = $paymentProcess->model;

                // As above, preserve separate purchases for separate
                // verified references; same-reference retries return before
                // reaching this branch.
                $userMemberShip = UserMemberShip::create([
                    'member_ship_id' => $paymentProcess->model_id,
                    'user_id'        => $paymentProcess->user_id,
                    'color'          => $model->color,
                    'price'          => $model->price,
                    'expired_at'     => date('Y-m-d H:i:s', strtotime("+$model->time")),
                    'sessions'       => $model->sessions,
                    'sessions_count' => $model->sessions_count,
                    'remainder'      => $model->sessions_count,
                ]);

                $userMemberShip->createTransaction([
                    'price'          => $model->price,
                    'payment_sys_id' => data_get($paymentProcess->data, 'payment_id'),
                    'user_id'        => $paymentProcess->user_id,
                    'payment_trx_id' => $token,
                    'status'         => $status,
                ]);

                return [
                    'status'  => true,
                    'message' => 'membership success',
                ];
            }

            if ($paymentProcess->model_type === AuctionUser::class) {

                $paymentProcess->model->createTransaction([
                    'price'          => $paymentProcess->model?->price ?? 1,
                    'payment_sys_id' => data_get($paymentProcess->data, 'payment_id'),
                    'user_id'        => $paymentProcess->user_id,
                    'payment_trx_id' => $token,
                    'status'         => $status,
                ]);

                if ($status === Transaction::STATUS_PAID) {
                    $paymentProcess->model->update([
                        'status' => AuctionUser::DEPOSITED
                    ]);
                }

                return [
                    'status'  => true,
                    'message' => 'auction success',
                ];
            }

            if ($paymentProcess->model_type === Wallet::class && $status === Transaction::STATUS_PAID) {

                $totalPrice = (double)data_get($paymentProcess->data, 'total_price') / 100;

                $user = $paymentProcess->user;

                (new WalletHistoryService)->create([
                    'type'           => 'topup',
                    'payment_sys_id' => data_get($paymentProcess->data, 'payment_id'),
                    'created_by'     => data_get($paymentProcess->data, 'created_by'),
                    'payment_trx_id' => $token,
                    'price'          => $totalPrice,
                    'note'           => __('errors.' . ResponseError::WALLET_TOP_UP, ['sender' => ''], $user?->lang ?? $this->language),
                    'status'         => WalletHistory::PAID,
                    'user'           => $user
                ]);

                return [
                    'status'  => true,
                    'message' => 'wallet success',
                ];
            }

            if ($paymentProcess->model_type !== Booking::class) {
                $paymentProcess->model?->transaction?->update(['payment_trx_id' => $token, 'status' => $status]);
            }

            if ($paymentProcess->model_type === ShopAdsPackage::class) {

                $time = $paymentProcess->model?->adsPackage?->time ?? 1;
                $type = $paymentProcess->model?->adsPackage?->time_type ?? 'day';

                $paymentProcess->model->createTransaction([
                    'price'          => $paymentProcess->model?->adsPackage?->price ?? 1,
                    'payment_sys_id' => data_get($paymentProcess->data, 'payment_id'),
                    'user_id'        => $paymentProcess->user_id,
                    'payment_trx_id' => $token,
                    'status'         => $status,
                ]);

                if ($status === Transaction::STATUS_PAID) {
                    $paymentProcess->model->update([
                        'active'     => true,
                        'expired_at' => date('Y-m-d H:i:s', strtotime("+$time $type"))
                    ]);
                }

                return [
                    'status'  => true,
                    'message' => 'success',
                ];
            }

            if ($paymentProcess->model_type === Cart::class) {

                $paymentProcess->update([
                    'data' => array_merge($paymentProcess->data, ['trx_status' => $status])
                ]);

                $orders = [
                    'data' => null
                ];

                if ($status === Transaction::STATUS_PAID) {
                    $orders = (new OrderService)->create($paymentProcess->data);
                    if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()
                        && !data_get($orders, 'status')) {
                        throw new \DomainException('Verified Product finalization failed; original intent remains safely retryable.');
                    }
                }

                if (isset($paymentProcess->data['cart_id'])) {

                    $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin') )
                        ->whereNotNull('firebase_token')
                        ->select(['id', 'lang', 'firebase_token'])
                        ->get();

                    Order::with([
                        'transaction' => fn($q) => $q->where('payment_sys_id', $paymentId),
                        'shop'
                    ])
                        ->where('cart_id', $paymentProcess->data['cart_id'])
                        ->get()
                        ->map(function (Order $order) use ($admins) {

                            try {
                                $this->sendUsers($order, $admins);

                                if ($order->shop?->user_id) {
                                    $seller = User::select(['firebase_token', 'id', 'lang'])->find($order->shop->user_id);
                                    $this->sendUsers($order, [$seller]);
                                }

                            } catch (Throwable $e) {
                                $this->error($e);
                            }

                            try {

                                if (!\App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
                                    $order->transaction?->update(['price' => $order->total_price]);
                                }

                            } catch (Throwable $e) {
                                $this->error($e);
                            }

                        });
                }

                $orderId = collect($orders['data'] ?? null)->first()?->id;

                if ($orderId && !\App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
                    $paymentProcess->model->transactions()->update([
                        'payable_id'   => $orderId,
                        'payable_type' => Order::class,
                    ]);
                }

            }

            if ($paymentProcess->model_type === Booking::class && !isset($paymentProcess->data['tips']) && !isset($paymentProcess->data['extra_time'])) {

                $paymentProcess->update([
                    'data' => array_merge($paymentProcess->data, ['trx_status' => $status])
                ]);

                $model = $paymentProcess->model->load([
                    'children:id,parent_id',
                    'children.transaction' => fn($q) => $q->where('payment_sys_id', $paymentId)
                ]);

                $model->transaction?->update([
                    'payment_trx_id' => $token,
                    'payment_sys_id' => $paymentId,
                    'status'         => $status
                ]);

                foreach ($model->children as $child) {
                    $child->transaction?->update([
                        'payment_trx_id' => $token,
                        'payment_sys_id' => $paymentId,
                        'status'         => $status
                    ]);
                }

            } elseif ($paymentProcess->model_type === Booking::class && isset($paymentProcess->data['tips'])) {

                $paymentProcess->update([
                    'data' => array_merge($paymentProcess->data, ['tips_trx_status' => $status])
                ]);

                $paymentProcess->model?->update(['tips' => $paymentProcess->data['tips']]);

            } elseif ($paymentProcess->model_type === Booking::class && isset($paymentProcess->data['extra_time'])) {

                $paymentProcess->update([
                    'data' => array_merge($paymentProcess->data, ['extra_time_trx_status' => $status])
                ]);

                /** @var Booking $booking */
                $booking = $paymentProcess->model;

                /** @var BookingExtraTime $extraTime */
                $extraTime = $booking->extraTimes()->create([
                    'price'         => $paymentProcess->data['total_price'],
                    'duration'      => $paymentProcess->data['duration'],
                    'duration_type' => $paymentProcess->data['duration_type'],
                ]);

                $booking->update([
                    'extra_time_price' => $extraTime->price,
                    'total_price'      => $booking->total_price + $extraTime->price,
                ]);

                $booking->transaction?->update([
                    'price'          => $booking->transaction?->price + $extraTime->price,
                    'payment_trx_id' => $token,
                    'status'         => $status,
                ]);

                $unit = "+$extraTime->duration $extraTime->duration_type";

                $booking->update([
                    'end_date' => date('Y-m-d H:i:s', strtotime("$booking->end_date $unit"))
                ]);

            }

            if ($paymentProcess->model_type === ParcelOrder::class) {

                $transaction = $paymentProcess->model?->transaction
                    ?->where('payment_sys_id',$paymentId)
                    ?->where('status',Transaction::STATUS_PAID)
                    ->first();

                if ($transaction) {
                    $transaction->update([
                        'status' => Transaction::STATUS_REFUND
                    ]);
                }

                (new TransactionService)->orderTransaction($paymentProcess->model_id, [
                    'payment_sys_id' => data_get($paymentProcess->data, 'payment_id'),
                    'payment_trx_id' => $paymentProcess->id,
                ], ParcelOrder::class);

            }

            return [
                'status'  => true,
                'message' => 'success',
            ];
            });
        } catch (Throwable $e) {
            Log::error('Payment settlement failed', [
                'exception' => get_class($e), 'source' => basename($e->getFile()), 'line' => $e->getLine(),
            ]);
            return [
                'status'  => false,
                'message' => 'Payment settlement failed',
            ];
        }
    }

    /**
     * Validate a normalized provider verification response against the
     * immutable local checkout intent. Provider adapters must authenticate
     * their event and normalize its authoritative amount/currency/reference
     * before calling afterHook().
     */
    private function matchesVerifiedIntent(PaymentProcess $process, string $status, ?array $verification): bool
    {
        if (empty($verification) || ($verification['authenticated'] ?? null) !== true) {
            return false;
        }
        if (($verification['merchant_verified'] ?? null) !== true) {
            return false;
        }

        $intent = $process->data ?? [];
        $generic = !empty($intent['generic_attempt_id']) && \App\Services\PaymentAccounting\MerchantRevisions::installed()
            ? DB::table('electronic_collection_attempts')->find($intent['generic_attempt_id']) : null;
        if ((string) data_get($verification, 'reference') !== (string) $process->id
            && (string) data_get($verification, 'reference') !== (string) data_get($intent, 'payment_reference')
            && (!$generic || !in_array((string)data_get($verification,'reference'),
                array_filter([$generic->provider_reference,$generic->provider_payment_id]),true))) {
            return false;
        }

        $expectedPaymentId = $this->positiveInteger(data_get($intent, 'payment_id'));
        $actualPaymentId = $this->positiveInteger(data_get($verification, 'payment_id'));
        $expectedModelId = $this->positiveInteger(data_get($intent, 'model_id'));
        $actualModelId = $this->positiveInteger(data_get($verification, 'model_id'));
        $modelType = (string) data_get($intent, 'model_type', '');
        if ($expectedPaymentId === null || $actualPaymentId !== $expectedPaymentId
            || $expectedModelId === null || $actualModelId !== $expectedModelId
            || $modelType === ''
            || (string) data_get($verification, 'model_type') !== $modelType) {
            return false;
        }

        if (!in_array($status, [Transaction::STATUS_PAID, Transaction::STATUS_CANCELED, Transaction::STATUS_REJECTED, Transaction::STATUS_PROGRESS], true)) {
            return false;
        }

        $expectedAmount = $this->positiveInteger(data_get($intent, 'total_price'));
        $actualAmount = data_get($verification, 'amount_minor');
        if ($expectedAmount === null || !is_numeric($actualAmount) || !is_finite((float) $actualAmount)
            || (float) $actualAmount !== (float) (int) $actualAmount || (int) $actualAmount !== $expectedAmount) {
            return false;
        }

        $expectedCurrency = strtoupper((string) data_get($intent, 'currency', ''));
        if ($expectedCurrency === '' || strtoupper((string) data_get($verification, 'currency', '')) !== $expectedCurrency) {
            return false;
        }

        return true;
    }

    private function positiveInteger(mixed $value): ?int
    {
        if (!is_numeric($value) || !is_finite((float) $value)) {
            return null;
        }

        $integer = (int) $value;

        return $integer > 0 && (float) $value === (float) $integer ? $integer : null;
    }

    /**
     * Different provider references for the same payable must not grant or
     * debit it twice. Lock the payable row as the serialization point, then
     * consult both settled intents and settled transactions. Wallet top-ups
     * are intentionally per-intent and are excluded.
     */
    private function hasPaidAnotherIntentForPayable(PaymentProcess $process): bool
    {
        if ($process->model_type === Wallet::class) {
            return false;
        }

        $intent = $process->data ?? [];
        if ($process->model_type === Booking::class
            && (isset($intent['tips']) || isset($intent['extra_time']))) {
            return false;
        }

        $modelType = $process->model_type;
        if (!is_string($modelType) || !class_exists($modelType) || !is_subclass_of($modelType, Model::class)) {
            return true;
        }

        $payable = $modelType::query()->whereKey($process->model_id)->lockForUpdate()->first();
        if (!$payable) {
            return true;
        }

        // A catalogue row describes the product, not a unique purchase
        // obligation. Each independently verified checkout reference may
        // purchase the same gift card or membership again; afterHook's
        // terminal status check already makes replays of one reference
        // idempotent.
        if (in_array($process->model_type, [GiftCart::class, MemberShip::class], true)) {
            return false;
        }

        $otherIntents = PaymentProcess::query()
            ->where('model_type', $process->model_type)
            ->where('model_id', $process->model_id)
            ->where('id', '!=', $process->id);

        $alreadyPaid = $otherIntents->get(['data'])
            ->contains(fn (PaymentProcess $other) => data_get($other->data, 'status') === Transaction::STATUS_PAID);

        $paidTransactions = Transaction::query()
            ->where('payable_type', $process->model_type)
            ->where('payable_id', $process->model_id)
            ->where('status', Transaction::STATUS_PAID)
            ->get();
        $walletPaymentId = $paidTransactions->isEmpty()
            ? null
            : Payment::query()->where('tag', 'wallet')->value('id');
        $alreadySettled = $paidTransactions->contains(
            fn (Transaction $transaction) => !$this->isPartialWalletContribution($transaction, $process, $walletPaymentId)
        );

        if ($alreadyPaid || $alreadySettled) {
            return true;
        }

        if ($process->model_type === Cart::class) {
            return Order::query()
                ->where('cart_id', $process->model_id)
                ->whereHas('transaction', fn ($query) => $query->where('status', Transaction::STATUS_PAID))
                ->exists();
        }

        return false;
    }

    private function isPartialWalletContribution(Transaction $transaction, PaymentProcess $process, mixed $walletPaymentId): bool
    {
        if (!$walletPaymentId || (int) $transaction->payment_sys_id !== (int) $walletPaymentId) {
            return false;
        }

        // walletPriceWithdraw records a PAID wallet-ledger transaction
        // immediately, even when a provider still has a positive remainder
        // to collect. Ignore only that exact contribution, verified against
        // both the frozen process data and the ledger amount. A fully
        // wallet-funded checkout (zero external amount) is not a partial
        // contribution and continues to block another settlement.
        $externalAmount = data_get($process->data, 'total_price');
        if (!is_int($externalAmount) && !(is_string($externalAmount) && preg_match('/^[1-9][0-9]*$/D', $externalAmount))) {
            return false;
        }
        if ((int) $externalAmount <= 0) {
            return false;
        }

        $frozenWalletAmount = DecimalAmount::minor(data_get($process->data, 'from_wallet_price'));
        $ledgerWalletAmount = DecimalAmount::minor($transaction->price);

        return $frozenWalletAmount !== null
            && $frozenWalletAmount > 0
            && $frozenWalletAmount === $ledgerWalletAmount;
    }

    /**
     * @param array $data
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function getPayload(array $data, array $payload, ?int $paymentId = null): array
    {
        foreach (array_keys($data) as $inputKey) {
            if (str_starts_with($inputKey, 'accounting_') || str_starts_with($inputKey, '_accounting_')) {
                unset($data[$inputKey]);
            }
        }
        $targetKeys = [
            'cart_id', 'booking_id', 'member_ship_id', 'parcel_id',
            'subscription_id', 'ads_package_id', 'wallet_id',
            'gift_cart_id', 'auction_id',
        ];
        $targets = array_values(array_filter(
            $targetKeys,
            fn (string $key) => isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null
        ));

        if (count($targets) !== 1) {
            throw new Exception('Exactly one payment target is required');
        }

        $key = $targets[0];
        $this->authorizePaymentTarget($key, (int) $data[$key]);
        if ($paymentId !== null) {
            if (in_array($key, ['booking_id', 'cart_id'], true)) {
                $context = (new \App\Services\PaymentEligibility\PaymentContextFactory)
                    ->target($key, (int) $data[$key], isset($data['currency_id']) ? (int) $data['currency_id'] : null);
                (new \App\Services\PaymentEligibility\PaymentEligibilityService)->assertEligible($paymentId, $context);
            } else {
                // Legacy platform-level targets stay separate from business checkout.
                $this->assertPaymentEnabledForTargetCountry($paymentId, $key, (int) $data[$key]);
            }
        }

        // Gateway/merchant routing must fail before beforeBooking/beforeCart
        // can debit a wallet contribution as part of building the intent.
        $routing = $this->freezeGatewayRouting([], $key, (int) $data[$key], $paymentId);
        $accounting = [];
        if (in_array($key, ['booking_id','cart_id'], true) && $paymentId !== null
            && \App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
            $method = Payment::findOrFail($paymentId);
            $accounting = (new \App\Services\PaymentAccounting\ProviderContributionAdapter)
                ->prepare($key, $data, $method, $routing, $this);
        }

        $before = match ($key) {
            'cart_id'        => $this->beforeCart($data, $payload),
            'booking_id'     => $this->beforeBooking($data, $payload),
            'member_ship_id' => $this->beforeMemberShip($data, $payload),
            'parcel_id'      => $this->beforeParcel($data, $payload),
            'subscription_id'=> $this->beforeSubscription($data),
            'ads_package_id' => $this->beforePackage($data, $payload),
            'wallet_id'      => $this->beforeWallet($data, $payload),
            'gift_cart_id'   => $this->beforeGiftCart($data, $payload),
            'auction_id'     => $this->beforeAuction($data, $payload),
        };
        $before = array_merge($before, $routing, $accounting);
        if ($accounting && (int) $before['total_price'] !== $accounting['accounting_expected_minor']) {
            throw new \DomainException('Native provider charge does not match frozen Shop economic quotes.');
        }

        return [$key, $before];
    }

    /**
     * Freeze the checkout's collection mode into the local provider intent.
     * Global credentials can only collect for platform mode. MTN/Orange have
     * explicit shop credentials and are routed by resolveGatewayConfig().
     *
     * @throws Exception
     */
    private function freezeGatewayRouting(array $before, string $key, int $id, ?int $paymentId): array
    {
        if (!in_array($key, ['booking_id', 'cart_id'], true)) {
            return $before;
        }

        if ($key === 'booking_id') {
            $collectViaPlatform = (bool) Booking::query()->whereKey($id)->value('collect_via_platform');
        } else {
            $shopId = $this->resolveGatewayShopId(Cart::class, $id);
            $collectViaPlatform = (bool) Shop::query()->whereKey($shopId)->value('collect_via_platform');
        }

        $before['collect_via_platform'] = $collectViaPlatform;
        $before['checkout_collection_mode'] = $collectViaPlatform ? 'platform' : 'shop';

        if ($paymentId === null) {
            return $before;
        }

        $payment = Payment::query()->find($paymentId);
        if (!$payment) {
            throw new Exception('Payment method is unavailable');
        }

        if (in_array($payment->tag, Payment::SHOP_CREDENTIAL_TAGS, true)) {
            return $before;
        }

        if (!$collectViaPlatform) {
            throw new Exception('This payment method cannot collect directly for this shop');
        }

        return $before;
    }

    /**
     * Provider initiation is a money-moving boundary. Recheck the actor
     * against the selected payable here (not only in request validation),
     * so direct service calls and stale/forged API payloads cannot use
     * another customer's or tenant's identifier.
     *
     * @throws Exception
     */
    protected function authorizePaymentTarget(string $key, int $id): void
    {
        $actorId = auth('sanctum')->id();
        if (!$actorId || $id <= 0) {
            throw new Exception('Authenticated payment owner is required');
        }

        $owned = match ($key) {
            'booking_id' => Booking::query()->whereKey($id)->where('user_id', $actorId)->exists(),
            'cart_id' => Cart::query()->whereKey($id)->where('owner_id', $actorId)->exists(),
            'parcel_id' => ParcelOrder::query()->whereKey($id)->where('user_id', $actorId)->exists(),
            'wallet_id' => Wallet::query()->whereKey($id)->where('user_id', $actorId)->exists(),
            'auction_id' => AuctionUser::query()->whereKey($id)->where('user_id', $actorId)->exists(),
            'member_ship_id' => MemberShip::query()->whereKey($id)->where('active', true)->exists(),
            'gift_cart_id' => GiftCart::query()->whereKey($id)->where('active', true)->exists(),
            'subscription_id' => Subscription::query()->whereKey($id)->where('active', true)->exists()
                && $this->actorOwnsCurrentShop((int) GetShop::shop()?->id, (int) $actorId),
            'ads_package_id' => AdsPackage::query()->whereKey($id)->where('active', true)->exists()
                && $this->actorOwnsCurrentShop((int) GetShop::shop()?->id, (int) $actorId),
            default => false,
        };

        if (!$owned) {
            throw new Exception('Payment target is unavailable to this account');
        }
    }

    private function actorOwnsCurrentShop(int $shopId, int $actorId): bool
    {
        return $shopId > 0 && Shop::query()
            ->whereKey($shopId)
            ->where('user_id', $actorId)
            ->exists();
    }

    /**
     * Enforce the country allowlist at the final payment-initiation
     * boundary. Discovery filtering is not an authorization control: a
     * stale app or direct API call must not use a disabled country gateway.
     *
     * Some legacy payable types do not persist an unambiguous country. For
     * those, a country-scoped provider is rejected rather than guessed.
     *
     * @throws Exception
     */
    private function assertPaymentEnabledForTargetCountry(int $paymentId, string $key, int $id): void
    {
        $payment = Payment::query()->find($paymentId);
        if (!$payment || !$payment->active) {
            throw new Exception('Payment method is unavailable');
        }

        $countryId = match ($key) {
            'booking_id' => Booking::query()->with('shop')->find($id)?->shop
                ?->checkoutCountry(ShopLocation::SERVICE)?->id,
            'cart_id' => Cart::query()->find($id)?->country_id,
            'member_ship_id' => MemberShip::query()->with('shop')->find($id)?->shop
                ?->checkoutCountry(ShopLocation::SERVICE)?->id,
            'gift_cart_id' => GiftCart::query()->with('shop')->find($id)?->shop
                ?->checkoutCountry(ShopLocation::PRODUCT)?->id,
            'subscription_id' => GetShop::shop()
                ?->checkoutCountry(ShopLocation::PRODUCT)?->id,
            'ads_package_id' => GetShop::shop()
                ?->checkoutCountry(ShopLocation::PRODUCT)?->id,
            'parcel_id' => data_get(ParcelOrder::query()->find($id)?->address_to, 'country_id')
                ?? data_get(ParcelOrder::query()->find($id)?->address_from, 'country_id'),
            'wallet_id' => $this->countryIdForCurrency((int) Wallet::query()->find($id)?->currency_id),
            'auction_id' => $this->countryIdForCurrency((int) User::query()->find(auth('sanctum')->id())?->currency_id),
            default => null,
        };

        // A cart's country is the checkout country selected by its owner.
        // If old carts lack that snapshot, a single-shop cart has a safe
        // shop-country fallback; mixed-shop carts are intentionally not
        // guessed.
        if (!$countryId && $key === 'cart_id') {
            try {
                $shopId = $this->resolveGatewayShopId(Cart::class, $id);
                $shop = Shop::query()->find($shopId);
                $countryId = ($shop?->checkoutCountry(ShopLocation::PRODUCT)
                    ?? $shop?->checkoutCountry(ShopLocation::SERVICE))?->id;
            } catch (Throwable) {
                $countryId = null;
            }
        }

        if (!$countryId && in_array($key, ['member_ship_id', 'gift_cart_id'], true)) {
            $shopId = $key === 'member_ship_id'
                ? MemberShip::query()->whereKey($id)->value('shop_id')
                : GiftCart::query()->whereKey($id)->value('shop_id');
            $shop = Shop::query()->find($shopId);
            $countryId = ($shop?->checkoutCountry(ShopLocation::SERVICE)
                ?? $shop?->checkoutCountry(ShopLocation::PRODUCT))?->id;
        }

        if (!$countryId) {
            // Internal cash/wallet handling is always available; all
            // external providers must have a country context to enforce
            // their allowlist.
            if (in_array($payment->tag, [Payment::TAG_CASH, Payment::TAG_WALLET], true)) {
                return;
            }

            throw new Exception('Unable to verify gateway availability for this checkout country');
        }

        $country = Country::query()->find($countryId);
        if (!$country || !$country->activePaymentIds()->contains($paymentId)) {
            throw new Exception('Payment method is unavailable in this checkout country');
        }
    }

    private function countryIdForCurrency(int $currencyId): ?int
    {
        if ($currencyId <= 0) {
            return null;
        }

        $countries = Country::query()->where('currency_id', $currencyId)->pluck('id');

        return $countries->count() === 1 ? (int) $countries->first() : null;
    }

    /**
     * Gateways that keep their credentials in a single global PaymentPayload
     * row (Stripe, Flutterwave, PayStack, ZainCash - unlike MTN/Orange,
     * which resolve a per-shop/per-platform GatewayConfig via
     * resolveGatewayConfig()) need the same "not configured yet" check
     * those two already do, rather than either a raw TypeError from
     * getPayload()'s non-nullable array param (when $payload is null) or
     * an undefined-array-key crash further down (when it's an empty array).
     *
     * @throws Exception
     */
    public function requireConfiguredPayload(?array $payload, string $gatewayLabel): array
    {
        if (empty($payload)) {
            throw new Exception("$gatewayLabel has not been configured for this transaction yet");
        }

        return $payload;
    }

    /**
     * Convert a major-unit domain amount to the two-decimal minor-unit
     * intent used throughout the existing transaction/API contract. This
     * deliberately does not ceil whole major units: supported gateways
     * must receive the exact amount represented by the booking/order.
     *
     * @throws Exception
     */
    private function toMinorUnits(mixed $amount): int
    {
        if (!is_numeric($amount) || !is_finite((float) $amount) || (float) $amount <= 0) {
            throw new Exception('Payment amount is invalid');
        }

        return (int) round((float) $amount * 100, 0, PHP_ROUND_HALF_UP);
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     * @throws Exception
     */
    public function beforeCart(array $data, array|null $payload): array
    {
        $cart       = Cart::find(data_get($data, 'cart_id'));
        $calculate  = (new CartRepository)->calculateByCartId((int)data_get($data, 'cart_id'), $data);

        if (!data_get($calculate, 'status')) {
            throw new Exception('Cart is empty');
        }

        $tips        = data_get($calculate, 'data.tips');
        $totalPrice  = data_get($calculate, 'data.total_price') + $tips;
        $totalPrice -= $this->walletPriceWithdraw($cart, $data);

        return [
            'model_type'  => get_class($cart),
            'model_id'    => $cart->id,
            'total_price' => $this->toMinorUnits($totalPrice),
            'tips'        => $tips,
            'currency'    => $cart->currency?->title ?? data_get($payload, 'currency'),
            'cart_id'     => $cart->id,
            'user_id'     => auth('sanctum')->id(),
            'status'      => Order::STATUS_NEW,
        ] + $data;
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     * @throws Exception
     */
    public function beforeBooking(array $data, array|null $payload): array
    {
        /** @var Booking $booking */
        $booking    = Booking::with(['children'])->find(data_get($data, 'booking_id'));
        $totalPrice = $booking->rate_total_price + $booking->children?->sum('rate_total_price');

        if (isset($data['tips']) && $booking->status === Booking::STATUS_ENDED) {
            $totalPrice = $totalPrice / 100 * $data['tips'];
        }

        if (isset($data['extra_time']) && $booking->status === Booking::STATUS_PROGRESS) {
            $totalPrice = $data['price'];
        }

        $totalPrice -= $this->walletPriceWithdraw($booking, $data);

        return [
            'model_type'  => get_class($booking),
            'model_id'    => $booking->id,
            'total_price' => $this->toMinorUnits($totalPrice),
            'currency'    => $booking->currency?->title ?? data_get($payload, 'currency'),
            'booking_id'  => $booking->id,
            'user_id'     => $booking->user_id ?? auth('sanctum')->id(),
            'status'      => $booking->status ?? Booking::STATUS_PROGRESS,
        ] + $data;
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     * @throws Exception
     */
    public function beforeMemberShip(array $data, array|null $payload): array
    {
        $memberShip  = MemberShip::find(data_get($data, 'member_ship_id'));
        $totalPrice  = $memberShip->price;
        $totalPrice -= $this->walletPriceWithdraw($memberShip, $data);
        $currency    = $payload['currency'] ?? Currency::currenciesList()->where('id', $this->currency)->first()?->title;

        return [
            'model_type'     => get_class($memberShip),
            'model_id'       => $memberShip->id,
            'total_price'    => $this->toMinorUnits($totalPrice),
            'currency'       => $currency,
            'user_id'        => auth('sanctum')->id(),
            'member_ship_id' => $memberShip->id,
        ] + $data;
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     * @throws Exception
     */
    public function beforeParcel(array $data, array|null $payload): array
    {
        $parcel      = ParcelOrder::find(data_get($data, 'parcel_id'));
        $totalPrice  = $parcel->rate_total_price;
        $totalPrice -= $this->walletPriceWithdraw($parcel, $data);

        return [
            'model_type'  => get_class($parcel),
            'model_id'    => $parcel->id,
            'total_price' => $this->toMinorUnits($totalPrice),
            'currency'    => $parcel->currency?->title ?? data_get($payload, 'currency')
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function beforeSubscription(array $data): array
    {
        $subscription = Subscription::find(data_get($data, 'subscription_id'));
        $totalPrice   = $subscription->price;
        $totalPrice  -= $this->walletPriceWithdraw($subscription, $data);

        $currency     = Currency::currenciesList()->where('active', 1)->where('default', 1)->first()?->title;

        return [
            'model_type'      => get_class($subscription),
            'model_id'        => $subscription->id,
            'currency'        => $data['currency'] ?? $currency,
            'total_price'     => $this->toMinorUnits($totalPrice),
            'shop_id'         => GetShop::shop()?->id,
            'subscription_id' => $subscription->id,
        ];
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     * @throws Exception
     */
    public function beforePackage(array $data, array|null $payload): array
    {
        $adsPackage  = AdsPackage::find(data_get($data, 'ads_package_id'));
        $totalPrice  = $adsPackage->price;
        $totalPrice -= $this->walletPriceWithdraw($adsPackage, $data);

        $model = ShopAdsPackage::updateOrCreate([
            'ads_package_id' => $adsPackage->id,
            'shop_id'        => GetShop::shop()?->id,
            'active'         => false,
        ]);

        $currency = Currency::find($this->currency);

        return [
            'model_type'  => get_class($model),
            'model_id'    => $model->id,
            'total_price' => $this->toMinorUnits($totalPrice),
            'currency'    => $currency?->title ?? data_get($payload, 'currency'),
            'shop_id'     => $model->shop_id,
        ];
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     */
    public function beforeWallet(array $data, array|null $payload): array
    {
        $model = Wallet::find(data_get($data, 'wallet_id'));

        $totalPrice = $this->toMinorUnits((double)data_get($data, 'total_price'));

        $currency = Currency::find($this->currency);

        return [
            'model_type'  => get_class($model),
            'model_id'    => $model->id,
            'total_price' => $totalPrice,
            'currency'    => $currency?->title ?? data_get($payload, 'currency')
        ];
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     * @throws Exception
     */
    public function beforeGiftCart(array $data, array|null $payload): array
    {
        $model = GiftCart::find($data['gift_cart_id']);

        $totalPrice  = $model->price;
        $totalPrice -= $this->walletPriceWithdraw($model, $data);

        $currency = Currency::find($this->currency);

        return [
            'model_type'  => get_class($model),
            'model_id'    => $model->id,
            'total_price' => $this->toMinorUnits($totalPrice),
            'currency'    => $currency?->title ?? data_get($payload, 'currency')
        ];
    }

    /**
     * @param array $data
     * @param array|null $payload
     * @return array
     * @throws Exception
     */
    public function beforeAuction(array $data, array|null $payload): array
    {
        $model = AuctionUser::find(data_get($data, 'auction_id'));

        $totalPrice  = $model->price;
        $totalPrice -= $this->walletPriceWithdraw($model, $data);

        $currency = Currency::find($this->currency);

        return [
            'model_type'  => get_class($model),
            'model_id'    => $model->id,
            'total_price' => $this->toMinorUnits($totalPrice),
            'currency'    => $currency?->title ?? data_get($payload, 'currency')
        ];
    }

    /**
     * @param Model $model
     * @param array $data
     * @param User|null $user
     * @return int|mixed
     * @throws Exception
     */
    public function walletPriceWithdraw(Model $model, array $data, ?User $user = null): int|float
    {
        if (!isset($data['from_wallet_price'])) {
            return 0;
        }

        if (!is_numeric($data['from_wallet_price']) || !is_finite((float) $data['from_wallet_price']) || (float) $data['from_wallet_price'] < 0) {
            throw new Exception('Wallet contribution is invalid');
        }

        $ratePrice = (float) $data['from_wallet_price'];
        if ($ratePrice === 0.0) {
            return 0;
        }

        if (empty($user)) {
            $user = User::with('wallet')->find(auth('sanctum')->id());
        }

        if (!$user) {
            throw new Exception('Authenticated payment owner is required');
        }

        if (empty($user->wallet?->uuid)) {
            $user = (new UserWalletService)->create($user);
        }

        $wallet = Payment::where('tag', 'wallet')->first();
        if (!$wallet) {
            throw new Exception('Wallet payment method is unavailable');
        }

        if ($model instanceof Booking || $model instanceof Cart) {
            $context = (new \App\Services\PaymentEligibility\PaymentContextFactory)
                ->target($model instanceof Booking ? 'booking_id' : 'cart_id', (int) $model->id);
            (new \App\Services\PaymentEligibility\PaymentEligibilityService)->assertEligible((int) $wallet->id, $context);
        }

        return DB::transaction(function () use ($model, $user, $wallet, $ratePrice): float {
            if (($model instanceof Booking || $model instanceof Order)
                && \App\Services\PaymentAccounting\NativePaymentAccounting::installed()
                && !(new \App\Services\PaymentAccounting\WalletContributionAdapter)->prepared($model)) {
                (new \App\Services\PaymentAccounting\NativePaymentAccounting)->prepare(
                    $model, $wallet, (string) $ratePrice, 'wallet_contribution'
                );
            }
            \App\Services\WalletHistoryService\WalletDebit::debit(
                $user->wallet, $ratePrice, (int) $user->id
            );

            /** @var Order|Cart|Wallet $model */
            $transaction = $model->createTransaction([
                'price'              => $ratePrice,
                'user_id'            => $user->id,
                'payment_sys_id'     => $wallet->id,
                'payment_trx_id'     => null,
                'note'               => "$wallet->id",
                'perform_time'       => now(),
                'status_description' => "Transaction #$wallet->id",
                'request'            => null,
            ]);

            (new TransactionService)->walletHistoryAdd($user, $transaction, $model, 'Wallet', 'withdraw');

            return $ratePrice;
        });
    }

    /**
     * Resolves the single shop a gateway transaction is for — needed by
     * gateways whose credentials are per-shop rather than platform-level
     * (Orange Money, MTN Mobile Money). A cart spanning more than one
     * shop has no single answer: split-payment per shop isn't supported,
     * so this throws rather than guessing which shop's account should
     * receive the whole payment. Bookings always belong to exactly one
     * shop already, so they're unaffected.
     *
     * @throws Exception
     */
    public function resolveGatewayShopId(string $modelType, int $modelId): int
    {
        if ($modelType === Booking::class) {
            $shopId = Booking::find($modelId)?->shop_id;

            if (!$shopId) {
                throw new Exception('Booking not found');
            }

            return $shopId;
        }

        if ($modelType === Cart::class) {
            $shopIds = CartDetail::whereHas('userCart', fn ($q) => $q->where('cart_id', $modelId))
                ->distinct()
                ->pluck('shop_id');

            if ($shopIds->count() !== 1) {
                throw new Exception($shopIds->count() > 1
                    ? 'This payment method does not support a cart with items from more than one shop'
                    : 'Cart is empty');
            }

            return $shopIds->first();
        }

        throw new Exception('This payment method is only available for shop orders and bookings');
    }

    /**
     * The single chokepoint deciding which credential/currency source a
     * gateway transaction resolves against. For customer-facing checkout
     * (bookings, carts/orders): Orange/MTN (Payment::SHOP_CREDENTIAL_TAGS)
     * always resolve to the shop's own ShopPayment config, or null if the
     * shop hasn't set one up — never a silent fallback to the platform's
     * credentials. Every other gateway (PayPal) has no per-shop merchant
     * account to configure, so it resolves to the platform's own
     * PlatformPaymentConfig for the shop's country instead — used there
     * purely for a currency override (PayPalService), since PayPal's
     * actual credentials still come from PaymentPayload. A platform-fee
     * purchase (a Subscription plan, an ads package) always uses that
     * same PlatformPaymentConfig for the paying shop's country — the
     * platform, not the shop, is the merchant of record for its own fees.
     * Callers never see which source they got; every GatewayConfig
     * implementation shares the same contract.
     *
     * The fallback throw is a plain Exception, caught the same way every
     * other error in these services already is: PaymentBaseController
     * ::processTransaction() wraps the whole call in try/catch and turns
     * any Throwable into a clean 400 JSON error response carrying the
     * message below — never a raw 500.
     *
     * @throws Exception
     */
    public function resolveGatewayConfig(array $before, int $paymentId): ?GatewayConfig
    {
        $modelType = data_get($before, 'model_type');
        $modelId   = (int) data_get($before, 'model_id');

        if (in_array($modelType, [Booking::class, Cart::class], true)) {
            $shopId = $this->resolveGatewayShopId($modelType, $modelId);
            $tag    = Payment::find($paymentId)?->tag;
            $shop   = Shop::find($shopId);
            // Bookings persist their collection choice at creation. Use
            // that frozen intent here as well as in TransactionObserver;
            // a shop setting change between booking and checkout must not
            // silently switch which merchant account receives the charge.
            $collectViaPlatform = $modelType === Booking::class
                ? (bool) Booking::query()->whereKey($modelId)->value('collect_via_platform')
                : (array_key_exists('collect_via_platform', $before)
                    ? (bool) data_get($before, 'collect_via_platform')
                    : (bool) $shop?->collect_via_platform);

            // Orange/MTN normally resolve through the shop's own
            // ShopPayment (or nothing at all) - a missing row there must
            // keep meaning "this shop hasn't configured it", never
            // silently fall back to the platform's own credentials. The
            // one opt-in exception: a shop that has explicitly asked the
            // platform to collect on its behalf (collect_via_platform -
            // e.g. because a buyer's country/currency isn't one its own
            // mobile-money account can settle) routes through the
            // platform's own config instead, exactly like PayPal below.
            // TransactionObserver reads this same flag at settlement time
            // to record the shop's payable share - see
            // PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE.
            if (in_array($tag, Payment::SHOP_CREDENTIAL_TAGS, true) && !$collectViaPlatform) {
                return ShopPayment::forShopAndPayment($shopId, $paymentId);
            }

            // Every other gateway (PayPal), or an Orange/MTN shop that
            // opted into platform collection, settles at the platform
            // level - there is no per-shop merchant account to use, so
            // fall back to the platform's own per-country config (same
            // source a platform-fee purchase resolves to below).
            $country = $shop?->checkoutCountry($modelType === Booking::class
                ? ShopLocation::SERVICE : ShopLocation::PRODUCT);

            return $country ? PlatformPaymentConfig::forCountryAndPayment($country->id, $paymentId) : null;
        }

        if (in_array($modelType, [Subscription::class, ShopAdsPackage::class], true)) {
            $shopId = (int) data_get($before, 'shop_id');
            /** @var Shop|null $shop */
            $shop = $shopId ? Shop::find($shopId) : null;

            $country = $shop?->checkoutCountry(ShopLocation::PRODUCT) ?? $shop?->checkoutCountry(ShopLocation::SERVICE);

            if (!$country) {
                throw new Exception('Unable to resolve a country for this shop\'s platform fee payment');
            }

            return PlatformPaymentConfig::forCountryAndPayment($country->id, $paymentId);
        }

        throw new Exception('This payment method is only available for shop orders, bookings, subscriptions, and ads packages');
    }

    public function getValidateData(array $data): array
    {
        $shop     = GetShop::shop();
        $currency = Currency::currenciesList()->where('active', 1)->where('default', 1)->first()?->title;

        if ($shop?->id) {
            $data['shop_id']  = $shop->id;
            $data['currency'] = $currency;
        }

        return $data;
    }

}
