<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Helpers\AdminSmtpTestPolicy;
use App\Http\Requests\EmailSetting\StoreRequest;
use App\Http\Resources\EmailSettingResource;
use App\Models\EmailSetting;
use App\Models\User;
use App\Services\EmailSettingService\EmailSendService;
use App\Services\EmailSettingService\EmailSettingService;
use Illuminate\Database\Schema\Blueprint;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\AbstractLogger;

final class SmtpCredentialSecurityTest extends IsolatedTestCase
{
    private array $logs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance('files', new \Illuminate\Filesystem\Filesystem);
        $this->app['config']->set('development.email.mode', 'log');
        $this->app['config']->set('development.email.admin_test_enabled', false);
        $this->database->schema()->create('email_settings', function (Blueprint $t): void {
            $t->id(); $t->string('host'); $t->integer('port'); $t->text('password')->nullable();
            $t->string('from_to'); $t->string('from_site'); $t->boolean('active')->default(false);
            $t->boolean('smtp_auth')->default(true); $t->boolean('smtp_debug')->default(true);
            $t->text('ssl')->nullable(); $t->timestamps();
        });
        $logs = &$this->logs;
        $this->app->instance('log', new class($logs) extends AbstractLogger {
            private array $entries;
            public function __construct(array &$entries) { $this->entries = &$entries; }
            public function log($level, \Stringable|string $message, array $context = []): void
            { $this->entries[] = ['level' => $level, 'message' => (string) $message, 'context' => $context]; }
            public function channel($name): self { return $this; }
        });
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('log');
    }

    private function setting(): EmailSetting
    {
        return EmailSetting::create([
            'host' => 'smtp.example.invalid', 'port' => 465,
            'password' => 'SYNTHETIC-SECRET-NOT-A-REAL-CREDENTIAL',
            'from_to' => 'fixture@example.invalid', 'from_site' => 'Fixture',
            'ssl' => ['ssl' => ['verify_peer' => false, 'allow_self_signed' => true]],
        ]);
    }

    private function sender(): EmailSendService
    {
        // Native sender methods, without unrelated Currency/Language directory queries.
        return new class extends EmailSendService { public function __construct() {} };
    }

    private function authorizeTest(): void
    {
        $state = realpath(dirname(__DIR__, 4) . '/.local/staging-mvp');
        $this->app->instance('agendaally.isolated_admin_smtp_runtime', $state);
        $this->app->useBootstrapPath($state . '/bootstrap');
        $this->app['config']->set([
            'app.env' => 'production', 'app.debug' => false,
            'app.url' => 'https://localhost:8443',
            'development.urls.admin' => 'https://localhost:8444',
            'development.email.admin_test_enabled' => true,
            'database.default' => 'staging',
            'database.connections.staging.database' => 'agendaally_staging_mvp',
        ]);
        // Eloquent retains the separate in-memory hardening connection; no real DB.
    }

    public function test_ciphertext_persistence_and_resource_and_model_serialization_do_not_expose_secret(): void
    {
        $setting = $this->setting();
        $raw = $setting->getRawOriginal('password');
        self::assertStringStartsWith(EmailSetting::PASSWORD_PREFIX, $raw);
        self::assertStringNotContainsString('SYNTHETIC-SECRET', $raw);
        self::assertSame('SYNTHETIC-SECRET-NOT-A-REAL-CREDENTIAL', $setting->password);
        $api = (new EmailSettingResource($setting))->resolve();
        self::assertArrayNotHasKey('password', $api);
        self::assertTrue($api['password_configured']);
        self::assertStringNotContainsString('SYNTHETIC-SECRET', json_encode($api));
        self::assertArrayNotHasKey('password', $setting->toArray());
    }

    public function test_blank_and_null_edits_preserve_ciphertext_and_replacement_reencrypts(): void
    {
        $setting = $this->setting();
        $original = $setting->getRawOriginal('password');
        foreach (['', '   ', null] as $blank) {
            $setting->update(['password' => $blank, 'from_site' => 'Edited']);
            self::assertSame($original, $setting->fresh()->getRawOriginal('password'));
        }
        $setting->update(['password' => 'SYNTHETIC-REPLACEMENT']);
        self::assertNotSame($original, $setting->fresh()->getRawOriginal('password'));
        self::assertSame('SYNTHETIC-REPLACEMENT', $setting->fresh()->password);
    }

    public function test_legacy_and_corrupt_credentials_fail_closed_without_serializing_values(): void
    {
        $setting = $this->setting();
        $setting->setRawAttributes([...$setting->getAttributes(), 'password' => 'LEGACY-SYNTHETIC-SECRET'], true);
        self::assertSame('replacement_required', $setting->credentialStatus());
        self::assertFalse((new EmailSettingResource($setting))->resolve()['password_configured']);
        try { $unused = $setting->password; self::fail('Legacy credential was usable'); }
        catch (\RuntimeException $e) { self::assertSame('SMTP_CREDENTIAL_REPLACEMENT_REQUIRED', $e->getMessage()); }
        $setting->setRawAttributes([...$setting->getAttributes(), 'password' => EmailSetting::PASSWORD_PREFIX . 'broken'], true);
        self::assertSame('unavailable', $setting->credentialStatus());
    }

    public function test_create_requires_password_and_update_blanks_are_removed_before_validation(): void
    {
        $create = StoreRequest::create('/settings', 'POST', ['host' => 'smtp.example.invalid', 'port' => 465]);
        self::assertTrue($this->app['validator']->make($create->all(), $create->rules())->fails());
        foreach (['', ' ', null] as $blank) {
            $update = StoreRequest::create('/settings/1', 'PUT', [
                'host' => 'smtp.example.invalid', 'port' => 465,
                'from_to' => 'fixture@example.invalid', 'from_site' => 'Fixture', 'password' => $blank,
            ]);
            $prepare = new \ReflectionMethod($update, 'prepareForValidation');
            $prepare->invoke($update);
            self::assertArrayNotHasKey('password', $update->all());
            self::assertFalse($this->app['validator']->make($update->all(), $update->rules())->fails());
        }
    }

    public function test_default_and_untrusted_production_flag_cannot_construct_transport(): void
    {
        $setting = $this->setting();
        $this->app->bind(PHPMailer::class, fn () => throw new \RuntimeException('Transport must not be constructed'));
        self::assertFalse($this->sender()->sendTest($setting, 'recipient@example.invalid')['status']);
        $this->app['config']->set(['app.env' => 'production', 'development.email.admin_test_enabled' => true]);
        self::assertFalse(AdminSmtpTestPolicy::allowed());
        self::assertFalse($this->sender()->sendTest($setting, 'recipient@example.invalid')['status']);
    }

    public function test_normal_preview_permission_requires_every_trusted_runtime_boundary(): void
    {
        $facts = [
            'published' => false, 'sapi' => 'cli-server', 'authority_matches' => true,
            'root_matches' => true, 'database_matches' => true, 'driver' => 'sqlite',
            'environment' => 'local', 'debug' => false, 'development' => true,
            'owned_database' => true, 'permission' => true, 'email_mode' => 'log',
            'mailer' => 'log', 'payments' => 'disabled', 'sms' => 'log',
            'firebase' => false, 'maps' => false, 'admin_port' => 3003, 'api_port' => 8000,
            'tls_socket_available' => true,
        ];
        self::assertTrue(AdminSmtpTestPolicy::normalPreviewFactsAllowed($facts));
        foreach ([
            'published' => true, 'sapi' => 'cli', 'authority_matches' => false,
            'root_matches' => false, 'database_matches' => false, 'driver' => 'mysql',
            'environment' => 'production', 'debug' => true, 'development' => false,
            'owned_database' => false, 'permission' => false, 'email_mode' => 'smtp',
            'mailer' => 'smtp', 'payments' => 'test', 'sms' => 'provider',
            'firebase' => true, 'maps' => true, 'admin_port' => 8444, 'api_port' => 8443,
            'tls_socket_available' => false,
        ] as $key => $value) {
            self::assertFalse(AdminSmtpTestPolicy::normalPreviewFactsAllowed([...$facts, $key => $value]), $key);
        }
        $this->app['config']->set('development.email.normal_admin_test_enabled', true);
        self::assertFalse(AdminSmtpTestPolicy::isNormalPreviewRuntime(), 'CLI workers never gain permission');
    }

    public function test_isolated_permission_is_default_off_strict_and_denied_in_restore_or_other_runtime(): void
    {
        $this->authorizeTest();
        $this->app['config']->set('development.email.admin_test_enabled', false);
        self::assertFalse(AdminSmtpTestPolicy::allowed());
        $this->app['config']->set('development.email.admin_test_enabled', 'true');
        self::assertFalse(AdminSmtpTestPolicy::allowed());
        $this->app['config']->set('development.email.admin_test_enabled', true);
        $this->app->useBootstrapPath(realpath(dirname(__DIR__, 4) . '/.local/staging-mvp') . '/restore/bootstrap');
        self::assertFalse(AdminSmtpTestPolicy::allowed());
        $this->app->useBootstrapPath(realpath(dirname(__DIR__, 4) . '/.local/staging-mvp') . '/bootstrap');
        $this->app['config']->set('app.url', 'https://production.example.invalid');
        self::assertFalse(AdminSmtpTestPolicy::allowed());
    }

    public function test_published_runtime_cannot_use_the_exception_even_with_copied_isolated_authority(): void
    {
        $this->authorizeTest();
        $original = getenv('REPLIT_DEPLOYMENT');
        try {
            // Process-local fixture only; no workspace env variable is changed.
            putenv('REPLIT_DEPLOYMENT=1');
            self::assertFalse(AdminSmtpTestPolicy::allowed());
        } finally {
            $original === false ? putenv('REPLIT_DEPLOYMENT') : putenv('REPLIT_DEPLOYMENT=' . $original);
        }
    }

    public function test_native_service_blank_update_preserves_encrypted_secret_and_refuses_insecure_tls_options(): void
    {
        $setting = $this->setting();
        $original = $setting->getRawOriginal('password');
        $service = new class extends EmailSettingService {
            public function __construct() { $this->model = new EmailSetting; }
        };
        $result = $service->update($setting, ['password' => null, 'from_site' => 'Changed',
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]]);
        self::assertTrue($result['status']);
        self::assertSame($original, $setting->fresh()->getRawOriginal('password'));
        self::assertSame(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'allow_self_signed' => false]], $setting->fresh()->ssl);
        self::assertStringNotContainsString('SYNTHETIC-SECRET', json_encode($result));
    }

    public function test_authorized_exception_only_reaches_test_and_enforces_tls_with_no_real_network(): void
    {
        $setting = $this->setting();
        $this->authorizeTest();
        self::assertTrue(AdminSmtpTestPolicy::allowed());
        $mail = new class(true) extends PHPMailer {
            public int $sends = 0;
            public function send() { ++$this->sends; return true; }
        };
        $this->app->instance(PHPMailer::class, $mail);
        $this->app->instance('view', new class {
            public function make(...$args) { return new class { public function render() { return '<p>fixture</p>'; } }; }
        });
        self::assertTrue($this->sender()->sendTest($setting, 'recipient@example.invalid')['status']);
        self::assertSame(1, $mail->sends);
        self::assertSame(PHPMailer::ENCRYPTION_SMTPS, $mail->SMTPSecure);
        self::assertSame(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'allow_self_signed' => false]], $mail->SMTPOptions);
        foreach (['sendVerify', 'sendEmailPasswordReset'] as $operation) {
            self::assertFalse($this->sender()->$operation(new User, 'fixture-challenge')['status']);
        }
        self::assertFalse($this->sender()->sendDeliveryDriverInvitation('recipient@example.invalid', 'Fixture', 'fixture', 'fixture')['status']);
        self::assertSame(1, $mail->sends);
        self::assertSame('log', \App\Helpers\EnvironmentPolicy::emailMode());
    }

    public function test_errors_and_debug_output_are_generic_even_when_transport_contains_a_secret(): void
    {
        $setting = $this->setting();
        $this->authorizeTest();
        $mail = new class(true) extends PHPMailer {
            public function send() {
                ($this->Debugoutput)('AUTH SYNTHETIC-SECRET-NOT-A-REAL-CREDENTIAL', 1);
                $this->ErrorInfo = 'SYNTHETIC-SECRET-NOT-A-REAL-CREDENTIAL';
                throw new \RuntimeException($this->ErrorInfo);
            }
        };
        $this->app->instance(PHPMailer::class, $mail);
        $this->app->instance('view', new class {
            public function make(...$args) { return new class { public function render() { return '<p>fixture</p>'; } }; }
        });
        $result = $this->sender()->sendTest($setting, 'recipient@example.invalid');
        self::assertFalse($result['status']);
        self::assertSame('smtp_delivery_unconfirmed', $result['error_code']);
        self::assertStringNotContainsString('SYNTHETIC-SECRET', json_encode([$result, $this->logs]));
        self::assertNotEmpty($this->logs);
    }
}
