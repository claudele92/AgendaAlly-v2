<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Auth;

use App\Helpers\ResponseError;
use App\Helpers\EnvironmentPolicy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordBeforeRequest;
use App\Http\Requests\Auth\EmailPasswordResetRequest;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\PasswordResetPhoneVerifyRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ProvideLoginRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService\PasswordResetService;
use App\Services\AuthService\AuthByMobilePhone;
use App\Services\EmailSettingService\EmailSendService;
use App\Services\UserServices\UserService;
use App\Services\UserServices\UserWalletService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Laravel\Sanctum\PersonalAccessToken;
use Lcobucci\JWT\UnencryptedToken;
use Spatie\Permission\Models\Role;
use App\Traits\ApiResponse;
use App\Models\User;
use Str;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    use ApiResponse;

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if ($request->input('phone')) {
            return $this->loginByPhone($request);
        }

        if (!auth()->attempt($request->only(['email', 'password']))) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_102,
                'message' => __('errors.' . ResponseError::ERROR_102, locale: $this->language)
            ]);
        }

        /** @var User $user */
        $user  = auth()->user();
        $token = $user->createToken('api_token')->plainTextToken;

        /** @var User $user */
        $user  = auth('sanctum')->user();

        return $this->successResponse('User successfully login', [
            'token'         => $token,
            'access_token'  => $token,
            'token_type'    => 'Bearer',
            'user'          => UserResource::make($user->load([
                'roles',
                'wallet',
                'invite.shop:id,slug,uuid,logo_img',
                'invite.shop.translation' => fn($q) => $q->where('locale', $this->language)->select([
                    'id',
                    'title',
                    'shop_id',
                    'locale'
                ])
            ])),
        ]);
    }

    /**
     * @param $request
     * @return JsonResponse
     */
    protected function loginByPhone($request): JsonResponse
    {
        if (!auth()->attempt($request->only('phone', 'password'))) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_102,
                'message' => __('errors.' . ResponseError::ERROR_102, locale: $this->language)
            ]);
        }

        /** @var User $user */
        $user  = auth()->user();
        $token = $user->createToken('api_token')->plainTextToken;

        /** @var User $user */
        $user  = auth('sanctum')->user();

        return $this->successResponse('User successfully login', [
            'token'        => $token,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => UserResource::make($user->load(['roles'])),
        ]);
    }

    /**
     * Obtain the user information from Provider.
     *
     * @param $provider
     * @param ProvideLoginRequest $request
     * @return JsonResponse
     */
    public function handleProviderCallback($provider, ProvideLoginRequest $request): JsonResponse
    {
        if (!EnvironmentPolicy::firebaseEnabled()) {
            return $this->errorResponse(
                'ERROR_503',
                'Firebase identity verification is disabled for this environment.',
                503
            );
        }

        try {
            $this->validateProvider($request->input('id'));
        } catch (Throwable $e) {
            $this->error($e);

            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_107,
                'message' => __('errors.' . ResponseError::ERROR_107, locale: $this->language)
            ]);
        }

        try {
            $result = DB::transaction(function () use ($request, $provider) {

                @[$firstname, $lastname] = explode(' ', (string)$request->input('name', ''));

                $defaultName      = Str::before($request->input('email'), '@');
                $defaultFirstName = Str::ucfirst(Str::replace('.', ' ', $defaultName));

                $user = User::where('email', $request->input('email'))->first();

                if (empty($user)) {
                    $user = User::create([
                        'email'             => $request->input('email'),
                        'email_verified_at' => now(),
                        'referral'          => $request->input('referral'),
                        'active'            => true,
                        'firstname'         => !empty($firstname) ? $firstname : $defaultFirstName,
                        'lastname'          => $lastname,
                    ]);
                }

                if ($request->input('avatar') && empty($user->img)) {
                    $user->update(['img' => $request->input('avatar')]);
                }

                $user->socialProviders()->updateOrCreate([
                    'provider'      => $provider,
                    'provider_id'   => $request->input('id'),
                ], [
                    'avatar' => $request->input('avatar')
                ]);

                if (!$user->hasAnyRole(Role::query()->pluck('name')->toArray())) {
                    $user->syncRoles('user');
                }

                (new UserService)->notificationSync($user);

                if (empty($user->wallet)) {
                    (new UserWalletService)->create($user);
                }

                $token = $user->createToken('api_token')->plainTextToken;

                return [
                    'token'         => $token,
                    'access_token'  => $token,
                    'token_type'    => 'Bearer',
                    'user'          => UserResource::make($user->load(['roles'])),
                ];
            });

            return $this->successResponse('User successfully login', [
                'token'         => data_get($result, 'token'),
                'access_token'  => data_get($result, 'access_token'),
                'token_type'    => 'Bearer',
                'user'          => data_get($result, 'user'),
            ]);
        } catch (Throwable $e) {
            $this->error($e);
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function checkPhone(FilterParamsRequest $request): JsonResponse
    {
        $user = User::select('phone')
            ->where('phone', $request->input('phone'))
            ->exists();

        return $this->successResponse('Success', [
            'exist' => !empty($request->input('phone')) && $user,
        ]);
    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function logout(FilterParamsRequest $request): JsonResponse
    {
        /** @var User|null $user */
        $user = auth('sanctum')->user();
        if (empty($user)) {
            return $this->successResponse();
        }

        // Authentication revocation is authoritative and cannot depend on
        // optional push cleanup. Fail visibly if revocation itself fails.
        $current = $user->currentAccessToken();
        if ($current instanceof PersonalAccessToken && $current->delete() !== true) {
            throw new \RuntimeException('Session revocation failed.');
        }
        if ($request->hasSession() && (int)auth('web')->id() === (int)$user->id) {
            auth('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $pushToken = $request->input('firebase_token') ?: $request->input('token');
        if (is_string($pushToken) && $pushToken !== '') {
            try {
                $user->update([
                    'firebase_token' => collect($user->firebase_token)
                        ->reject(fn($item) => (string)$item === $pushToken)
                        ->values()->all(),
                ]);
            } catch (Throwable $e) {
                // Already-revoked auth must not be reported as still active
                // merely because optional push-registration cleanup failed.
                logger()->warning('Optional logout push cleanup failed.', ['user_id' => $user->id]);
            }
        }

        return $this->successResponse('User successfully logout');
    }

    /**
     * @param $idToken
     * @return UnencryptedToken|bool
     * @throws Exception
     */
    public function validateProvider($idToken): UnencryptedToken|bool
    {
        if (!EnvironmentPolicy::firebaseEnabled()) {
            throw new \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException(
                null,
                'Firebase identity verification is disabled for this environment.'
            );
        }

        $data = Firebase::auth()->verifyIdToken($idToken);
        $tokenEmail = $data->claims()->get('email');

        if (!$data->isExpired(now()) && $tokenEmail === request('email')) {
            return true;
        }

        throw new Exception('expired');
    }

    /**
     * @param ForgetPasswordRequest $request
     * @return JsonResponse
     */
    public function forgetPassword(ForgetPasswordRequest $request): JsonResponse
    {
        return (new AuthByMobilePhone)->authentication(
            $request->validated(),
            AuthByMobilePhone::PURPOSE_PASSWORD_RESET
        );
    }

    /**
     * @param EmailPasswordResetRequest $request
     * @return JsonResponse
     */
    public function forgetPasswordEmail(EmailPasswordResetRequest $request): JsonResponse
    {
        $email = mb_strtolower(trim((string)$request->input('email')));
        $resets = new PasswordResetService();

        if ($resets->allowIssuance('email', $email)) {
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user) {
                $token = $resets->issueEmailToken($user);
                app(\App\Services\EmailSettingService\SelectedEmailDelivery::class)->enqueue($user,'reset',$token);
            }
        }

        // Do not reveal whether an address is registered or whether delivery
        // succeeded. The client follows the same recovery path in either case.
        return $this->successResponse('If the account exists, reset instructions have been queued for delivery.');
    }

    /**
     * @param string $hash
     * @return JsonResponse
     */
    public function forgetPasswordVerifyEmail(string $hash): JsonResponse
    {
        $email = request()->query('email');
        return $this->exchangeEmailResetCode($hash, is_string($email) ? $email : null);
    }

    /** Body-only native client contract: no OTP or recipient in access-log URLs. */
    public function forgetPasswordVerifyEmailBody(\Illuminate\Http\Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'otp' => ['required', 'string', 'regex:/^\d{6}$/']]);
        return $this->exchangeEmailResetCode($data['otp'], $data['email']);
    }

    private function exchangeEmailResetCode(string $hash, ?string $email): JsonResponse
    {
        $resets = new PasswordResetService();
        $login = DB::transaction(function () use ($resets, $hash, $email): ?array {
            $user = $resets->consumeEmailToken($hash, is_string($email) ? $email : null);

            if (!$user) {
                return null;
            }

            $user->tokens()->delete();
            $token = $user->createToken('api_token')->plainTextToken;

            return ['user' => $user, 'token' => $token];
        });

        if (!$login) {
            return $this->invalidResetResponse();
        }

        $user = $login['user'];

        return $this->successResponse('User successfully login', [
            'token'         => $login['token'],
            'access_token'  => $login['token'],
            'token_type'    => 'Bearer',
            'user'          => UserResource::make($user->load(['roles'])),
        ]);
    }

    /**
     * @param ForgetPasswordBeforeRequest $request
     * @return JsonResponse
     */
    public function forgetPasswordBefore(ForgetPasswordBeforeRequest $request): JsonResponse
    {
        if (!EnvironmentPolicy::firebaseEnabled()) {
            return $this->errorResponse(
                'ERROR_503',
                'Firebase phone verification is disabled for this environment.',
                503
            );
        }

        $phone = preg_replace('/\D/', '', (string)$request->input('phone'));
        $resets = new PasswordResetService();

        if (!$resets->allowIssuance('mobile-firebase', $phone)) {
            return $this->mobileResetAccepted();
        }

        $identity = $this->verifiedFirebasePhoneIdentity((string)$request->input('id'), $phone);

        if (!$identity) {
            return $this->mobileResetAccepted();
        }

        $resets->issueMobileChallenge($identity['user']);

        return $this->mobileResetAccepted();
    }

    /**
     * @param PasswordResetPhoneVerifyRequest $request
     * @return JsonResponse
     */
    public function forgetPasswordVerify(PasswordResetPhoneVerifyRequest $request): JsonResponse
    {
        $resets = new PasswordResetService();
        $phone = preg_replace('/\D/', '', (string)$request->input('phone'));

        if ($request->input('type') === 'firebase') {
            if (!EnvironmentPolicy::firebaseEnabled()) {
                return $this->errorResponse(
                    'ERROR_503',
                    'Firebase phone verification is disabled for this environment.',
                    503
                );
            }

            if (!$resets->allowVerification('mobile-firebase', $phone)) {
                return $this->invalidResetResponse();
            }

            $identity = $this->verifiedFirebasePhoneIdentity((string)$request->input('id'), $phone);

            if (!$identity) {
                return $this->invalidResetResponse();
            }

            $user = $identity['user'];
            $token = DB::transaction(function () use ($resets, $user, $request, $identity): ?string {
                $passwordHash = $request->filled('password')
                    ? Hash::make($request->input('password'))
                    : null;

                if (!$resets->completeFirebasePasswordReset(
                    $user,
                    (string)$request->input('id'),
                    $identity['expires_at'],
                    $passwordHash
                )) {
                    return null;
                }

                return $user->createToken('api_token')->plainTextToken;
            });

            if (!$token) {
                return $this->invalidResetResponse();
            }
        } else {
            $verifyId = (string)$request->input('verifyId');
            $verifyCode = (string)$request->input('verifyCode');
            $login = DB::transaction(function () use ($resets, $phone, $verifyId, $verifyCode, $request): ?array {
                $user = $resets->completeSmsPasswordReset(
                    $phone,
                    $verifyId,
                    $verifyCode,
                    Hash::make($request->input('password'))
                );

                if (!$user) {
                    return null;
                }

                return ['user' => $user, 'token' => $user->createToken('api_token')->plainTextToken];
            });
            $user = $login['user'] ?? null;

            if (!$user) {
                if (!$resets->hasSmsChallenge($phone, $verifyId)) {
                    Cache::forget('sms-' . $verifyId);
                }
                return $this->invalidResetResponse();
            }

            $token = $login['token'];
            Cache::forget('sms-' . $verifyId);
        }

        return $this->successResponse('User successfully login', [
            'token' => $token,
            'access_token' => $token,
            'user' => UserResource::make($user),
        ]);
    }

    private function invalidResetResponse(): JsonResponse
    {
        return $this->onErrorResponse([
            'code' => ResponseError::ERROR_400,
            'message' => 'Invalid or expired reset request.',
        ]);
    }

    private function mobileResetAccepted(): JsonResponse
    {
        return $this->successResponse('If the account can be verified, continue with the reset confirmation.');
    }

    /**
     * The shipped mobile clients submit phone, type and Firebase ID token,
     * but not email. Bind Firebase's signed phone_number claim directly to
     * the requested phone and the existing user, optionally cross-checking
     * an email claim when Firebase supplies one.
     *
     * @return array{user: User, expires_at: Carbon}|null
     */
    private function verifiedFirebasePhoneIdentity(string $idToken, string $phone): ?array
    {
        try {
            $verified = Firebase::auth()->verifyIdToken($idToken);
            if ($verified->isExpired(now())) {
                return null;
            }

            $claims = $verified->claims();
            $claimPhone = preg_replace('/\D/', '', (string)$claims->get('phone_number'));
            $phone = preg_replace('/\D/', '', $phone);

            if ($claimPhone === '' || $phone === '' || !hash_equals($phone, $claimPhone)) {
                return null;
            }

            $user = User::query()->where('phone', $phone)->first();
            if (!$user) {
                return null;
            }

            $claimEmail = mb_strtolower(trim((string)$claims->get('email', '')));
            if ($claimEmail !== '' && mb_strtolower(trim((string)$user->email)) !== $claimEmail) {
                return null;
            }

            $expiration = $claims->get('exp');
            $expiresAt = $expiration instanceof \DateTimeInterface
                ? Carbon::instance($expiration)
                : Carbon::createFromTimestamp((int)$expiration);

            return ['user' => $user, 'expires_at' => $expiresAt];
        } catch (Throwable $e) {
            return null;
        }
    }

}
