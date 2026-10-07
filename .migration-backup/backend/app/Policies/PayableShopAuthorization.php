<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Booking;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopAdsPackage;
use App\Models\ShopSubscription;
use App\Models\User;
use App\Models\UserGiftCart;
use App\Models\UserMemberShip;
use Illuminate\Database\Eloquent\Model;

/**
 * Authorizes an already persisted payable against native Shop ownership/grants.
 * Role and transaction-state checks remain the responsibility of the caller.
 * No request-provided ownership identifier participates in this decision.
 */
final class PayableShopAuthorization
{
    public const TRANSACTION_PERMISSION = 'payments.payouts.manage';

    public function allows(User $actor, Model $payable, string $permission): bool
    {
        $shop = $this->shop($payable);
        if (!$shop || !$shop->user_id) {
            return false;
        }

        if ((int) $shop->user_id !== (int) $actor->id) {
            $invitation = $actor->invitations()
                ->where('shop_id', $shop->id)
                ->where('status', Invitation::ACCEPTED)
                ->whereNotNull('shop_role_id')
                ->with('shopRole')
                ->first();

            // A role belonging to another Shop cannot supply this Shop's grant.
            if (!$invitation?->shopRole
                || (int) $invitation->shopRole->shop_id !== (int) $shop->id) {
                return false;
            }
        }

        return $actor->hasShopPermission((int) $shop->id, $permission);
    }

    public function supportsStatusType(string $type, Model $payable): bool
    {
        $class = match ($type) {
            'order' => Order::class,
            'booking' => Booking::class,
            'subscription' => ShopSubscription::class,
            'ads', 'ads-package' => ShopAdsPackage::class,
            'member-ship' => UserMemberShip::class,
            'gift-cart' => UserGiftCart::class,
            default => null,
        };

        return $class !== null && get_class($payable) === $class;
    }

    private function shop(Model $payable): ?Shop
    {
        $shopId = match (get_class($payable)) {
            Order::class, Booking::class, ShopSubscription::class, ShopAdsPackage::class
                => $payable->shop_id,
            UserMemberShip::class => $payable->memberShip?->shop_id,
            UserGiftCart::class => $payable->giftCart?->shop_id,
            default => null,
        };

        if (!$shopId) {
            return null;
        }

        if ($payable instanceof Booking && $payable->service_id) {
            // Booking.shop_id is canonical, but a conflicting/deleted linked
            // Service must not turn inconsistent data into financial authority.
            if (!$payable->service
                || (int) $payable->service->shop_id !== (int) $shopId) {
                return null;
            }
        }

        return Shop::query()->find((int) $shopId);
    }
}