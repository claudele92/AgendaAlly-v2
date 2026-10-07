<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Models\AuctionQuestion;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\AuctionQuestionResource;
use App\Http\Requests\AuctionQuestion\StoreRequest;
use App\Http\Requests\AuctionQuestion\UpdateRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\AuctionQuestionService\AuctionQuestionService;
use App\Repositories\AuctionQuestionRepository\AuctionQuestionRepository;

class AuctionQuestionController extends AdminBaseController
{
    public function __construct(
        private AuctionQuestionRepository $repository,
        private AuctionQuestionService $service
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
        $models = $this->repository->paginate($request->all());

        return AuctionQuestionResource::collection($models);
    }

    /**
     * Display the specified resource.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), []);
    }

    /**
     * Display the specified resource.
     *
     * @param AuctionQuestion $auctionQuestion
     * @return JsonResponse
     */
    public function show(AuctionQuestion $auctionQuestion): JsonResponse
    {
        $auctionQuestion = $this->repository->show($auctionQuestion);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionQuestionResource::make($auctionQuestion)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AuctionQuestion $auctionQuestion
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(AuctionQuestion $auctionQuestion, UpdateRequest $request): JsonResponse
    {
        $result = $this->service->update($auctionQuestion, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            AuctionQuestionResource::make($result['data'])
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $this->service->delete($request->input('ids', []));

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }

    /**
     * @return JsonResponse
     */
    public function dropAll(): JsonResponse
    {
        $this->service->dropAll();

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }
}
