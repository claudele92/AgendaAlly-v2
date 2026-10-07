<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Console\Commands\DevelopmentDatabaseGuard;
use RuntimeException;

/**
 * Central guard for optional external integrations and local-only behavior.
 *
 * All mock/log behavior requires both DEVELOPMENT_MODE=true and APP_ENV to
 * be exactly local or testing. This class deliberately never treats staging
 * as a local development environment.
 */
final class EnvironmentPolicy
{
    private const SAFE_ENVIRONMENTS = ['local', 'testing'];

    private const PAYMENT_MODES = ['disabled', 'sandbox', 'live', 'test'];

    private const LOGICAL_MODES = ['disabled', 'log', 'provider'];

    /**
     * Return configuration problems without revealing any configured values.
     *
     * @param array<string, mixed> $development
     * @param array<string, mixed> $application
     * @return list<string>
     */
    public static function configurationErrors(
        array $development,
        string $environment,
        array $application = []
    ): array {
        $errors = [];
        $developmentEnabled = (bool) ($development['enabled'] ?? false);
        $isSafeDevelopmentEnvironment = $developmentEnabled
            && in_array($environment, self::SAFE_ENVIRONMENTS, true);
        $paymentMode = $development['payments']['mode'] ?? null;
        $smsMode = $development['sms']['mode'] ?? null;
        $emailMode = $development['email']['mode'] ?? null;
        $recaptchaEnabled = (bool) ($development['recaptcha']['enabled'] ?? true);
        $ownedDevelopmentDatabaseEnabled = (bool) ($development['database']['owned_sqlite_enabled'] ?? false);

        if (!(bool) ($development['database']['owned_sqlite_setting_is_valid'] ?? true)) {
            $errors[] = 'AGENDAALLY_DEVELOPMENT_DATABASE must be explicitly true or false.';
        }

        if ($developmentEnabled && !in_array($environment, self::SAFE_ENVIRONMENTS, true)) {
            $errors[] = 'DEVELOPMENT_MODE may only be enabled with APP_ENV=local or testing.';
        }

        if ($ownedDevelopmentDatabaseEnabled && (!$developmentEnabled || $environment !== 'local')) {
            $errors[] = 'AGENDAALLY_DEVELOPMENT_DATABASE=true requires DEVELOPMENT_MODE=true and APP_ENV=local.';
        }

        if (!is_string($paymentMode) || !in_array($paymentMode, self::PAYMENT_MODES, true)) {
            $errors[] = 'PAYMENT_MODE must be disabled, sandbox, or live (test is reserved for APP_ENV=testing).';
        } elseif ($paymentMode === 'test' && $environment !== 'testing') {
            $errors[] = 'The test payment mode may only be used with APP_ENV=testing.';
        } elseif ($paymentMode === 'live' && $environment !== 'production') {
            $errors[] = 'Live payments may only be enabled with APP_ENV=production.';
        } elseif ($paymentMode === 'sandbox' && $environment === 'production') {
            $errors[] = 'Sandbox payments may not be enabled with APP_ENV=production.';
        }

        if (
            $isSafeDevelopmentEnvironment
            && is_string($paymentMode)
            && $paymentMode === 'live'
        ) {
            $errors[] = 'Local development mode cannot enable live payments.';
        }

        if (!is_string($smsMode) || !in_array($smsMode, self::LOGICAL_MODES, true)) {
            $errors[] = 'SMS_MODE must be disabled, log, or provider.';
        } elseif ($isSafeDevelopmentEnvironment && $smsMode === 'provider') {
            $errors[] = 'Local development mode cannot send SMS through a live provider.';
        }

        if (!is_string($emailMode) || !in_array($emailMode, ['disabled', 'log', 'smtp'], true)) {
            $errors[] = 'EMAIL_MODE must be disabled, log, or smtp.';
        } elseif ($isSafeDevelopmentEnvironment && $emailMode === 'smtp') {
            $errors[] = 'Local development mode cannot send email through SMTP.';
        }

        if ($isSafeDevelopmentEnvironment && (bool) ($development['firebase']['enabled'] ?? false)) {
            $errors[] = 'Firebase authentication must be explicitly disabled in local development mode.';
        }

        if ($isSafeDevelopmentEnvironment && !$recaptchaEnabled
            && !(bool) ($development['recaptcha']['enabled_is_explicit'] ?? false)) {
            $errors[] = 'RECAPTCHA_ENABLED=false must be explicitly configured in local development mode.';
        }

        if (!in_array($environment, self::SAFE_ENVIRONMENTS, true) && !$recaptchaEnabled) {
            $errors[] = 'reCAPTCHA may only be disabled with APP_ENV=local or testing.';
        }

        if ($environment === 'production') {
            if (!(bool) ($development['payments']['mode_is_explicit'] ?? false)) {
                $errors[] = 'PAYMENT_MODE must be explicitly configured in production.';
            }

            if (!self::isValidEncryptionKey($application['key'] ?? null)) {
                $errors[] = 'APP_KEY must be a valid AES-256 application key in production.';
            }

            if ((bool) ($application['debug'] ?? false)) {
                $errors[] = 'APP_DEBUG must be false in production.';
            }

            $origins = $application['cors_origins'] ?? [];
            if (!is_array($origins) || $origins === []) {
                $errors[] = 'CORS_ALLOWED_ORIGINS must contain the production web application origins.';
            } elseif (in_array('*', $origins, true)) {
                $errors[] = 'Production CORS origins must be explicit; wildcard origins are not allowed.';
            } else {
                foreach ($origins as $origin) {
                    if (!self::isHttpsOrigin($origin)) {
                        $errors[] = 'Every production CORS origin must be an absolute HTTPS origin.';
                        break;
                    }
                }
            }

            foreach ([
                'backend_url' => 'LARAVEL_BACKEND_URL',
                'storefront_url' => 'CUSTOMER_STOREFRONT_URL',
                'admin_url' => 'VENDOR_ADMIN_URL',
            ] as $urlKey => $variable) {
                if (!self::isHttpsUrl($application[$urlKey] ?? null)) {
                    $errors[] = "$variable must be an absolute HTTPS URL in production.";
                }
            }
        }

        return array_values(array_unique($errors));
    }

