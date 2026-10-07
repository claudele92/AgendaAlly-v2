<?php

declare(strict_types=1);

namespace App\Repositories\CountryInviteRepository;

use App\Models\CountryInvitation;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CountryInviteRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return CountryInvitation::class;
    }

    public function paginate(array $filter): LengthAwarePaginator
    {
        $column = $filter['column'] ?? 'id';

        return CountryInvitation::query()
            ->filter($filter)
            ->with([
                'user' => fn ($q) => $q->select('id', 'firstname', 'lastname', 'img'),
                'countryRole',
            ])
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }
}
