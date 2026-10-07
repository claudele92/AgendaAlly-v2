<?php

declare(strict_types=1);

$appEnvironment = (string) env('APP_ENV', 'production');
$developmentMode = filter_var(env('DEVELOPMENT_MODE', false), FILTER_VALIDATE_BOOL);
$isSafeDevelopmentEnvironment = $developmentMode
    && in_array($appEnvironment, ['local', 'testing'], true);
$developmentDatabaseOptIn = filter_var(
    env('AGENDAALLY_DEVELOPMENT_DATABASE', false),
    FILTER_VALIDATE_BOOLEAN,
    FILTER_NULL_ON_FAILURE
);
$paymentMode = env('PAYMENT_MODE');

return [
    // Explicit reviewed-MVP scheduler selection. Do not run the legacy broad
    // schedule during selected acceptance or an approved limited release.
    'selected_mvp_scheduler_only' => filter_var(env('AGENDAALLY_SELECTED_MVP_SCHEDULER_ONLY', false), FILTER_VALIDATE_BOOL),
    /*
     * A development override is effective only when APP_ENV is local/testing.
     * Each external integration is configured separately and remains disabled
     * or log-only by default in the development .env.example.
     */
    'enabled' => $developmentMode,
    'database' => [
        'owned_sqlite_enabled' => $developmentDatabaseOptIn === true,
        'owned_sqlite_setting_is_valid' => $developmentDatabaseOptIn !== null,
    ],

    'urls' => [
        'api' => rtrim((string) env(
            'LARAVEL_BACKEND_URL',
            env('API_URL', env('APP_URL', 'http://localhost:8000'))
        ), '/'),
        'storefront' => env(
            'CUSTOMER_STOREFRONT_URL',
            env('CUSTOMER_URL', env('FRONT_URL', 'http://localhost:3000/'))
        ),
        'admin' => env(
            'VENDOR_ADMIN_URL',
            env('ADMIN_URL', 'http://localhost:3001/')
        ),
    ],

    'payments' => [
        'mode' => strtolower((string) ($paymentMode ?? (
            $appEnvironment === 'testing' ? 'test' : 'disabled'
        ))),
        'mode_is_explicit' => $paymentMode !== null,
        'sandbox_providers' => ['stripe', 'paypal'],
    ],

    'sms' => [
        'mode' => strtolower((string) env(
            'SMS_MODE',
            $isSafeDevelopmentEnvironment ? 'log' : 'provider'
        )),
    ],

    'email' => [
        // Only the isolated bootstrap may override this; no ordinary env bypass.
        'admin_test_enabled' => false,
        // Requires the owned CLI-server launch authority as well as this opt-in.
        'normal_admin_test_enabled' => env('AGENDAALLY_NORMAL_ADMIN_SMTP_TEST', false) === true,
        'mode' => strtolower((string) env(
            'EMAIL_MODE',
            $isSafeDevelopmentEnvironment ? 'log' : 'smtp'
        )),
    ],

    'firebase' => [
        'enabled' => filter_var(
            env('FIREBASE_ENABLED', !$isSafeDevelopmentEnvironment),
            FILTER_VALIDATE_BOOL
        ),
    ],

    'recaptcha' => [
        'enabled' => filter_var(
            env('RECAPTCHA_ENABLED', !$isSafeDevelopmentEnvironment),
            FILTER_VALIDATE_BOOL
        ),
        'enabled_is_explicit' => env('RECAPTCHA_ENABLED') !== null,
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    'maps' => [
        'enabled' => filter_var(
            env('MAPS_ENABLED', !$isSafeDevelopmentEnvironment),
            FILTER_VALIDATE_BOOL
        ),
    ],
];