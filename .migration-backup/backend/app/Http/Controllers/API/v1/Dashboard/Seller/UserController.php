<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\Invitation;
use App\Models\User;
use App\Repositories\UserRepository\UserRepository;
use App\Services\AuthService\UserVerifyService;
use App\Services\DeliveryDriver\DriverMembership;
use App\Services\DeliveryDriver\DriverInvitationService;
use App\Services\UserServices\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Schema;

class UserController extends SellerBaseController
{

    public function __construct(
        private UserRepository $repository,
        private UserService $service
    )
    {
        parent::__construct();
    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $users = $this->repository->usersPaginate($request->merge(['role' => 'user', 'active' => true])->all());

        $pageUserIds = $users->getCollection()->pluck('id')->all();
        if ($pageUserIds !== []) {
            // usersPaginate eager-loads relations according to the requested
            // customer role, which can conceal a second global driver role.
            $driverIds = User::query()
                ->whereIn('users.id', $pageUserIds)
                ->whereHas('roles', fn($q) => $q->where('name', 'deliveryman'))
                ->pluck('users.id')
                ->map(static fn($id) => (int)$id)
                ->all();

            foreach ($users as $user) {
                if (in_array((int)$user->id, $driverIds, true)) {
                    $this->stripDriverPrivateRelations($user, null, false);
                }
            }
        }

        return UserResource::collection($users);
    }

