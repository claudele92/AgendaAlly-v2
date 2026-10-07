<?php
declare(strict_types=1);

namespace App\Services\OrderService;

use App\Helpers\OrderHelper;
use App\Helpers\ResponseError;
use App\Jobs\PayReferral;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderRefund;
use App\Models\PaymentToPartner;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserDigitalFile;
use App\Models\WalletHistory;
use App\Services\CoreService;
use App\Services\WalletHistoryService\WalletHistoryService;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Throwable;

class OrderRefundService extends CoreService
{
    protected function getModelClass(): string
    {
        return OrderRefund::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            return DB::transaction(function () use ($data): array {
                // Creation and settlement serialize on the same persisted Order.
                $order = Order::query()->lockForUpdate()->findOrFail(data_get($data, 'order_id'));
                if (OrderRefund::query()->where('order_id', $order->id)
                    ->whereIn('status', [OrderRefund::STATUS_PENDING, OrderRefund::STATUS_ACCEPTED])->exists()) {
                    return ['status' => false, 'code' => ResponseError::ERROR_506];
                }

                $this->checkDigital($order->id);
                $orderRefund = OrderRefund::create(array_merge(
                    Arr::only($data, ['order_id', 'cause']),
                    ['status' => OrderRefund::STATUS_PENDING]
                ));
                if (data_get($data, 'images.0')) {
                    $orderRefund->uploads(data_get($data, 'images'));
                }

                return ['status' => true, 'message' => ResponseError::NO_ERROR];
            });

        } catch (Throwable $e) {
            $this->error($e);

            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_501,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param int $orderId
     * @return void
     * @throws Exception
     */
    public function checkDigital(int $orderId): void
    {
        /** @var Order $order */
        $order = Order::with([
            'orderDetails:id,order_id,stock_id',
            'orderDetails.stock:id,product_id',
            'orderDetails.stock.product:id',
            'orderDetails.stock.product.digitalFile:id,product_id',
        ])
            ->select(['id'])
            ->find($orderId);

        $digital = 0;
        $product = 0;

        foreach ($order->orderDetails as $orderDetail) {

            $digitalFile = UserDigitalFile::where([
                'digital_file_id' => $orderDetail->stock?->product?->digitalFile?->id,
                'user_id'         => $order->user_id,
            ])
                ->first();

            if (!empty($digitalFile)) {
                $digital += 1;
                continue;
            }

            $product += 1;

        }

        if ($digital > 0 && $product === 0) {
            throw new Exception('can not refund digital order');
        }

    }

    public function update(OrderRefund $orderRefund, array $data): array
    {
        $referralUser = null;
        try {
            $result = DB::transaction(function () use ($orderRefund, $data, &$referralUser): array {
                // Reload, never trust a caller's stale status/relations or payload
                // ownership. Order first also serializes duplicate request rows.
                $persisted = OrderRefund::query()->findOrFail($orderRefund->id);
                $order = Order::query()->lockForUpdate()->findOrFail($persisted->order_id);
                $refund = OrderRefund::query()->lockForUpdate()->findOrFail($persisted->id);
                $status = data_get($data, 'status');
                if ($refund->status !== OrderRefund::STATUS_PENDING
                    || !in_array($status, [OrderRefund::STATUS_ACCEPTED, OrderRefund::STATUS_CANCELED], true)) {
                    return ['status' => false, 'code' => ResponseError::ERROR_252];
                }
                if (OrderRefund::query()->where('order_id', $order->id)->where('id', '!=', $refund->id)
                    ->where('status', OrderRefund::STATUS_ACCEPTED)->exists()) {
                    return ['status' => false, 'code' => ResponseError::ERROR_501];
                }

                // Atomic claim is also necessary on SQLite, where row locks
                // compile to no-ops. The terminal marker and all SQL effects
                // commit together; every failure rolls the claim back.
                $claimed = OrderRefund::query()->whereKey($refund->id)
                    ->where('status', OrderRefund::STATUS_PENDING)
                    ->update(Arr::only($data, ['status', 'answer']));
                if ($claimed !== 1) {
                    throw new Exception('Refund transition was not claimed');
                }
                if ($status === OrderRefund::STATUS_CANCELED) {
                    return ['status' => true, 'message' => ResponseError::NO_ERROR];
                }

                if (\App\Services\PaymentAccounting\NativePaymentAccounting::installed()
                    && DB::table('commerce_payment_allocations')->where('payable_type','order')->where('payable_id',$order->id)->exists()) {
                    throw new Exception('Linked Product refund requires an authorized original-context accounting effect group.');
                }
                $order->load([
                    'shop.seller.wallet', 'deliveryman.wallet', 'user.wallet',
                ]);
                $user = $order->user;
                if (!$user?->wallet) {
                    throw new Exception(__('errors.' . ResponseError::ERROR_108, locale: $this->language));
                }
                if ($order->transactions()->where('status', Transaction::STATUS_REFUND)->exists()
                    || !$order->transactions()->where('status', Transaction::STATUS_PAID)->exists()) {
                    throw new Exception('A paid, not previously refunded Order is required');
                }

                if ($order->status === Order::STATUS_DELIVERED) {
                    $referralUser = $user;

                    if (!$order->shop?->seller?->wallet?->id) {
                        throw new Exception(__('errors.' . ResponseError::ERROR_114, locale: $this->language));
                    }

                    /** @var PaymentToPartner $sellerPartner */
                    $sellerPartner = PaymentToPartner::with(['transaction'])
                        ->where([
                            'user_id'    => $order->shop->seller->id,
                            'model_id'   => $order->id,
                            'model_type' => Order::class,
                            'type'	     => PaymentToPartner::SELLER,
                        ])
                        ->first();

                    if ($sellerPartner?->transaction?->status === Transaction::STATUS_PAID) {
                        $this->walletEffect([
                            'type'   => 'withdraw',
                            'price'  => $order->seller_fee,
                            'note'   => "For Order #$order->id",
                            'status' => WalletHistory::PAID,
                            'user'   => $order->shop->seller
                        ]);
                    }

                    if ($order->delivery_type == Order::DELIVERY && $order->deliveryman?->wallet?->id) {

                        /** @var PaymentToPartner $deliveryManPartner */
                        $deliveryManPartner = PaymentToPartner::with(['transaction'])
                            ->where([
                                'user_id'    => $order->deliveryman_id,
                                'model_id'   => $order->id,
                                'model_type' => Order::class,
                                'type'       => PaymentToPartner::DELIVERYMAN,
                            ])
                            ->first();

                        if ($deliveryManPartner?->transaction?->status === Transaction::STATUS_PAID) {
                            $this->walletEffect([
                                'type'   => 'withdraw',
                                'price'  => $order->delivery_fee,
                                'note'   => "For Order #$order->id",
                                'status' => WalletHistory::PAID,
                                'user'   => $order->deliveryman
                            ]);
                        }

                    }

                }

                $totalPrice = $this->refundProduct($order, $order->total_price);

                $this->walletEffect([
                    'type'   => 'topup',
                    'price'  => $totalPrice,
                    'note'   => "For Order #$order->id",
                    'status' => WalletHistory::PAID,
                    'user'   => $user
                ]);

                return ['status' => true, 'message' => ResponseError::NO_ERROR];
            });
        } catch (Throwable $e) {
            $this->error($e);

            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }

        // Never register an after-response financial job for rolled-back work.
        if ($referralUser && data_get($result, 'status')) {
            PayReferral::dispatchAfterResponse($referralUser, 'decrement');
        }
        return $result;
    }

    private function walletEffect(array $data): void
    {
        $result = app(WalletHistoryService::class)->create($data);
        if (!data_get($result, 'status')) {
            throw new Exception('Refund Wallet operation failed');
        }
    }

    public function delete(?array $ids = [], ?int $shopId = null, ?bool $isAdmin = false): array
    {
        try {

            foreach (OrderRefund::find(is_array($ids) ? $ids : []) as $orderRefund) {
                DB::transaction(function () use ($orderRefund, $shopId, $isAdmin): void {
                    // The retained accepted row is the settlement witness. Even
                    // privileged deletion must not erase it and enable a new credit.
                    Order::query()->lockForUpdate()->findOrFail($orderRefund->order_id);
                    $orderRefund = OrderRefund::query()->lockForUpdate()->findOrFail($orderRefund->id);
                    if ($orderRefund->status === OrderRefund::STATUS_ACCEPTED) {
                        return;
                    }

                    if (!$isAdmin) {
                        if (empty($shopId) && data_get($orderRefund->order, 'user_id') !== auth('sanctum')->id()) {
                            return;
                        } else if ($orderRefund->status !== OrderRefund::STATUS_CANCELED) {
                            return;
                        }
                    }

                    if (!empty($shopId) && $orderRefund->order?->shop_id !== $shopId) {
                        return;
                    }

                    $orderRefund->galleries()->delete();
                    $orderRefund->delete();
                });
            }

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
            ];

        } catch (Throwable $e) {
            $this->error($e);

            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_503,
                'message' => __('errors.' . ResponseError::ERROR_503, locale: $this->language),
            ];
        }
    }

    public function dropAll(?array $exclude = []): array
    {
        $ids = OrderRefund::query()->when(
            data_get($exclude, 'column') && data_get($exclude, 'value'),
            fn ($query) => $query->where($exclude['column'], '!=', $exclude['value'])
        )->pluck('id')->all();
        return $this->delete($ids, null, true);
    }

    public function refundProduct(Order $order, int|float|null $totalPrice): int|float|null
    {
        $order->orderDetails->map(function (OrderDetail $orderDetail) use ($order, &$totalPrice) {

            $digitalFile = UserDigitalFile::where([
                'digital_file_id' => $orderDetail->stock?->product?->digitalFile?->id,
                'user_id'         => $order->user_id,
                'downloaded'      => true,
            ])
                ->first();

            if (!empty($digitalFile)) {
                $totalPrice -= $orderDetail->total_price;
            }

            OrderHelper::updateStatCount($orderDetail->stock, $orderDetail->quantity, false);

        });

        return $totalPrice;
    }
}
