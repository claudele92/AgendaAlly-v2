<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Seed a non-actionable local catalog and the supported offline cash method.
 *
 * Provider integrations and wallet checkout remain inactive in the preview.
 * This adds no provider credentials/configuration or wallet/payment activity.
 */
class DevelopmentPaymentCatalogSeeder extends Seeder
{
    /**
     * Original PaymentSeeder catalog order, excluding cash (enabled below).
     * These entries are never checkout options from this development seed.
     * Wallet is an internal balance, not a provider, and stays inactive.
     *
     * @var list<array{tag: string, input: int}>
     */
    private const DISABLED_METHODS = [
        ['tag' => Payment::TAG_WALLET, 'input' => 2],
        ['tag' => Payment::TAG_ZAIN_CASH, 'input' => 3],
        ['tag' => Payment::TAG_PAY_TABS, 'input' => 4],
        ['tag' => Payment::TAG_FLUTTER_WAVE, 'input' => 5],
        ['tag' => Payment::TAG_PAY_STACK, 'input' => 6],
        ['tag' => Payment::TAG_MERCADO_PAGO, 'input' => 7],
        ['tag' => Payment::TAG_RAZOR_PAY, 'input' => 8],
        ['tag' => Payment::TAG_STRIPE, 'input' => 9],
        ['tag' => Payment::TAG_PAY_PAL, 'input' => 10],
        ['tag' => Payment::TAG_MOYA_SAR, 'input' => 11],
        ['tag' => Payment::TAG_MOLLIE, 'input' => 12],
        ['tag' => Payment::TAG_IYZICO, 'input' => 13],
        ['tag' => Payment::TAG_MAKSEKESKUS, 'input' => 14],
        ['tag' => Payment::TAG_ORANGE, 'input' => 15],
        ['tag' => Payment::TAG_MTN, 'input' => 16],
        ['tag' => Payment::TAG_PAY_FAST, 'input' => 17],
        ['tag' => Payment::TAG_PAYU, 'input' => 16],
    ];

    public function run(): void
    {
        $basePath = base_path();
        $databaseConfiguration = (array) config('database.connections.sqlite', []);

        DevelopmentDatabaseGuard::requireOptIn(
            (string) config('app.env', 'production'),
            config('development.database.owned_sqlite_enabled', false),
            config('development.enabled', false)
        );

        $manifest = DevelopmentDatabaseGuard::reviewedManifest($basePath);
        $path = DevelopmentDatabaseGuard::resolveSqlitePath(
            $basePath,
            (string) config('database.default', ''),
            $databaseConfiguration
        );

        if (!DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest)) {
            throw new RuntimeException(
                'The local offline payment fixture requires the owned development SQLite marker. '
                . 'Bootstrap the isolated database with the reviewed development:database-bootstrap command.'
            );
        }

        foreach (self::DISABLED_METHODS as $method) {
            Payment::query()->firstOrCreate(
                ['tag' => $method['tag']],
                [
                    'input' => $method['input'],
                    'active' => false,
                    'sandbox' => false,
                ]
            );
        }

        Payment::query()->updateOrCreate(
            ['tag' => Payment::TAG_CASH],
            [
                'input' => 1,
                'active' => true,
                'sandbox' => false,
            ]
        );
    }
}