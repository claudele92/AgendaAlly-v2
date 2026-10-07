<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Invitation\InviteUpdateRequest;
use App\Http\Requests\Invitation\SellerRequest;
use App\Http\Requests\Invitation\StatusRequest;
use App\Http\Resources\InviteResource;
use App\Models\User;
use App\Repositories\InviteRepository\InviteRepository;
use App\Services\InviteService\InviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InviteController extends SellerBaseController
{

    public function __construct(private InviteRepository $repository, private InviteService $service)
    {
        parent::__construct();
    }

    public function paginate(FilterParamsRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $invites = $this->repository->paginate($request->merge(['shop_id' => $this->shop->id])->all());

        return InviteResource::collection($invites);
    }

    /**
     * Resolves an exact email or phone to the existing platform user it
     * belongs to, so the seller can confirm identity before inviting them
     * as staff (the invite itself is keyed by user_id, not contact info —
     * see Invitation\SellerRequest). Deliberately exact-match only and a
     * minimal, masked response: this is an identity check for someone who
     * isn't staff yet, not a general user directory.
     */
    public function searchUser(Request $request): JsonResponse
    {
        $request->validate(['query' => ['required', 'string']]);

        $query = $request->input('query');

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $query)
            ->orWhere('phone', $query)
            ->first();

        if (!$user || $user->hasAnyRole(['seller', 'admin'])) {
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

    /**
     * @param SellerRequest $request
     * @return JsonResponse
     */
    public function create(SellerRequest $request): JsonResponse
    {
        $data              = $request->validated();
        if (($data['role'] ?? null) === 'deliveryman') {
            return $this->onErrorResponse([
                'code' => ResponseError::ERROR_400,
                'message' => 'Use the dedicated delivery-driver invitation flow'
            ]);
        }
        $data['shop_id']   = $this->shop->id;
        $data['shop_name'] = $this->shop->translation?->title;

        $result = $this->service->sellerCreate($data);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            InviteResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $invite = $this->service->show($this->shop->id, $id);

        if (!$invite) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            InviteResource::make($invite)
        );
    }

    /**
     * @param int $id
     * @param InviteUpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, InviteUpdateRequest $request): JsonResponse
    {
        $data            = $request->validated();
        $data['shop_id'] = $this->shop->id;

        $result = $this->service->update($id, $data);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            InviteResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @param int $id
     * @param StatusRequest $request
     * @return InviteResource|JsonResponse
     */
    public function changeStatus(int $id, StatusRequest $request): InviteResource|JsonResponse
    {
        $data            = $request->validated();
        $data['shop_id'] = $this->shop->id;

        $result = $this->service->changeStatus($id, $data);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            InviteResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @param FilterParamsRequest $request
     * @return InviteResource|JsonResponse
     */
    public function delete(FilterParamsRequest $request): InviteResource|JsonResponse
    {
        $this->service->delete($request->input('ids'), $this->shop->id);

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language));
    }

}
