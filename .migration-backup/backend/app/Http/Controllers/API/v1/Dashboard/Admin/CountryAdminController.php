<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\CountryAdmin\StoreRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\CountryAdminResource;
use App\Http\Resources\UserResource;
use App\Models\CountryAdmin;
use App\Models\User;
use App\Repositories\CountryAdminRepository\CountryAdminRepository;
use App\Repositories\UserRepository\UserRepository;
use App\Services\CountryAdminService\CountryAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Assigning a user to a single country (or reassigning/revoking that
 * assignment) is a global action, not something a country admin can do
 * to themselves or anyone else — only an unrestricted superadmin may
 * call these endpoints. Deliberately not a CountryBaseController: there
 * is no "acting country" here, the country is always the target's.
 */
class CountryAdminController extends AdminBaseController
{
    public function __construct(
        private CountryAdminRepository $repository,
        private CountryAdminService $service
    )
    {
        parent::__construct();

        /** @var User|null $user */
        $user = auth('sanctum')->user();

        if (!$user?->isSuperAdmin()) {
            abort(403, __('errors.' . ResponseError::ERROR_101, locale: request('lang', 'en')));
        }
    }

    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        return CountryAdminResource::collection($this->repository->paginate($request->all()));
    }

    /**
     * Candidate search for the "Add Country Admin" form - restricted to
     * users who already hold accepted country-staff status, not the
     * entire platform user base (sellers, masters, customers, etc.).
     */
    public function searchUser(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $users = (new UserRepository)->usersSearch(
            $request->merge(['eligible_for_country_admin' => true])->all()
        );

        return UserResource::collection($users);
    }

    public function show(CountryAdmin $countryAdmin): JsonResponse
    {
        $countryAdmin = $this->repository->show($countryAdmin->id);

        if (!$countryAdmin) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            CountryAdminResource::make($countryAdmin)
        );
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $result = $this->service->create($request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            CountryAdminResource::make(data_get($result, 'data'))
        );
    }

    public function update(CountryAdmin $countryAdmin, StoreRequest $request): JsonResponse
    {
        $result = $this->service->update($countryAdmin, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            CountryAdminResource::make(data_get($result, 'data'))
        );
    }

    public function destroy(CountryAdmin $countryAdmin): JsonResponse
    {
        $result = $this->service->deleteAdmin($countryAdmin);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }
}
