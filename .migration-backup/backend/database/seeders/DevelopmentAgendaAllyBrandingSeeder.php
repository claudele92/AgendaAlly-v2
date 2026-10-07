<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Guarded, settings-only registration of the approved canonical platform brand.
 *
 * This deliberately does not run from DatabaseSeeder. Invoke it explicitly
 * only after the owning agent approves the protected database boundary.
 */
class DevelopmentAgendaAllyBrandingSeeder extends Seeder
{
    private const LOGO_SHA256 = '6d88f37bf506eb28eadc729e520e03175031f92fbe6b9be9809f6211cac2ff9d';
    private const MARK_SHA256 = '0e2bbcff4732e05710344437e647e2b258c92b5b193b66cc98db1f55226eace7';

    public function run(): void
    {
        $this->assertOwnedDevelopmentDatabase();

        $logo = $this->readApprovedAsset(
            base_path('../../attached_assets/logo_1790960747954.png'),
            self::LOGO_SHA256
        );
        $mark = $this->readApprovedAsset(
            base_path('../../attached_assets/favicon_1790960747952.png'),
            self::MARK_SHA256
        );

        $logoPath = 'images/settings/agendaally-platform-logo.png';
        $markPath = 'images/settings/agendaally-platform-mark.png';

        DB::transaction(function () use ($logo, $mark, $logoPath, $markPath): void {
            $this->putExactAsset($logoPath, $logo);
            $this->putExactAsset($markPath, $mark);

            $host = rtrim(
                (string) (config('app.img_host') ?: config('app.url') ?: 'http://localhost'),
                '/'
            );
            $logoUrl = $host . '/storage/' . $logoPath;
            $markUrl = $host . '/storage/' . $markPath;

            foreach ([
                'logo' => $logoUrl,
                // The source logo remains unchanged; dark surfaces provide a
                // light neutral carrier around this same complete wordmark.
                'dark_logo' => $logoUrl,
                'favicon' => $markUrl,
                'admin_favicon' => $markUrl,
            ] as $key => $value) {
                $setting = Settings::query()->firstOrCreate(['key' => $key], ['value' => $value]);
                if (
                    $setting->value !== $value
                    && ($setting->value === null || trim((string) $setting->value) === '' || $this->isKnownAgendaAllyBootstrapAsset($key, (string) $setting->value))
                ) {
                    $setting->update(['value' => $value]);
                }
            }
        });
    }

    private function readApprovedAsset(string $path, string $expectedSha256): string
    {
        $contents = @file_get_contents($path);
        if ($contents === false || !hash_equals($expectedSha256, hash('sha256', $contents))) {
            throw new RuntimeException('Approved AgendaAlly source asset is missing or does not match its verified source checksum: ' . $path);
        }

        return $contents;
    }

    private function putExactAsset(string $path, string $contents): void
    {
        $disk = Storage::disk('public');
        $existing = $disk->exists($path) ? $disk->get($path) : null;
        if (!is_string($existing) || !hash_equals(hash('sha256', $contents), hash('sha256', $existing))) {
            $disk->put($path, $contents);
        }
    }

    /**
     * Only replace exact historic repository fallback paths. Arbitrary
     * non-empty settings remain editable owner configuration.
     */
    private function isKnownAgendaAllyBootstrapAsset(string $key, string $value): bool
    {
        $path = parse_url($value, PHP_URL_PATH);
        $path = is_string($path) ? $path : $value;
        $known = [
            'logo' => ['/img/logo.png', '/storage/images/settings/agendaally-logo.png'],
            'dark_logo' => ['/img/logo.png', '/storage/images/settings/agendaally-logo.png'],
            'favicon' => ['/favicon.png', '/img/favicon.png', '/storage/images/settings/agendaally-favicon.png'],
            'admin_favicon' => ['/favicon.png', '/img/favicon.png', '/storage/images/settings/agendaally-favicon.png'],
        ];

        return in_array($path, $known[$key] ?? [], true);
    }

    private function assertOwnedDevelopmentDatabase(): void
    {
        $basePath = base_path();
        $databaseConfiguration = (array) config('database.connections.sqlite', []);

        DevelopmentDatabaseGuard::requireOptIn(
            (string) config('app.env'),
            env('AGENDAALLY_DEVELOPMENT_DATABASE'),
            env('DEVELOPMENT_MODE')
        );

        $manifest = DevelopmentDatabaseGuard::reviewedManifest($basePath);
        $path = DevelopmentDatabaseGuard::resolveSqlitePath(
            $basePath,
            (string) config('database.default'),
            $databaseConfiguration
        );

        if (!DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest)) {
            throw new RuntimeException(
                'Canonical branding seeding requires the owned development SQLite marker. Bootstrap an isolated development database before seeding.'
            );
        }

        $relativePath = str_replace('\\', '/', substr($path, strlen(rtrim($basePath, DIRECTORY_SEPARATOR)) + 1));
        $row = DB::table('agendaally_development_environment')->where('database_path', $relativePath)->sole();

        if (
            config('database.default') !== 'sqlite'
            || $row->environment !== 'local'
            || (int) $row->schema_version !== (int) $manifest['schema_version']
            || (int) $row->demo_seed_version < 0
            || (int) $row->demo_seed_version > (int) $manifest['demo_seed_version']
            || !hash_equals((string) $manifest['migration_set_sha256'], (string) $row->migration_set_sha256)
            || !Schema::hasTable('settings')
        ) {
            throw new RuntimeException(
                'Refusing canonical branding seed: selected SQLite database does not match the reviewed local ownership marker.'
            );
        }
    }
}