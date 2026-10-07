<?php
declare(strict_types=1);

namespace App\Repositories\ShopPaymentRepository;

use App\Models\Payment;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPayment;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Schema;

class ShopPaymentRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return ShopPayment::class;
    }

    /**
     * @param array $filter
     * @return mixed
     */
    public function list(array $filter): mixed
    {
        /** @var ShopPayment $shopPayment */
        $shopPayment = $this->model();

        return $shopPayment
            ->filter($filter)
            ->with('payment')
            ->get();
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter): LengthAwarePaginator
    {
        /** @var ShopPayment $shopPayment */
        $shopPayment = $this->model();

        return $shopPayment->filter($filter)
            ->with('payment')
            ->paginate(data_get($filter, 'perPage'));
    }

    /**
     * Gateways the shop can still add — active platform-wide, not yet
     * configured for this shop, AND actually offered in the shop's own
     * country (see Country::activePaymentIds(), which always includes
     * cash/wallet regardless of country_payments). No resolvable country
     * means no gateways, matching the fail-closed rule used elsewhere for
     * country-scoped shop data.
     *
     * @param Shop $shop
     * @return Collection
     */
    public function shopNonExist(Shop $shop, ?int $locationType = null): Collection
    {
        $policy = (new \App\Services\PaymentEligibility\PaymentEligibilityService)->vendorPolicy($shop, $locationType);
        $ids = collect($policy['methods'])->where('available_for_configuration', true)->pluck('id');
        return Payment::whereIn('id', $ids)
            ->whereDoesntHave('shopPayment', fn ($q) => $q->where('shop_id', $shop->id) )
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param ShopPayment $shopPayment
     * @return ShopPayment
     */
    public function show(ShopPayment $shopPayment): ShopPayment
    {
        return $shopPayment
            ->loadMissing('payment');
    }
}
