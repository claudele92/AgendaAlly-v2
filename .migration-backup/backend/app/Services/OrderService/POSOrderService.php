<?php
declare(strict_types=1);

namespace App\Services\OrderService;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Services\CoreService;
use Exception;
use Throwable;

class POSOrderService extends CoreService
{
    protected function getModelClass(): string
    {
        return Order::class;
    }

    /**
     * @param array $data
     * @return array
     * @throws Throwable
     */
    public function create(array $data): array
    {
        if (!isset($data['user_id'])) {
            $data['user_id'] = auth('sanctum')->id();
        }

        $data['total_price']    = 0;
        $data['commission_fee'] = 0;

        $parentId = null;
        $orders   = [];

        foreach ($data['data'] as $key => $item) {

            $shop = Shop::find($item['shop_id']);

            if (empty($shop)) {
                throw new Exception('shop not found');
            }

            // Each shop resolves its own currency — a single POS batch can
            // cover multiple shops (a walk-in ordering from several stalls),
            // and they are not guaranteed to share a country/currency.
            $country = $shop->checkoutCountry(ShopLocation::PRODUCT);

            if (!$country) {
                throw new Exception(__('errors.' . ResponseError::ERROR_400, locale: $this->language));
            }

            $data['currency_id']  = $country->currency_id;
            $data['rate']         = $country->currency->rate;
            $data['type']         = $shop->delivery_type;
            $data['shop_id']      = $item['shop_id'];
            $data['parent_id']    = $parentId;
            $data['otp']          = random_int(1000, 9999);
            $data['note']         = data_get($data, "notes.$shop->id");

            if ((int)Settings::where('key', 'order_auto_approved')->first()?->value === 1) {
                $data['status'] = Order::STATUS_ACCEPTED;
            }

            $pickupContext = $data;
            $stockIds = array_values(array_unique(array_filter(array_column($item['products'] ?? [], 'stock_id'))));
            $stocks = \App\Models\Stock::with('product:id,digital')->whereIn('id', $stockIds)->get();
            if ($stockIds && $stocks->count() === count($stockIds) &&
                $stocks->every(fn ($stock) => (bool) $stock->product?->digital)) {
                $pickupContext['delivery_type'] = Order::DIGITAL;
            }
            /** @var Order $order */
            $order = Order::createForFulfillment(array_diff_key(array_merge($data,
                (new \App\Services\ProductPickupService)->orderFields($shop, $pickupContext)
            ), ['pickup_selections' => true]));

            if (data_get($item, "images.$shop->id.0")) {

                $order->update([
                    'img' => $item['images'][$shop->id][0]
                ]);

                $order->uploads($item['images'][$shop->id]);

            }

            $order = (new OrderDetailService)->create($order, data_get($item, 'products', []));

            if ($key === 0) {
                $parentId = $order->id;
            }

            $orders[] = $order;
        }

        return $orders;
    }

}
