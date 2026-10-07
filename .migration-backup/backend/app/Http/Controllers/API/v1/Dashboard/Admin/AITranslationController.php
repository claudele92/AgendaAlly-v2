<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AILogResource;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\AITranslation\StoreRequest;
use App\Repositories\AILogRepository\AILogRepository;
use App\Services\AiTranslationService\AiTranslationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AITranslationController extends AdminBaseController
{
    public function __construct(private AiTranslationService $service, private AILogRepository $repository)
    {
        parent::__construct();
    }

    /**
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $models = $this->repository->paginate($request->all());

        return AILogResource::collection($models);
    }

    /**
     * @param FilterParamsRequest $request
     * @return array
     */
    public function chart(FilterParamsRequest $request): array
    {
        return $this->repository->chart($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $model = $this->repository->show($id);

        if (empty($model)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AILogResource::make($model)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->service->translate($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            data_get($result, 'data')
        );
    }

}
