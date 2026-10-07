<?php
declare(strict_types=1);

namespace App\Repositories\OrderRepository\DeliveryMan;

use App\Models\Language;
use App\Models\Order;
use App\Repositories\CoreRepository;
use App\Services\DeliveryDriver\DriverMembership;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Order::class;
    }

    /**
     * @param array $data
     * @return LengthAwarePaginator
     */
    public function paginate(array $data = []): LengthAwarePaginator
    {
        return DriverMembership::constrainAssignedOrders(
            $this->model()->newQuery(), (int) auth('sanctum')->id()
        )
            ->filter($data)
            ->withCount('orderDetails')
            ->withSum('children', 'total_price')
            ->with([
                'currency',
                'transactions.paymentSystem',
                'transactions.children',
                'user',
                'myAddress',
                'shop.translation' => fn($q) => $q
                    ->where('locale', $this->language),
                'deliveryman',
            ])
            ->when(data_get($data, 'shop_ids'), function ($q, $shopIds) {
                $q->whereIn('shop_id', is_array($shopIds) ? $shopIds : []);
            })
            ->paginate($data['perPage'] ?? 10);
    }

    /**
     * @param int|null $id
     * @return Builder|array|Collection|Model|null
     */
    public function show(?int $id): Builder|array|Collection|Model|null
    {
        /** @var Order $order */
        $order = $this->model();

        return DriverMembership::constrainAssignedOrders($order->newQuery(), (int) auth('sanctum')->id())
            ->with((new \App\Repositories\OrderRepository\OrderRepository)->getWith())
            ->find($id);
    }
}
