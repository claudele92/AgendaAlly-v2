<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\AdminSmtpTestPolicy;
use App\Helpers\EnvironmentPolicy;
use App\Models\EmailSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Explicit, in-place transition. No export/import, recipient or mail transport.
 * Counts only; exceptions/bindings/old/new credentials never reach output.
 */
final class EncryptSmtpCredentials extends Command
{
    protected $signature = 'email-settings:encrypt-credentials {--apply : Encrypt legacy values in place}';
    protected $description = 'Quarantine check or safe in-place SMTP credential encryption (no delivery).';

    public function handle(): int
    {
        try {
            $connection = DB::connection();
            $owned = EnvironmentPolicy::isLocalDevelopment()
                && $connection->getDriverName() === 'sqlite'
                && realpath($connection->getDatabaseName()) === realpath(database_path('development/agendaally.sqlite'));
            if ((!$owned && !AdminSmtpTestPolicy::isIsolatedRuntime())
                || EnvironmentPolicy::emailMode() !== 'log'
                || config('development.email.admin_test_enabled', false) !== false) {
                $this->error('Credential transition refused outside the isolated/owned log-only runtime.');
                return self::FAILURE;
            }
            $query = EmailSetting::query()->whereNotNull('password')
                ->where('password', '<>', '')
                ->where('password', 'not like', EmailSetting::PASSWORD_PREFIX . '%');
            if (!$this->option('apply')) {
                $this->info('Legacy credentials requiring transition: ' . $query->count() . '. No values displayed or changed.');
                return self::SUCCESS;
            }
            $count = $connection->transaction(function () use ($query): int {
                $count = 0;
                foreach ($query->lockForUpdate()->get() as $setting) {
                    // This value stays inside the application encryption routine.
                    $setting->password = $setting->getRawOriginal('password');
                    $setting->save();
                    ++$count;
                }
                return $count;
            });
            cache()->forget('email-settings-list');
            $this->info("Encrypted legacy credentials in place: $count. No values displayed; no email sent.");
            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('Credential transition failed safely. Inspect storage/key availability without exporting credentials.');
            return self::FAILURE;
        }
    }
}
