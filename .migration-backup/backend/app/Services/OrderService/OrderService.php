<?php
declare(strict_types=1);

namespace App\Services\OrderService;

use App\Services\DeliveryDriver\DriverMembership;

use DB;
use Exception;
use Throwable;
use App\Models\User;
use App\Models\Order;
use App\Models\Invitation;
use App\Models\Shop;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Settings;
use App\Helpers\OrderHelper;
use App\Helpers\Utility;
use App\Traits\Notification;
use App\Services\CoreService;
use App\Helpers\ResponseError;
use App\Models\PushNotification;
use App\Helpers\NotificationHelper;
use App\Services\PaymentService\BaseService;
use App\Services\EmailSettingService\EmailSendService;
use App\Services\TransactionService\TransactionService;

class OrderService extends CoreService
{
    use Notification;

    protected function getModelClass(): string
    {
        return Order::class;
    }

    private function with(): array
    {
        return [
            'user',
            'review',
            'pointHistories',
            'currency',
            'deliveryman',
            'coupon',
            'shop:id,latitude,longitude,tax,background_img,logo_img,uuid,phone,user_id,verify,email_statuses',
            'shop.translation' => fn($q) => $q
                ->select([
                    'id',
                    'shop_id',
                    'locale',
                    'title',
                    'address',
                ])
                ->where('locale', $this->language),

            'orderDetails.stock.discount' => fn($q) => $q->where('start', '<=', today())
                ->where('end', '>=', today())
                ->where('active', 1),

            'orderDetails.stock.product.translation' => fn($q) => $q
                ->select([
                    'id',
                    'product_id',
                    'locale',
                    'title',
                ])
                ->where('locale', $this->language),

            'orderDetails.stock.stockExtras.value',
            'orderDetails.stock.stockExtras.group.translation' => function ($q) {
                $q->select('id', 'extra_group_id', 'locale', 'title')->where('locale', $this->language);
            },

            'orderDetails.replaceStock.discount' => fn($q) => $q->where('start', '<=', today())
                ->where('end', '>=', today())
                ->where('active', 1),

            'orderDetails.replaceStock.product.translation' => fn($q) => $q
                ->select([
                    'id',
                    'product_id',
                    'locale',
                    'title',
                ])
                ->where('locale', $this->language),

            'orderDetails.replaceStock.stockExtras.value',
            'orderDetails.replaceStock.stockExtras.group.translation' => function ($q) {
                $q->select('id', 'extra_group_id', 'locale', 'title')->where('locale', $this->language);
            },
            'orderRefunds',
            'transactions.paymentSystem',
            'transactions.children',
            'galleries',
            'myAddress',
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            OrderHelper::checkPhoneIfRequired($data, $this->language);

            $orders = DB::transaction(function () use ($data) {
                $walletShares = [];
                if (isset($data['cart_id']) && \App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
                    $ids = (new \App\Services\PaymentAccounting\CartAllocationFactory)
                        ->prepare(\App\Models\Cart::findOrFail($data['cart_id']), $data);
                    $weights = DB::table('commerce_payment_allocations')->whereIn('id',array_values($ids))
                        ->get()->mapWithKeys(fn ($a) => [$a->shop_id => (int) $a->gross_amount])->all();
                    $walletShares = \App\Services\PaymentAccounting\ExactMoney::partition(
                        \App\Services\PaymentAccounting\ExactMoney::units((string)($data['from_wallet_price'] ?? '0')), $weights
                    );
                }

                $orders = match (true) {
                    isset($data['data'])    => (new POSOrderService)->create($data),
                    isset($data['cart_id']) => (new CartOrderService)->create($data, $data['notes'] ?? []),
                    default                 => throw new Exception('error data'),
                };

                // Each order already carries its own shop-resolved rate (a
                // multi-vendor cart can produce orders in different
                // currencies), so tips are converted per-order rather than
                // with one cart-wide currency.
                $tipsTotal = $data['tips'] ?? 0;

                $accountingCheckout = (string) \Illuminate\Support\Str::uuid();
                foreach ($orders as $key => $order) {

                    if ($tipsTotal > 0 && $order->rate > 0) {
                        $data['tips'] = ($tipsTotal / $order->rate) / count($orders);
                    }

                    $orderData = array_replace($data, ['_accounting_checkout' => $accountingCheckout]);
                    if ($walletShares) $orderData['from_wallet_price'] = \App\Services\PaymentAccounting\ExactMoney::decimal($walletShares[$order->shop_id],2);
                    $this->calculateOrder($order, $orderData, false, count($orders));
                    if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()
                        && $order->fresh()->transaction?->status === Transaction::STATUS_PAID) {
                        (new TransactionService)->digitalFile($order);
                    }

                    /** @var Order $order */
                    $order = $order->fresh($this->with());

                    $orders[$key] = $order;

                    if (in_array($order->status, $order->shop?->email_statuses ?? [])) {
                        (new EmailSendService)->sendOrder($order);
                    }

                }

                return $orders;
            });

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
                'data'    => $orders
            ];

        } catch (Throwable $e) {
            $this->error($e);

            return [
                'status'  => false,
                'message' => $e->getMessage(),
                'code'    => ResponseError::ERROR_501,
            ];
        }
    }

    /**
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        try {
//            OrderHelper::checkPhoneIfRequired($data, $this->language);

            /** @var Order $order */
            $order = DB::transaction(function () use ($data, $id) {

                /** @var Order $order */
                $order = $this->model()
                    ->with([
                        'orderDetails',
                        'transaction'
                    ])
                    ->find($id);

                if (!$order) {
                    throw new Exception(__('errors.' . ResponseError::ORDER_NOT_FOUND, locale: $this->language));
                }

                $order->update($data);

                if (data_get($data, 'images.0')) {

                    $order->galleries()->delete();
                    $order->update(['img' => data_get($data, 'images.0')]);
                    $order->uploads(data_get($data, 'images'));

                }

                $order = (new OrderDetailService)->update($order, data_get($data, 'products', []));

                $this->calculateOrder($order, $data, true);

                return $order;
            });

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
                'data'    => $order->fresh($this->with())
            ];

        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'  => false,
                'message' => $e->getMessage() . $e->getFile() . $e->getLine(),
                'code'    => ResponseError::ERROR_502
            ];
        }
    }

    /**
     * @param Order $order
     * @param array $data
     * @param bool $isUpdate
     * @param int $ordersCount
     * @return void
     * @throws Exception
     */
    private function calculateOrder(Order $order, array $data, bool $isUpdate = false, int $ordersCount = 0): void
    {
        /** @var Order $order */
        $order = $order->fresh([
            'shop.translation' => fn($q) => $q->where('locale', $this->language),
            'shop.subscription.subscription',
        ]);

        $isSubscribe = (int)Settings::where('key', 'by_subscription')->first()?->value;

        $totalPrice = $order->orderDetails->sum('total_price');
        $discount   = $order->orderDetails->sum('discount');

        $shopTax = max($totalPrice / 100 * $order->shop?->tax, 0);

        $deliveryFee = [];

        OrderHelper::checkShopDelivery($order->shop, $data, $this->language, $deliveryFee);

        $couponPrice = collect();
        $couponPrice = OrderHelper::checkCoupon($data, $order->shop_id, $totalPrice, $order->rate, $couponPrice, $deliveryFee);

        foreach ($couponPrice as $coupon) {
            $this->createOrderCoupon($coupon['coupon'], $order, $totalPrice);
        }

        $totalPrice += $shopTax;

        $percent = $order->shop?->percentage;

        $commissionFee = 0;

        if (!$isSubscribe && $percent > 0) {
            $commissionFee = max(($totalPrice / 100 * $percent), 0);
        }

        if ($isSubscribe) {

            $orderLimit = $order->shop?->subscription?->subscription?->order_limit;

            $shopOrdersCount = DB::table('orders')
                ->select(['shop_id'])
                ->where('shop_id', $order->shop_id)
                ->count('shop_id');

            if ($orderLimit <= $shopOrdersCount) {
                $order->shop?->update([
                    'visibility' => 0
                ]);
            }

        }

        // Base = $totalPrice as it stands here: post-tax, pre-delivery,
        // pre-coupon, pre-service-fee - the same subtotal $commissionFee
        // above already used for its own percentage.
        $serviceFee = !$isUpdate
            ? Utility::resolveServiceFee('service_fee', $totalPrice)
            : $order->service_fee;

        // Splitting one flat fee across every order a multi-shop cart
        // spawns only makes sense in fixed mode - a percentage fee is
        // already correctly scoped to each order's own subtotal, so
        // dividing it again would undercharge every order in the cart.
        if (!$isUpdate && $serviceFee > 0 && !Utility::isPercentageServiceFee()) {
            $serviceFee /= $ordersCount;
        }

        $couponPriceSum = collect($couponPrice)->sum('price');

        $deliveryFeeSum = collect($deliveryFee)->sum('price');
        $tips = $data['tips'] ?? $order->tips;

        $totalPrice += $deliveryFeeSum;
        $totalPrice -= $couponPriceSum;
        $totalPrice += $serviceFee;
        $totalPrice += $tips;

        $order->update([
            'total_price'    => $totalPrice,
            'commission_fee' => $commissionFee,
            'total_discount' => max($discount, 0),
            'total_tax'      => $shopTax,
            'delivery_fee'   => $deliveryFeeSum === 0 ? $order->delivery_fee : $deliveryFeeSum,
            'coupon_price'   => $couponPriceSum === 0 ? $order->coupon_price : $couponPriceSum,
            'service_fee'    => $serviceFee,
            'tips'           => $tips,
        ]);

        if (!$isUpdate && \App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
            $existing = DB::table('commerce_payment_allocations')->where('origin_type', 'cart')
                ->where('origin_id', $order->cart_id)->where('shop_id', $order->shop_id)
                ->whereIn('state', ['committed','funding'])->orderByDesc('id')->first();
            $quote = (new \App\Services\PaymentAccounting\NativeQuoteFactory)->payable(
                $order->fresh(), $data['_accounting_checkout']
            );
            if ($existing) {
                foreach (['gross_amount','commission_amount','vendor_entitlement_amount','adjustment_amount','currency_id','money_scale'] as $field) {
                    if ((string) $existing->$field !== (string) $quote[$field]) {
                        throw new \DomainException('Native Order no longer matches its committed Cart quote.');
                    }
                }
                (new \App\Services\PaymentAccounting\AllocationWriter)->bind((int) $existing->id, 'order', (int) $order->id);
                (new \App\Services\PaymentAccounting\AllocationWriter)->finalize((int) $existing->id);
            } else {
                (new \App\Services\PaymentAccounting\AllocationWriter)->commit($quote);
            }
            if (data_get($data, 'trx_status')) {
                $a = (new \App\Services\PaymentAccounting\NativePaymentAccounting)->allocation($order);
                $context = DB::table('payment_collection_contexts')->where('allocation_id',$a->id)
                    ->where('payment_id',$data['payment_id'])->where('funding_slot','selected_method')
                    ->where('state','confirmed')->first();
                $t = $order->createTransaction([
                    'price' => $context ? \App\Services\PaymentAccounting\ExactMoney::decimal((int)$context->amount,(int)$a->money_scale)
                        : $order->total_price, 'user_id' => $order->user_id,
                    'payment_sys_id' => $data['payment_id'], 'payment_trx_id' => $data['payment_trx_id'] ?? null,
                    'status' => $data['trx_status'], 'status_description' => "Transaction for order #{$order->id}",
                ]);
                if ($context) {
                    (new \App\Services\PaymentAccounting\AllocationWriter)->linkTransaction((int)$t->id,(int)$a->id,(int)$context->id);
                }
            }
        }

        if (data_get($data, 'payment_id') && !data_get($data, 'trx_status')) {

            /** @var User $user */
            $user = User::with('wallet')->find($order->user_id);

            $totalPrice -= (new BaseService)->walletPriceWithdraw($order, $data, $user);

            $payment = Payment::find($data['payment_id']);
            if ($payment?->tag === 'wallet' && \App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
                (new \App\Services\PaymentAccounting\NativePaymentAccounting)->prepare($order, $payment, (string) $totalPrice);
                $user->load('wallet');
            }

            $transaction = $order->createTransaction([
                'price'              => $totalPrice,
                'user_id'            => $order->user_id,
                'payment_sys_id'     => $data['payment_id'],
                'payment_trx_id'     => null,
                'note'               => $order->id,
                'perform_time'       => now(),
                'status_description' => "Transaction for order #$order->id",
                'request'            => null,
            ]);

            if ($payment->tag === 'wallet') {

                if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()) {
                    (new \App\Services\PaymentAccounting\NativePaymentAccounting)->prepare($order, $payment, (string) $totalPrice);
                }
                if ($user->wallet?->price <= $totalPrice) {
                    throw new Exception( __('errors.' . ResponseError::ERROR_109, locale: $this->language));
                }

                if ($user->wallet()->where('price',$user->wallet->price)->update(['price' => $user->wallet->price - $totalPrice]) !== 1) {
                    throw new \RuntimeException('Required selected Wallet debit failed.');
                }

                (new TransactionService)->walletHistoryAdd($order->user, $transaction, $order, 'Order', 'withdraw');
            }

        }

        OrderHelper::updateUserOrderStat($order);
    }

    /**
     * @param Coupon $coupon
     * @param Order $order
     * @param $totalPrice
     * @return float|int|null
     */
    private function createOrderCoupon(Coupon $coupon, Order $order, $totalPrice): float|int|null
    {
        if ($coupon->qty <= 0) {
            return 0;
        }

        $couponPrice = $coupon->type === 'percent' ? ($totalPrice / 100) * $coupon->price : $coupon->price;

        $order->coupon()->updateOrCreate([
            'user_id' => $order->user_id,
            'name'    => $coupon->name,
        ], [
            'price'   => $couponPrice,
        ]);

        $coupon->decrement('qty');

        return $couponPrice;
    }

    /**
     * @param int|null $orderId
     * @param int $deliveryman
     * @param int|null $shopId
     * @return array
     */
    public function updateDeliveryMan(?int $orderId, int $deliveryman, ?int $shopId = null): array
    {
        try {
            // A global Admin assignment is still global-authorized, but
            // shares the shop lock when the order is shop-owned so Vendor
            // offboarding cannot race an assignment write.
            $shopToLock = $shopId ?? Order::query()->whereKey($orderId)->value('shop_id');
            $result = DB::transaction(function () use ($orderId, $deliveryman, $shopId, $shopToLock): array {
                if ($shopToLock !== null) {
                    $shop = Shop::query()->whereKey($shopToLock)->lockForUpdate()->first();
                    if (!$shop && $shopId !== null) {
                        return [
                            'status' => false,
                            'code' => ResponseError::ERROR_404,
                            'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language),
                        ];
                    }
                }

                $memberships = null;
                if ($shopId !== null) {
                    $memberships = Invitation::query()
                        ->where('shop_id', $shopId)
                        ->where('user_id', $deliveryman)
                        ->lockForUpdate()
                        ->get();
                }

                /** @var User|null $user */
                $user = User::query()->whereKey($deliveryman)->lockForUpdate()->first();
                if ($shopId !== null && $user
                    && ($memberships?->count() !== 1
                        || $memberships?->first()->role !== 'deliveryman'
                        || !DriverMembership::isEligible($shopId, (int) $user->id))
                ) {
                    $user = null;
                }

                /** @var Order|null $order */
                $order = Order::query()
                    ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                    ->whereKey($orderId)
                    ->lockForUpdate()
                    ->first();

                if (!$order) {
                    return [
                        'status'  => false,
                        'code'    => ResponseError::ERROR_404,
                        'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
                    ];
                }

                if ($order->delivery_type != Order::DELIVERY) {
                    return [
                        'status'  => false,
                        'code'    => ResponseError::ERROR_502,
                        'message' => __('errors.' . ResponseError::ORDER_POINT, locale: $this->language)
                    ];
                }

                if ($shopToLock !== null && (int) $order->shop_id !== (int) $shopToLock) {
                    return [
                        'status' => false,
                        'code' => ResponseError::ERROR_400,
                        'message' => 'The order shop changed during delivery assignment. Retry the operation.',
                    ];
                }

                if (!$user || !$user->active || !$user->hasRole('deliveryman')
                    || ($shopId !== null && !DriverMembership::isEligible($shopId, (int) $user->id))) {
                    return [
                        'status'  => false,
                        'code'    => ResponseError::ERROR_211,
                        'message' => __('errors.' . ResponseError::ERROR_211, locale: $this->language)
                    ];
                }

                $order->update(['deliveryman_id' => $user->id]);

                return ['status' => true, 'data' => $order, 'user' => $user];
            }, 3);

            if (!$result['status']) {
                return $result;
            }

            /** @var Order $order */
            $order = $result['data'];
            /** @var User $user */
            $user = $result['user'];

            $this->sendNotification(
                $order,
                is_array($user->firebase_token) ? $user->firebase_token : [$user->firebase_token],
                __('errors.' . ResponseError::NEW_ORDER, ['id' => $order->id], $user->lang ?? $this->language),
                __('errors.' . ResponseError::NEW_ORDER, ['id' => $order->id], $user->lang ?? $this->language),
                (new NotificationHelper)->deliveryManOrder($order, PushNotification::NEW_ORDER),
                [$user->id]
            );

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
                'data'    => $order,
                'user'    => $user
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_501,
                'message' => __('errors.' . ResponseError::ERROR_501, locale: $this->language)
            ];
        }
    }

    /**
     * @param int|null $id
     * @return array
     */
    public function attachDeliveryMan(?int $id): array
    {
        // No explicit platform-dispatch grant exists in the current source.
        // Shop membership permits assignment by the Vendor, not self-claim.
        // Preserve the route but fail closed until a dispatch contract is approved.
        return [
            'status' => false,
            'code' => ResponseError::ERROR_101,
            'message' => __('errors.' . ResponseError::ERROR_101, locale: $this->language),
        ];
    }

    /**
     * @param array|null $ids
     * @param int|null $shopId
     * @return array
     */
    public function destroy(?array $ids = [], ?int $shopId = null): array
    {
        $errors = [];

        $orders = Order::with([
            'coupon',
            'orderDetails.stock.product'
        ])
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->find(is_array($ids) ? $ids : []);

        foreach ($orders as $order) {

            try {
                DB::transaction(function () use ($order) {

                    /** @var Order $order */
                    foreach ($order->orderDetails as $orderDetail) {

                        OrderHelper::updateStatCount(
                            $orderDetail->stock,
                            $orderDetail?->quantity,
                            false
                        );

                        $orderDetail->delete();
                    }

                    DB::table('push_notifications')
                        ->where('model_type', Order::class)
                        ->where('model_id', $order->id)
                        ->delete();

                    $order->user->update([
                        'o_count' => $order->user->o_count - 1,
                        'o_sum'   => $order->user->o_sum - $order->total_price,
                    ]);

                    $order->delete();

                });
            } catch (Throwable $e) {
                $errors[] = $order->id;

                $this->error($e);
            }

        }

        return $errors;
    }

    /**
     * @param int $id
     * @param int|null $userId
     * @return array
     */
    public function setCurrent(int $id, ?int $userId = null): array
    {
        if ($userId !== null && !DriverMembership::constrainAssignedOrders(Order::query(), $userId)
            ->whereKey($id)->exists()) {
            return [
                'status' => false,
                'code' => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language),
            ];
        }

        $errors = [];

        $orders = Order::when($userId !== null, fn($q) => DriverMembership::constrainAssignedOrders($q, $userId))
            ->where(fn ($q) => $q->where('current', 1)->orWhere('id', $id))
            ->get();

        $getOrder = new Order;

        foreach ($orders as $order) {

            try {

                if ($order->id === $id) {

                    $order->update([
                        'current' => true,
                    ]);

                    $getOrder = $order;

                    continue;

                }

                $order->update([
                    'current' => false,
                ]);

            } catch (Throwable $e) {
                $errors[] = $order->id;

                $this->error($e);
            }

        }

        return count($errors) === 0 ? [
            'status' => true,
            'code' => ResponseError::NO_ERROR,
            'data' => $getOrder
        ] : [
            'status'  => false,
            'code'    => ResponseError::ERROR_400,
            'message' => __(
                'errors.' . ResponseError::CANT_UPDATE_ORDERS,
                [
                    'ids' => implode(', #', $errors)
                ],
                $this->language
            )
        ];
    }

    /**
     * @param int $orderId
     * @param array $data
     * @return Order
     * @throws Exception
     */
    public function trackingUpdate(int $orderId, array $data): Order
    {
        $order = Order::find($orderId);

        if (!$order) {
            throw new Exception(__('errors.' . ResponseError::ORDER_NOT_FOUND, locale: $this->language));
        }

        $order->update($data);

        return $order;
    }
}
