<?php

declare(strict_types=1);

namespace App\Repositories\PlatformPaymentConfigRepository;

use App\Models\PlatformPaymentConfig;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PlatformPaymentConfigRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return PlatformPaymentConfig::class;
    }

    public function paginate(array $filter): LengthAwarePaginator
    {
        /** @var PlatformPaymentConfig $config */
        $config = $this->model();

        return $config
            ->when(data_get($filter, 'country_id'), fn ($q, $countryId) => $q->where('country_id', $countryId))
            ->when(data_get($filter, 'payment_id'), fn ($q, $paymentId) => $q->where('payment_id', $paymentId))
            ->with(['country.translation', 'payment'])
            ->orderBy('id', $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }

    public function show(PlatformPaymentConfig $config): PlatformPaymentConfig
    {
        return $config->loadMissing(['country.translation', 'payment']);
    }
}
