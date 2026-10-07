<?php
declare(strict_types=1);

namespace App\Services\OrderService;

use App\Models\Order;
use App\Models\User;
use App\Services\DeliveryDriver\DriverMembership;

/** Actor/target authority, deliberately separate from financial finality. */
final class OrderFulfillmentAuthority
{
    public static function allows(Order $order): bool
    {
        /** @var User|null $actor */
        $actor = auth('sanctum')->user();
        if (!$actor) {
            return false;
        }

        // Resolve the current target through the same native country scope.
        $target = Order::query()->find($order->id);
        if (!$target) {
            return false;
        }

        if ($actor->hasRole(['admin', 'manager'])) {
            return true;
        }

        if ($actor->hasRole(['seller', 'moderator', 'shop_manager'])) {
            $shop = $actor->shop ?? $actor->moderatorShop;
            return $shop && (int) $shop->id === (int) $target->shop_id
                && $actor->hasShopPermission((int) $target->shop_id, 'orders.manage');
        }

        return $actor->hasRole('deliveryman')
            && DriverMembership::constrainAssignedOrders(
                Order::query(), (int) $actor->id
            )->whereKey($target->id)->exists();
    }
}