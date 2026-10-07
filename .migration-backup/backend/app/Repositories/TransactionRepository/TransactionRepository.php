<?php
declare(strict_types=1);

namespace App\Repositories\TransactionRepository;

use App\Models\Booking;
use App\Models\Order;
use App\Models\PaymentToPartner;
use App\Models\ShopAdsPackage;
use App\Models\ShopSubscription;
use App\Models\Transaction;
use App\Models\UserGiftCart;
use App\Models\UserMemberShip;
use App\Repositories\CoreRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Schema;

class TransactionRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Transaction::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter): LengthAwarePaginator
    {

        $column = $filter['column'] ?? 'id';

        if ($column !== 'id') {
            $column = Schema::hasColumn('transactions', $column) ? $column : 'id';
        }

        return $this->model()
            ->filter($filter)
            ->with([
                'payable',
                'user',
                'paymentSystem'
            ])
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * @param int $id
     * @param int|null $shopId
     * @return Transaction|null
     */
    public function show(int $id, ?int $shopId = null): ?Transaction
    {

        return $this->model()
            ->with([
                'payable',
                'user',
                'paymentSystem'
            ])
            ->when($shopId, function (Builder $query, $shopId) {
                $query
                    ->whereHasMorph('payable', [
                        Booking::class,
                        Order::class,
                        ShopAdsPackage::class,
                        ShopSubscription::class,
                        PaymentToPartner::class,
                        UserGiftCart::class,
                        UserMemberShip::class,
                    ], function ($query, $type) use ($shopId) {
                        if (in_array($type, [Order::class, Booking::class, ShopAdsPackage::class,  ShopSubscription::class, ])) {
                            $query->where('shop_id', $shopId);
                        } else if ($type == PaymentToPartner::class) {
                            $query->whereHas('model', fn($q) => $q->where('shop_id', $shopId));
                        } else if ($type == UserGiftCart::class) {
                            $query->whereHas('giftCart', fn($q) => $q->where('shop_id', $shopId));
                        } else if ($type == UserMemberShip::class) {
                            $query->whereHas('memberShip', fn($q) => $q->where('shop_id', $shopId));
                        }
                    });
            })
            ->find($id);
    }
}
