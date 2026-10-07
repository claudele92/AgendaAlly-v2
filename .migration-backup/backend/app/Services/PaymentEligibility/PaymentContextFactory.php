<?php
declare(strict_types=1);

namespace App\Services\PaymentEligibility;

use App\Models\Booking;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopLocation;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Resolves metadata from business locations and owned payables, never client
 * market country or display currency. No amounts, credentials or FX mutation.
 */
final class PaymentContextFactory
{
    public function shop(Shop $shop, ?int $locationType = null, ?int $requestedCurrency = null): array
    {
        if ($locationType === null) {
            $product = $shop->checkoutCountry(ShopLocation::PRODUCT);
            $service = $shop->checkoutCountry(ShopLocation::SERVICE);
            if ($product && $service && $product->id !== $service->id) {
                return $this->invalid('business_location_type_required', $requestedCurrency);
            }
            $locationType = $product ? ShopLocation::PRODUCT : ShopLocation::SERVICE;
        }
        if (!in_array($locationType, ShopLocation::TYPES, true)) {
            return $this->invalid('business_location_type_required', $requestedCurrency);
        }
        $country = $shop->checkoutCountry($locationType);
        if (!$country) {
            return $this->invalid('business_country_currency_missing', $requestedCurrency);
        }
        $currency = $country->currency;
        return [
            'valid' => true, 'reason' => null,
            'shop_ids' => [(int) $shop->id], 'country_ids' => [(int) $country->id],
            'transaction_type' => $locationType === ShopLocation::SERVICE ? 'booking' : 'product',
            'transaction_currency_id' => (int) $currency->id,
            'transaction_currency' => strtoupper((string) $currency->title),
            'requested_currency_id' => $requestedCurrency,
            'display_currency_is_charge' => !$requestedCurrency || $requestedCurrency === (int) $currency->id,
            'collection_mode' => $shop->collect_via_platform ? 'platform' : 'vendor_direct',
            'location_type' => $locationType,
            'country_policy_mode' => (string) config('payment_eligibility.internal_country_policy', 'legacy_compatibility'),
        ];
    }

    public function target(string $key, int $id, ?int $requestedCurrency = null): array
    {
        $actorId = auth('sanctum')->id();
        if (!$actorId) {
            throw new AccessDeniedHttpException('Authenticated payment owner is required');
        }
        $target = match ($key) {
            'booking_id' => Booking::query()->whereKey($id)->where('user_id', $actorId)->first(),
            'cart_id' => Cart::query()->whereKey($id)->where('owner_id', $actorId)->first(),
            'order_id' => Order::query()->whereKey($id)->where('user_id', $actorId)->first(),
            default => null,
        };
        if (!$target) {
            throw new AccessDeniedHttpException('Payment target is unavailable to this account');
        }
        if ($key === 'cart_id') {
            $shopIds = CartDetail::query()
                ->whereHas('userCart', fn ($query) => $query->where('cart_id', $id))
                ->distinct()->pluck('shop_id');
            if ($shopIds->isEmpty()) {
                return $this->invalid('cart_empty', $requestedCurrency);
            }
            $contexts = $shopIds->map(function ($shopId) use ($requestedCurrency) {
                $shop = Shop::query()->find($shopId);
                return $shop ? $this->shop($shop, ShopLocation::PRODUCT, $requestedCurrency)
                    : $this->invalid('business_shop_missing', $requestedCurrency);
            });
            if ($contexts->contains(fn ($context) => !$context['valid'])) {
                return $this->invalid('business_country_currency_missing', $requestedCurrency);
            }
            if ($contexts->pluck('transaction_currency_id')->unique()->count() !== 1) {
                return $this->invalid('multi_currency_cart_unsupported', $requestedCurrency);
            }
            $context = $contexts->first();
            $context['shop_ids'] = $shopIds->map(fn ($value) => (int) $value)->values()->all();
            $context['country_ids'] = $contexts->pluck('country_ids')->flatten()->unique()->values()->all();
            $modes = $contexts->pluck('collection_mode')->unique();
            $context['collection_mode'] = $modes->count() === 1 ? $modes->first() : 'mixed';
        } else {
            $shop = $target->shop;
            if (!$shop) {
                return $this->invalid('business_shop_missing', $requestedCurrency);
            }
            $context = $this->shop($shop, $key === 'booking_id'
                ? ShopLocation::SERVICE : ShopLocation::PRODUCT, $requestedCurrency);
            if ($key === 'booking_id') {
                // The existing booking snapshot wins over the shop's live toggle.
                $context['collection_mode'] = $target->collect_via_platform ? 'platform' : 'vendor_direct';
            }
        }
        if ($context['valid'] && (int) $target->currency_id !== $context['transaction_currency_id']) {
            return $this->invalid('transaction_currency_mismatch', $requestedCurrency);
        }
        return $context;
    }

    public function request(array $input): ?array
    {
        $keys = array_filter(['booking_id', 'cart_id', 'order_id'], fn ($key) => isset($input[$key]));
        $requested = isset($input['currency_id']) ? (int) $input['currency_id'] : null;
        if (count($keys) > 1) {
            return $this->invalid('exactly_one_payment_target_required', $requested);
        }
        if ($keys) {
            $key = reset($keys);
            return $this->target($key, (int) $input[$key], $requested);
        }
        if (isset($input['shop_id'])) {
            $shop = Shop::query()->find((int) $input['shop_id']);
            return $shop ? $this->shop($shop, isset($input['location_type'])
                ? (int) $input['location_type'] : null, $requested)
                : $this->invalid('business_shop_missing', $requested);
        }
        return null; // Compatibility catalog, never an initiation authorization.
    }

    public function invalid(string $reason, ?int $requested = null): array
    {
        return ['valid' => false, 'reason' => $reason, 'shop_ids' => [], 'country_ids' => [],
            'transaction_type' => null, 'transaction_currency_id' => null,
            'transaction_currency' => null, 'requested_currency_id' => $requested,
            'display_currency_is_charge' => false, 'collection_mode' => null];
    }
}