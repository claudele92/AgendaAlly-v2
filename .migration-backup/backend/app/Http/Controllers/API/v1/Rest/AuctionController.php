<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Resources\AuctionQuestionResource;
use App\Repositories\AuctionQuestionRepository\AuctionQuestionRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AuctionResource;
use App\Http\Requests\FilterParamsRequest;
use App\Repositories\AuctionRepository\AuctionRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuctionController extends RestBaseController
{

    public function __construct(
        private AuctionRepository $repository,
        private AuctionQuestionRepository $questionRepository,
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
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $model = $this->repository->paginate($request->all());

        return AuctionResource::collection($model);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $auction = $this->repository->showById($id);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionResource::make($auction)
        );
    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function users(FilterParamsRequest $request): JsonResponse
    {
        if (empty($request->input('auction_id'))) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => __('errors.' . ResponseError::ERROR_400, locale: $this->language),
            ]);
        }

        $auction = $this->repository->users($request->all());

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionResource::make($auction)
        );
    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function questions(FilterParamsRequest $request): JsonResponse
    {
        if (empty($request->input('auction_id'))) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => __('errors.' . ResponseError::ERROR_400, locale: $this->language),
            ]);
        }

        $models = $this->questionRepository->restPaginate($request->all());

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionQuestionResource::collection($models)
        );
    }

}
