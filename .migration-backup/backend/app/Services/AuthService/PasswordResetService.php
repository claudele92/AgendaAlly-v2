<?php
declare(strict_types=1);

namespace App\Services\AuthService;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Password reset challenges use the existing password_resets table. Tokens
 * are stored only as SHA-256 digests and are bound to the user's email.
 */
class PasswordResetService
{
    public const EXPIRES_MINUTES = 60;
    private const SMS_EXPIRES_MINUTES = 10;

    public function allowIssuance(string $channel, string $identity): bool
    {
        return $this->allow('issue', $channel, $identity, 3, 3600, 10, 3600);
    }

    public function allowVerification(string $channel, ?string $identity = null): bool
    {
        return $this->allow('verify', $channel, $identity ?? 'unknown', 5, 60, 20, 60);
    }

    private function allow(
        string $action,
        string $channel,
        string $identity,
        int $identityLimit,
        int $identityDecay,
        int $ipLimit,
        int $ipDecay
    ): bool {
        $identityKey = 'password-reset:' . $action . ':' . $channel . ':identity:' .
            hash('sha256', mb_strtolower(trim($identity)));
        $ipKey = 'password-reset:' . $action . ':' . $channel . ':ip:' .
            hash('sha256', (string)request()->ip());
        $allowed = !RateLimiter::tooManyAttempts($identityKey, $identityLimit)
            && !RateLimiter::tooManyAttempts($ipKey, $ipLimit);

        RateLimiter::hit($identityKey, $identityDecay);
        RateLimiter::hit($ipKey, $ipDecay);

        return $allowed;
    }

    public function issueEmailToken(User $user): string
    {
        $email = mb_strtolower(trim((string)$user->email));
        $identity = $this->emailResetIdentity($email);
        $plainToken = '';

        DB::transaction(function () use ($user, $email, $identity, &$plainToken): void {
            User::query()->whereKey($user->getKey())->lockForUpdate()->first();
            DB::table('password_resets')->where('email', $identity)->delete();
            RateLimiter::clear($this->emailAttemptKey($email));

            for ($attempt = 0; $attempt < 20; $attempt++) {
                $candidate = (string)random_int(100000, 999999);
                $digest = $this->emailCodeDigest($email, $candidate);

                if (!DB::table('password_resets')->where('token', $digest)->exists()) {
                    $plainToken = $candidate;
                    break;
                }
            }

            if ($plainToken === '') {
                throw new \RuntimeException('Unable to allocate a unique password reset code.');
            }

            DB::table('password_resets')->insert([
                'email' => $identity,
                'token' => $this->emailCodeDigest($email, $plainToken),
                'created_at' => now(),
            ]);
        });

        return $plainToken;
    }

    public function isCurrentEmailToken(User $user, string $plainToken): bool
    {
        return DB::table('password_resets')
            ->where('email',$this->emailResetIdentity(mb_strtolower(trim((string)$user->email))))
            ->where('token',$this->emailCodeDigest((string)$user->email,$plainToken))
            ->where('created_at','>',now()->subMinutes(self::EXPIRES_MINUTES))->exists();
    }

    public function discardEmailToken(User $user, string $plainToken): void
    {
        DB::table('password_resets')
            ->where('email', $this->emailResetIdentity(mb_strtolower(trim((string)$user->email))))
            ->where('token', $this->emailCodeDigest((string)$user->email, $plainToken))
            ->delete();
    }

