<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\ShopRole\StoreRequest;
use App\Http\Resources\ShopPermissionResource;
use App\Http\Resources\ShopRoleResource;
use App\Models\ShopPermission;
use App\Models\ShopRole;
use App\Repositories\ShopRoleRepository\ShopRoleRepository;
use App\Services\ShopRoleService\ShopRoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoleController extends SellerBaseController
{
    public function __construct(
        private ShopRoleRepository $repository,
        private ShopRoleService $service
    )
    {
        parent::__construct();
    }

    /**
     * The full permission catalog staff roles can be built from.
     *
     * @return AnonymousResourceCollection
     */
    public function permissions(): AnonymousResourceCollection
    {
        return ShopPermissionResource::collection(ShopPermission::query()->orderBy('group')->get());
    }

    /**
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $roles = $this->repository->paginate($this->shop->id, $request->all());

        return ShopRoleResource::collection($roles);
    }

    /**
     * @param ShopRole $role
     * @return JsonResponse
     */
    public function show(ShopRole $role): JsonResponse
    {
        $role = $this->repository->show($this->shop->id, $role->id);

        if (!$role) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ShopRoleResource::make($role)
        );
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $result = $this->service->create($this->shop->id, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            ShopRoleResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @param ShopRole $role
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function update(ShopRole $role, StoreRequest $request): JsonResponse
    {
        if ($role->shop_id !== $this->shop->id) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $result = $this->service->update($role, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            ShopRoleResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @param ShopRole $role
     * @return JsonResponse
     */
    public function destroy(ShopRole $role): JsonResponse
    {
        if ($role->shop_id !== $this->shop->id) {
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
