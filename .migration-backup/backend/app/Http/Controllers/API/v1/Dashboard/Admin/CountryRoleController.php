<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\CountryRole\StoreRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\CountryPermissionResource;
use App\Http\Resources\CountryRoleResource;
use App\Models\CountryPermission;
use App\Models\CountryRole;
use App\Repositories\CountryRoleRepository\CountryRoleRepository;
use App\Services\CountryRoleService\CountryRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CountryRoleController extends CountryBaseController
{
    public function __construct(
        private CountryRoleRepository $repository,
        private CountryRoleService $service
    )
    {
        parent::__construct();
    }

    /**
     * The full permission catalog country roles can be built from.
     */
    public function permissions(): AnonymousResourceCollection
    {
        return CountryPermissionResource::collection(CountryPermission::query()->orderBy('group')->get());
    }

    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection|JsonResponse
    {
        if (!$this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $roles = $this->repository->paginate($this->countryId, $request->all());

        return CountryRoleResource::collection($roles);
    }

    public function show(CountryRole $role): JsonResponse
    {
        if (!$this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $role = $this->repository->show($this->countryId, $role->id);

        if (!$role) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            CountryRoleResource::make($role)
        );
    }

    public function store(StoreRequest $request): JsonResponse
    {
        if (!$this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $result = $this->service->create($this->countryId, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            CountryRoleResource::make(data_get($result, 'data'))
        );
    }

    public function update(CountryRole $role, StoreRequest $request): JsonResponse
    {
        if ($role->country_id !== $this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $result = $this->service->update($role, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            CountryRoleResource::make(data_get($result, 'data'))
        );
    }

    public function destroy(CountryRole $role): JsonResponse
    {
        if ($role->country_id !== $this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $result = $this->service->deleteRole($role);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }
}
