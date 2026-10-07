<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Auction;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AuctionResource;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Auction\StoreRequest;
use App\Http\Requests\Auction\UpdateRequest;
use App\Services\AuctionService\AuctionService;
use App\Repositories\AuctionRepository\AuctionRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuctionController extends AdminBaseController
{
    public function __construct(
        private AuctionRepository $repository,
        private AuctionService $service
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

        return AuctionResource::collection($models);
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
     * @param Auction $auction
     * @return JsonResponse
     */
    public function show(Auction $auction): JsonResponse
    {
        $auction = $this->repository->show($auction);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionResource::make($auction)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Auction $auction
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(Auction $auction, UpdateRequest $request): JsonResponse
    {
        $result = $this->service->update($auction, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            AuctionResource::make($result['data'])
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
