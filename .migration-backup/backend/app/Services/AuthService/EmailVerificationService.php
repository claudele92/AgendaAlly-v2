<?php
declare(strict_types=1);
namespace App\Services\AuthService;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class EmailVerificationService
{
    public function issue(User $user): string
    {
        return DB::transaction(function () use ($user): string {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $code = (string) random_int(100000, 999999);
            $digest = $this->digest($locked->email, $code);
            $locked->forceFill(['verify_token' => 'email:' . $digest])->save();
            Cache::put($this->key($locked->id), $digest, now()->addMinutes(10));
            return $code;
        }, 3);
    }

    public function consume(string $email, string $code): ?User
    {
        if (!preg_match('/^\d{6}$/', $code)) return null;
        return DB::transaction(function () use ($email, $code): ?User {
            $user = User::query()->where('email', $email)->lockForUpdate()->first();
            $digest = $this->digest($email, $code);
            if (!$user || $user->email_verified_at
                || !hash_equals('email:' . $digest, (string) $user->verify_token)
                || !hash_equals($digest, (string) Cache::get($this->key($user->id), ''))
            ) return null;
            $user->forceFill(['email_verified_at' => now(), 'verify_token' => null])->save();
            DB::afterCommit(fn () => Cache::forget($this->key($user->id)));
            return $user;
        }, 3);
    }

    public function isCurrent(User $user, string $code): bool
    {
        $digest=$this->digest((string)$user->email,$code);
        return !$user->email_verified_at && $user->verify_token==='email:'.$digest
            && hash_equals($digest,(string)Cache::get($this->key($user->id),''));
    }

    private function digest(string $email, string $code): string
    {
        return hash_hmac('sha256', strtolower(trim($email)) . '|' . $code, (string) config('app.key'));
    }

    private function key(int $id): string { return 'email-verification:' . $id; }
}