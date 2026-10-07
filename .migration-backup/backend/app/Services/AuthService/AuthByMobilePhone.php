<?php
declare(strict_types=1);

namespace App\Services\AuthService;

use App\Helpers\ResponseError;
use App\Helpers\EnvironmentPolicy;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService\PasswordResetService;
use App\Services\CoreService;
use App\Services\SMSGatewayService\SMSBaseService;
use App\Services\UserServices\UserService;
use App\Services\UserServices\UserWalletService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Spatie\Permission\Models\Role;
use Throwable;

class AuthByMobilePhone extends CoreService
{
    public const PURPOSE_REGISTRATION = 'registration';
    public const PURPOSE_PASSWORD_RESET = 'password-reset';

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return User::class;
    }

    /**
     * @param array $array
     * @return JsonResponse
     */
    public function authentication(array $array, string $purpose): JsonResponse
    {
        if (!in_array($purpose, [self::PURPOSE_REGISTRATION, self::PURPOSE_PASSWORD_RESET], true)) {
            throw new \InvalidArgumentException('Unsupported phone verification purpose.');
        }

        $isPasswordReset = $purpose === self::PURPOSE_PASSWORD_RESET;
        $phone = preg_replace('/\D/', '', data_get($array, 'phone'));
        $passwordResets = new PasswordResetService();

        if (!$passwordResets->allowIssuance($isPasswordReset ? 'sms' : 'sms-registration', $phone)) {
            return $isPasswordReset
                ? $this->successResponse('If the account can be verified, a reset code has been sent.')
                : $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => 'Please wait before requesting another verification code.',
                ]);
        }

        $sms = (new SMSBaseService)->smsGateway($phone, $purpose);

        if (!data_get($sms, 'status')) {
            return $isPasswordReset
                ? $this->successResponse('If the account can be verified, a reset code has been sent.')
                : $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => data_get($sms, 'message', ''),
                ]);
        }

        $verifyId = (string)data_get($sms, 'verifyId');
        $challenge = Cache::get('sms-' . $verifyId);

        if (
            !$challenge ||
            !data_get($challenge, 'OTPCode') ||
            !data_get($challenge, 'expiredAt') ||
            now()->greaterThan(data_get($challenge, 'expiredAt'))
        ) {
            return $isPasswordReset
                ? $this->successResponse('If the account can be verified, a reset code has been sent.')
                : $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => 'Unable to create a phone verification challenge.',
                ]);
        }

        $challenge['purpose'] = $purpose;
        Cache::put('sms-' . $verifyId, $challenge, Carbon::parse(data_get($challenge, 'expiredAt')));

        if ($isPasswordReset && !$passwordResets->issueSmsChallenge(
            $phone,
            $verifyId,
            (string)data_get($challenge, 'OTPCode'),
            data_get($challenge, 'expiredAt')
        )) {
            unset($challenge['OTPCode']);
            $challenge['revoked'] = true;
            Cache::put('sms-' . $verifyId, $challenge, Carbon::parse(data_get($challenge, 'expiredAt')));
            return $this->successResponse('If the account can be verified, a reset code has been sent.');
        }

        if ($isPasswordReset) {
            unset($challenge['OTPCode']);
            Cache::put('sms-' . $verifyId, $challenge, Carbon::parse(data_get($challenge, 'expiredAt')));
        }

        return $this->successResponse($isPasswordReset
            ? 'If the account can be verified, a reset code has been sent.'
            : __('errors.' . ResponseError::SUCCESS, locale: $this->language), [
            'verifyId'  => $verifyId,
            'phone'     => data_get($sms, 'phone'),
        ]);
    }

    /**
     * @param array $array
     * @return JsonResponse
     */
    public function confirmOPTCode(array $array): JsonResponse
    {
        if (data_get($array, 'type') !== 'firebase') {

            $data = Cache::get('sms-' . data_get($array, 'verifyId'));

            if (empty($data)) {
                return $this->onErrorResponse([
                    'code'      => ResponseError::ERROR_404,
                    'message'   => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
                ]);
            }

            if (Carbon::parse(data_get($data, 'expiredAt')) < now()) {
                return $this->onErrorResponse([
                    'code'      => ResponseError::ERROR_203,
                    'message'   => __('errors.' . ResponseError::ERROR_203, locale: $this->language)
                ]);
            }

            $passwordResets = new PasswordResetService();
            $challengePhone = preg_replace('/\D/', '', (string)data_get($data, 'phone'));
            $purpose = data_get($data, 'purpose');
            $isPasswordReset = $purpose === self::PURPOSE_PASSWORD_RESET;

            if (!in_array($purpose, [self::PURPOSE_REGISTRATION, self::PURPOSE_PASSWORD_RESET], true)) {
                Cache::forget('sms-' . data_get($array, 'verifyId'));
                return $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => 'Invalid or expired verification request.',
                ]);
            }

            $hasResetRecord = $passwordResets->hasSmsChallenge(
                $challengePhone,
                (string)data_get($array, 'verifyId')
            );

            if (data_get($data, 'revoked', false) || data_get($data, 'consumed', false)) {
                return $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => 'Invalid or expired verification request.',
                ]);
            }

            if ($isPasswordReset) {
                if (!$passwordResets->consumeSmsChallenge(
                    $challengePhone,
                    (string)data_get($array, 'verifyId'),
                    (string)data_get($array, 'verifyCode')
                )) {
                    return $this->onErrorResponse([
                        'code' => ResponseError::ERROR_400,
                        'message' => 'Invalid or expired reset request.',
                    ]);
                }
            } else {
                $attemptKey = 'sms-registration';
                if (
                    $hasResetRecord ||
                    !$passwordResets->allowVerification($attemptKey, $challengePhone) ||
                    (int)data_get($data, 'verifyAttempts', 0) >= 5
                ) {
                    $data['revoked'] = true;
                    Cache::put(
                        'sms-' . data_get($array, 'verifyId'),
                        $data,
                        Carbon::parse(data_get($data, 'expiredAt'))
                    );
                    return $this->onErrorResponse([
                        'code' => ResponseError::ERROR_400,
                        'message' => 'Invalid or expired verification request.',
                    ]);
                }

                $otpCode = (string)data_get($data, 'OTPCode');
                $verifyCode = (string)data_get($array, 'verifyCode');
                if (
                    !preg_match('/\A\d{6}\z/', $otpCode) ||
                    !preg_match('/\A\d{6}\z/', $verifyCode) ||
                    !hash_equals($otpCode, $verifyCode)
                ) {
                    $data['verifyAttempts'] = (int)data_get($data, 'verifyAttempts', 0) + 1;
                    if ($data['verifyAttempts'] >= 5) {
                        $data['revoked'] = true;
                    }
                    Cache::put(
                        'sms-' . data_get($array, 'verifyId'),
                        $data,
                        Carbon::parse(data_get($data, 'expiredAt'))
                    );

                    $revoked = (bool)data_get($data, 'revoked', false);
                    return $this->onErrorResponse([
                        'code' => $revoked ? ResponseError::ERROR_400 : ResponseError::ERROR_201,
                        'message' => $revoked
                            ? 'Invalid or expired verification request.'
                            : __('errors.' . ResponseError::ERROR_201, locale: $this->language),
                    ]);
                }
            }

            $data['consumed'] = true;
            unset($data['OTPCode']);
            Cache::put(
                'sms-' . data_get($array, 'verifyId'),
                $data,
                Carbon::parse(data_get($data, 'expiredAt'))
            );

            $user = $this->model()->where('phone', data_get($data, 'phone'))->first();

            if ($isPasswordReset && empty($user)) {
                return $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => 'Invalid or expired reset request.',
                ]);
            }

            if (!$isPasswordReset && !empty($user)) {
                return $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => 'Existing accounts must use password recovery.',
                ]);
            }

        } else {
            if (!EnvironmentPolicy::firebaseEnabled()) {
                return $this->errorResponse(
                    'ERROR_503',
                    'Firebase phone verification is disabled for this environment.',
                    503
                );
            }

            $phone = preg_replace('/\D/', '', (string)data_get($array, 'phone'));
            $firebaseToken = trim((string)data_get($array, 'id'));
            $passwordResets = new PasswordResetService();

            if (
                !$passwordResets->allowVerification('firebase-registration', $phone) ||
                !$this->firebaseTokenMatchesPhone($firebaseToken, $phone)
            ) {
                return $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => 'Invalid or expired verification request.',
                ]);
            }

            $user = $this->model()->where('phone', $phone)->first();
            if (!empty($user)) {
                return $this->onErrorResponse([
                    'code' => ResponseError::ERROR_400,
                    'message' => 'Existing accounts must use password recovery.',
                ]);
            }

            $data['phone']      = $phone;
            $data['email']      = data_get($array, 'email');
            $data['referral']   = data_get($array, 'referral');
            $data['firstname']  = data_get($array, 'firstname', $phone);
            $data['lastname']   = data_get($array, 'lastname');
            $data['password']   = data_get($array, 'password');
            $data['gender']     = data_get($array, 'gender', 'male');
            $user = null;
        }

        if (empty($user)) {
            try {
                $phone = preg_replace('/\D/', '', (string)data_get($data, 'phone'));
                $user = $this->model()
                    ->create([
                        'phone'             => $phone,
                        'email'             => data_get($data, 'email'),
                        'referral'          => data_get($data, 'referral'),
                        'active'            => 1,
                        'phone_verified_at' => now(),
                        'firstname'         => data_get($data, 'firstname', $phone),
                        'lastname'          => $data['lastname'] ?? '',
                        'gender'            => $data['gender'] ?? 'male',
                        'password'          => bcrypt(data_get($data, 'password', 'password')),
                    ]);
            } catch (Throwable $e) {
                $this->error($e);
                return $this->onErrorResponse([
                    'code'    => ResponseError::ERROR_400,
                    'message' => 'Email or phone already exist',
                ]);
            }

            (new UserService)->notificationSync($user);

            $user->emailSubscription()->updateOrCreate([
                'user_id' => $user->id
            ], [
                'active' => true
            ]);
        }

        if (!$user->hasAnyRole(Role::query()->pluck('name')->toArray())) {
            $user->syncRoles('user');
        }

        if (empty($user->wallet?->uuid)) {
            $user = (new UserWalletService)->create($user);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        if (data_get($array, 'type') === 'firebase') {
            Cache::forget('sms-' . data_get($array, 'verifyId'));
        }

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR, locale: $this->language), [
            'access_token'  => $token,
            'token_type'    => 'Bearer',
            'user'          => UserResource::make($user),
        ]);

    }

    private function firebaseTokenMatchesPhone(string $idToken, string $phone): bool
    {
        if ($idToken === '' || $phone === '') {
            return false;
        }

        try {
            $verifiedToken = Firebase::auth()->verifyIdToken($idToken);
            if ($verifiedToken->isExpired(now())) {
                return false;
            }

            $claimPhone = preg_replace('/\D/', '', (string)$verifiedToken->claims()->get('phone_number'));

            return $claimPhone !== '' && hash_equals($phone, $claimPhone);
        } catch (Throwable) {
            return false;
        }
    }

}
