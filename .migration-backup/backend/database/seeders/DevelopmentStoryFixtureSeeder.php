<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Product;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Three synthetic, shop-501-only Story rows backed by reviewed local artwork.
 *
 * Story identity is the exact shop/model/media tuple; there is deliberately no
 * schema marker or expiry exemption. Only an unchanged exact fixture row has
 * its created_at refreshed during an explicit guarded seed.
 */
class DevelopmentStoryFixtureSeeder extends Seeder
{
    private const SHOP_ID = 501;
    private const OWNER_ID = 107;
    private const OWNER_EMAIL = 'owner@agendaally.test';
    private const OWNER_PHONE = '+12025550107';

    /**
     * These are the exact reviewed Stage 2 mockup-sandbox illustrations.
     * The fixed timestamp/UUID filenames satisfy StoryService's upload naming
     * contract while making a repeated seed identify the same media rows.
     *
     * @var array<string, array{source: string, filename: string, sha256: string}>
     */
    private const ARTWORK = [
        'shop' => [
            'source' => 'stage2-local-business.jpg',
            'filename' => '1780000000-50100000-0000-4000-8000-000000000001.jpg',
            'sha256' => 'b97162f16e748af78779ec2455f1edcfcdc931f37a7f620dde59c39c6dd903b2',
        ],
        'product' => [
            'source' => 'stage2-products.jpg',
            'filename' => '1780000000-50100000-0000-4000-8000-000000000002.jpg',
            'sha256' => '94c8a65b6d513efb235dd64b6b7045fcfdc90e16f15d2971740e3dded3ab183e',
        ],
        'service' => [
            'source' => 'stage2-local-business.jpg',
            'filename' => '1780000000-50100000-0000-4000-8000-000000000003.jpg',
            'sha256' => 'b97162f16e748af78779ec2455f1edcfcdc931f37a7f620dde59c39c6dd903b2',
        ],
    ];

