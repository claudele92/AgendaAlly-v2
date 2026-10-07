<?php

declare(strict_types=1);

namespace App\Repositories\CountryRoleRepository;

use App\Models\CountryRole;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CountryRoleRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return CountryRole::class;
    }

    public function paginate(int $countryId, array $filter): LengthAwarePaginator
    {
        return CountryRole::query()
            ->where('country_id', $countryId)
            ->with('permissions')
            ->withCount('invitations')
            ->orderBy('id', $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }

    public function show(int $countryId, int $id): ?CountryRole
    {
        return CountryRole::query()
            ->where('country_id', $countryId)
            ->with('permissions')
            ->withCount('invitations')
            ->find($id);
    }
}
