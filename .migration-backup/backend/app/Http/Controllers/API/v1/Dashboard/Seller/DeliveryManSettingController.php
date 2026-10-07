<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\DeliveryManSetting\AdminRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\DeliveryManSettingResource;
use App\Models\DeliveryManSetting;
use App\Models\Invitation;
use App\Models\User;
use App\Repositories\DeliveryManSettingRepository\DeliveryManSettingRepository;
use App\Services\DeliveryDriver\DriverMembership;
use App\Services\DeliveryManSettingService\DeliveryManSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeliveryManSettingController extends SellerBaseController
{

    public function __construct(
        private DeliveryManSettingRepository $repository,
        private DeliveryManSettingService $service
    )
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $filter = $request->all();
        unset($filter['shop_id'], $filter['user_id'], $filter['role']);

        $deliveryMans = DeliveryManSetting::query()
            ->filter($filter)
            ->whereIn(
                'user_id',
                DriverMembership::eligibleUsers((int)$this->shop->id)->select('users.id')
            )
            ->with('deliveryman:id,uuid,active,firstname,lastname,phone,img')
            ->paginate(max(1, min((int)$request->input('perPage', 10), 100)));

        return DeliveryManSettingResource::collection($deliveryMans);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AdminRequest $request
     * @return JsonResponse
     */
    public function store(AdminRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var User $deliveryMan */
        $deliveryMan = User::find(data_get($validated, 'user_id'));

        if (!$deliveryMan
            || !$deliveryMan->hasRole('deliveryman')
            || !DriverMembership::isEligible((int)$this->shop->id, (int)$deliveryMan->id)
        ) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => 'You need change delivery man'
            ]);
        }

        // Settings are global per user. Do not let create/updateOrCreate turn
        // this path into a mutation of an existing shared settings row.
        if (DeliveryManSetting::where('user_id', $deliveryMan->id)->exists()) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => 'Delivery driver settings already exist'
            ]);
        }

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            DeliveryManSettingResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $deliverymanSetting = $this->repository->detail($id);
        $deliveryMan = $deliverymanSetting?->deliveryman;

        if (empty($deliverymanSetting)
            || !$deliveryMan
            || !$deliveryMan->hasRole('deliveryman')
            || !DriverMembership::isEligible((int)$this->shop->id, (int)$deliveryMan->id)
        ) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            DeliveryManSettingResource::make($deliverymanSetting)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param AdminRequest $request
     * @return JsonResponse
     */
    public function update(int $id, AdminRequest $request): JsonResponse
    {
        // Establish ownership from the persisted row before inspecting any
        // submitted replacement identity.
        $deliveryManSetting = DeliveryManSetting::with('deliveryman')->find($id);
        $deliveryMan = $deliveryManSetting?->deliveryman;

        if (empty($deliveryManSetting)
            || !$deliveryMan
            || !$deliveryMan->hasRole('deliveryman')
            || !DriverMembership::isEligible((int)$this->shop->id, (int)$deliveryMan->id)
        ) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $validated = $request->validated();

        if ((int)data_get($validated, 'user_id') !== (int)$deliveryManSetting->user_id) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => 'A delivery driver settings owner cannot be changed'
            ]);
        }

        /** @var DeliveryManSetting $deliveryManSetting */
        $result = $this->service->update($deliveryManSetting, $validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            DeliveryManSettingResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $ids = array_values(array_unique(array_map('intval', (array)$request->input('ids', []))));
        $settings = DeliveryManSetting::with('deliveryman')->whereIn('id', $ids)->get();

        if ($ids === [] || $settings->count() !== count($ids)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $authorizedUserIds = [];
        foreach ($settings as $setting) {
            $driver = $setting->deliveryman;
            if (!$driver
                || !$driver->hasRole('deliveryman')
                || !DriverMembership::isEligible((int)$this->shop->id, (int)$driver->id)
            ) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
            }

            $authorizedUserIds[] = (int)$driver->id;

            if (Invitation::where('user_id', $driver->id)
                ->where('role', 'deliveryman')
                ->where('shop_id', '!=', $this->shop->id)
                ->exists()
            ) {
                return $this->onErrorResponse([
                    'code'    => ResponseError::ERROR_400,
                    'message' => 'Shared delivery driver settings cannot be deleted'
                ]);
            }
        }

        $result = $this->service->destroy($ids, (int)$this->shop->id, $authorizedUserIds);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }

    public function destroyOne(int $id): JsonResponse
    {
        $setting = DeliveryManSetting::with('deliveryman')->find($id);
        $driver = $setting?->deliveryman;

        if (!$setting
            || !$driver
            || !$driver->hasRole('deliveryman')
            || !DriverMembership::isEligible((int)$this->shop->id, (int)$driver->id)
        ) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        if (Invitation::where('user_id', $driver->id)
            ->where('role', 'deliveryman')
            ->where('shop_id', '!=', $this->shop->id)
            ->exists()
        ) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => 'Shared delivery driver settings cannot be deleted'
            ]);
        }

        $result = $this->service->destroy([$id], (int)$this->shop->id, [(int)$driver->id]);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }

}
