<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Requests\Booking\ExtraTimeRequest;
use App\Http\Requests\Booking\NotesUpdateRequest;
use App\Http\Requests\Booking\StatusUpdateRequest;
use App\Http\Requests\Booking\UpdateRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Booking\StoreRequest;
use App\Http\Requests\Order\AddReviewRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Repositories\BookingRepository\BookingRepository;
use App\Services\BookingService\BookingService;
use App\Traits\Notification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class BookingController extends UserBaseController
{
    use Notification;

    public function __construct(private BookingRepository $repository, private BookingService $service)
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
        $filter = $request->merge(['user_id' => auth('sanctum')->id()])->all();

        // A Booking row is created by BookingService::create() at the
        // final checkout submission (the storefront never calls it
        // earlier - see payment-finish.tsx), and starts at the DB default
        // status 'new' regardless of payment method; the seller/staff
        // move it to 'booked' afterward. So 'new' is the normal status of
        // every fresh booking, cash included - it is NOT itself a sign of
        // an abandoned checkout, and excluding it outright hid real cash
        // bookings a customer had just placed. The only bookings actually
        // worth hiding are ones that never completed checkout at all: see
        // Booking::scopeFilter()'s hide_abandoned_new, which keys off
        // whether a Transaction row exists rather than status alone.
        // Canceled bookings stay visible either way, since the app already
        // has dedicated handling for that status (see BookingCard's
        // showOptions). Skipped if the request already asked for a
        // specific status/statuses itself, so this only supplies a default.
        if (!isset($filter['status']) && !isset($filter['statuses'])) {
            $filter['hide_abandoned_new'] = true;
        }

        $models = $this->repository->paginate($filter);

        return BookingResource::collection($models);
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
        $validated['user_id'] = auth('sanctum')->id();

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            BookingResource::collection(data_get($result, 'data'))
        );
    }

    /**
     * Display the specified resource.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function calculate(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth('sanctum')->id();

        $result = $this->repository->calculate($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), $result);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function bookingsByParent(int $id): JsonResponse
    {
        $bookings = $this->repository->bookingsByParentId($id, auth('sanctum')->id());

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            BookingResource::collection($bookings)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Booking $booking
     * @return JsonResponse
     */
    public function show(Booking $booking): JsonResponse
    {
        if ($booking->user_id !== auth('sanctum')->id()) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            BookingResource::make($this->repository->show($booking))
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Booking $booking
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(Booking $booking, UpdateRequest $request): JsonResponse
    {
        if ($booking->user_id !== auth('sanctum')->id()) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        $result = $this->service->update($booking, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            BookingResource::make(data_get($result, 'data'))
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
        $this->service->delete(
            $request->input('ids', []),
            $request->merge(['user_id' => auth('sanctum')->id()])->all()
        );

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $id
     * @param StatusUpdateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function statusUpdate(int $id, StatusUpdateRequest $request): JsonResponse
    {
        try {
            if ($request->input('status') !== Booking::STATUS_CANCELED) {
                return $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => __('errors.' . ResponseError::ERROR_400, locale: $this->language)
                ]);
            }

            $model = $this->service->statusUpdate($id, $request->validated());

            $this->bookingStatusUpdateNotify($model);

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
                BookingResource::make($model)
            );
        } catch (Throwable $e) {
            return $this->onErrorResponse([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param int $id
     * @param NotesUpdateRequest $request
     * @return JsonResponse
     */
    public function notesUpdate(int $id, NotesUpdateRequest $request): JsonResponse
    {
        try {
            $model = $this->service->notesUpdate($id, $request->validated());

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
                BookingResource::make($model)
            );
        } catch (Throwable $e) {
            return $this->onErrorResponse([
                'message' => $e->getMessage() . $e->getFile() . $e->getLine()
            ]);
        }
    }

    /**
     * @param int $id
     * @param ExtraTimeRequest $request
     * @return JsonResponse
     */
    public function extraTime(int $id, ExtraTimeRequest $request): JsonResponse
    {
        try {
            $model = $this->service->extraTime($id, $request->validated());

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
                BookingResource::make($model)
            );
        } catch (Throwable $e) {
            return $this->onErrorResponse([
                'message' => $e->getMessage() . $e->getFile() . $e->getLine()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $id
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function canceledByParent(int $id, FilterParamsRequest $request): JsonResponse
    {
        try {
            $data = [
                'status' => Booking::STATUS_CANCELED,
                'canceled_note' => (string)$request->input('canceled_note')
            ];

            /** @var Booking $booking */
            $booking = $this->service->canceledByParent($id, $data);

            $this->bookingStatusUpdateNotify($booking);

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            );
        } catch (Throwable $e) {
            return $this->onErrorResponse([
                'message' => $e->getMessage() . $e->getFile() . $e->getLine()
            ]);
        }
    }

    /**
     * Add review to Shop
     *
     * @param int $id
     * @param AddReviewRequest $request
     * @return JsonResponse
     */
    public function addReviews(int $id, AddReviewRequest $request): JsonResponse
    {
        $model = Booking::find($id);

        if (empty($model) || $model->user_id !== auth('sanctum')->id()) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $result = $this->service->addReview($model, $request->validated());

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            BookingResource::make(data_get($result, 'data'))
        );
    }

}
