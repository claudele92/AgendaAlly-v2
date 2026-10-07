<?php

declare(strict_types=1);

namespace App\Repositories\CountryAdminRepository;

use App\Models\CountryAdmin;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CountryAdminRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return CountryAdmin::class;
    }

    public function paginate(array $filter): LengthAwarePaginator
    {
        return CountryAdmin::query()
            ->with(['user', 'country.translation', 'createdBy'])
            ->orderBy('id', $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }

    public function show(int $id): ?CountryAdmin
    {
        return CountryAdmin::query()
            ->with(['user', 'country.translation', 'createdBy'])
            ->find($id);
    }
}
