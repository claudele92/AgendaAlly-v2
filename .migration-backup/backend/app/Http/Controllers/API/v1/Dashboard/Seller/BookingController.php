<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\Booking\AdminUpdateRequest;
use App\Http\Requests\Booking\ExtraTimeRequest;
use App\Http\Requests\Booking\NotesUpdateRequest;
use App\Http\Requests\Booking\StatusUpdateRequest;
use App\Http\Requests\Booking\TimesUpdateRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Booking\SellerStoreRequest;
use App\Http\Requests\Booking\SellerUpdateRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Invitation;
use App\Models\SellerBookingClient;
use App\Models\ServiceMaster;
use App\Models\ShopLocation;
use App\Models\User;
use App\Policies\PayableShopAuthorization;
use App\Repositories\BookingRepository\BookingRepository;
use App\Services\BookingService\BookingService;
use App\Traits\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookingController extends SellerBaseController
{
    use Notification;

    public function __construct(private BookingRepository $repository, private BookingService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $filter = $this->applyBranchScope($request->merge(['shop_id' => $this->shop->id])->all());

        $models = $this->repository->paginate($filter);

        return BookingResource::collection($models);
    }

    /**
     * Display the specified resource.
     *
     * @param SellerStoreRequest $request
     * @return JsonResponse
     */
    public function store(SellerStoreRequest $request): JsonResponse
    {
        $this->assertRequestedShop($request);
        $validated = $request->validated();
        $validated['shop_id'] = $this->shop->id;
        $validated = $this->validateSellerBooking($validated);

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            BookingResource::collection(data_get($result, 'data'))
        );
    }

    /**
     * Display the specified resource.
     *
     * @param SellerStoreRequest $request
     * @return JsonResponse
     */
    public function calculate(SellerStoreRequest $request): JsonResponse
    {
        $this->assertRequestedShop($request);
        $validated = $request->validated();
        $validated['shop_id'] = $this->shop->id;
        $validated = $this->validateSellerBooking($validated);

        $result = $this->repository->calculate($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), $result);
    }

    /**
     * Display the specified resource.
     *
     * @param Booking $booking
     * @return JsonResponse
     */
    public function show(Booking $booking): JsonResponse
    {
        if ($booking->shop_id !== $this->shop->id || $this->bookingOutsideBranchScope($booking)) {
            return $this->onErrorResponse([
                'status'  => false,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language),
                'code'    => ResponseError::ERROR_404
            ]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            BookingResource::make($this->repository->show($booking))
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Booking $booking
     * @param SellerUpdateRequest $request
     * @return JsonResponse
     */
    public function update(Booking $booking, SellerUpdateRequest $request): JsonResponse
    {
        $this->assertRequestedShop($request);
        if ($booking->shop_id !== $this->shop->id || $this->bookingOutsideBranchScope($booking)) {
            return $this->onErrorResponse([
                'status'  => false,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language),
                'code'    => ResponseError::ERROR_404
            ]);
        }

        $validated = $request->validated();
        $validated = $this->validateSellerBooking($validated, $booking);

        if (array_key_exists('local_client_id', $validated) && $validated['local_client_id']) {
            $validated['user_id'] = null;
        } elseif (array_key_exists('user_id', $validated) && $validated['user_id']) {
            $validated['local_client_id'] = null;
        }

        $result = $this->service->update($booking, $validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            BookingResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function bookingsByParent(int $id): JsonResponse
    {
        $bookings = $this->repository->bookingsByParentId($id, shopId: $this->shop->id);

        $bookings = $bookings
            ?->reject(fn (Booking $booking) => $this->bookingOutsideBranchScope($booking))
            ?->values();

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            BookingResource::collection($bookings)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $id
     * @param StatusUpdateRequest $request
     * @return JsonResponse
     */
    public function statusUpdate(int $id, StatusUpdateRequest $request): JsonResponse
    {
        if (!$this->canManageOperationalBooking($id, 'bookings.status')) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        try {
            $model = $this->service->statusUpdate($id, $request->validated());

            $this->bookingStatusUpdateNotify($model);

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
                BookingResource::make($model)
            );
        } catch (Throwable $e) {
            return $this->onErrorResponse([
                'message' => $e->getMessage() . $e->getFile() . $e->getLine()
            ]);
        }
    }

    /**
     * @param int $id
     * @param NotesUpdateRequest $request
     * @return JsonResponse
     */
    public function notesUpdate(int $id, NotesUpdateRequest $request): JsonResponse
    {
        if (!$this->canManageOperationalBooking($id, 'bookings.manage', true)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }
        try {
            $model = $this->service->notesUpdate($id, $request->validated());

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
                BookingResource::make($model)
            );
        } catch (Throwable $e) {
            return $this->onErrorResponse([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param int $id
     * @param TimesUpdateRequest $request
     * @return JsonResponse
     */
    public function timesUpdate(int $id, TimesUpdateRequest $request): JsonResponse
    {
        if (!$this->canManageOperationalBooking($id, 'bookings.manage', true)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }
        try {
            $model = $this->service->timesUpdate($id, $request->validated());

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
                BookingResource::make($model)
            );
        } catch (Throwable $e) {
            return $this->onErrorResponse([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param int $id
     * @param ExtraTimeRequest $request
     * @return JsonResponse
     */
    public function extraTime(int $id, ExtraTimeRequest $request): JsonResponse
    {
        if (!$this->canManageOperationalBooking($id, 'bookings.manage')) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        try {
            $model = $this->service->extraTime($id, $request->validated());

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
                BookingResource::make($model)
            );
        } catch (Throwable $e) {
            return $this->onErrorResponse([
                'message' => $e->getMessage() . $e->getFile() . $e->getLine()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        // Validate the complete batch before deleting anything. The native
        // filter's caller-supplied shop_id is not an authorization boundary.
        foreach ($request->input('ids', []) as $id) {
            if (!is_scalar($id)
                || !$this->canManageOperationalBooking((int) $id, 'bookings.manage', true)) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
            }
        }

        $this->service->delete($request->input('ids', []), $request->all());

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }

    /**
     * Bind operational Staff to the actual target before shared lifecycle code.
     * Admin authority and the shared Customer/Master/Seller assignment rules
     * remain unchanged. Bulk deletion also needs this boundary for Sellers,
     * because that native service has no assignment check of its own.
     */
    private function canManageOperationalBooking(
        int $id, string $permission, bool $allOperationalActors = false
    ): bool {
        $actor = auth('sanctum')->user();
        if (!$actor) {
            return false;
        }

        if ($actor->hasRole('admin')) {
            return true;
        }

        if (!$allOperationalActors && !$actor->hasRole(['moderator', 'shop_manager'])) {
            return true;
        }

        $booking = Booking::query()->find($id);
        if (!$booking || !$this->shop
            || (int) $booking->shop_id !== (int) $this->shop->id) {
            return false;
        }

        return (new PayableShopAuthorization)->allows($actor, $booking, $permission);
    }

    /**
     * Merges the viewing user's branch-visibility scope (see
     * User::bookingBranchScope()) into a booking filter array, for the
     * paginated index() listing. Unrestricted viewers get the filter back
     * unchanged.
     *
     * @param array $filter
     * @return array
     */
    private function applyBranchScope(array $filter): array
    {
        $scope = auth('sanctum')->user()->bookingBranchScope($this->shop->id);

        if ($scope['unrestricted']) {
            return $filter;
        }

        $filter['branch_scope_active']       = true;
        $filter['branch_scope_location_ids'] = $scope['location_ids'];

        return $filter;
    }

    /**
     * Same restriction as applyBranchScope(), enforced against a single,
     * already-resolved Booking — for show()/update()/bookingsByParent(),
     * where the branch-scoped filter above never runs. Stops a branch-
     * scoped viewer reaching a booking outside their branch by id, not
     * just by listing.
     *
     * @param Booking $booking
     * @return bool
     */
    private function bookingOutsideBranchScope(Booking $booking): bool
    {
        $scope = auth('sanctum')->user()->bookingBranchScope($this->shop->id);

        if ($scope['unrestricted']) {
            return false;
        }

        if (empty($scope['location_ids'])) {
            return true;
        }

        return !$booking->master
            ?->invitations()
            ->where('shop_id', $this->shop->id)
            ->where('status', Invitation::ACCEPTED)
            ->whereHas('shopLocations', fn ($q) => $q->whereIn('shop_locations.id', $scope['location_ids']))
            ->exists();
    }

    /**
     * Enforce the Vendor's customer, specialist, shop and branch boundaries
     * independently of the caller-provided IDs. Admin booking requests retain
     * their separate AdminStoreRequest authorization contract.
     *
     * @param array $data
     * @param Booking|null $booking
     * @return void
     */
    private function validateSellerBooking(array $data, ?Booking $booking = null): array
    {
        $shopId = (int) $this->shop->id;
        $actor = auth('sanctum')->user();
        // Creation is limited to branches the actor is assigned to, even if
        // a separate view-all permission widens booking reads.
        $scope = $actor->shopLocationBranchScope($shopId);
        $items = $data['data'] ?? [];
        $serviceLocationIds = ShopLocation::query()
            ->where('shop_id', $shopId)
            ->where('type', ShopLocation::SERVICE)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
        $authorizedServiceLocationIds = $scope['unrestricted']
            ? $serviceLocationIds
            : array_values(array_intersect($scope['location_ids'], $serviceLocationIds));

        foreach ($items as &$item) {
            if (empty($item['shop_location_id'])) {
                if (count($authorizedServiceLocationIds) === 1) {
                    $item['shop_location_id'] = $authorizedServiceLocationIds[0];
                }
            }
        }
        unset($item);

        if (isset($data['service_master_id']) && empty($data['shop_location_id'])) {
            if (count($authorizedServiceLocationIds) === 1) {
                $data['shop_location_id'] = $authorizedServiceLocationIds[0];
            }
        }

        $data['data'] = $items;
        $bookingLocationIds = collect($items)
            ->pluck('shop_location_id')
            ->filter()
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (array_key_exists('local_client_id', $data) && $data['local_client_id'] !== null) {
            $client = SellerBookingClient::query()
                ->where('id', $data['local_client_id'])
                ->where('shop_id', $shopId)
                ->first();

            if (!$client || (!$scope['unrestricted']
                && ($client->shop_location_id === null
                    || !in_array((int) $client->shop_location_id, $scope['location_ids'], true)))) {
                throw ValidationException::withMessages([
                    'local_client_id' => ['Select a client available to your business and branch.'],
                ]);
            }

            $hasFinancialInput = !empty($data['payment_id'])
                || !empty($data['from_wallet_price'])
                || !empty($data['user_gift_cart_id'])
                || !empty($data['user_member_ship_id'])
                || !empty($data['coupon'])
                || !empty($data['transaction_status'])
                || collect($items)->contains(fn ($item) =>
                    !empty($item['user_member_ship_id'] ?? null)
                    || !empty($item['user_gift_cart_id'] ?? null));
            if ($hasFinancialInput) {
                throw ValidationException::withMessages([
                    'payment_id' => ['Walk-in bookings remain pending and cannot use payment, wallet, coupon, gift-card, or membership inputs.'],
                ]);
            }
        } elseif (array_key_exists('user_id', $data) && $data['user_id'] !== null) {
            $customerQuery = User::query()
                ->whereKey($data['user_id'])
                ->whereHas('roles', fn ($query) => $query->where('name', 'user'))
                ->whereDoesntHave('roles', fn ($query) => $query->where('name', '!=', 'user'))
                ->whereDoesntHave('shop')
                ->whereDoesntHave('serviceMasters', fn ($query) => $query->where('shop_id', $shopId))
                ->whereDoesntHave('invitations', fn ($query) => $query
                    ->where('shop_id', $shopId)
                    ->where('status', Invitation::ACCEPTED))
                ->whereHas('bookings', function ($query) use ($shopId, $scope, $bookingLocationIds) {
                    $query->where('shop_id', $shopId)->whereNotNull('user_id');
                    if ($bookingLocationIds !== []) {
                        $query->whereIn('shop_location_id', $bookingLocationIds);
                    } elseif (!$scope['unrestricted']) {
                        $scope['location_ids'] === []
                            ? $query->whereRaw('1 = 0')
                            : $query->whereIn('shop_location_id', $scope['location_ids']);
                    }
                });

            if (!$customerQuery->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => ['Select an existing client authorized for this business and branch.'],
                ]);
            }
        } elseif (!$booking) {
            throw ValidationException::withMessages([
                'user_id' => ['Select an existing client or add a new client.'],
            ]);
        }

        if (isset($client) && $client->shop_location_id !== null
            && $bookingLocationIds !== []
            && !in_array((int) $client->shop_location_id, $bookingLocationIds, true)) {
            throw ValidationException::withMessages([
                'local_client_id' => ['Select a walk-in client associated with the selected service branch.'],
            ]);
        }

        if (isset($data['service_master_id'])) {
            $items[] = [
                'service_master_id' => $data['service_master_id'],
                'shop_location_id' => $data['shop_location_id'] ?? null,
            ];
        }

        foreach ($items as $item) {
            $serviceMaster = ServiceMaster::query()
                ->whereKey($item['service_master_id'] ?? null)
                ->where('shop_id', $shopId)
                ->where('active', true)
                ->whereHas('service', fn ($query) => $query->where('shop_id', $shopId))
                ->whereHas('master', fn ($query) => $query->where('active', true)
                    ->whereHas('invitations', fn ($invitation) => $invitation
                        ->where('shop_id', $shopId)->where('role', 'master')
                        ->where('status', Invitation::ACCEPTED)))
                ->first();

            if (!$serviceMaster) {
                throw ValidationException::withMessages([
                    'data' => ['Select an active service and Specialist assigned to this business.'],
                ]);
            }

            $locationId = isset($item['shop_location_id']) ? (int) $item['shop_location_id'] : null;
            if ($locationId !== null) {
                $locationExists = ShopLocation::query()
                    ->whereKey($locationId)
                    ->where('shop_id', $shopId)
                    ->where('type', ShopLocation::SERVICE)
                    ->exists();
                if (!$locationExists || (!$scope['unrestricted'] && !in_array($locationId, $scope['location_ids'], true))) {
                    throw ValidationException::withMessages([
                        'data' => ['Select a service branch you are authorized to manage.'],
                    ]);
                }
            } elseif (!$scope['unrestricted'] && count($scope['location_ids']) !== 1) {
                throw ValidationException::withMessages([
                    'data' => ['Select one of your assigned service branches.'],
                ]);
            } elseif ($scope['unrestricted'] && ShopLocation::query()
                ->where('shop_id', $shopId)
                ->where('type', ShopLocation::SERVICE)
                ->count() > 1) {
                throw ValidationException::withMessages([
                    'data' => ['Select a service branch before booking.'],
                ]);
            }

            if (!$scope['unrestricted']) {
                $eligibleLocations = $locationId === null
                    ? $scope['location_ids']
                    : [$locationId];
                $eligibleAtBranch = User::query()
                    ->availableAtShopLocations($shopId, $eligibleLocations)
                    ->whereKey($serviceMaster->master_id)
                    ->exists();
                if (!$eligibleAtBranch) {
                    throw ValidationException::withMessages([
                        'data' => ['Select a Specialist assigned to one of your authorized service branches.'],
                    ]);
                }
            } elseif ($locationId !== null) {
                $eligibleAtBranch = User::query()
                    ->availableAtShopLocations($shopId, [$locationId])
                    ->whereKey($serviceMaster->master_id)
                    ->exists();
                if (!$eligibleAtBranch) {
                    throw ValidationException::withMessages([
                        'data' => ['Select a Specialist assigned to the chosen service branch.'],
                    ]);
                }
            }
        }

        return $data;
    }

}