    /**
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $user = $this->repository->userByUUID($uuid);

        if (empty($user)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        if ($user->hasRole('deliveryman')) {
            $membership = DriverMembership::relationship((int)$this->shop->id, (int)$user->id);
            $ownAcceptedMembership = $membership && (int)$membership->status === Invitation::ACCEPTED
                ? $membership
                : null;

            // This is the generic customer profile route, not a driver
            // settings/account endpoint. Preserve identity projection but
            // never serialize the global vehicle, wallet, or other-shop data.
            $this->stripDriverPrivateRelations($user, $ownAcceptedMembership);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            UserResource::make($user)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserCreateRequest $request
     * @return JsonResponse
     */
    public function store(UserCreateRequest $request): JsonResponse
    {
        $validated            = $request->validated();

        // Direct seller-created driver accounts require a vendor-supplied
        // password and bypass identity verification. Driver onboarding is
        // intentionally disabled until the invitation flow exists.
        if (($validated['role'] ?? null) === 'deliveryman') {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => 'Delivery driver account creation is unavailable'
            ]);
        }

        $validated['shop_id'] = [$this->shop->id];

        if (!empty(data_get($validated, 'email'))) {
            $validated['email_verified_at'] = now();
        }

        if (!empty(data_get($validated, 'phone'))) {
            $validated['phone_verified_at'] = now();
        }

        // A seller-defined shop_role always implies the 'shop_manager'
        // platform role — the fixed string is no longer client-choosable
        // directly, it's derived from picking one of the seller's own roles.
        if (!empty($validated['shop_role_id'])) {
            $validated['role'] = 'shop_manager';
        } elseif (!in_array($validated['role'], ['user', 'moderator', 'deliveryman', 'master'])) {
            $validated['role'] = 'user';
        }

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        (new UserVerifyService)->verifyEmail(data_get($result, 'data'));

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            UserResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @param UserUpdateRequest $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, string $uuid): JsonResponse
    {
        // A driver is a global identity shared across shops. Generic seller
        // account editing could change credentials, verification, role, or
        // other shop relationships, so only dedicated membership/settings
        // controls may operate on an existing driver.
        $existing = $this->repository->userByUUID($uuid);
        $requestedRoles = array_merge(
            is_array($request->input('roles')) ? $request->input('roles') : [],
            is_array($request->input('role_ids')) ? $request->input('role_ids') : []
        );
        if ($existing?->hasRole('deliveryman')
            || $request->input('role') === 'deliveryman'
            || in_array('deliveryman', $requestedRoles, true)
        ) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => 'Delivery driver accounts cannot be edited here'
            ]);
        }

        $validated = $request->validated();
        $validated['shop_id'] = [$this->shop->id];

        if (!empty(data_get($validated, 'email'))) {
            $validated['email_verified_at'] = now();
        }

        if (!empty(data_get($validated, 'phone'))) {
            $validated['phone_verified_at'] = now();
        }

        if (!empty($validated['shop_role_id'])) {
            $validated['role'] = 'shop_manager';
        }

        $result = $this->service->update($uuid, $validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            UserResource::make(data_get($result, 'data'))
        );

    }

    /**
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function shopUsersPaginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $shopId = (int)$this->shop->id;
        $filter = $request->merge(['shop_id' => $shopId])->all();

        if (($filter['role'] ?? null) === 'deliveryman') {
            return $this->getDeliveryman($request);
        }

        $column = data_get($filter, 'column', 'id');
        if ($column !== 'id' && !Schema::hasColumn('users', $column)) {
            $column = 'id';
        }

        $staffRole = $filter['role'] ?? null;

        // Keep genuine accepted local staff relationships even when the
        // identity also has the global driver role for a different shop.
        // Driver-only rows are admitted only from this shop's eligible roster.
        $users = User::query()
            ->filter($filter)
            ->where(function ($query) use ($shopId, $staffRole) {
                $query
                    ->whereDoesntHave('roles', fn($q) => $q->where('name', 'deliveryman'))
                    ->orWhereHas('invitations', fn($q) => $q
                        ->where('shop_id', $shopId)
                        ->where('status', Invitation::ACCEPTED)
                        ->where('role', '!=', 'deliveryman'));

                if ($staffRole === null) {
                    $query->orWhereIn(
                        'users.id',
                        DriverMembership::eligibleUsers($shopId)->select('users.id')
                    );
                }
            })
            ->with([
                'roles',
                'invitations' => fn($q) => $q->where('shop_id', $shopId),
            ])
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->paginate(max(1, min((int)$request->input('perPage', 10), 100)));

        foreach ($users as $user) {
            if ($user->hasRole('deliveryman')) {
                $membership = DriverMembership::relationship($shopId, (int)$user->id);
                if ($membership && DriverMembership::isEligible($shopId, (int)$user->id)) {
                    $this->scopeDriverResource($user, $shopId, $membership);
                } else {
                    $this->stripDriverRoleFromStaffProjection($user, $shopId);
                }
            }
        }

        return UserResource::collection($users);
    }

    /**
     * @param string $uuid
     * @return JsonResponse
     */
    public function shopUserShow(string $uuid): JsonResponse
    {
        /** @var User $user */
        $user = $this->repository->userByUUID($uuid);

        if (!$user) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $shopId = (int)$this->shop->id;
        $isDriver = $user->hasRole('deliveryman');
        $membership = $isDriver
            ? DriverMembership::relationship($shopId, (int)$user->id)
            : null;
        $acceptedDriverMembership = $membership
            && (int)$membership->status === Invitation::ACCEPTED;
        $hasLocalDriverInvitation = $user->invitations
            ->contains(fn($invite) => (int)$invite->shop_id === $shopId && $invite->role === 'deliveryman');
        $localStaffInvitations = $user->invitations
            ->where('shop_id', $shopId)
            ->where('role', '!=', 'deliveryman')
            ->where('status', Invitation::ACCEPTED)
            ->values();

        if ($isDriver && !$acceptedDriverMembership
            && ($hasLocalDriverInvitation || $localStaffInvitations->isEmpty())
        ) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }
        if (!$isDriver && !$user->invitations->contains('shop_id', $shopId)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        if ($isDriver && $acceptedDriverMembership) {
            $this->stripDriverPrivateRelations($user, $membership, true, false);
            // An eligible own-shop driver may see the existing vehicle
            // setting on this staff profile; inactive memberships may not.
            if (DriverMembership::isEligible($shopId, (int)$user->id)) {
                $user->setRelation(
                    'deliveryManSetting',
                    $user->deliveryManSetting()->first()
                );
            }
        } elseif ($isDriver) {
            $this->stripDriverPrivateRelations($user);
            $user->setRelation('invitations', $localStaffInvitations);
        } else {
            // Do not serialize the user's other-shop invitations from the
            // repository's broad profile eager-load.
            $user->setRelation(
                'invitations',
                $user->invitations->where('shop_id', $shopId)->values()
            );
        }

        if ($isDriver || $user->invitations->isNotEmpty()) {
            return $this->successResponse(
                __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
                UserResource::make($user)
            );
        }

        return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
    }

    /**
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function getDeliveryman(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $includeInactive = $request->input('include_inactive_memberships') === '1'
            || $request->input('include_inactive_memberships') === 1;

        // Never let query-string role/shop filters widen the authority scope.
        $query = ($includeInactive
            ? DriverMembership::members((int)$this->shop->id)
                ->whereHas('invitations', fn($q) => $q
                    ->where('shop_id', $this->shop->id)
                    ->where('role', 'deliveryman')
                    ->where('status', Invitation::ACCEPTED))
            : DriverMembership::eligibleUsers((int)$this->shop->id)
        );

        $searchValue = $request->input('search', '');
        $search = is_string($searchValue) ? trim($searchValue) : '';
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $pattern = '%' . $search . '%';
                $q->where('firstname', 'like', $pattern)
                    ->orWhere('lastname', 'like', $pattern)
                    ->orWhere('email', 'like', $pattern)
                    ->orWhere('phone', 'like', $pattern);
            });
        }

        $users = $query->with([
            'roles' => fn($q) => $q->where('name', 'deliveryman'),
            'invitations' => fn($q) => $q
                ->where('shop_id', $this->shop->id)
                ->where('role', 'deliveryman'),
        ])->paginate(max(1, min((int)$request->input('perPage', 10), 100)));

        foreach ($users as $user) {
            $membership = DriverMembership::relationship((int)$this->shop->id, (int)$user->id);
            $this->scopeDriverResource($user, (int)$this->shop->id, $membership);
        }

        return UserResource::collection($users);
    }

    /**
     * @param $uuid
     * @return JsonResponse
     */
    public function setUserActive($uuid): JsonResponse
    {
        /** @var User $user */
        $user = $this->repository->userByUUID($uuid);

        // Driver accounts must stay on the shop-specific membership branch.
        // Never fall through to the legacy global account-active toggle when
        // their exact local relationship is missing, duplicated, or pending.
        if ($user?->hasRole('deliveryman')) {
            $membership = DriverMembership::relationship((int)$this->shop->id, (int)$user->id);

            if (!$membership) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
            }

            if ((int)$membership->status !== Invitation::ACCEPTED) {
                return $this->onErrorResponse([
                    'code'    => ResponseError::ERROR_400,
                    'message' => 'Only accepted delivery driver memberships can be changed'
                ]);
            }

            $membership = app(DriverInvitationService::class)->setActive(
                (int) $this->shop->id,
                (int) $membership->id,
                !(bool) $membership->driver_active
            );
            $user->unsetRelation('wallet');
            $user->unsetRelation('shop');
            $user->unsetRelation('deliveryManSetting');
            $user->unsetRelation('deliveryManOrders');
            $user->unsetRelation('orders');
            $this->scopeDriverResource($user, (int)$this->shop->id, $membership);

            return $this->successResponse(
                __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
                UserResource::make($user)
            );
        }

        // Preserve the original seller behavior for non-driver staff only.
        $legacyShopMembership = $user?->invite?->shop_id == $this->shop->id;
        if ($user && $legacyShopMembership) {
            $this->service->setActive($user);
            return $this->successResponse(
                __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
                UserResource::make($user)
            );
        }

        return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
    }

    private function scopeDriverResource(User $user, int $shopId, ?Invitation $membership = null): void
    {
        if ($user->relationLoaded('roles')) {
            $user->setRelation('roles', $user->roles->where('name', 'deliveryman')->values());
        }

        if ($user->relationLoaded('invitations')) {
            $user->setRelation(
                'invitations',
                $user->invitations
                    ->where('shop_id', $shopId)
                    ->where('role', 'deliveryman')
                    ->values()
            );
        }

        $membership ??= DriverMembership::relationship($shopId, (int)$user->id);
        $user->setAttribute(
            'driver_membership_active',
            $membership !== null && (bool)$membership->driver_active
        );
    }

    private function stripDriverPrivateRelations(
        User $user,
        ?Invitation $localAcceptedMembership = null,
        bool $stripWallet = true,
        bool $removeDriverRole = true
    ): void {
        $user->unsetRelation('deliveryManSetting');
        $user->unsetRelation('deliveryManOrders');
        $user->unsetRelation('orders');
        $user->unsetRelation('shop');
        $user->unsetRelation('invite');

        if ($stripWallet) {
            $user->unsetRelation('wallet');
        }

        if ($removeDriverRole && $user->relationLoaded('roles')) {
            $user->setRelation(
                'roles',
                $user->roles->reject(fn($role) => $role->name === 'deliveryman')->values()
            );
        }

        if ($localAcceptedMembership) {
            $user->setRelation(
                'invitations',
                $localAcceptedMembership->newCollection([$localAcceptedMembership])
            );
        } else {
            $user->unsetRelation('invitations');
        }
    }

    private function stripDriverRoleFromStaffProjection(User $user, int $shopId): void
    {
        $user->unsetRelation('deliveryManSetting');
        $user->unsetRelation('deliveryManOrders');

        if ($user->relationLoaded('roles')) {
            $user->setRelation(
                'roles',
                $user->roles->reject(fn($role) => $role->name === 'deliveryman')->values()
            );
        }

        if ($user->relationLoaded('invitations')) {
            $user->setRelation(
                'invitations',
                $user->invitations
                    ->where('shop_id', $shopId)
                    ->where('role', '!=', 'deliveryman')
                    ->where('status', Invitation::ACCEPTED)
                    ->values()
            );
        }
    }
}
