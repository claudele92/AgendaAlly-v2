<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Invitation\StatusRequest;
use App\Http\Requests\Invitation\UserRequest;
use App\Http\Resources\InviteResource;
use App\Models\Invitation;
use App\Repositories\InviteRepository\InviteRepository;
use App\Services\InviteService\InviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InviteController extends UserBaseController
{

    public function __construct(private InviteRepository $repository, private InviteService $service)
    {
        parent::__construct();
    }

    /**
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $invites = $this->repository->paginate($request->all());

        return InviteResource::collection($invites);
    }

    /**
     * @param string $uuid
     * @param UserRequest $request
     * @return JsonResponse
     */
    public function create(string $uuid, UserRequest $request): JsonResponse
    {
        $result = $this->service->create($uuid, $request->validated());

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
     * @param StatusRequest $request
     * @return InviteResource|JsonResponse
     */
    public function changeStatus(int $id, StatusRequest $request): InviteResource|JsonResponse
    {
        $invitation = Invitation::query()
            ->whereKey($id)
            ->where('user_id', auth('sanctum')->id())
            ->first();
        if (!$invitation) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }
        $data = $request->validated();
        $data['shop_id'] = $invitation->shop_id;

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
        $this->service->delete($request->input('ids'), userId: auth('sanctum')->id());

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language));
    }
}
