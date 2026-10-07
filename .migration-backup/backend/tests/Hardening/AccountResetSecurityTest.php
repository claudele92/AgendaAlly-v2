<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Rest\InstallController;
use App\Http\Controllers\API\v1\Auth\LoginController;
use App\Services\AuthService\AuthByMobilePhone;
use App\Http\Requests\Auth\EmailPasswordResetRequest;
use App\Models\User;
use App\Services\AuthService\PasswordResetService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\UnencryptedToken;

final class AccountResetSecurityTest extends IsolatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('app.key', 'synthetic-hardening-test-key');
        Config::set('auth.defaults.guard', 'web');
        Config::set('permission.models.permission', \Spatie\Permission\Models\Permission::class);
        Config::set('permission.models.role', \Spatie\Permission\Models\Role::class);
        Config::set('permission.cache.key', 'hardening.permission.cache');
        Config::set('permission.cache.store', 'default');
        Config::set('permission.teams', false);
        Config::set('permission.table_names', [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);

        Schema::create('users', function ($table): void {
            $table->increments('id');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('password')->nullable();
            $table->string('referral')->nullable();
            $table->boolean('active')->nullable();
            $table->string('gender')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamps();
        });
        Schema::create('roles', function ($table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });
        Schema::create('model_has_roles', function ($table): void {
            $table->unsignedInteger('role_id');
            $table->string('model_type');
            $table->unsignedInteger('model_id');
        });
        Schema::create('notification_user', function ($table): void {
            $table->unsignedInteger('notification_id');
            $table->unsignedInteger('user_id');
            $table->boolean('active')->default(true);
        });
        Schema::create('notifications', function ($table): void {
            $table->increments('id');
            $table->string('type')->nullable();
        });
        Schema::create('email_subscriptions', function ($table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('currencies', function ($table): void {
            $table->increments('id');
            $table->string('symbol')->nullable();
            $table->string('title')->nullable();
            $table->decimal('rate', 10, 4)->default(1);
            $table->string('position')->nullable();
            $table->boolean('default')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('languages', function ($table): void {
            $table->increments('id');
            $table->string('locale')->nullable();
            $table->boolean('default')->default(true);
            $table->timestamps();
        });
        Schema::create('translations', function ($table): void {
            $table->increments('id');
            $table->string('locale')->nullable();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::create('wallets', function ($table): void {
            $table->increments('id');
            $table->string('uuid')->nullable();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('currency_id')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('personal_access_tokens', function ($table): void {
            $table->increments('id');
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('password_resets', function ($table): void {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_installer_actions_fail_closed_and_routes_are_not_exposed(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2) . '/routes/api.php');
        self::assertIsString($routes);
        self::assertStringNotContainsString("['prefix' => 'install']", $routes);

        $controller = (new \ReflectionClass(InstallController::class))->newInstanceWithoutConstructor();
        foreach ([
            $controller->checkInitFile(),
            $controller->setInitFile(new \stdClass()),
            $controller->setDatabase(new \stdClass()),
            $controller->createAdmin(new \stdClass()),
            $controller->migrationRun(),
        ] as $response) {
            self::assertSame(404, $response->getStatusCode());
            self::assertSame('Installer endpoints are disabled.', $response->getData(true)['message']);
        }
    }

    public function test_email_reset_uses_random_digest_and_is_single_use(): void
    {
        $user = User::query()->create([
            'email' => 'alice@example.test',
            'phone' => '15551234567',
            'password' => 'old-hash',
        ]);
        $resets = new PasswordResetService();
        $plainToken = $resets->issueEmailToken($user);

        self::assertMatchesRegularExpression('/\A\d{6}\z/', $plainToken);
        self::assertSame('email-reset:alice@example.test', DB::table('password_resets')->value('email'));
        self::assertSame(
            hash_hmac(
                'sha256',
                "email-reset\0alice@example.test\0" . $plainToken,
                'synthetic-hardening-test-key'
            ),
            DB::table('password_resets')->value('token')
        );
        self::assertNotSame($plainToken, DB::table('password_resets')->value('token'));
        self::assertNull($resets->consumeEmailToken($plainToken));
        self::assertNull($resets->consumeEmailToken($plainToken, 'bob@example.test'));
        self::assertSame(1, DB::table('password_resets')->count(), 'A code checked against another account must not consume the reset.');
        self::assertSame($user->id, $resets->consumeEmailToken($plainToken, 'alice@example.test')?->id);
        self::assertNull($resets->consumeEmailToken($plainToken, 'alice@example.test'));
        self::assertSame(0, DB::table('password_resets')->count());
    }

    public function test_unknown_email_reset_request_has_generic_success_response(): void
    {
        $controller = (new \ReflectionClass(LoginController::class))->newInstanceWithoutConstructor();
        $request = new EmailPasswordResetRequest();
        $request->replace(['email' => 'unknown@example.test']);

        $response = $controller->forgetPasswordEmail($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            'If the account exists, reset instructions have been queued for delivery.',
            $response->getData(true)['message']
        );
        self::assertSame(0, DB::table('password_resets')->count());
    }

    public function test_email_reset_tokens_expire_and_are_bound_to_user_records(): void
    {
        $user = User::query()->create(['email' => 'bob@example.test', 'password' => 'old-hash']);
        $resets = new PasswordResetService();
        $plainToken = $resets->issueEmailToken($user);

        Carbon::setTestNow(now()->addMinutes(PasswordResetService::EXPIRES_MINUTES + 1));

        self::assertNull($resets->consumeEmailToken($plainToken));
        self::assertNull($resets->consumeEmailToken($plainToken, 'bob@example.test'));
        self::assertSame(1, DB::table('password_resets')->count());
        self::assertNull($resets->consumeEmailToken(str_repeat('1', 6), 'bob@example.test'));
    }

    public function test_email_reset_endpoint_fails_closed_when_the_email_context_is_missing(): void
    {
        $user = User::query()->create(['email' => 'missing-context@example.test']);
        $resets = new PasswordResetService();
        $plainToken = $resets->issueEmailToken($user);
        $this->app->instance(
            'request',
            \Illuminate\Http\Request::create('/api/v1/auth/forgot/email-password/' . $plainToken, 'POST')
        );

        $controller = (new \ReflectionClass(LoginController::class))->newInstanceWithoutConstructor();
        $language = new \ReflectionProperty(\App\Http\Controllers\Controller::class, 'language');
        $language->setValue($controller, 'en');
        $response = $controller->forgetPasswordVerifyEmail($plainToken);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(1, DB::table('password_resets')->count());
        self::assertNull($resets->consumeEmailToken($plainToken));
    }

    public function test_mobile_reset_challenge_expires_and_can_only_be_consumed_once(): void
    {
        $user = User::query()->create(['email' => 'mobile@example.test', 'phone' => '15550001111']);
        $otherUser = User::query()->create(['email' => 'other@example.test', 'phone' => '15550002222']);
        $resets = new PasswordResetService();

        $resets->issueMobileChallenge($user);
        self::assertFalse($resets->consumeMobileChallenge($otherUser));
        self::assertTrue($resets->consumeMobileChallenge($user));
        self::assertFalse($resets->consumeMobileChallenge($user));

        $resets->issueMobileChallenge($user);
        Carbon::setTestNow(now()->addMinutes(PasswordResetService::EXPIRES_MINUTES + 1));
        self::assertFalse($resets->consumeMobileChallenge($user));
    }

    public function test_sms_reset_code_is_bound_to_phone_expires_and_is_single_use(): void
    {
        $resets = new PasswordResetService();
        $expiresAt = now()->addMinutes(5);
        $verifyId = (string)\Illuminate\Support\Str::uuid();
        $resets->issueSmsChallenge('15551230000', $verifyId, '482901', $expiresAt);

        self::assertFalse($resets->consumeSmsChallenge('15550000000', $verifyId, '482901'));
        self::assertFalse($resets->consumeSmsChallenge('15551230000', $verifyId, '000000'));
        self::assertTrue($resets->consumeSmsChallenge('15551230000', $verifyId, '482901'));
        self::assertFalse($resets->consumeSmsChallenge('15551230000', $verifyId, '482901'));

        $expiredVerifyId = (string)\Illuminate\Support\Str::uuid();
        $resets->issueSmsChallenge(
            '15551230000',
            $expiredVerifyId,
            '381024',
            now()->addMinutes(5)
        );
        Carbon::setTestNow(now()->addMinutes(6));
        self::assertFalse($resets->consumeSmsChallenge('15551230000', $expiredVerifyId, '381024'));
    }

    public function test_valid_registration_purpose_otp_can_create_a_new_phone_user(): void
    {
        $this->prepareNewUserRegistrationDependencies();
        $verifyId = (string)\Illuminate\Support\Str::uuid();
        $phone = '15550006666';
        Cache::put('sms-' . $verifyId, [
            'phone' => $phone,
            'verifyId' => $verifyId,
            'OTPCode' => 482901,
            'expiredAt' => now()->addMinutes(5),
            'purpose' => AuthByMobilePhone::PURPOSE_REGISTRATION,
        ], now()->addMinutes(5));

        $response = (new AuthByMobilePhone())->confirmOPTCode([
            'verifyId' => $verifyId,
            'verifyCode' => '482901',
        ]);

        $user = User::query()->where('phone', $phone)->first();
        self::assertNotNull($user, 'A valid registration-purpose OTP should create the new phone account.');
        self::assertNotNull($user->phone_verified_at);
        self::assertSame(200, $response->getStatusCode());
        self::assertTrue(Cache::has('sms-' . $verifyId));
        self::assertTrue(Cache::get('sms-' . $verifyId)['consumed']);
        self::assertSame(AuthByMobilePhone::PURPOSE_REGISTRATION, Cache::get('sms-' . $verifyId)['purpose']);

        $replay = (new AuthByMobilePhone())->confirmOPTCode([
            'verifyId' => $verifyId,
            'verifyCode' => '482901',
        ]);
        self::assertSame(400, $replay->getStatusCode());
        self::assertSame(1, User::query()->where('phone', $phone)->count());
    }

    public function test_superseded_sms_reset_codes_fail_closed_if_their_database_challenge_is_removed(): void
    {
        $phone = '15550007777';
        $oldVerifyId = (string)\Illuminate\Support\Str::uuid();
        $newVerifyId = (string)\Illuminate\Support\Str::uuid();
        $resets = new PasswordResetService();

        $resets->issueSmsChallenge($phone, $oldVerifyId, '482901', now()->addMinutes(5));
        $resets->issueSmsChallenge($phone, $newVerifyId, '731905', now()->addMinutes(5));

        $phoneKey = hash('sha256', $phone);
        $oldIdentity = 'sms-reset:' . $phoneKey . ':' . $oldVerifyId;
        self::assertStringStartsWith(
            'revoked:',
            (string)DB::table('password_resets')->where('email', $oldIdentity)->value('token')
        );

        // Simulate revocation by a cleanup or legacy path that removed the
        // challenge row. Cached purpose remains authoritative until expiry.
        DB::table('password_resets')->where('email', $oldIdentity)->delete();
        foreach (['482901', '000000'] as $code) {
            Cache::put('sms-' . $oldVerifyId, [
                'phone' => $phone,
                'verifyId' => $oldVerifyId,
                'OTPCode' => '482901',
                'expiredAt' => now()->addMinutes(5),
                'purpose' => AuthByMobilePhone::PURPOSE_PASSWORD_RESET,
            ], now()->addMinutes(5));

            $response = (new AuthByMobilePhone())->confirmOPTCode([
                'verifyId' => $oldVerifyId,
                'verifyCode' => $code,
            ]);

            self::assertSame(400, $response->getStatusCode());
            self::assertSame(
                AuthByMobilePhone::PURPOSE_PASSWORD_RESET,
                Cache::get('sms-' . $oldVerifyId)['purpose']
            );
            self::assertSame(0, User::query()->where('phone', $phone)->count());
        }

        self::assertTrue($resets->hasSmsChallenge($phone, $newVerifyId));
    }

    public function test_registration_otp_is_revoked_after_five_failed_attempts(): void
    {
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $verifyId = (string)\Illuminate\Support\Str::uuid();
        $phone = '15550009999';
        Cache::put('sms-' . $verifyId, [
            'phone' => $phone,
            'verifyId' => $verifyId,
            'OTPCode' => '731905',
            'expiredAt' => now()->addMinutes(5),
            'purpose' => AuthByMobilePhone::PURPOSE_REGISTRATION,
        ], now()->addMinutes(5));
        $auth = new AuthByMobilePhone();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            self::assertNotSame(200, $auth->confirmOPTCode([
                'verifyId' => $verifyId,
                'verifyCode' => '000000',
            ])->getStatusCode());
        }

        self::assertTrue(Cache::get('sms-' . $verifyId)['revoked']);
        self::assertSame(5, Cache::get('sms-' . $verifyId)['verifyAttempts']);
        self::assertNotSame(200, $auth->confirmOPTCode([
            'verifyId' => $verifyId,
            'verifyCode' => '731905',
        ])->getStatusCode());
        self::assertSame(0, User::query()->where('phone', $phone)->count());
    }

    public function test_firebase_phone_verification_rejects_missing_wrong_phone_and_expired_tokens(): void
    {
        $phone = '15550101010';
        $auth = new AuthByMobilePhone();

        $missingToken = $auth->confirmOPTCode([
            'type' => 'firebase',
            'phone' => $phone,
        ]);
        self::assertSame(400, $missingToken->getStatusCode());

        $this->mockFirebasePhoneToken('wrong-phone-token', '+15550101011');
        $wrongPhone = $auth->confirmOPTCode([
            'type' => 'firebase',
            'phone' => $phone,
            'id' => 'wrong-phone-token',
            'password' => 'a-valid-password',
        ]);
        self::assertSame(400, $wrongPhone->getStatusCode());

        $this->mockFirebasePhoneToken('expired-token', '+15550101010', true);
        $expired = $auth->confirmOPTCode([
            'type' => 'firebase',
            'phone' => $phone,
            'id' => 'expired-token',
            'password' => 'a-valid-password',
        ]);

        self::assertSame(400, $expired->getStatusCode());
        self::assertSame(0, User::query()->where('phone', $phone)->count());
        self::assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_valid_firebase_phone_token_can_register_a_new_phone_user(): void
    {
        $this->prepareNewUserRegistrationDependencies();
        $phone = '15550102020';
        $this->mockFirebasePhoneToken('valid-new-phone-token', '+1 (555) 010-2020');

        $response = (new AuthByMobilePhone())->confirmOPTCode([
            'type' => 'firebase',
            'phone' => $phone,
            'id' => 'valid-new-phone-token',
            'password' => 'new-account-password',
            'firstname' => 'New',
            'lastname' => 'User',
        ]);

        $user = User::query()->where('phone', $phone)->first();
        self::assertNotNull($user);
        self::assertTrue(password_verify('new-account-password', $user->password));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, DB::table('personal_access_tokens')->count());
    }

    public function test_firebase_registration_never_overwrites_an_existing_phone_account(): void
    {
        $phone = '15550103030';
        $user = User::query()->create([
            'phone' => $phone,
            'email' => 'existing@example.test',
            'password' => password_hash('existing-password', PASSWORD_BCRYPT),
        ]);
        $originalPassword = $user->password;
        $this->mockFirebasePhoneToken('existing-phone-token', '+15550103030');

        $response = (new AuthByMobilePhone())->confirmOPTCode([
            'type' => 'firebase',
            'phone' => $phone,
            'id' => 'existing-phone-token',
            'email' => 'attacker@example.test',
            'password' => 'attacker-password',
        ]);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame($originalPassword, $user->fresh()->password);
        self::assertSame('existing@example.test', $user->fresh()->email);
        self::assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_sms_registration_purpose_cannot_log_in_an_existing_phone_account(): void
    {
        $phone = '15550104040';
        $user = User::query()->create([
            'phone' => $phone,
            'email' => 'sms-existing@example.test',
            'password' => password_hash('existing-password', PASSWORD_BCRYPT),
        ]);
        $originalPassword = $user->password;
        $verifyId = (string)\Illuminate\Support\Str::uuid();
        Cache::put('sms-' . $verifyId, [
            'phone' => $phone,
            'verifyId' => $verifyId,
            'OTPCode' => '482901',
            'expiredAt' => now()->addMinutes(5),
            'purpose' => AuthByMobilePhone::PURPOSE_REGISTRATION,
        ], now()->addMinutes(5));

        $response = (new AuthByMobilePhone())->confirmOPTCode([
            'verifyId' => $verifyId,
            'verifyCode' => '482901',
        ]);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame($originalPassword, $user->fresh()->password);
        self::assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_sms_reset_wrong_code_attempt_limit_persists_revocation_and_prevents_replay(): void
    {
        $phone = '15550008888';
        $verifyId = (string)\Illuminate\Support\Str::uuid();
        $resets = new PasswordResetService();
        $resets->issueSmsChallenge($phone, $verifyId, '731905', now()->addMinutes(5));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            self::assertFalse($resets->consumeSmsChallenge($phone, $verifyId, '000000'));
        }

        $identity = 'sms-reset:' . hash('sha256', $phone) . ':' . $verifyId;
        $revokedToken = (string)DB::table('password_resets')->where('email', $identity)->value('token');
        self::assertStringStartsWith('revoked:', $revokedToken);
        self::assertTrue($resets->hasSmsChallenge($phone, $verifyId));
        self::assertFalse($resets->consumeSmsChallenge($phone, $verifyId, '731905'));
        self::assertSame($revokedToken, DB::table('password_resets')->where('email', $identity)->value('token'));
    }

    public function test_mobile_firebase_proof_cannot_consume_an_email_reset_and_password_update_is_atomic(): void
    {
        $user = User::query()->create([
            'email' => 'firebase@example.test',
            'phone' => '15550003333',
            'password' => 'old-hash',
        ]);
        $resets = new PasswordResetService();
        $emailCode = $resets->issueEmailToken($user);
        $expiresAt = now()->addHour();
        $firebaseIdToken = 'synthetic-signed-firebase-id-token';
        $newPasswordHash = password_hash('new-password', PASSWORD_BCRYPT);

        self::assertTrue($resets->completeFirebasePasswordReset(
            $user,
            $firebaseIdToken,
            $expiresAt,
            $newPasswordHash
        ));
        self::assertTrue(password_verify('new-password', $user->fresh()->password));
        self::assertSame(
            1,
            DB::table('password_resets')->where('email', 'email-reset:firebase@example.test')->count(),
            'Firebase completion must not consume an email-purpose code.'
        );
        self::assertFalse($resets->completeFirebasePasswordReset(
            $user,
            $firebaseIdToken,
            $expiresAt,
            password_hash('replay-password', PASSWORD_BCRYPT)
        ));
        self::assertFalse(password_verify('replay-password', $user->fresh()->password));
        self::assertSame(6, strlen($emailCode));
    }

    public function test_shipped_mobile_firebase_request_shape_does_not_require_email_or_password(): void
    {
        $request = \Illuminate\Http\Request::create('/api/v1/auth/forgot/password/confirm', 'POST', [
            'phone' => '15550004444',
            'type' => 'firebase',
            'id' => 'synthetic-firebase-id-token',
        ]);
        $this->app->instance('request', $request);

        $form = new \App\Http\Requests\Auth\PasswordResetPhoneVerifyRequest();
        $rules = $form->rules();

        self::assertContains('required', $rules['id']);
        self::assertContains('required', $rules['phone']);
        self::assertContains('nullable', $rules['email']);
        self::assertContains('nullable', $rules['password']);

        $phoneVerifyRules = (new \App\Http\Requests\Auth\PhoneVerifyRequest())->rules();
        self::assertContains('required', $phoneVerifyRules['id']);
    }

    public function test_sms_reset_consumption_and_password_change_share_one_transaction(): void
    {
        $user = User::query()->create([
            'email' => 'sms@example.test',
            'phone' => '15550005555',
            'password' => 'old-hash',
        ]);
        $resets = new PasswordResetService();
        $verifyId = (string)\Illuminate\Support\Str::uuid();
        $resets->issueSmsChallenge('15550005555', $verifyId, '731905', now()->addMinutes(5));
        $newPasswordHash = password_hash('sms-new-password', PASSWORD_BCRYPT);

        self::assertNull($resets->completeSmsPasswordReset(
            '15550005555',
            $verifyId,
            '000000',
            $newPasswordHash
        ));
        self::assertSame(1, DB::table('password_resets')->where('email', 'like', 'sms-reset:%')->count());

        $updated = $resets->completeSmsPasswordReset(
            '15550005555',
            $verifyId,
            '731905',
            $newPasswordHash
        );

        self::assertSame($user->id, $updated?->id);
        self::assertTrue(password_verify('sms-new-password', $user->fresh()->password));
        self::assertSame(1, DB::table('password_resets')->where('email', 'like', 'sms-reset:%')->count());
        self::assertNull($resets->completeSmsPasswordReset(
            '15550005555',
            $verifyId,
            '731905',
            password_hash('replay', PASSWORD_BCRYPT)
        ));
    }

    public function test_email_reset_code_revokes_after_five_guesses_for_the_target_account(): void
    {
        $user = User::query()->create(['email' => 'throttled@example.test']);
        $resets = new PasswordResetService();
        $issued = $resets->issueEmailToken($user);
        $wrongCode = $issued === '000000' ? '000001' : '000000';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            self::assertNull($resets->consumeEmailToken($wrongCode, 'throttled@example.test'));
        }

        self::assertSame(0, DB::table('password_resets')
            ->where('email', 'email-reset:throttled@example.test')
            ->count());
        self::assertNull($resets->consumeEmailToken($issued, 'throttled@example.test'));
    }

    public function test_reset_issuance_and_verification_are_rate_limited(): void
    {
        $resets = new PasswordResetService();
        $issueResults = [];
        for ($attempt = 0; $attempt < 4; $attempt++) {
            $issueResults[] = $resets->allowIssuance('email', 'alice@example.test');
        }
        self::assertSame([true, true, true, false], $issueResults);

        $verifyResults = [];
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $verifyResults[] = $resets->allowVerification('sms', '15551234567');
        }
        self::assertSame([true, true, true, true, true, false], $verifyResults);
    }

    private function prepareNewUserRegistrationDependencies(): void
    {
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        DB::table('roles')->insert([
            'name' => 'user',
            'guard_name' => config('auth.defaults.guard'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $roleId = DB::table('roles')->where('name', 'user')->value('id');
        User::created(function (User $user) use ($roleId): void {
            DB::table('model_has_roles')->insert([
                'role_id' => $roleId,
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        });

        $authGuard = \Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $authGuard->shouldReceive('id')->andReturn(null);
        $authFactory = \Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);
        $authFactory->shouldReceive('guard')->with('sanctum')->andReturn($authGuard);
        $this->app->instance('auth', $authFactory);
    }

    private function mockFirebasePhoneToken(string $idToken, string $phoneClaim, bool $expired = false): void
    {
        $verifiedToken = \Mockery::mock(UnencryptedToken::class);
        $verifiedToken->shouldReceive('isExpired')->andReturn($expired);
        $verifiedToken->shouldReceive('claims')->andReturn(
            new DataSet(['phone_number' => $phoneClaim], 'synthetic')
        );

        $firebaseAuth = \Mockery::mock(\Kreait\Firebase\Contract\Auth::class);
        $firebaseAuth->shouldReceive('verifyIdToken')
            ->once()
            ->with($idToken)
            ->andReturn($verifiedToken);

        $firebaseManager = \Mockery::mock();
        $firebaseManager->shouldReceive('auth')->once()->andReturn($firebaseAuth);
        $this->app->instance('firebase.manager', $firebaseManager);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('firebase.manager');
    }
}