<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Auth;

use Throwable;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\Notification;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Events\Mails\SendEmailVerification;
use App\Http\Requests\Auth\PhoneVerifyRequest;
use App\Http\Requests\Auth\ReSendVerifyRequest;
use App\Services\AuthService\AuthByMobilePhone;
use App\Services\AuthService\EmailVerificationService;
use App\Services\DeliveryDriver\DriverInvitationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class VerifyAuthController extends Controller
{
    use ApiResponse, Notification;

    public function verifyPhone(PhoneVerifyRequest $request): JsonResponse
    {
        return (new AuthByMobilePhone)->confirmOPTCode($request->all());
    }

    public function resendVerify(ReSendVerifyRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))
            ->whereNotNull('verify_token')
            ->whereNull('email_verified_at')
            ->first();

        if (!$user) {
            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language));
        }

        $verificationEvent = new SendEmailVerification($user);
        event($verificationEvent);

        $data = app(DriverInvitationService::class)->hasDedicatedContactInvitation($user)
            ? ['notification_status' => $verificationEvent->deliveryStatus]
            : null;

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), $data);
    }

    public function verifyEmail(?string $verifyToken): JsonResponse
    {
        // General six-digit codes require the recipient-bound POST endpoint.
        // Preserve the dedicated high-entropy Driver verification protocol.
        if (!is_string($verifyToken) || !preg_match('/^[a-f0-9]{64}$/i', $verifyToken)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }
        $user = User::where('verify_token', $verifyToken)
            ->whereNull('email_verified_at')
            ->first();

        if (empty($user)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $driverInvitations = app(DriverInvitationService::class);
        $isDriverContact = $driverInvitations->hasDedicatedContactInvitation($user);
        if ($isDriverContact && (!is_string($verifyToken)
            || !preg_match('/^[a-f0-9]{64}$/i', $verifyToken))
        ) {
            // A legacy six-digit timestamp code must not mint a Sanctum token
            // for an account bound to a Driver invitation.
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        try {
            $token = DB::transaction(function () use ($user, $verifyToken, $isDriverContact, $driverInvitations): string {
                // Match acceptance/offboarding lock order: shops, pending
                // contact rows, then identity. Verification binds null-user
                // invitations to this exact account before issuing a token.
                $contactInvitations = $isDriverContact
                    ? $driverInvitations->lockNativeContactInvitations($user)
                    : null;
                $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->first();
                if (!$lockedUser || $lockedUser->email_verified_at || $lockedUser->verify_token !== $verifyToken) {
                    throw new \RuntimeException('Verification request is no longer valid.');
                }

                $lockedUser->forceFill([
                    'email_verified_at' => now(),
                    'verify_token' => $isDriverContact ? null : $lockedUser->verify_token,
                ])->save();
                if ($isDriverContact) {
                    $driverInvitations->markNativeContactVerified($lockedUser, $contactInvitations);
                }

                return $lockedUser->createToken('api_token')->plainTextToken;
            }, 3);

            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), [
                'token'         => $token,
                'access_token'  => $token,
                'token_type'    => 'Bearer',
                'email'         => $user->email
            ]);
        } catch (ConflictHttpException $exception) {
            throw $exception;
        } catch (Throwable) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_501]);
        }
    }

    public function verifyEmailCode(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email', 'otp' => 'required|digits:6']);
        $user = app(EmailVerificationService::class)->consume($data['email'], (string) $data['otp']);
        if (!$user) return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        $token = $user->createToken('api_token')->plainTextToken;
        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), [
            'token' => $token, 'access_token' => $token, 'token_type' => 'Bearer', 'email' => $user->email,
        ]);
    }
}
