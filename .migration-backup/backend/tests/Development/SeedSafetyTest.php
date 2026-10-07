<?php

declare(strict_types=1);

namespace Tests\Development;

use PHPUnit\Framework\TestCase;

class SeedSafetyTest extends TestCase
{
    public function test_development_seed_remains_synthetic_idempotent_and_offline(): void
    {
        $backendPath = dirname(__DIR__, 2);
        $seeder = file_get_contents($backendPath . '/database/seeders/DevelopmentDemoSeeder.php');

        self::assertNotFalse($seeder);
        self::assertStringContainsString('firstOrCreate', $seeder);
        self::assertStringContainsString('updateOrCreate', $seeder);
        self::assertStringContainsString('agendaally.test', $seeder);
        self::assertStringContainsString('STATUS_NEW', $seeder);
        self::assertStringContainsString('synthetic', strtolower($seeder));

        foreach ([
            'DatabaseSeeder::class',
            'EmailSettingSeeder::class',
            'PaymentSeeder::class',
            'Http::',
            'Mail::',
            'migrate:fresh',
        ] as $unsafePath) {
            self::assertStringNotContainsString($unsafePath, $seeder);
        }
    }

    public function test_content_only_seed_is_content_scoped_and_retains_ownership_transaction_guards(): void
    {
        $backendPath = dirname(__DIR__, 2);
        $command = file_get_contents($backendPath . '/app/Console/Commands/SeedDevelopmentData.php');
        $demoSeeder = file_get_contents($backendPath . '/database/seeders/DevelopmentDemoSeeder.php');

        self::assertNotFalse($command);
        self::assertNotFalse($demoSeeder);
        $contentOnlyStart = strpos($command, 'if ($onlyContent) {');
        $fullSeedStart = strpos($command, '} else {', $contentOnlyStart ?: 0);
        self::assertNotFalse($contentOnlyStart);
        self::assertNotFalse($fullSeedStart);

        $contentOnlyBranch = substr($command, $contentOnlyStart, $fullSeedStart - $contentOnlyStart);
        self::assertStringNotContainsString('DevelopmentPaymentCatalogSeeder', $contentOnlyBranch);
        self::assertStringContainsString('DevelopmentTranslationSeeder::class', $contentOnlyBranch);
        self::assertStringContainsString('DevelopmentPreviewContentSeeder::class', $contentOnlyBranch);
        self::assertStringContainsString('DB::transaction(function', $contentOnlyBranch);
        self::assertStringContainsString('DevelopmentDatabaseGuard::requireOptIn', $command);
        self::assertStringContainsString('DevelopmentDatabaseGuard::ownsDatabase', $command);

        // The separate full bootstrap/demo path retains its existing payment
        // catalog behavior; this scoped correction must not redesign it.
        self::assertStringContainsString('DevelopmentPaymentCatalogSeeder::class', $demoSeeder);
    }

    public function test_development_user_factory_requires_reserved_email_and_a_strong_demo_password(): void
    {
        $factory = file_get_contents(dirname(__DIR__, 2) . '/database/factories/UserFactory.php');

        self::assertNotFalse($factory);
        self::assertStringContainsString('developmentAccount(string $email, string $password', $factory);
        self::assertStringContainsString('@agendaally.test', $factory);
        self::assertStringContainsString('strlen($password) < 16', $factory);
        self::assertStringContainsString('Hash::make($password)', $factory);
    }
}