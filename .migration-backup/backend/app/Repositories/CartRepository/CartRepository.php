<?php
declare(strict_types=1);

namespace App\Repositories\CartRepository;

use App\Helpers\OrderHelper;
use App\Helpers\ResponseError;
use App\Helpers\Utility;
use App\Http\Resources\CurrencyResource;
use App\Models\Cart;
use App\Models\CartDetailProduct;
use App\Repositories\CoreRepository;
use App\Services\CartService\CartService;
use App\Traits\ByLocation;
use App\Traits\SetCurrency;
use Throwable;

class CartRepository extends CoreRepository
{
    use SetCurrency, ByLocation;

    protected function getModelClass(): string
    {
        return Cart::class;
    }

    public function with(): array
    {
        return [
                'currency',
                'userCarts.cartDetails' => fn($q) => $q->with([
                    'shop:id,latitude,longitude,tax,uuid,slug,logo_img,status,type,delivery_type,delivery_time,verify,product_fulfillment_methods',
                    'shop.translation' => fn($q) => $q->where('locale', $this->language),
                    'shop.locations' => fn($q) => $q->where('type', \App\Models\ShopLocation::PRODUCT),
                    'shop.locations.country.translation' => fn($q) => $q->where('locale', $this->language),
                    'shop.locations.city.translation' => fn($q) => $q->where('locale', $this->language),
                    'shop.locations.area.translation' => fn($q) => $q->where('locale', $this->language),
                    'cartDetailProducts' => fn($q) => $q
                        ->with([
                            'galleries',
                            'stock.discount' => fn($d) => $d->where('start', '<=', today())
                                ->where('end', '>=', today())
                                ->where('active', 1),
                            'stock.product.translation' => fn($q) => $q->where('locale', $this->language),
                            'stock.stockExtras.value',
                            'stock.stockExtras.group.translation' => fn($q) => $q->where('locale', $this->language),
                            'stock.wholeSalePrices'
                        ])
                        ->whereNull('parent_id'),
                ]),
            ] + $this->getWith();
    }

    /**
     * @param array $filter
     * @return Cart|null
     */
    public function get(array $filter): ?Cart
    {
        $userId = auth('sanctum')->id();

        if (!empty($userId) && !isset($filter['user_cart_uuid'])) {
            $filter['user_id'] = $userId;
        }

        $cart = $this->model()
            ->filter($filter)
            ->with($this->with())
            ->first();

        if (empty($cart)) {
            /** @var Cart $cart */
            return $cart;
        }

        /** @var Cart $cart */
        (new CartService)->calculateTotalPrice($cart);

        // Currency is set once, from the shop's own country, when items are
        // added to the cart (CartService) — it must not drift on every read
        // based on whatever currency_id happens to be in the request.
        return $this->model()
            ->filter($filter)
            ->with($this->with())
            ->first();
    }

    /**
     * @param int $id
     * @param array $data
     * @return array
     */
    public function calculateByCartId(int $id, array $data): array
    {
        $cart = Cart::with($this->with())
            ->withCount('userCarts')
            ->find($id);

        if (empty($cart)) {

            return ['status' => false, 'code' => ResponseError::ERROR_404];

        } else if (data_get($cart, 'user_carts_count') === 0) {

            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => 'Cart is empty'];

        }

        /** @var Cart $cart */
        // Currency is set once, from the shop's own country, when items are
        // added to the cart (CartService) — never overwritten here from a
        // client-supplied currency_id.
        $rate           = $cart->rate;
        $totalTax       = 0;
        $totalShopTax   = 0;
        $price          = 0;
        $totalDiscount  = 0;
        $deliveryFee    = [];
        $errors         = [];
        $couponPrice    = collect();

        foreach ($cart->userCarts as $userCart) {

            foreach ($userCart->cartDetails as $cartDetail) {

                $discount = 0;

                try {
                    if (collect($deliveryFee)->where('shop_id', $cartDetail->shop_id)->isEmpty()) {
                        OrderHelper::checkShopDelivery($cartDetail->shop, $data, $this->language, $deliveryFee);
                    }
                } catch (Throwable $e) {
                    $errors[] = [
                        'shop_id' => $cartDetail->shop_id,
                        'message' => $e->getMessage(),
                    ];
                }

                foreach ($cartDetail->cartDetailProducts as $cartDetailProduct) {

                    if (empty($cartDetailProduct->stock)) {
                        $cartDetailProduct->delete();
                    }

                    /** @var CartDetailProduct $cartDetailProduct */
                    $totalTax += $cartDetailProduct->stock->rate_tax_price;
                    $price    += $cartDetailProduct->rate_price;
                    $discount += $cartDetailProduct->rate_discount;

                }

                $totalPrice = $cartDetail->cartDetailProducts->sum('price');

                $shopTax = max((($totalPrice - $discount) / $rate) / 100 * $cartDetail->shop->tax, 0) * $rate;

                $totalShopTax  += $shopTax;

                // recalculate shop bonus
                $totalDiscount += $discount;

                $cartDetail->setAttribute('shop_tax', $shopTax);
                $cartDetail->setAttribute('discount', $discount);
                $cartDetail->setAttribute('total_price', $totalPrice + $shopTax);

                // if что бы по каждым юзерам в group order не минусовать сумму купона
                $coupon = $couponPrice->where('shop_id', $cartDetail->shop_id)->first();

                if ($coupon?->price <= 0) {
                    $couponPrice = OrderHelper::checkCoupon($data, $cartDetail->shop_id, $totalPrice - $discount, $rate, $couponPrice, $deliveryFee);
                }

            }

        }

        // Base = the cart's own already-rate-converted subtotal (rate_total_price),
        // matching how a percentage rate applies against a pre-fee subtotal
        // elsewhere (OrderService::calculateOrder()'s existing commission/tax).
        $serviceFee = Utility::resolveServiceFee('service_fee', $cart->rate_total_price, $rate);

        $totalPrice = $cart->rate_total_price + $serviceFee + $totalShopTax;

        $couponPriceSum = collect($couponPrice)->sum('price');

        $deliveryFeeSum = collect($deliveryFee)->sum('price');

        $tips = (double)data_get($data, 'tips', 0);
        $tips = round($tips, 2);

        $data = [
            'total_tax'      => round($totalTax, 2),
            'price'          => $price,
            'total_shop_tax' => round($totalShopTax, 2),
            'total_price'    => round(max($totalPrice + $deliveryFeeSum - $couponPriceSum + $tips, 0), 2),
            'delivery_fee'   => $deliveryFee,
            'total_discount' => $totalDiscount,
            'coupon'         => $couponPrice,
            'rate'           => $rate,
            'currency_id'    => $cart->currency_id,
            'currency'       => CurrencyResource::make($cart->currency),
            'service_fee'    => $serviceFee,
            'tips'           => $tips
        ];

        if (count($errors) > 0) {
            $data['errors'] = $errors;
        }

        return [
            'status' => true,
            'code'   => ResponseError::NO_ERROR,
            'data'   => $data,
        ];
    }

}
