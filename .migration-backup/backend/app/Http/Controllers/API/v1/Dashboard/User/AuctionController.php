<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AuctionResource;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\AuctionQuestionResource;
use App\Http\Requests\Auction\UserStoreRequest;
use App\Services\AuctionService\AuctionService;
use App\Repositories\AuctionRepository\AuctionRepository;
use App\Services\AuctionQuestionService\AuctionQuestionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\AuctionQuestionRepository\AuctionQuestionRepository;
use App\Http\Requests\AuctionQuestion\UserStoreRequest as QuestionUserStoreRequest;

class AuctionController extends UserBaseController
{

    public function __construct(
        private AuctionRepository $repository,
        private AuctionService $service,
        private AuctionQuestionRepository $questionRepository,
        private AuctionQuestionService $questionService,
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
        $model = $this->repository->paginate($request->merge(['user_id' => auth('sanctum')->id()])->all());

        return AuctionResource::collection($model);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $auction = $this->repository->showById($id, auth('sanctum')->id());

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionResource::make($auction)
        );
    }

    /**
     * @param UserStoreRequest $request
     * @return JsonResponse
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth('sanctum')->id();

        $auction = $this->service->userAssign($validated);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionResource::make($auction)
        );
    }

    /**
     * @param int $id
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function questions(int $id, FilterParamsRequest $request): JsonResponse
    {
        $models = $this->questionRepository->restPaginate($request->merge(['auction_id' => $id])->all());

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionQuestionResource::collection($models)
        );
    }

    /**
     * @param int $id
     * @param QuestionUserStoreRequest $request
     * @return JsonResponse
     */
    public function storeQuestion(int $id, QuestionUserStoreRequest $request): JsonResponse
    {
        $validated               = $request->validated();
        $validated['user_id']    = auth('sanctum')->id();
        $validated['auction_id'] = $id;

        $result = $this->questionService->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionQuestionResource::make($result['data'])
        );
    }

}
