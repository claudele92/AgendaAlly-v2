<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Helpers\ResponseError;
use App\Http\Requests\Payment\TransactionRequest;
use App\Http\Requests\Payment\TransactionUpdateRequest;
use App\Http\Resources\AuctionUserResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ParcelOrderResource;
use App\Http\Resources\ShopAdsPackageResource;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\UserGiftCartResource;
use App\Http\Resources\UserMemberShipResource;
use App\Http\Resources\WalletResource;
use App\Models\Booking;
use App\Models\Order;
use App\Models\ParcelOrder;
use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\ShopAdsPackage;
use App\Models\ShopSubscription;
use App\Models\Transaction;
use App\Models\UserGiftCart;
use App\Models\UserMemberShip;
use App\Models\Wallet;
use App\Policies\PayableShopAuthorization;
use App\Services\TransactionService\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TransactionController extends PaymentBaseController
{

    public function __construct(private TransactionService $service)
    {
        parent::__construct($service);
    }

    public function store(string $type, int $id, TransactionRequest $request): JsonResponse
    {
        if (in_array($type, ['booking', 'order'], true)) {
            $context = (new \App\Services\PaymentEligibility\PaymentContextFactory)->target($type . '_id', $id);
            (new \App\Services\PaymentEligibility\PaymentEligibilityService)->assertEligible(
                (int) $request->validated('payment_sys_id'), $context
            );
        }
        if ($type === 'order') {

            $result = $this->service->orderTransaction($id, $request->validated());

            if (!data_get($result, 'status')) {
                return $this->onErrorResponse($result);
            }

            return $this->successResponse(
                __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
                OrderResource::make(data_get($result, 'data'))
            );

        } else if ($type === 'booking') {

            $result = $this->service->bookingTransaction($id, $request->validated());

            if (!data_get($result, 'status')) {
                return $this->onErrorResponse($result);
            }

            return $this->successResponse(
                __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
                BookingResource::make(data_get($result, 'data'))
            );

        } else if ($type === 'parcel-order') {

            $result = $this->service->orderTransaction($id, $request->validated(), ParcelOrder::class);

            if (!data_get($result, 'status')) {
                return $this->onErrorResponse($result);
            }

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
                ParcelOrderResource::make(data_get($result, 'data'))
            );

        } else if ($type === 'subscription') {
            $result = $this->service->subscriptionTransaction($id, $request->validated());

            if (!data_get($result, 'status')) {
                return $this->onErrorResponse($result);
            }

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
                SubscriptionResource::make(data_get($result, 'data'))
            );
        } else if ($type === 'ads') {
            $result = $this->service->adsTransaction($id, $request->validated());

            if (!data_get($result, 'status')) {
                return $this->onErrorResponse($result);
            }

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
                ShopAdsPackageResource::make(data_get($result, 'data'))
            );
        } else if ($type === 'gift-cart') {
            $result = $this->service->giftCartTransaction($id, $request->validated());

            if (!data_get($result, 'status')) {
                return $this->onErrorResponse($result);
            }

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
                UserGiftCartResource::make(data_get($result, 'data'))
            );
        } else if ($type === 'auction-user') {
            $result = $this->service->auctionTransaction($id, $request->validated());

            if (!data_get($result, 'status')) {
                return $this->onErrorResponse($result);
            }

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
                AuctionUserResource::make(data_get($result, 'data'))

            );
        } else if ($type === 'member-ship') {
            $result = $this->service->memberShipTransaction($id, $request->validated());

            if (!data_get($result, 'status')) {
                return $this->onErrorResponse($result);
            }

            return $this->successResponse(
                __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
                UserMemberShipResource::make(data_get($result, 'data'))
            );
        }

        $result = $this->service->walletTransaction($id, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            WalletResource::make(data_get($result, 'data'))
        );
    }

    public function updateStatus(string $type, int $id, TransactionUpdateRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth('sanctum')->user();

        if (!$user?->hasRole(['admin', 'seller'])) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        /** @var Order $model */
        $model = match($type) {
            'parcel-order'  => ParcelOrder::with('transaction.paymentSystem')->find($id),
            'subscription'  => ShopSubscription::with('transaction.paymentSystem')->find($id),
            'ads-package', 'ads' => ShopAdsPackage::with('transaction.paymentSystem')->find($id),
            'wallet'        => Wallet::with(['transaction' => fn($q) => $q->orderBy('id', 'desc')->with('paymentSystem')])->find($id),
            'booking'       => Booking::with(['transaction' => fn($q) => $q->orderBy('id', 'desc')->with('paymentSystem')])->find($id),
            'member-ship'   => UserMemberShip::with(['transaction' => fn($q) => $q->orderBy('id', 'desc')->with('paymentSystem')])->find($id),
            'gift-cart'     => UserGiftCart::with(['transaction' => fn($q) => $q->orderBy('id', 'desc')->with('paymentSystem')])->find($id),
            default         => Order::with('transaction.paymentSystem')->find($id),
        };

        if (!$model) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        // Admin retains its existing authority and country scopes. Seller
        // authority must come from the persisted payable's Shop and grant,
        // never the global seller role or request-supplied ownership IDs.
        if (!$user->hasRole('admin')) {
            $authorization = new PayableShopAuthorization;
            if (!$authorization->supportsStatusType($type, $model)
                || !$authorization->allows(
                    $user, $model, PayableShopAuthorization::TRANSACTION_PERMISSION
                )) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
            }
        }

        if (!$model->transaction) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_501,
                'message' => __('errors.' . ResponseError::ERROR_501, locale: $this->language)
            ]);
        }

        $paymentTag = $model->transaction->paymentSystem?->tag;
        if (!\App\Services\TransactionService\BookingPaymentAuthority::allowsManualStatus(
            $model->transaction, $request->input('status')
        )) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_400,
                'message' => 'Electronic Booking payment requires provider verification.']);
        }
        $isCash     = $paymentTag === Payment::TAG_CASH;

        // A gateway (non-cash) transaction is real money moving through a
        // third party - only an admin may override its status by hand, and
        // only with a reason, since there's no other audit trail for why a
        // human overrode what the gateway itself reported. A seller can
        // still confirm their own cash bookings, same as before.
        if (!$isCash && !$user->hasRole('admin')) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $reason = trim((string) $request->input('reason'));

        if (!$isCash && $reason === '') {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => __('errors.' . ResponseError::ERROR_400, locale: $this->language)
                    . ': a reason is required to manually override a non-cash transaction\'s status'
            ]);
        }

        $paymentProcess = PaymentProcess::find($request->input('token'));

        if (empty($paymentProcess) && !in_array($paymentTag, [Payment::TAG_CASH, Payment::TAG_WALLET])) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => 'Order not paid'
            ]);
        }

        $previousStatus = $model->transaction->status;

        /** @var Transaction $transaction */
        \Illuminate\Support\Facades\DB::transaction(fn () => $model->transaction->update([
            'status' => $request->input('status'),
            'note'   => $isCash
                ? $model->transaction->note
                : trim(($model->transaction->note ? $model->transaction->note . "\n" : '')
                    . now()->toDateTimeString() . " manual override by {$user->email} (#{$user->id}): $reason"),
        ]), 3);

        if (!$isCash) {
            Log::warning('Manual non-cash transaction status override', [
                'transaction_id'  => $model->transaction->id,
                'payable_type'    => $type,
                'payable_id'      => $id,
                'previous_status' => $previousStatus,
                'new_status'      => $request->input('status'),
                'actor_id'        => $user->id,
                'actor_email'     => $user->email,
                'reason'          => $reason,
            ]);
        }

        $paymentProcess?->delete();

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            $model->fresh('transaction')
        );
    }
}
