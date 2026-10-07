<?php

declare(strict_types=1);

namespace App\Repositories\ShopRoleRepository;

use App\Models\ShopRole;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShopRoleRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return ShopRole::class;
    }

    public function paginate(int $shopId, array $filter): LengthAwarePaginator
    {
        return ShopRole::query()
            ->where('shop_id', $shopId)
            ->with('permissions')
            ->withCount('invitations')
            ->orderBy('id', $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }

    public function show(int $shopId, int $id): ?ShopRole
    {
        return ShopRole::query()
            ->where('shop_id', $shopId)
            ->with('permissions')
            ->withCount('invitations')
            ->find($id);
    }
}
