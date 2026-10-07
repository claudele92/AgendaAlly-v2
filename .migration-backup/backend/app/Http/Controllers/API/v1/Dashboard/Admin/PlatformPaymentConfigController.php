<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\PlatformPaymentConfig\StoreRequest;
use App\Http\Requests\PlatformPaymentConfig\UpdateRequest;
use App\Http\Resources\PlatformPaymentConfigResource;
use App\Models\PlatformPaymentConfig;
use App\Models\User;
use App\Repositories\PlatformPaymentConfigRepository\PlatformPaymentConfigRepository;
use App\Services\PlatformPaymentConfigService\PlatformPaymentConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * The platform's own Orange Money/MTN Mobile Money merchant config —
 * platform revenue, not shop revenue — so this is superadmin-only
 * regardless of the country-admin hierarchy: a country admin manages
 * their own country's shops/staff, never the platform's own credentials.
 * Deliberately not a CountryBaseController and not gated by
 * CheckCountryPermission, same reasoning as CountryAdminController.
 */
class PlatformPaymentConfigController extends AdminBaseController
{
    public function __construct(
        private PlatformPaymentConfigRepository $repository,
        private PlatformPaymentConfigService $service
    )
    {
        parent::__construct();

        /** @var User|null $user */
        $user = auth('sanctum')->user();

        if (!$user?->isSuperAdmin()) {
            abort(403, __('errors.' . ResponseError::ERROR_101, locale: request('lang', 'en')));
        }
    }

    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        return PlatformPaymentConfigResource::collection($this->repository->paginate($request->all()));
    }

    public function show(PlatformPaymentConfig $platformPaymentConfig): JsonResponse
    {
        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            PlatformPaymentConfigResource::make($this->repository->show($platformPaymentConfig))
        );
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $validated               = $request->validated();
        $validated['created_by'] = auth('sanctum')->id();

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            PlatformPaymentConfigResource::make(data_get($result, 'data'))
        );
    }

    public function update(PlatformPaymentConfig $platformPaymentConfig, UpdateRequest $request): JsonResponse
    {
        $result = $this->service->update($request->validated(), $platformPaymentConfig);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            PlatformPaymentConfigResource::make(data_get($result, 'data'))
        );
    }

    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $this->service->delete($request->input('ids', []));

        return $this->successResponse(__('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language), []);
    }
}
