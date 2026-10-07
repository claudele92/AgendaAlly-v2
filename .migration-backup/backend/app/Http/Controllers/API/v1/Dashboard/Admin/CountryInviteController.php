<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\CountryInvitation\CreateAccountRequest;
use App\Http\Requests\CountryInvitation\StatusRequest;
use App\Http\Requests\CountryInvitation\StoreRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\CountryInvitationResource;
use App\Models\User;
use App\Repositories\CountryInviteRepository\CountryInviteRepository;
use App\Services\CountryInviteService\CountryInviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class CountryInviteController extends CountryBaseController
{
    public function __construct(
        private CountryInviteRepository $repository,
        private CountryInviteService $service
    )
    {
        parent::__construct();
    }

    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection|JsonResponse
    {
        if (!$this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $invites = $this->repository->paginate($request->merge(['country_id' => $this->countryId])->all());

        return CountryInvitationResource::collection($invites);
    }

    /**
     * Resolves an exact email or phone to the existing platform user it
     * belongs to, so the acting admin can confirm identity before inviting
     * them as country staff — mirrors Seller\InviteController::searchUser.
     * Excludes existing admin/manager-role users: they already hold
     * platform-wide admin access, so country-staff status is redundant for
     * them (unlike a seller, who is a legitimate country-staff candidate).
     */
    public function searchUser(Request $request): JsonResponse
    {
        if (!$this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $request->validate(['query' => ['required', 'string']]);

        $query = $request->input('query');

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $query)
            ->orWhere('phone', $query)
            ->first();

        if (!$user || $user->hasAnyRole(['admin', 'manager'])) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            [
                'id'    => $user->id,
                'name'  => trim("$user->firstname $user->lastname"),
                'email' => $user->email ? $this->maskEmail($user->email) : null,
                'phone' => $user->phone ? $this->maskPhone($user->phone) : null,
            ]
        );
    }

    private function maskEmail(string $email): string
    {
        $at = strpos($email, '@');

        if ($at === false || $at < 1) {
            return $email;
        }

        return Str::mask($email, '*', 1, $at - 1);
    }

    private function maskPhone(string $phone): string
    {
        $visibleStart = 2;
        $visibleEnd   = 4;
        $maskLength   = strlen($phone) - $visibleStart - $visibleEnd;

        if ($maskLength <= 0) {
            return $phone;
        }

        return Str::mask($phone, '*', $visibleStart, $maskLength);
    }

    public function create(StoreRequest $request): JsonResponse
    {
        if (!$this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $result = $this->service->create($this->countryId, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            CountryInvitationResource::make(data_get($result, 'data'))
        );
    }

    public function createAccount(CreateAccountRequest $request): JsonResponse
    {
        if (!$this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $result = $this->service->createAccount($this->countryId, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            CountryInvitationResource::make(data_get($result, 'data'))
        );
    }

    public function changeStatus(int $id, StatusRequest $request): JsonResponse
    {
        if (!$this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $result = $this->service->changeStatus($this->countryId, $id, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            CountryInvitationResource::make(data_get($result, 'data'))
        );
    }

    public function delete(FilterParamsRequest $request): JsonResponse
    {
        if (!$this->countryId) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $this->service->deleteInvitations($request->input('ids', []), $this->countryId);

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language));
    }
}
