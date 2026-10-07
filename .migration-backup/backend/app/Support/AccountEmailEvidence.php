<?php
declare(strict_types=1);
namespace App\Support;

use Illuminate\Support\Facades\DB;

/** Selected acceptance metadata only; never request bodies, codes or SMTP dialogue. */
final class AccountEmailEvidence
{
    public static function context(): ?array
    {
        $root = dirname(__DIR__, 4);
        $path = $root.'/.local/staging-mvp/account-email-recovery-context.json';
        $owned = realpath(base_path('database/development/agendaally.sqlite'));
        if (getenv('REPLIT_DEPLOYMENT') !== false || config('app.env') !== 'local'
            || config('database.default') !== 'sqlite'
            || $owned === false || realpath(DB::connection()->getDatabaseName()) !== $owned
            || !is_file($path) || is_link($path) || (fileperms($path) & 0077) !== 0) return null;
        $context = json_decode((string)file_get_contents($path), true);
        return is_array($context) && ($context['version'] ?? null) === 1
            && ($context['kind'] ?? null) === 'verify'
            && is_int($context['user_id'] ?? null) && $context['user_id'] > 0
            && is_string($context['attempt_id'] ?? null)
            && \Illuminate\Support\Str::isUuid($context['attempt_id'])
            && preg_match('/^[a-f0-9]{64}$/',(string)($context['recipient_sha256'] ?? ''))
            && ($context['expires_at'] ?? 0) > time() ? $context : null;
    }

    public static function append(string $event, array $facts = []): void
    {
        $context = self::context();
        if (!$context) return;
        $path = dirname(__DIR__,4).'/.local/staging-mvp/account-email-recovery-events.jsonl';
        if (is_link($path)) throw new \RuntimeException('Unsafe acceptance evidence path.');
        $stream = fopen($path, 'ab');
        if (!$stream || !chmod($path,0600) || !flock($stream, LOCK_EX)) {
            throw new \RuntimeException('Acceptance evidence unavailable.');
        }
        try {
            $line = json_encode(['at'=>gmdate('c'),'attempt_id'=>$context['attempt_id'],
                'event'=>$event,'facts'=>$facts], JSON_THROW_ON_ERROR)."\n";
            if (fwrite($stream,$line) !== strlen($line) || !fflush($stream) || !fsync($stream)) {
                throw new \RuntimeException('Acceptance evidence persistence failed.');
            }
        } finally {
            flock($stream,LOCK_UN);fclose($stream);
        }
    }

    public static function selected(object $row): bool
    {
        $context = self::context();
        return $context && (int)$row->user_id === (int)$context['user_id'] && $row->kind === 'verify';
    }
}
