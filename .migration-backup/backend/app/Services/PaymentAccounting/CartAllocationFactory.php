<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

use App\Helpers\OrderHelper;
use App\Helpers\Utility;
use App\Models\Cart;
use App\Models\Shop;
use App\Models\ShopLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Serialized server Cart generation. Never reprice an existing live snapshot. */
final class CartAllocationFactory
{
    public function prepare(Cart $cart, array $data): array
    {
        return DB::transaction(function () use ($cart, $data): array {
            // Actual write reservation on SQLite, row lock on MySQL.
            if (DB::table('carts')->where('id', $cart->id)->update(['updated_at' => now()]) !== 1) {
                throw new \DomainException('Cart no longer exists.');
            }
            DB::table('carts')->where('id', $cart->id)->lockForUpdate()->first();
            $quotes = $this->quotes($cart, $data);
            $latest = DB::table('commerce_payment_allocations')->where('origin_type', 'cart')
                ->where('origin_id', $cart->id)->orderByDesc('id')->first();
            $checkout = $latest?->checkout_key ?? (string) Str::uuid();
            if ($latest) {
                $generation = DB::table('commerce_payment_allocations')->where('checkout_key', $checkout)->get();
                if ($generation->every(fn ($a) => $a->state === 'canceled')) {
                    if (DB::table('payment_collection_contexts')->whereIn('allocation_id', $generation->pluck('id'))
                        ->whereNotIn('state', ['rejected','canceled'])->exists()) {
                        throw new \DomainException('A new native checkout requires no confirmed or unresolved original collection.');
                    }
                    $checkout = (string) Str::uuid();
                } else {
                    $shops = $generation->pluck('shop_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
                    $newShops = array_keys($quotes); sort($newShops);
                    if ($shops !== $newShops) {
                        throw new \DomainException('Cart membership changed while original funding is unresolved.');
                    }
                }
            }
            $ids = [];
            foreach ($quotes as $shop => $quote) {
                $quote['checkout_key'] = $checkout;
                $ids[$shop] = (new AllocationWriter)->commit($quote);
            }
            return $ids;
        }, 3);
    }

    private function quotes(Cart $cart, array $data): array
    {
        if ((string) $cart->rate !== '1' || !empty($data['coupon']) || !empty($data['tips'])) {
            throw new \DomainException('Cart quote requires source-proven currency and native adjustment responsibility.');
        }
        $cart->load('userCarts.cartDetails.cartDetailProducts.stock.product', 'currency');
        $byShop = []; $items = [];
        foreach ($cart->userCarts as $userCart) {
            foreach ($userCart->cartDetails as $detail) {
                foreach ($detail->cartDetailProducts as $line) {
                    $stock = $line->stock;
                    if (!$stock || $stock->quantity < $line->quantity) {
                        throw new \DomainException('Cart stock/quantity changed before immutable quote.');
                    }
                    $params = OrderHelper::setItemParams($line, $stock);
                    if ((int)OrderHelper::actualQuantity($stock,$line->quantity,$line->bonus) !== (int)$line->quantity
                        || ($stock->discount && (int)$stock->discount->shop_id !== (int)$detail->shop_id)) {
                        throw new \DomainException('Quantity/discount ownership must match the trusted native listing.');
                    }
                    if ((int) $stock->product?->shop_id !== (int) $detail->shop_id
                        || ExactMoney::units((string) $params['tax']) !== 0 || $params['bonus']) {
                        throw new \DomainException('Product tax/bonus or cross-Shop stock requires a proven economic contract.');
                    }
                    $byShop[(int) $detail->shop_id][] = ExactMoney::units((string) $params['total_price']);
                    $items[(int)$detail->shop_id][] = [
                        'stock_id'=>(int)$stock->id,'product_id'=>(int)$stock->product_id,'quantity'=>(int)$line->quantity,
                        'net_units'=>(string)ExactMoney::units((string)$params['total_price']),
                        'discount_units'=>(string)ExactMoney::units((string)$params['discount']),
                    ];
                }
            }
        }
        if (!$byShop) {
            throw new \DomainException('Cart quote is empty.');
        }
        ksort($byShop);
        $quotes = [];
        foreach ($byShop as $shopId => $amounts) {
            $shop = Shop::findOrFail($shopId);
            $country = $shop->checkoutCountry(ShopLocation::PRODUCT);
            if (!$country || (int) $country->currency_id !== (int) $cart->currency_id || (string) $shop->tax !== '0'
                || (string) $shop->percentage !== '0') {
                throw new \DomainException('Native Cart tax/commission/currency quote requires a proven frozen contract.');
            }
            $delivery = [];
            OrderHelper::checkShopDelivery($shop, $data, 'en', $delivery);
            if (collect($delivery)->sum('price') != 0) {
                throw new \DomainException('Delivery adjustment requires a proven original responsibility.');
            }
            $subtotal = ExactMoney::sum($amounts);
            // Invoke the existing native fee calculator; never invent a fee rate.
            $fee = Utility::resolveServiceFee('service_fee', (float) ExactMoney::decimal($subtotal, 2));
            if (!Utility::isPercentageServiceFee()) {
                $fee /= count($byShop); // exact native fixed-fee division; unsupported precision rejects below
            }
            $c = ExactMoney::units((string) $fee);
            $g = ExactMoney::sum([$subtotal, $c]);
            if (\Illuminate\Support\Facades\Schema::hasTable('points')
                && \App\Models\Point::getActualPoint(ExactMoney::decimal($g,2)) > 0) {
                throw new \DomainException('Cashback sponsorship requires a proven frozen economic contract.');
            }
            $quotes[$shopId] = [
                'checkout_key' => '', 'origin_type' => 'cart', 'origin_id' => (int) $cart->id,
                'shop_id' => $shopId, 'vendor_user_id' => (int) $shop->user_id,
                'payer_user_id' => (int) $cart->owner_id, 'local_client_id' => null,
                'country_id' => (int) $country->id, 'currency_id' => (int) $country->currency_id,
                'currency_code' => strtoupper($country->currency->title), 'money_scale' => 2,
                'purpose' => 'base', 'obligation_key' => 'base', 'payable_type' => null, 'payable_id' => null,
                'gross_amount' => $g, 'commission_amount' => $c, 'vendor_entitlement_amount' => $subtotal,
                'adjustment_amount' => 0, 'native_components' => [
                    'authority' => 'native_cart_quote', 'service_fee_units' => (string) $c,
                    'subtotal_units' => (string) $subtotal, 'fee_mode' => Utility::isPercentageServiceFee() ? 'percentage' : 'fixed',
                    'native_rate' => '1', 'adjustment_units' => '0',
                    'price_beneficiary' => 'vendor', 'listing_discount_sponsor' => 'vendor',
                    'product_tax_units' => '0', 'shop_tax_units' => '0',
                    'items' => $items[$shopId],
                ],
                'policy_key' => 'platform_held_first_commission',
            ];
        }
        return $quotes;
    }
}