<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\Shop\StoreRequest;
use App\Http\Resources\ShopResource;
use App\Models\Language;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository\ShopRepository;
use App\Services\ShopServices\ShopActivityService;
use App\Services\ShopServices\ShopService;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class ShopController extends SellerBaseController
{

    public function __construct(private ShopRepository $shopRepository, private ShopService $shopService)
    {
        parent::__construct();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function shopCreate(StoreRequest $request): JsonResponse
    {
        $result = $this->shopService->create($request->merge(['user_id' => auth('sanctum')->id()])->all());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        /** @var User $user */
        $user = auth('sanctum')->user();

        $user?->invitations()->delete();

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            ShopResource::make(data_get($result, 'data'))
        );

    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function shopShow(): JsonResponse
    {
        if (!$this->shop?->uuid) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_204, 'http' => 401]);
        }

        

        $scope = $this->shopLocationBranchScope();
        $shop = $this->shopRepository->shopDetails(
            $this->shop->uuid,
            [],
            $scope['unrestricted'] ? null : $scope['location_ids']
        );

        if (empty($shop)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        /** @var Shop $shop */
        try {
            DB::table('shop_subscriptions')
                ->where('shop_id', $shop->id)
                ->whereDate('expired_at', '<', now())
                ->delete();
        } catch (Throwable) {}

        $shop = $shop->load([
            'translations',
            'seller.wallet',
            'subscription' => fn ($q) => $q->where('expired_at', '>=', now())->where('active', true),
            'subscription.subscription',
            'tags.translation' => fn ($q) => $q->where('locale', $this->language),
        ]);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ShopResource::make($shop)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function shopUpdate(StoreRequest $request): JsonResponse
    {
        $result = $this->shopService->update($this->shop->uuid, $request->all());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            ShopResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function setWorkingStatus(): JsonResponse
    {
        (new ShopActivityService)->changeOpenStatus($this->shop->uuid);

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            ShopResource::make($this->shop)
        );
    }

    /**
     * Explicit desired-state collection choice. Omitted state retains the
     * legacy toggle contract; neither choice activates an external provider.
     */
    public function setCollectViaPlatform(\Illuminate\Http\Request $request): JsonResponse
    {
        $input = $request->validate([
            'collection_mode' => 'nullable|string|in:platform,vendor_direct',
            'collect_via_platform' => 'nullable|boolean',
            'location_type' => 'nullable|integer|in:1,2',
        ]);
        if (!auth('sanctum')->user()?->hasShopPermission((int) $this->shop->id, 'payments.gateways.manage')) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_403]);
        }
        $desired = isset($input['collection_mode']) ? $input['collection_mode'] === 'platform'
            : (isset($input['collect_via_platform']) ? (bool) $input['collect_via_platform'] : !$this->shop->collect_via_platform);
        if (isset($input['collection_mode'], $input['collect_via_platform'])
            && $desired !== (bool) $input['collect_via_platform']) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_400, 'message' => 'Conflicting collection choices']);
        }
        if (!$desired) {
            $policy = (new \App\Services\PaymentEligibility\PaymentEligibilityService)->collectionPolicy(
                // The persisted setting is shop-wide: a caller cannot unlock it
                // by selecting only the easier transaction domain.
                $this->shop, null
            );
            if (!$policy['vendor_direct_available']) {
                return $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => 'No supported merchant gateway is available for vendor-direct configuration in this business context',
                ]);
            }
        }
        (new ShopActivityService)->changeCollectViaPlatformStatus($this->shop->uuid, $desired);

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            ShopResource::make($this->shop->fresh())
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return void
     */
    public function destroy(int $id): void
    {
        //
    }

}
