<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\WalletHistory\SendRequest;
use App\Http\Requests\WalletHistory\WithdrawRequest;
use App\Http\Resources\WalletHistoryResource;
use App\Models\Currency;
use App\Models\NotificationUser;
use App\Models\PointHistory;
use App\Models\PushNotification;
use App\Models\User;
use App\Models\WalletHistory;
use App\Repositories\WalletRepository\WalletHistoryRepository;
use App\Rules\PositiveWalletAmount;
use App\Services\UserServices\UserWalletService;
use App\Services\WalletHistoryService\WalletHistoryService;
use App\Traits\Notification;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class WalletController extends UserBaseController
{
    use Notification;

    public function __construct(
        private WalletHistoryRepository $walletHistoryRepository,
        private WalletHistoryService $walletHistoryService
    )
    {
        parent::__construct();
    }

    /**
     * @param FilterParamsRequest $request
     *
     * @return AnonymousResourceCollection
     */
    public function walletHistories(FilterParamsRequest $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = auth('sanctum')->user();

        if (empty($user->wallet?->uuid)) {
            $user = (new UserWalletService)->create($user);
        }

        $data = $request->merge(['wallet_uuid' => $user->wallet->uuid])->all();

        $histories = $this->walletHistoryRepository->walletHistoryPaginate($data);

        return WalletHistoryResource::collection($histories);
    }

    /**
     * @param WithdrawRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(WithdrawRequest $request): JsonResponse
    {
        $result = $this->withDraw($request);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            WalletHistoryResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @param BaseRequest $request
     * @return array
     * @throws Throwable
     */
    public function withDraw(BaseRequest $request): array
    {
        // Also protects internal calls, including the normalized send amount.
        if (!PositiveWalletAmount::accepts($request->input('price'))) {
            return ['status' => false, 'code' => ResponseError::ERROR_400];
        }

        $user = auth('sanctum')->user();

        if (empty($user->wallet) || $user->wallet->price < $request->input('price')) {
            return [
                'status' => false,
                'code'   => ResponseError::ERROR_109
            ];
        }

        $filter = $request->all();
        $filter['status'] = WalletHistory::PROCESSED;
        $filter['type']   = 'withdraw';
        $filter['user']   = auth('sanctum')->user();

        try {
            return $this->walletHistoryService->create($filter);
        } catch (\DomainException $e) {
            if ($e->getCode() !== 109) throw $e;
            return ['status' => false, 'code' => ResponseError::ERROR_109];
        }
    }

    /**
     * @param SendRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function send(SendRequest $request): JsonResponse
    {
        // Reject the original amount before normalization or either leg.
        if (!PositiveWalletAmount::accepts($request->input('price'))) {
            return $this->onErrorResponse(['status' => false, 'code' => ResponseError::ERROR_400]);
        }

        /** @var User $sendingUser */
        $sendingUser = User::with(['wallet', 'notifications'])->firstWhere('uuid', $request->input('uuid'));

        if (empty($sendingUser->wallet)) {
            return $this->onErrorResponse([
                'status' => false,
                'code'   => ResponseError::ERROR_109
            ]);
        }

        $rate = Currency::find($request->input('currency_id'))?->rate ?? 1;
        if (!PositiveWalletAmount::accepts($rate)) {
            return $this->onErrorResponse(['status' => false, 'code' => ResponseError::ERROR_400]);
        }
        $price = $request->input('price') / $rate;
        if (!PositiveWalletAmount::accepts($price)) {
            return $this->onErrorResponse(['status' => false, 'code' => ResponseError::ERROR_400]);
        }

        return DB::transaction(function () use ($request, $sendingUser, $price) {
            $sender = auth('sanctum')->user();
            if (!$sender->wallet) {
                throw new HttpResponseException($this->onErrorResponse(['code' => ResponseError::ERROR_109]));
            }
            \App\Services\WalletHistoryService\WalletDebit::lockOrdered([
                [$sender->wallet, (int) $sender->id],
                [$sendingUser->wallet, (int) $sendingUser->id],
            ]);

            $request->merge([
                'price' => $price,
                'note'  => "$sendingUser->firstname $sendingUser->lastname"
            ]);

            $result = $this->withDraw($request);

            if (!data_get($result, 'status')) {
                // Returning a response from DB::transaction would commit.
                throw new HttpResponseException($this->onErrorResponse($result));
            }
            $senderDebit = data_get($result, 'data');

            /** @var User $sender */
            $sender = auth('sanctum')->user();

            $filter = $request->all();
            $filter['status'] = WalletHistory::PAID;
            $filter['type']   = 'topup';
            $filter['user']   = $sendingUser;
            $filter['created_by'] = $sender->id;

            $result = $this->walletHistoryService->create($filter);

            if (!data_get($result, 'status')) {
                throw new HttpResponseException($this->onErrorResponse($result));
            }

            // Both legs commit together. A completed debit must never remain
            // in the generic withdrawal cancellation state.
            $completed = $this->walletHistoryService->changeStatus(
                (string) $senderDebit->uuid,
                WalletHistory::PAID,
                (int) $sender->id
            );
            if (!data_get($completed, 'status')) {
                throw new HttpResponseException($this->onErrorResponse($completed));
            }

            $notification = $sendingUser
                ?->notifications
                ?->where('type', \App\Models\Notification::PUSH)
                ?->first();

            /** @var NotificationUser $notification */
            if ($notification?->notification?->active) {
                $this->sendNotification(
                    $sendingUser,
                    $sendingUser->firebase_token ?? [],
                    __('errors.' . ResponseError::WALLET_TOP_UP, ['sender' => "$sender->firstname $sender->lastname"], $sendingUser?->lang ?? $this->language),
                    __('errors.' . ResponseError::WALLET_TOP_UP, ['sender' => "$sender->firstname $sender->lastname"], $sendingUser?->lang ?? $this->language),
                    [
                        'id'     => $sendingUser->id,
                        'price'  => $price,
                        'type'   => PushNotification::WALLET_TOP_UP
                    ],
                    [$sendingUser->id]
                );
            }

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
                WalletHistoryResource::make(data_get($result, 'data'))
            );
        }, 3);
    }

    /**
     * @param string $uuid
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function changeStatus(string $uuid, FilterParamsRequest $request): JsonResponse
    {
        if (
            !$request->input('status') ||
            !in_array($request->input('status'), [WalletHistory::REJECTED, WalletHistory::CANCELED])
        ) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_253]);
        }

        $result = $this->walletHistoryService->changeStatus(
            $uuid,
            $request->input('status'),
            (int) auth('sanctum')->id()
        );

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language));
    }

    /**
     * @param FilterParamsRequest $request
     * @return LengthAwarePaginator
     */
    public function pointHistories(FilterParamsRequest $request): LengthAwarePaginator
    {
        return PointHistory::where('user_id', auth('sanctum')->id())
            ->orderBy($request->input('column', 'created_at'), $request->input('sort', 'desc'))
            ->paginate($request->input('perPage', 10));
    }
}
