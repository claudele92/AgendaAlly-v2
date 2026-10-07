<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Synchronize the local demo currency catalog by title. The legacy
 * CurrencySeeder looks up USD by an explicit, guarded primary key, which can
 * overwrite XAF and create another XAF row when run again on SQLite.
 */
class DevelopmentCurrencyCatalogSeeder extends Seeder
{
    private const CURRENCIES = [
        [
            'symbol' => '$',
            'title' => 'USD',
            'rate' => 1 / 600,
            'default' => 0,
            'active' => 1,
        ],
        [
            'symbol' => 'FCFA',
            'title' => 'XAF',
            'rate' => 1.0,
            'default' => 1,
            'active' => 1,
        ],
        [
            'symbol' => 'FCFA',
            'title' => 'XOF',
            'rate' => 600 / 600,
            'default' => 0,
            'active' => 1,
        ],
        [
            'symbol' => '€',
            'title' => 'EUR',
            'rate' => 0.92 / 600,
            'default' => 0,
            'active' => 1,
        ],
        [
            'symbol' => '₦',
            'title' => 'NGN',
            'rate' => 1550 / 600,
            'default' => 0,
            'active' => 1,
        ],
        [
            'symbol' => 'GH₵',
            'title' => 'GHS',
            'rate' => 15 / 600,
            'default' => 0,
            'active' => 1,
        ],
        [
            'symbol' => 'CA$',
            'title' => 'CAD',
            'rate' => 1.37 / 600,
            'default' => 0,
            'active' => 1,
        ],
        [
            'symbol' => '£',
            'title' => 'GBP',
            'rate' => 0.79 / 600,
            'default' => 0,
            'active' => 1,
        ],
        [
            'symbol' => 'FC',
            'title' => 'CDF',
            'rate' => 2870 / 600,
            'default' => 0,
            'active' => 1,
        ],
        [
            'symbol' => 'KSh',
            'title' => 'KES',
            'rate' => 129 / 600,
            'default' => 0,
            'active' => 1,
        ],
    ];

    public function run(): void
    {
        $basePath = base_path();

        DevelopmentDatabaseGuard::requireOptIn(
            (string) config('app.env'),
            env('AGENDAALLY_DEVELOPMENT_DATABASE'),
            env('DEVELOPMENT_MODE')
        );

        $manifest = DevelopmentDatabaseGuard::reviewedManifest($basePath);
        $path = DevelopmentDatabaseGuard::resolveSqlitePath(
            $basePath,
            (string) config('database.default', ''),
            (array) config('database.connections.sqlite', [])
        );

        if (!DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest)) {
            throw new RuntimeException(
                'The local currency catalog requires the owned development SQLite marker.'
            );
        }

        Currency::query()
            ->where('default', 1)
            ->where('title', '!=', 'XAF')
            ->update(['default' => 0]);

        foreach (self::CURRENCIES as $currency) {
            Currency::query()->updateOrCreate(
                ['title' => $currency['title']],
                $currency
            );
        }
    }
}