    /**
     * Atomically consumes an emailed bearer token, returning its bound user
     * only once and only within the configured expiry window.
     */
    public function consumeEmailToken(string $plainToken, ?string $email = null): ?User
    {
        $email = $email === null ? '' : mb_strtolower(trim($email));

        // Six-digit codes are never globally searchable: the recipient address
        // is required to select the challenge before its digest is checked.
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        $identity = $this->emailResetIdentity($email);
        $rateLimited = $this->allowVerification('email', $email);
        $accountAllowed = $this->allowEmailCodeAttempt($email);
        $digest = preg_match('/\A\d{6}\z/', $plainToken)
            ? $this->emailCodeDigest($email, $plainToken)
            : null;

        if (!$rateLimited || !$accountAllowed || $digest === null) {
            if (!$accountAllowed || RateLimiter::attempts($this->emailAttemptKey($email)) >= 5) {
                DB::table('password_resets')->where('email', $identity)->delete();
            }
            return null;
        }

        return DB::transaction(function () use ($email, $identity, $digest): ?User {
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->lockForUpdate()->first();
            $reset = DB::table('password_resets')
                ->where('email', $identity)
                ->where('token', $digest)
                ->lockForUpdate()
                ->first();

            if (!$user || !$reset || !$this->isUnexpired($reset->created_at)) {
                if (RateLimiter::attempts($this->emailAttemptKey($email)) >= 5) {
                    DB::table('password_resets')->where('email', $identity)->delete();
                }
                return null;
            }

            DB::table('password_resets')
                ->where('email', $identity)
                ->where('token', $digest)
                ->delete();

            return $user;
        });
    }

    /**
     * Issue an opaque, server-side challenge for mobile v1 clients that
     * prove possession of the Firebase identity again on confirmation.
     */
    public function issueMobileChallenge(User $user): void
    {
        DB::transaction(function () use ($user): void {
            User::query()->whereKey($user->getKey())->lockForUpdate()->first();
            $identity = $this->mobileResetIdentity((string)$user->email);
            DB::table('password_resets')->where('email', $identity)->delete();
            DB::table('password_resets')->insert([
                'email' => $identity,
                'token' => hash('sha256', Str::random(64)),
                'created_at' => now(),
            ]);
        });
    }

    public function consumeMobileChallenge(User $user): bool
    {
        $identity = $this->mobileResetIdentity((string)$user->email);

        return DB::transaction(function () use ($user, $identity): bool {
            User::query()->whereKey($user->getKey())->lockForUpdate()->first();
            $reset = DB::table('password_resets')
                ->where('email', $identity)
                ->lockForUpdate()
                ->first();

            if (!$reset || !$this->isUnexpired($reset->created_at)) {
                return false;
            }

            DB::table('password_resets')->where('email', $identity)->delete();

            return true;
        });
    }

    public function issueSmsChallenge(string $phone, string $verifyId, string $code, $expiresAt): bool
    {
        if (
            !preg_match('/\A\d{6}\z/', $code) ||
            !Str::isUuid($verifyId) ||
            !$expiresAt ||
            Carbon::parse($expiresAt)->isPast()
        ) {
            return false;
        }

        $phoneKey = hash('sha256', preg_replace('/\D/', '', $phone));
        $identity = 'sms-reset:' . $phoneKey . ':' . $verifyId;
        $createdAt = Carbon::parse($expiresAt)->subMinutes(self::SMS_EXPIRES_MINUTES);

        DB::transaction(function () use ($phoneKey, $identity, $code, $createdAt): void {
            DB::table('password_resets')
                ->where('email', 'like', 'sms-reset:%')
                ->where('created_at', '<=', now()->subMinutes(self::SMS_EXPIRES_MINUTES))
                ->delete();
            $previousChallenges = DB::table('password_resets')
                ->where('email', 'like', 'sms-reset:' . $phoneKey . ':%')
                ->lockForUpdate()
                ->get();

            foreach ($previousChallenges as $previousChallenge) {
                if (
                    !str_starts_with($previousChallenge->token, 'consumed:') &&
                    !str_starts_with($previousChallenge->token, 'revoked:')
                ) {
                    DB::table('password_resets')
                        ->where('email', $previousChallenge->email)
                        ->update(['token' => 'revoked:' . $previousChallenge->token]);
                }
            }

            DB::table('password_resets')->insert([
                'email' => $identity,
                'token' => $this->secretDigest($code),
                'created_at' => $createdAt,
            ]);
        });

        return true;
    }

