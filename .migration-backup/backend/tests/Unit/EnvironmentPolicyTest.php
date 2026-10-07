<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\EnvironmentPolicy;
use PHPUnit\Framework\TestCase;

class EnvironmentPolicyTest extends TestCase
{
    public function test_opted_in_local_configuration_is_safe_and_fully_explicit(): void
    {
        $errors = EnvironmentPolicy::configurationErrors([
            'enabled' => true,
            'payments' => ['mode' => 'disabled', 'mode_is_explicit' => true],
            'sms' => ['mode' => 'log'],
            'email' => ['mode' => 'log'],
            'firebase' => ['enabled' => false],
            'recaptcha' => ['enabled' => false, 'enabled_is_explicit' => true],
        ], 'local');

        $this->assertSame([], $errors);
    }

    public function test_the_development_flag_never_activates_local_behavior_outside_local_or_testing(): void
    {
        $errors = EnvironmentPolicy::configurationErrors([
            'enabled' => true,
            'payments' => ['mode' => 'disabled', 'mode_is_explicit' => true],
            'sms' => ['mode' => 'provider'],
            'email' => ['mode' => 'smtp'],
            'firebase' => ['enabled' => true],
            'recaptcha' => ['enabled' => false, 'enabled_is_explicit' => true],
        ], 'production');

        $this->assertContains(
            'DEVELOPMENT_MODE may only be enabled with APP_ENV=local or testing.',
            $errors
        );
        $this->assertContains(
            'reCAPTCHA may only be disabled with APP_ENV=local or testing.',
            $errors
        );
    }

    public function test_explicit_staging_sandbox_is_allowed_but_live_payments_are_not(): void
    {
        $development = [
            'enabled' => false,
            'payments' => ['mode' => 'sandbox', 'mode_is_explicit' => true],
            'sms' => ['mode' => 'provider'],
            'email' => ['mode' => 'smtp'],
            'firebase' => ['enabled' => true],
            'recaptcha' => ['enabled' => true],
        ];

        $this->assertSame([], EnvironmentPolicy::configurationErrors($development, 'staging'));

        $development['payments']['mode'] = 'live';
        $this->assertContains(
            'Live payments may only be enabled with APP_ENV=production.',
            EnvironmentPolicy::configurationErrors($development, 'staging')
        );
    }

    public function test_local_live_smtp_sms_and_firebase_settings_fail_closed(): void
    {
        $errors = EnvironmentPolicy::configurationErrors([
            'enabled' => true,
            'payments' => ['mode' => 'live', 'mode_is_explicit' => true],
            'sms' => ['mode' => 'provider'],
            'email' => ['mode' => 'smtp'],
            'firebase' => ['enabled' => true],
            'recaptcha' => ['enabled' => true],
        ], 'testing');

        $this->assertContains('Local development mode cannot enable live payments.', $errors);
        $this->assertContains('Local development mode cannot send SMS through a live provider.', $errors);
        $this->assertContains('Local development mode cannot send email through SMTP.', $errors);
        $this->assertContains('Firebase authentication must be explicitly disabled in local development mode.', $errors);
    }

    public function test_owned_sqlite_opt_in_requires_local_development_mode(): void
    {
        $errors = EnvironmentPolicy::configurationErrors([
            'enabled' => false,
            'database' => ['owned_sqlite_enabled' => true],
            'payments' => ['mode' => 'disabled', 'mode_is_explicit' => true],
            'sms' => ['mode' => 'log'],
            'email' => ['mode' => 'log'],
            'firebase' => ['enabled' => false],
            'recaptcha' => ['enabled' => false, 'enabled_is_explicit' => true],
        ], 'local');

        $this->assertContains(
            'AGENDAALLY_DEVELOPMENT_DATABASE=true requires DEVELOPMENT_MODE=true and APP_ENV=local.',
            $errors
        );
    }

    public function test_production_requires_a_strong_key_explicit_payment_mode_and_https_origins(): void
    {
        $errors = EnvironmentPolicy::configurationErrors([
            'enabled' => false,
            'payments' => ['mode' => 'disabled', 'mode_is_explicit' => false],
            'sms' => ['mode' => 'provider'],
            'email' => ['mode' => 'smtp'],
            'firebase' => ['enabled' => true],
            'recaptcha' => ['enabled' => true],
        ], 'production', [
            'key' => '',
            'cors_origins' => ['*'],
            'backend_url' => 'http://localhost:8000',
            'storefront_url' => 'http://localhost:3000/',
            'admin_url' => null,
        ]);

        $this->assertContains('PAYMENT_MODE must be explicitly configured in production.', $errors);
        $this->assertContains('APP_KEY must be a valid AES-256 application key in production.', $errors);
        $this->assertContains('Production CORS origins must be explicit; wildcard origins are not allowed.', $errors);
        $this->assertCount(3, array_filter($errors, fn (string $error) => str_contains($error, 'must be an absolute HTTPS URL')));
    }

    public function test_an_explicitly_configured_production_environment_passes_validation(): void
    {
        $errors = EnvironmentPolicy::configurationErrors([
            'enabled' => false,
            'payments' => ['mode' => 'live', 'mode_is_explicit' => true],
            'sms' => ['mode' => 'provider'],
            'email' => ['mode' => 'smtp'],
            'firebase' => ['enabled' => true],
            'recaptcha' => ['enabled' => true],
        ], 'production', [
            'key' => 'base64:' . base64_encode(str_repeat('k', 32)),
            'debug' => false,
            'cors_origins' => ['https://store.example.test', 'https://admin.example.test'],
            'backend_url' => 'https://api.example.test',
            'storefront_url' => 'https://store.example.test',
            'admin_url' => 'https://admin.example.test',
        ]);

        $this->assertSame([], $errors);
    }
}