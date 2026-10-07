<?php
declare(strict_types=1);

namespace App\Repositories\SubscriptionRepository;

use App\Models\Subscription;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Schema;

class SubscriptionRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Subscription::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter): LengthAwarePaginator
    {
        $column = $filter['column'] ?? 'id';
        $sort   = $filter['sort'] ?? 'desc';

        if ($column !== 'id') {
            $column = Schema::hasColumn('subscriptions', $column) ? $column : 'id';
        }

        return Subscription::query()
            ->when(isset($filter['active']), fn($q) => $q->where('active', $filter['active']))
            ->orderBy($column, $sort)
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * @param Subscription $subscription
     * @return Subscription
     */
    public function show(Subscription $subscription): Subscription
    {
        return $subscription;
    }
}