    public function run(): void
    {
        $connection = $this->assertOwnedLocalDatabase();
        $this->assertApprovedShopOwner($connection);
        $this->assertLocalStoryStorage();

        $shop = Shop::query()->find(self::SHOP_ID);
        $product = Product::query()
            ->where('shop_id', self::SHOP_ID)
            ->where('active', true)
            ->where('status', Product::PUBLISHED)
            ->orderBy('id')
            ->first();
        $service = Service::query()
            ->where('shop_id', self::SHOP_ID)
            ->where('status', Service::STATUS_ACCEPTED)
            ->orderBy('id')
            ->first();

        if (!$shop || !$product || !$service
            || (int) $product->shop_id !== self::SHOP_ID
            || (int) $service->shop_id !== self::SHOP_ID) {
            throw new RuntimeException(
                'Stories fixture requires the approved shop 501 and its existing published Product and accepted Service.'
            );
        }

        $targets = [
            ['slot' => 'shop', 'model_type' => Shop::class, 'model_id' => (int) $shop->id],
            ['slot' => 'product', 'model_type' => Product::class, 'model_id' => (int) $product->id],
            ['slot' => 'service', 'model_type' => Service::class, 'model_id' => (int) $service->id],
        ];
        $mediaBase = $this->mediaBaseUrl();
        $now = now()->format('Y-m-d H:i:s');
        $inserted = 0;
        $refreshed = 0;
        $preserved = 0;

        foreach ($targets as $target) {
            $artwork = self::ARTWORK[$target['slot']];
            $mediaPath = 'images/stories/shops/' . self::SHOP_ID . '/' . $artwork['filename'];
            $mediaUrl = $mediaBase . '/storage/' . $mediaPath;
            $existing = $this->findMediaReferences($connection, $mediaUrl);

            if (count($existing) > 1) {
                $preserved++;

                continue;
            }

            if ($existing !== []) {
                $row = $existing[0];
                $urls = json_decode((string) $row->file_urls, true);
                $isExactFixture = (int) $row->shop_id === self::SHOP_ID
                    && (int) $row->model_id === $target['model_id']
                    && (string) $row->model_type === $target['model_type']
                    && (int) $row->active === 1
                    && $urls === [$mediaUrl];

                if (!$isExactFixture) {
                    // A matching deterministic media key in a user-modified or
                    // differently-owned row is never claimed or rewritten.
                    $preserved++;

                    continue;
                }

                $this->installReviewedArtwork($artwork, $mediaPath);
                // Refresh expiry only. In particular, do not change updated_at
                // or any other user-visible Story field on a repeat seed.
                $connection->table('stories')->where('id', $row->id)->update(['created_at' => $now]);
                $refreshed++;

                continue;
            }

            $this->installReviewedArtwork($artwork, $mediaPath);
            $connection->table('stories')->insert([
                'shop_id' => self::SHOP_ID,
                'model_id' => $target['model_id'],
                'model_type' => $target['model_type'],
                'active' => true,
                'file_urls' => json_encode([$mediaUrl], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $inserted++;
        }

        $this->command?->info(
            "Synthetic Story fixture: {$inserted} inserted, {$refreshed} exact rows refreshed, {$preserved} existing rows preserved."
        );
    }

    private function assertOwnedLocalDatabase(): \Illuminate\Database\Connection
    {
        DevelopmentDatabaseGuard::requireOptIn(
            (string) config('app.env'),
            env('AGENDAALLY_DEVELOPMENT_DATABASE'),
            env('DEVELOPMENT_MODE')
        );

        if ((string) config('database.default') !== 'sqlite') {
            throw new RuntimeException('Stories fixtures may write only to the explicitly selected local SQLite database.');
        }

        $manifest = DevelopmentDatabaseGuard::reviewedManifest(base_path());
        $path = DevelopmentDatabaseGuard::resolveSqlitePath(
            base_path(),
            (string) config('database.default'),
            (array) config('database.connections.sqlite')
        );

        if (!is_file($path) || is_link($path)
            || !DevelopmentDatabaseGuard::ownsDatabase($path, base_path(), $manifest)) {
            throw new RuntimeException('Stories fixture requires the valid ownership marker of the local development SQLite database.');
        }

        $connection = DB::connection('sqlite');
        if ($connection->getDatabaseName() !== $path) {
            throw new RuntimeException('Stories fixture refused a SQLite connection different from the verified local database path.');
        }

        $relativePath = str_replace('\\', '/', substr($path, strlen(rtrim(base_path(), DIRECTORY_SEPARATOR)) + 1));
        $marker = $connection->table('agendaally_development_environment')
            ->where('database_path', $relativePath)
            ->sole();

        if ((string) $marker->environment !== 'local'
            || (int) $marker->schema_version !== (int) $manifest['schema_version']
            || !hash_equals((string) $manifest['migration_set_sha256'], (string) $marker->migration_set_sha256)) {
            throw new RuntimeException('Stories fixture refused a database whose local ownership marker does not match the reviewed manifest.');
        }

        return $connection;
    }

    private function assertApprovedShopOwner(\Illuminate\Database\Connection $connection): void
    {
        $owner = User::query()->find(self::OWNER_ID);
        $shop = $connection->table('shops')->where('id', self::SHOP_ID)->first();
        $awsSetting = $connection->table('settings')->where('key', 'aws')->value('value');

        if (!$owner
            || (string) $owner->email !== self::OWNER_EMAIL
            || (string) $owner->phone !== self::OWNER_PHONE
            || !$owner->email_verified_at
            || !$owner->hasRole('seller')
            || !$shop
            || (int) $shop->user_id !== self::OWNER_ID
            || (string) $shop->status !== Shop::APPROVED) {
            throw new RuntimeException(
                'Stories fixture owner verification failed: shop 501 must belong to the reserved, verified owner@agendaally.test seller contact.'
            );
        }

        if ((bool) $awsSetting) {
            throw new RuntimeException('Stories fixtures require local public storage; AWS media storage is not permitted.');
        }
    }

    private function assertLocalStoryStorage(): void
    {
        if (config('filesystems.disks.public.driver') !== 'local') {
            throw new RuntimeException('Stories fixture requires the local public storage disk.');
        }

        $publicRoot = storage_path('app/public');
        $configuredRoot = config('filesystems.disks.public.root');
        if (!is_string($configuredRoot)
            || realpath($configuredRoot) === false
            || realpath($configuredRoot) !== realpath($publicRoot)
            || realpath($publicRoot) !== $publicRoot
            || is_link($configuredRoot)
            || !is_dir($publicRoot)) {
            throw new RuntimeException('Stories fixture refuses a non-native or symlinked public media directory.');
        }
    }

    /**
     * @return list<object>
     */
    private function findMediaReferences(\Illuminate\Database\Connection $connection, string $mediaUrl): array
    {
        $matches = [];

        foreach ($connection->table('stories')->get([
            'id', 'shop_id', 'model_id', 'model_type', 'active', 'file_urls',
        ]) as $row) {
            $urls = json_decode((string) $row->file_urls, true);
            if (is_array($urls) && in_array($mediaUrl, $urls, true)) {
                $matches[] = $row;
            }
        }

        return $matches;
    }

    /**
     * Copy only the pinned, reviewed local illustration; never replace an
     * existing file whose content has diverged from that source.
     *
     * @param array{source: string, filename: string, sha256: string} $artwork
     */
    private function installReviewedArtwork(array $artwork, string $relativePath): void
    {
        $sourcePath = dirname(base_path(), 2) . '/artifacts/mockup-sandbox/public/images/' . $artwork['source'];
        if (!is_file($sourcePath) || is_link($sourcePath)
            || !hash_equals($artwork['sha256'], (string) hash_file('sha256', $sourcePath))) {
            throw new RuntimeException(
                "Reviewed synthetic Story artwork is missing or changed: {$artwork['source']}."
            );
        }

        $publicRoot = storage_path('app/public');
        $segments = explode('/', $relativePath);
        $filename = array_pop($segments);
        $directory = $publicRoot;
        foreach ($segments as $segment) {
            $directory .= DIRECTORY_SEPARATOR . $segment;
            if (is_link($directory)) {
                throw new RuntimeException('Stories fixture refuses a symlink in its shop-scoped upload path.');
            }
            if (!is_dir($directory) && !@mkdir($directory, 0700)) {
                throw new RuntimeException('Unable to create the local shop-scoped Story upload directory.');
            }
        }

        $targetPath = $directory . DIRECTORY_SEPARATOR . $filename;
        if (is_link($targetPath)) {
            throw new RuntimeException('Stories fixture refuses a symlinked Story media file.');
        }

        if (is_file($targetPath)) {
            if (!hash_equals($artwork['sha256'], (string) hash_file('sha256', $targetPath))) {
                throw new RuntimeException('Existing deterministic Story media differs from its reviewed illustration; it was not overwritten.');
            }

            return;
        }

        if (!copy($sourcePath, $targetPath)) {
            throw new RuntimeException('Unable to copy the reviewed illustration into the shop-scoped Story upload path.');
        }

        Storage::disk('public')->setVisibility($relativePath, 'public');
    }

    private function mediaBaseUrl(): string
    {
        $base = rtrim((string) (config('app.img_host') ?: config('app.url')), '/');
        $parts = parse_url($base);
        if (!is_array($parts)
            || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower((string) $parts['scheme']), ['http', 'https'], true)
            || array_intersect(['user', 'pass', 'query', 'fragment'], array_keys($parts)) !== []) {
            throw new RuntimeException('Stories fixture requires a valid local application media URL.');
        }

        return $base;
    }
}