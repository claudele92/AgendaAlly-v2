<?php
declare(strict_types=1);

namespace App\Services\ShopLocationService;

use App\Helpers\ResponseError;
use App\Models\ShopLocation;
use App\Services\CoreService;
use Throwable;

class ShopLocationService extends CoreService
{
    protected function getModelClass(): string
    {
        return ShopLocation::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $conflict = $this->conflictingCountryLocation(
            (int) data_get($data, 'shop_id'),
            (int) (data_get($data, 'type') ?? ShopLocation::PRODUCT),
            data_get($data, 'country_id'),
        );

        if ($conflict) {
            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_259,
                'message'   => __('errors.' . ResponseError::ERROR_259),
            ];
        }

        try {
            $result = $this->model()->updateOrCreate($data);

            return [
                'status'    => true,
                'message'   => ResponseError::NO_ERROR,
                'data'      => $result
            ];
        } catch (Throwable $e) {
            $this->error($e);

            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_501,
                'message'   => $e->getMessage()
            ];
        }
    }

    /**
     * @param ShopLocation $shopLocation
     * @param array $data
     * @return array
     */
    public function update(ShopLocation $shopLocation, array $data): array
    {
        $conflict = $this->conflictingCountryLocation(
            (int) (data_get($data, 'shop_id') ?? $shopLocation->shop_id),
            (int) (data_get($data, 'type') ?? $shopLocation->type),
            data_get($data, 'country_id', $shopLocation->country_id),
            $shopLocation->id,
        );

        if ($conflict) {
            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_259,
                'message'   => __('errors.' . ResponseError::ERROR_259),
            ];
        }

        try {
            $shopLocation->update($data);

            return [
                'status'    => true,
                'message'   => ResponseError::NO_ERROR,
                'data'      => $shopLocation
            ];
        } catch (Throwable $e) {
            $this->error($e);

            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_502,
                'message'   => $e->getMessage()
            ];
        }
    }

    /**
     * A shop's locations of the same type (SERVICE or PRODUCT) must all be
     * in the same country — checkoutCountry() on the Shop model resolves
     * currency/country by type alone (via serviceLocation()/productLocation(),
     * both HasOne), with no location-instance awareness. If a shop had two
     * same-type locations in different countries, whichever one Eloquent's
     * HasOne happened to pick would silently decide the currency for every
     * checkout of that type. This keeps that "arbitrary pick" behavior safe
     * by construction rather than merely assumed-safe.
     *
     * A null country_id (on either the candidate or an existing sibling) is
     * never a conflict - the column is nullable, and null means "not set
     * yet", not a country to enforce.
     */
    private function conflictingCountryLocation(int $shopId, int $type, mixed $countryId, ?int $excludeId = null): ?ShopLocation
    {
        if (!$countryId) {
            return null;
        }

        return ShopLocation::query()
            ->where('shop_id', $shopId)
            ->where('type', $type)
            ->where('country_id', '!=', $countryId)
            ->whereNotNull('country_id')
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->first();
    }

    public function delete(?array $ids = [], ?int $shopId = null): array
    {
        $models = $this->model()->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->whereIn('id', is_array($ids) ? $ids : [])
            ->get();

        foreach ($models as $model) {
            $model->delete();
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }
}
