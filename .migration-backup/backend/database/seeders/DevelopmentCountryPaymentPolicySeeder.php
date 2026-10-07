<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use Database\Seeders\Fixtures\DevelopmentCountryPaymentPolicy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Applies only the reviewed development country_payments assignments.
 * It never creates catalog, country, credential, or financial records.
 */
class DevelopmentCountryPaymentPolicySeeder extends Seeder
{
    public function run(): void
    {
        $lock = null;

        try {
            DevelopmentDatabaseGuard::requireOptIn(
                (string) config('app.env', 'production'),
                env('AGENDAALLY_DEVELOPMENT_DATABASE'),
                env('DEVELOPMENT_MODE')
            );

            $basePath = base_path();
            $manifest = DevelopmentDatabaseGuard::reviewedManifest($basePath);
            $path = DevelopmentDatabaseGuard::resolveSqlitePath(
                $basePath,
                (string) config('database.default', ''),
                (array) config('database.connections.sqlite', [])
            );

            if (!is_file($path) || is_link($path)
                || !DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest)) {
                throw new RuntimeException(
                    'Payment-policy seeding requires the positively verified owned local development SQLite database.'
                );
            }

            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $path,
                'database.connections.sqlite.url' => null,
            ]);
            DB::purge('sqlite');

            $lock = @fopen($path, 'c+b');
            if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Unable to obtain an exclusive lock on the owned development database.');
            }

            if (
                DB::connection('sqlite')->getDatabaseName() !== $path
                || !DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest)
            ) {
                throw new RuntimeException('Development database ownership changed before policy assignments could be applied.');
            }

            $relativePath = str_replace(
                '\\',
                '/',
                substr($path, strlen(rtrim($basePath, DIRECTORY_SEPARATOR)) + 1)
            );
            $marker = DB::connection('sqlite')->table('agendaally_development_environment')
                ->where('database_path', $relativePath)
                ->sole();

            if (
                $marker->environment !== 'local'
                || (int) $marker->schema_version !== (int) $manifest['schema_version']
                || !hash_equals(
                    (string) $manifest['migration_set_sha256'],
                    (string) $marker->migration_set_sha256
                )
            ) {
                throw new RuntimeException('The owned development database marker does not match the reviewed schema.');
            }

            $summary = DB::connection('sqlite')->transaction(fn (): array => $this->applyAssignments());

            $this->command?->info(sprintf(
                'Development policy assignments applied: %d assignments across %d countries (%d inserted, %d activated, %d unchanged). No payment-catalog, credential, or financial rows were changed.',
                $summary['assignments'],
                $summary['countries'],
                $summary['inserted'],
                $summary['activated'],
                $summary['unchanged']
            ));
        } finally {
            if (is_resource($lock)) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    /**
     * Apply the fixture matrix without touching unlisted pairs or any table
     * other than countries, payments (read-only lookup), and country_payments.
     *
     * @return array{assignments: int, countries: int, inserted: int, activated: int, unchanged: int}
     */
    public function applyAssignments(): array
    {
        $matrix = DevelopmentCountryPaymentPolicy::assignments();
        $inserted = 0;
        $activated = 0;
        $unchanged = 0;

        foreach ($matrix as $countryCode => $paymentTags) {
            $country = DB::table('countries')
                ->whereRaw('LOWER(code) = ?', [$countryCode])
                ->first(['id']);

            if ($country === null) {
                throw new RuntimeException(
                    'A required country is missing from the existing country catalog; seed geography separately first.'
                );
            }

            foreach ($paymentTags as $paymentTag) {
                $payment = DB::table('payments')->where('tag', $paymentTag)->first(['id']);
                if ($payment === null) {
                    throw new RuntimeException(
                        'A required payment method is missing from the existing payment catalog; seed the catalog separately first.'
                    );
                }

                $existing = DB::table('country_payments')
                    ->where('country_id', $country->id)
                    ->where('payment_id', $payment->id)
                    ->first(['id', 'active']);

                if ($existing === null) {
                    DB::table('country_payments')->insert([
                        'country_id' => $country->id,
                        'payment_id' => $payment->id,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $inserted++;
                } elseif (!(bool) $existing->active) {
                    DB::table('country_payments')
                        ->where('id', $existing->id)
                        ->update(['active' => true, 'updated_at' => now()]);
                    $activated++;
                } else {
                    $unchanged++;
                }
            }
        }

        return [
            'assignments' => array_sum(array_map('count', $matrix)),
            'countries' => count($matrix),
            'inserted' => $inserted,
            'activated' => $activated,
            'unchanged' => $unchanged,
        ];
    }
}