    public function consumeSmsChallenge(string $phone, string $verifyId, string $code): bool
    {
        $phoneKey = hash('sha256', preg_replace('/\D/', '', $phone));
        $identity = 'sms-reset:' . $phoneKey . ':' . $verifyId;

        $verificationAllowed = $this->allowVerification('sms', $phone);
        $attemptAllowed = $this->allowSmsCodeAttempt($identity);

        return DB::transaction(function () use ($identity, $code, $verificationAllowed, $attemptAllowed): bool {
            $reset = DB::table('password_resets')
                ->where('email', $identity)
                ->lockForUpdate()
                ->first();

            if (!$reset) {
                return false;
            }

            if (
                str_starts_with($reset->token, 'consumed:') ||
                str_starts_with($reset->token, 'revoked:')
            ) {
                return false;
            }

            if (!$verificationAllowed || !$attemptAllowed) {
                DB::table('password_resets')
                    ->where('email', $identity)
                    ->update(['token' => 'revoked:' . $reset->token]);
                return false;
            }

            if (!$this->isUnexpiredWithin($reset->created_at, self::SMS_EXPIRES_MINUTES)) {
                return false;
            }

            if (!preg_match('/\A\d{6}\z/', $code) || !hash_equals($reset->token, $this->secretDigest($code))) {
                if (RateLimiter::attempts($this->smsAttemptKey($identity)) >= 5) {
                    DB::table('password_resets')
                        ->where('email', $identity)
                        ->update(['token' => 'revoked:' . $reset->token]);
                }
                return false;
            }

            DB::table('password_resets')->where('email', $identity)->update([
                'token' => 'consumed:' . $reset->token,
            ]);

            return true;
        });
    }

    public function completeFirebasePasswordReset(
        User $user,
        string $firebaseToken,
        Carbon $expiresAt,
        ?string $passwordHash = null
    ): bool {
        $tokenIdentity = 'firebase-reset-token:' . hash('sha256', $firebaseToken);
        $mobileIdentity = $this->mobileResetIdentity((string)$user->email);
        $markerCreatedAt = $expiresAt->copy()->subMinutes(self::EXPIRES_MINUTES);

        return DB::transaction(function () use (
            $user,
            $firebaseToken,
            $tokenIdentity,
            $mobileIdentity,
            $markerCreatedAt,
            $passwordHash
        ): bool {
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->first();
            $marker = DB::table('password_resets')
                ->where('email', $tokenIdentity)
                ->lockForUpdate()
                ->first();

            if ($marker && $this->isUnexpired($marker->created_at)) {
                return false;
            }

            if ($marker) {
                DB::table('password_resets')->where('email', $tokenIdentity)->delete();
            }

            $pendingMobileChallenge = DB::table('password_resets')
                ->where('email', $mobileIdentity)
                ->lockForUpdate()
                ->first();

            if ($pendingMobileChallenge && $this->isUnexpired($pendingMobileChallenge->created_at)) {
                DB::table('password_resets')->where('email', $mobileIdentity)->delete();
            }

            if (!$lockedUser) {
                return false;
            }

            if ($passwordHash !== null) {
                $lockedUser->password = $passwordHash;
                $lockedUser->save();
            }
            $lockedUser->tokens()->delete();

            DB::table('password_resets')->insert([
                'email' => $tokenIdentity,
                'token' => hash('sha256', $firebaseToken),
                'created_at' => $markerCreatedAt,
            ]);

            return true;
        });
    }

    public function hasSmsChallenge(string $phone, string $verifyId): bool
    {
        $phoneKey = hash('sha256', preg_replace('/\D/', '', $phone));
        return DB::table('password_resets')
            ->where('email', 'sms-reset:' . $phoneKey . ':' . $verifyId)
            ->exists();
    }