    /**
     * Fail application boot before an unsafe configuration can reach a
     * controller, queued callback, or third-party SDK.
     */
    public static function assertRuntimeConfiguration(): void
    {
        $errors = self::configurationErrors(
            (array) config('development', []),
            self::currentEnvironment(),
            [
                'key' => config('app.key'),
                'debug' => config('app.debug'),
                'cors_origins' => config('cors.allowed_origins', []),
                'backend_url' => config('development.urls.api'),
                'storefront_url' => config('development.urls.storefront'),
                'admin_url' => config('development.urls.admin'),
            ]
        );

        if ($errors !== []) {
            throw new RuntimeException(
                "Unsafe application environment configuration:\n- " . implode("\n- ", $errors)
            );
        }

        if (
            function_exists('app')
            && !app()->runningInConsole()
            && (bool) config('development.database.owned_sqlite_enabled', false)
        ) {
            $basePath = base_path();

            DevelopmentDatabaseGuard::assertRuntimeDatabase(
                $basePath,
                self::currentEnvironment(),
                config('development.database.owned_sqlite_enabled', false),
                config('development.enabled', false),
                (string) config('database.default'),
                (array) config('database.connections.sqlite', []),
                DevelopmentDatabaseGuard::reviewedManifest($basePath)
            );
        }
    }

    public static function isLocalDevelopment(): bool
    {
        return (bool) config('development.enabled', false)
            && in_array(self::currentEnvironment(), self::SAFE_ENVIRONMENTS, true);
    }

    public static function paymentMode(): string
    {
        return (string) config(
            'development.payments.mode',
            self::currentEnvironment() === 'testing' ? 'test' : 'disabled'
        );
    }

    /**
     * Provider callbacks and payment-method listings share this allowlist.
     * Sandbox support is intentionally limited to providers whose credentials
     * are separately checked as test/sandbox credentials at the service edge.
     */
    public static function paymentProviderEnabled(string $provider): bool
    {
        $provider = strtolower($provider);
        $mode = self::paymentMode();
        $environment = self::currentEnvironment();

        if ($environment === 'testing' && $mode === 'test') {
            return true;
        }

        if ($mode === 'live') {
            return $environment === 'production';
        }

        if ($mode === 'sandbox') {
            return $environment !== 'production'
                && in_array($provider, (array) config('development.payments.sandbox_providers', []), true);
        }

        return false;
    }

    public static function smsMode(): string
    {
        return (string) config(
            'development.sms.mode',
            self::isLocalDevelopment() ? 'log' : 'provider'
        );
    }

    public static function emailMode(): string
    {
        return (string) config(
            'development.email.mode',
            self::isLocalDevelopment() ? 'log' : 'smtp'
        );
    }

    public static function firebaseEnabled(): bool
    {
        return (bool) config('development.firebase.enabled', true);
    }

    public static function mapsEnabled(): bool
    {
        return (bool) config('development.maps.enabled', true);
    }

    private static function currentEnvironment(): string
    {
        if (!function_exists('config')) {
            return 'production';
        }

        try {
            $environment = config('app.env', 'production');
        } catch (\Throwable) {
            return 'production';
        }

        return is_string($environment) && $environment !== ''
            ? $environment
            : 'production';
    }

    private static function isHttpsUrl(mixed $url): bool
    {
        if (!is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return parse_url($url, PHP_URL_SCHEME) === 'https';
    }

    private static function isHttpsOrigin(mixed $origin): bool
    {
        if (!is_string($origin) || filter_var($origin, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($origin);

        return ($parts['scheme'] ?? null) === 'https'
            && isset($parts['host'])
            && !isset($parts['user'])
            && !isset($parts['pass'])
            && !isset($parts['query'])
            && !isset($parts['fragment'])
            && in_array($parts['path'] ?? '', ['', '/'], true);
    }

    private static function isValidEncryptionKey(mixed $key): bool
    {
        if (!is_string($key) || $key === '') {
            return false;
        }

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            return $decoded !== false && strlen($decoded) === 32;
        }

        return strlen($key) === 32;
    }
}