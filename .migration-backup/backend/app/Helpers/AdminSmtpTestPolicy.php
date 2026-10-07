<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * An exception for the manually invoked Admin test, NOT a general email mode.
 * The ordinary application cannot create this authority from its dotenv.
 */
final class AdminSmtpTestPolicy
{
    public static function allowed(): bool
    {
        return (self::isIsolatedRuntime()
            && config('development.email.admin_test_enabled', false) === true)
            || self::isNormalPreviewRuntime();
    }

    public static function isIsolatedRuntime(): bool
    {
        // Replit sets this marker in published runtimes. Even a copied isolated
        // bootstrap/flag must never grant this exception in a published app.
        if (getenv('REPLIT_DEPLOYMENT') !== false) {
            return false;
        }
        $app = app();
        $expected = realpath(dirname(__DIR__, 4) . '/.local/staging-mvp');
        return $expected !== false
            && $app->bound('agendaally.isolated_admin_smtp_runtime')
            && $app->make('agendaally.isolated_admin_smtp_runtime') === $expected
            && realpath($app->bootstrapPath()) === $expected . '/bootstrap'
            && config('development.email.mode') === 'log'
            && config('app.env') === 'production'
            && config('app.debug') === false
            && config('database.default') === 'staging'
            && config('database.connections.staging.database') === 'agendaally_staging_mvp'
            && config('app.url') === 'https://localhost:8443'
            && config('development.urls.admin') === 'https://localhost:8444';
    }

    public static function isNormalPreviewRuntime(): bool
    {
        $root = realpath(dirname(__DIR__, 2));
        $database = (string) config('database.connections.sqlite.database', '');
        $owned = $root . '/database/development/agendaally.sqlite';
        return self::normalPreviewFactsAllowed([
            'published' => getenv('REPLIT_DEPLOYMENT') !== false,
            'sapi' => PHP_SAPI,
            'authority_matches' => $root !== false
                && get_cfg_var('agendaally.normal_admin_smtp_test_authority') === $root,
            'root_matches' => $root !== false && realpath(base_path()) === $root
                && realpath(app()->bootstrapPath()) === realpath($root . '/bootstrap'),
            'database_matches' => is_file($owned) && !is_link($owned) && !is_link($database)
                && realpath($database) === realpath($owned),
            'driver' => config('database.default'),
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'development' => config('development.enabled'),
            'owned_database' => config('development.database.owned_sqlite_enabled'),
            'permission' => config('development.email.normal_admin_test_enabled', false),
            'email_mode' => config('development.email.mode'),
            'mailer' => config('mail.default'),
            'payments' => config('development.payments.mode'),
            'sms' => config('development.sms.mode'),
            'firebase' => config('development.firebase.enabled'),
            'maps' => config('development.maps.enabled'),
            'admin_port' => parse_url((string) config('development.urls.admin'), PHP_URL_PORT),
            'api_port' => parse_url((string) config('development.urls.api'), PHP_URL_PORT),
            'tls_socket_available' => function_exists('stream_socket_client'),
        ]);
    }

    /** Pure predicate for regression tests; runtime facts above never come from a request. */
    public static function normalPreviewFactsAllowed(array $facts): bool
    {
        foreach (['authority_matches', 'root_matches', 'database_matches', 'development',
            'owned_database', 'permission', 'tls_socket_available'] as $key) {
            if (($facts[$key] ?? null) !== true) return false;
        }
        foreach (['published', 'debug', 'firebase', 'maps'] as $key) {
            if (($facts[$key] ?? null) !== false) return false;
        }
        return ($facts['sapi'] ?? null) === 'cli-server'
            && ($facts['environment'] ?? null) === 'local'
            && ($facts['driver'] ?? null) === 'sqlite'
            && ($facts['admin_port'] ?? null) === 3003
            && ($facts['api_port'] ?? null) === 8000
            && ($facts['payments'] ?? null) === 'disabled'
            && in_array($facts['email_mode'] ?? null, ['log', 'disabled'], true)
            && in_array($facts['mailer'] ?? null, ['log', 'array'], true)
            && in_array($facts['sms'] ?? null, ['log', 'disabled'], true);
    }
}
