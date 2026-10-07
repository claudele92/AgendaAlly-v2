<?php
declare(strict_types=1);
namespace App\Helpers;

use App\Models\User;

/**
 * Default-off, operator/CLI-only acceptance authority. Never grants general SMTP
 * or takes approval/recipient overrides from an HTTP request or ordinary dotenv.
 */
final class SelectedAccountEmailPolicy
{
    public static function permitsRow(object $row): bool
    {
        if (!self::runtimeAllowed() || !in_array($row->kind ?? null, ['verify', 'reset'], true)) return false;
        $path = dirname(__DIR__, 4) . '/.local/staging-mvp/account-email-approval.json';
        if (!is_file($path) || is_link($path)
            || (fileperms($path) & 0077) !== 0
            || realpath((string) get_cfg_var('agendaally.account_email_approval')) !== realpath($path)) return false;
        // The approval is operational metadata only: IDs and recipient hash, no OTP/password.
        $approval = json_decode((string) file_get_contents($path), true);
        if (!is_array($approval) || ($approval['version'] ?? null) !== 1
            || ($approval['owner_controlled_recipient'] ?? null) !== true
            || ($approval['disposable_or_explicitly_safe_account'] ?? null) !== true) return false;
        $records = $approval['records'] ?? null;
        if (!is_array($records) || count($records) < 1 || count($records) > 2) return false;
        $user = User::find($row->user_id);
        if (!$user) return false;
        $kinds = [];
        $matched = false;
        foreach ($records as $record) {
            if (!is_array($record) || !in_array($record['kind'] ?? null, ['verify', 'reset'], true)
                || in_array($record['kind'], $kinds, true)) return false;
            $kinds[] = $record['kind'];
            if (($record['delivery_id'] ?? null) === $row->id
                && ($record['user_id'] ?? null) === (int) $row->user_id
                && $record['kind'] === $row->kind
                && is_int($record['expires_at'] ?? null)
                && $record['expires_at'] > time()
                && $record['expires_at'] <= time() + 3600
                && hash_equals(hash('sha256', strtolower(trim((string) $user->email))),
                    (string) ($record['recipient_sha256'] ?? ''))) $matched = true;
        }
        return $matched;
    }

    public static function permitsSender(string $operation, ?User $user = null): bool
    {
        if (!app()->bound('agendaally.selected_account_delivery')) return false;
        $row = app('agendaally.selected_account_delivery');
        $kind = ['sendVerify' => 'verify', 'sendEmailPasswordReset' => 'reset'][$operation] ?? null;
        return is_object($row) && ($kind === $row->kind || $operation === 'emailBaseAuth')
            && ($user === null || ((int) $user->id === (int) $row->user_id
                && $user->email === User::find($row->user_id)?->email))
            && self::permitsRow($row);
    }

    public static function runtimeAllowed(): bool
    {
        $root = realpath(dirname(__DIR__, 2));
        $database = (string) config('database.connections.sqlite.database');
        $queue = (array) config('queue.connections.database', []);
        return self::factsAllowed([
            'published' => getenv('REPLIT_DEPLOYMENT') !== false,
            'cli' => PHP_SAPI === 'cli',
            'authority' => $root !== false && get_cfg_var('agendaally.account_email_authority') === $root,
            'root' => $root !== false && realpath(base_path()) === $root
                && realpath(app()->bootstrapPath()) === $root . '/bootstrap',
            'database' => !is_link($database) && realpath($database) === $root . '/database/development/agendaally.sqlite',
            'environment' => config('app.env'), 'driver' => config('database.default'),
            'development' => config('development.enabled'), 'debug' => config('app.debug'),
            'owned' => config('development.database.owned_sqlite_enabled'),
            'email' => config('development.email.mode'), 'mailer' => config('mail.default'),
            'payments' => config('development.payments.mode'), 'sms' => config('development.sms.mode'),
            'firebase' => config('development.firebase.enabled'), 'maps' => config('development.maps.enabled'),
            'tls' => function_exists('stream_socket_client'),
            'queue' => ($queue['driver'] ?? null) === 'database' && ($queue['table'] ?? null) === 'jobs'
                && in_array($queue['connection'] ?? null, [null, 'sqlite'], true)
                && (int) ($queue['retry_after'] ?? 0) >= 90,
        ]);
    }

    /** Pure fail-closed predicate for local tests; no transport/config values logged. */
    public static function factsAllowed(array $facts): bool
    {
        foreach (['cli', 'authority', 'root', 'database', 'development', 'owned', 'tls', 'queue'] as $key)
            if (($facts[$key] ?? null) !== true) return false;
        foreach (['published', 'debug', 'firebase', 'maps'] as $key)
            if (($facts[$key] ?? null) !== false) return false;
        return ($facts['environment'] ?? null) === 'local' && ($facts['driver'] ?? null) === 'sqlite'
            && in_array($facts['email'] ?? null, ['log', 'disabled'], true)
            && in_array($facts['mailer'] ?? null, ['log', 'array'], true)
            && ($facts['payments'] ?? null) === 'disabled'
            && in_array($facts['sms'] ?? null, ['log', 'disabled'], true);
    }
}
