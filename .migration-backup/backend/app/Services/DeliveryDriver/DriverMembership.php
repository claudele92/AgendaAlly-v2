<?php
declare(strict_types=1);

namespace App\Services\DeliveryDriver;

use App\Models\Invitation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * The existing invitation is the shop relationship, not a second identity.
 * Conflicting duplicate driver relationships require explicit reconciliation.
 */
final class DriverMembership
{
    public static function members(int $shopId): Builder
    {
        return User::query()
            ->whereHas('roles', fn (Builder $q) => $q->where('name', 'deliveryman'))
            ->whereHas('invitations', fn (Builder $q) => $q
                ->where('shop_id', $shopId), '=', 1)
            ->whereHas('invitations', fn (Builder $q) => $q
                ->where('shop_id', $shopId)->where('role', 'deliveryman'));
    }

    public static function eligibleUsers(int $shopId): Builder
    {
        return self::members($shopId)
            ->where('users.active', true)
            ->whereHas('invitations', fn (Builder $q) => $q
                ->where('shop_id', $shopId)
                ->where('role', 'deliveryman')
                ->where('status', Invitation::ACCEPTED)
                ->where('driver_active', true));
    }

    public static function isEligible(int $shopId, int $userId): bool
    {
        return self::eligibleUsers($shopId)->whereKey($userId)->exists();
    }

    public static function relationship(int $shopId, int $userId): ?Invitation
    {
        $relationships = Invitation::query()
            ->where('shop_id', $shopId)->where('user_id', $userId)
            ->limit(2)->get();

        return $relationships->count() === 1 && $relationships->first()->role === 'deliveryman'
            ? $relationships->first() : null;
    }

    public static function eligibleShopIds(int $userId): Builder
    {
        return Invitation::query()
            ->select('invitations.shop_id')
            ->where('invitations.user_id', $userId)
            ->where('invitations.role', 'deliveryman')
            ->where('invitations.status', Invitation::ACCEPTED)
            ->where('invitations.driver_active', true)
            ->whereHas('user', fn (Builder $q) => $q
                ->where('active', true)
                ->whereHas('roles', fn (Builder $roles) => $roles->where('name', 'deliveryman')))
            ->whereRaw('(SELECT COUNT(*) FROM invitations AS duplicate_memberships
                WHERE duplicate_memberships.shop_id = invitations.shop_id
                AND duplicate_memberships.user_id = invitations.user_id) = 1');
    }

    public static function constrainAssignedOrders(Builder $query, int $userId): Builder
    {
        return $query
            ->where('deliveryman_id', $userId)
            ->where('delivery_type', Order::DELIVERY)
            ->whereIn('shop_id', self::eligibleShopIds($userId));
    }
}