    public function completeSmsPasswordReset(
        string $phone,
        string $verifyId,
        string $code,
        string $passwordHash
    ): ?User {
        $phone = preg_replace('/\D/', '', $phone);
        $phoneKey = hash('sha256', $phone);
        $identity = 'sms-reset:' . $phoneKey . ':' . $verifyId;
        $verificationAllowed = $this->allowVerification('sms', $phone);
        $attemptAllowed = $this->allowSmsCodeAttempt($identity);

        return DB::transaction(function () use (
            $phone,
            $identity,
            $code,
            $passwordHash,
            $verificationAllowed,
            $attemptAllowed
        ): ?User {
            $user = User::query()->where('phone', $phone)->lockForUpdate()->first();
            $reset = DB::table('password_resets')
                ->where('email', $identity)
                ->lockForUpdate()
                ->first();

            if (!$user || !$reset) {
                return null;
            }

            if (
                str_starts_with($reset->token, 'consumed:') ||
                str_starts_with($reset->token, 'revoked:')
            ) {
                return null;
            }

            if (!$verificationAllowed || !$attemptAllowed) {
                DB::table('password_resets')
                    ->where('email', $identity)
                    ->update(['token' => 'revoked:' . $reset->token]);
                return null;
            }

            if (
                !$this->isUnexpiredWithin($reset->created_at, self::SMS_EXPIRES_MINUTES) ||
                !preg_match('/\A\d{6}\z/', $code) ||
                !hash_equals($reset->token, $this->secretDigest($code))
            ) {
                if (RateLimiter::attempts($this->smsAttemptKey($identity)) >= 5) {
                    DB::table('password_resets')
                        ->where('email', $identity)
                        ->update(['token' => 'revoked:' . $reset->token]);
                }
                return null;
            }

            DB::table('password_resets')->where('email', $identity)->update([
                'token' => 'consumed:' . $reset->token,
            ]);
            $user->password = $passwordHash;
            $user->save();
            $user->tokens()->delete();

            return $user;
        });
    }

    private function isUnexpired($createdAt): bool
    {
        return $this->isUnexpiredWithin($createdAt, self::EXPIRES_MINUTES);
    }

    private function isUnexpiredWithin($createdAt, int $expiresMinutes): bool
    {
        return $createdAt !== null
            && now()->subMinutes($expiresMinutes)->lt(Carbon::parse($createdAt));
    }

    private function emailResetIdentity(string $email): string
    {
        return 'email-reset:' . mb_strtolower(trim($email));
    }

    private function mobileResetIdentity(string $email): string
    {
        return 'mobile-reset:' . mb_strtolower(trim($email));
    }

    private function secretDigest(string $value): string
    {
        $key = (string)config('app.key');

        if ($key === '') {
            throw new \RuntimeException('Application key is required for password reset codes.');
        }

        return hash_hmac('sha256', $value, $key);
    }

    private function emailCodeDigest(string $email, string $plainCode): string
    {
        return $this->secretDigest(
            "email-reset\0" . mb_strtolower(trim($email)) . "\0" . $plainCode
        );
    }

    private function emailAttemptKey(string $email): string
    {
        return 'password-reset:email-code-attempt:' . hash('sha256', mb_strtolower(trim($email)));
    }

    private function allowEmailCodeAttempt(string $email): bool
    {
        $key = $this->emailAttemptKey($email);
        $allowed = !RateLimiter::tooManyAttempts($key, 5);
        RateLimiter::hit($key, self::EXPIRES_MINUTES * 60);

        return $allowed;
    }

    private function smsAttemptKey(string $identity): string
    {
        return 'password-reset:sms-code-attempt:' . hash('sha256', $identity);
    }

    private function allowSmsCodeAttempt(string $identity): bool
    {
        $key = $this->smsAttemptKey($identity);
        $allowed = !RateLimiter::tooManyAttempts($key, 5);
        RateLimiter::hit($key, self::SMS_EXPIRES_MINUTES * 60);

        return $allowed;
    }
}