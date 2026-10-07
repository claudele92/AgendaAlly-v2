<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopLocation;
use Illuminate\Support\Str;

/** Uses completed trusted native calculations, not presentation seller accessors. */
final class NativeQuoteFactory
{
    public function payable(Booking|Order $model, string $checkout): array
    {
        $shop = Shop::findOrFail($model->shop_id);
        if ((string) $model->rate !== '1') {
            throw new \DomainException('Native non-unit Wallet/charge currency mapping requires source-proven normalization.');
        }
        $country = $shop->checkoutCountry($model instanceof Booking ? ShopLocation::SERVICE : ShopLocation::PRODUCT);
        if (!$country || (int) $country->currency_id !== (int) $model->currency_id) {
            throw new \DomainException('Unproven native business currency/country.');
        }
        $g = ExactMoney::units((string) $model->getRawOriginal('total_price'));
        $c = ExactMoney::units((string) ($model->getRawOriginal('service_fee') ?? '0'));
        $commissionComponent = ExactMoney::units((string) ($model->getRawOriginal('commission_fee') ?? '0'));
        $coupon = ExactMoney::units((string) ($model->getRawOriginal('coupon_price') ?? '0'));
        $other = [$commissionComponent, $coupon];
        if ($model instanceof Order) {
            $other[] = ExactMoney::units((string) ($model->getRawOriginal('total_tax') ?? '0'));
            $other[] = ExactMoney::units((string) $shop->tax);
            if ($model->orderDetails()->where('tax', '!=', 0)->exists()) {
                throw new \DomainException('Product tax disposition is not proven.');
            }
            $other[] = ExactMoney::units((string) ($model->getRawOriginal('tips') ?? '0'));
            $other[] = ExactMoney::units((string) ($model->getRawOriginal('delivery_fee') ?? '0'));
        } else {
            $other[] = ExactMoney::units((string) ($model->getRawOriginal('gift_cart_price') ?? '0'));
            if ($model->getRawOriginal('user_gift_cart_id') || $model->getRawOriginal('user_member_ship_id')) {
                throw new \DomainException('Benefit sponsorship requires an original economic funding contract.');
            }
        }
        $a = ExactMoney::sum($other);
        if ($a !== 0) {
            // Approved policy explicitly requires a proven native adjustment
            // beneficiary/responsibility. Naming the component is not that proof.
            throw new \DomainException('Native non-service-fee adjustment disposition requires a proven frozen contract.');
        }
        if ($c > $g) {
            throw new \DomainException('Native fee exceeds original economic gross.');
        }
        $type = $model instanceof Booking ? 'booking' : 'order';
        if (\Illuminate\Support\Facades\Schema::hasTable('points')) {
            $cashback = $model instanceof Booking ? \App\Models\Point::getBookingActualPoint($model->total_price)
                : \App\Models\Point::getActualPoint($model->total_price);
            if ($cashback > 0) throw new \DomainException('Cashback sponsorship requires a proven frozen economic contract.');
        }
        return [
            'checkout_key' => $checkout, 'origin_type' => $type, 'origin_id' => (int) $model->id,
            'shop_id' => (int) $shop->id, 'vendor_user_id' => (int) $shop->user_id,
            'payer_user_id' => $model->user_id === null ? null : (int) $model->user_id,
            'local_client_id' => $model instanceof Booking && $model->local_client_id !== null ? (int) $model->local_client_id : null,
            'country_id' => (int) $country->id, 'currency_id' => (int) $model->currency_id,
            'currency_code' => strtoupper($country->currency->title), 'money_scale' => 2,
            'purpose' => 'base', 'obligation_key' => 'base', 'payable_type' => $type, 'payable_id' => (int) $model->id,
            'gross_amount' => $g, 'commission_amount' => $c, 'vendor_entitlement_amount' => $g - $c,
            'adjustment_amount' => 0, 'native_components' => [
                'authority' => 'native_creation_quote',
                'service_fee' => ['units' => (string) $c, 'beneficiary' => 'platform', 'source' => 'native_service_fee'],
                'commission_component_units' => (string) $commissionComponent, 'coupon_units' => (string) $coupon,
                'other_adjustment_units' => '0',
                'native_base_price_units' => (string) ExactMoney::units((string) ($model->getRawOriginal('price') ?? '0')),
                'native_extra_price_units' => $model instanceof Booking
                    ? (string) ExactMoney::units((string) ($model->getRawOriginal('extra_price') ?? '0')) : '0',
                'price_and_service_extra_beneficiary' => 'vendor',
                'listing_discount_sponsor' => 'vendor',
            ],
            'policy_key' => 'platform_held_first_commission',
        ];
    }

    public function commitNew(Booking|Order $model, ?string $checkout = null): int
    {
        if (!$model->wasRecentlyCreated) {
            throw new \DomainException('Legacy/current mutable commerce cannot establish original payment authority.');
        }
        return (new AllocationWriter)->commit($this->payable($model, $checkout ?? (string) Str::uuid()));
    }
}