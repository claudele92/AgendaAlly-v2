<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use RuntimeException;

/**
 * Seeds the English client strings missing from the checked-in translations
 * catalog. This is a safe, repeatable development-data seeder; it never runs
 * TranslationSeeder or MissingTranslationsSeeder and performs no deletions.
 */
class DevelopmentTranslationSeeder extends Seeder
{
    private const CLIENT_KEYS = [
        'N/A',
        'To see items that ship to a different country, change your delivery address.',
        'appointments',
        'ascending',
        'at',
        'buy',
        'congrats',
        'close',
        'descending',
        'detail',
        'dashboard',
        'dismiss',
        'downloading...',
        'editing',
        'favorites',
        'floor',
        'for',
        'members',
        'order.price.did.not.reach.the.min.amount.min.amount.is',
        'process',
        'referral.(optional)',
        'revenue.over.time',
        'see',
        'similar',
        'specification',
        'store',
        'subMonth',
        'subWeek',
        'subYear',
        'unlimited',
        'use',
        'want.to.pay.via.your.wallet?',
        'would.you.like.to.add.a.tip?',
        'you',
        'you.can.pay.the.full.amount.with.your.wallet',
        'Click or drag file to this area to upload',
        'In order to update database using this file you need to click button above',
        'The supplier is not assigned or delivery type pickup',
        'are.you.sure.you.want.to.change.the.activity?',
        'example@info.com',
        'in stock',
        'masters',
        'the.best.masters',
        'new.salons',
        'mobile.card.description.1',
        'mobile.card.description',
        'add.your.favorite.masters',
        'favorite.masters',
        'best.masters',
        'best.salon.and.master',
        'send.test.email',
        'test.email.failed',
        'test.email.sent.successfully',
        'top.selling.products.load.failed',
    ];

    /**
     * This public method can be called from the safe, idempotent development
     * demo seeder without invoking any of the historical translation seeders.
     */
    public static function seedTranslations(): void
    {
        self::assertOwnedDevelopmentDatabase();

        $source = require resource_path('lang/translations.php');
        $values = [];
        foreach ($source as $row) {
            if (
                ($row['locale'] ?? null) === 'en' &&
                ($row['group'] ?? null) === 'web' &&
                in_array($row['key'] ?? null, self::CLIENT_KEYS, true) &&
                !array_key_exists($row['key'], $values)
            ) {
                $values[$row['key']] = $row['value'] ?? null;
            }
        }

        foreach (self::CLIENT_KEYS as $key) {
            $value = $values[$key] ?? null;
            if (!is_string($value) || trim($value) === '') {
                throw new LogicException(
                    "The canonical English web catalog is missing client key [{$key}]."
                );
            }

            Translation::query()->updateOrCreate(
                ['locale' => 'en', 'group' => 'web', 'key' => $key],
                ['status' => 1, 'value' => $value]
            );
        }

        // The public translation endpoint caches each locale for a day.
        // Clear only the English catalog changed by this development seed.
        Cache::forget('language-en');
    }

    private static function assertOwnedDevelopmentDatabase(): void
    {
        DevelopmentDatabaseGuard::requireOptIn(
            (string) config('app.env'),
            env('AGENDAALLY_DEVELOPMENT_DATABASE'),
            env('DEVELOPMENT_MODE')
        );

        $manifest = DevelopmentDatabaseGuard::reviewedManifest(base_path());

        if (
            config('database.default') !== 'sqlite' ||
            !Schema::hasTable('agendaally_development_environment')
        ) {
            throw new RuntimeException(
                'Refusing the translation seed: bootstrap the owned development SQLite database first.'
            );
        }

        $path = DevelopmentDatabaseGuard::resolveSqlitePath(
            base_path(),
            (string) config('database.default'),
            (array) config('database.connections.sqlite')
        );
        $relativePath = str_replace(
            '\\',
            '/',
            substr($path, strlen(rtrim(base_path(), DIRECTORY_SEPARATOR)) + 1)
        );
        $environment = DB::table('agendaally_development_environment')->sole();

        if (
            $environment->environment !== 'local' ||
            $environment->database_path !== $relativePath ||
            (int) $environment->schema_version !== (int) $manifest['schema_version'] ||
            !hash_equals(
                (string) $manifest['migration_set_sha256'],
                (string) $environment->migration_set_sha256
            )
        ) {
            throw new RuntimeException(
                'Refusing the translation seed: development database ownership does not match this file.'
            );
        }
    }

    public function run(): void
    {
        self::seedTranslations();
    }
}