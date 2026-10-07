<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/** Media only, bound to reviewed existing demo IDs, owners and original images. */
final class DevelopmentServicePhotosSeeder extends Seeder
{
    public function run(): void
    {
        DevelopmentDatabaseGuard::requireOptIn((string) config('app.env'),
            env('AGENDAALLY_DEVELOPMENT_DATABASE'), env('DEVELOPMENT_MODE'));
        $manifest = DevelopmentDatabaseGuard::reviewedManifest(base_path());
        $db = DevelopmentDatabaseGuard::resolveSqlitePath(base_path(), (string) config('database.default'),
            (array) config('database.connections.sqlite', []));
        if (!DevelopmentDatabaseGuard::ownsDatabase($db, base_path(), $manifest)) {
            throw new RuntimeException('Service photo seeding requires the owned local development database.');
        }
        $directory = dirname(__DIR__, 4) . '/attached_assets/service-photos';
        $sources = json_decode(file_get_contents($directory . '/sources.json'), true, 512, JSON_THROW_ON_ERROR);
        $assets = array_column($sources['assets'], null, 'key');
        $stats = ['services_updated' => 0, 'assets_added' => 0, 'associations_added' => 0,
            'already_correct' => 0, 'protected_or_missing_skipped' => 0, 'services_created' => 0];
        DB::transaction(function () use ($sources, $assets, $directory, &$stats): void {
            foreach ($sources['targets'] as $target) {
                $service = Service::whereKey($target['id'])->where('shop_id', $target['shop_id'])
                    ->whereHas('shop', fn ($q) => $q->where('user_id', $target['owner_id']))
                    ->whereHas('translations', fn ($q) => $q->where('locale', 'en')->where('title', $target['title']))
                    ->first();
                if (!$service) {
                    $stats['protected_or_missing_skipped']++;
                    continue;
                }
                $asset = $directory . '/' . $target['asset'] . '.jpg';
                if (!is_file($asset) || (new \finfo(FILEINFO_MIME_TYPE))->file($asset) !== 'image/jpeg') {
                    throw new RuntimeException('Missing or invalid reviewed Service photograph: ' . $target['asset']);
                }
                $assetHash = hash_file('sha256', $asset);
                if (($assets[$target['asset']]['sha256'] ?? null) !== $assetHash) {
                    throw new RuntimeException('Reviewed Service photograph checksum mismatch.');
                }
                $previousHash = $assets[$target['previous_asset'] ?? $target['asset']]['sha256'];
                $legacyReference = self::mediaReference((int) $service->id, (int) $service->shop_id);
                $reference = self::mediaReference((int) $service->id, (int) $service->shop_id,
                    isset($target['previous_asset']) ? $assetHash : null);
                $key = substr($reference, strlen('/storage/'));
                $oldPath = is_string($service->img) && str_starts_with($service->img, '/storage/')
                    ? substr($service->img, strlen('/storage/')) : null;
                $oldHash = $oldPath && Storage::disk('public')->exists($oldPath)
                    ? hash('sha256', Storage::disk('public')->get($oldPath)) : null;
                // Match exact seeded bytes as well as IDs/owner/title/reference.
                // A Vendor replacement, removal or changed physical blob is protected.
                if (!self::isKnownDemoMedia($service->img, $target['expected_media'],
                    $legacyReference, $reference, $oldHash, $previousHash, $assetHash)) {
                    $stats['protected_or_missing_skipped']++;
                    continue;
                }
                if (Storage::disk('public')->exists($key) &&
                    hash('sha256', Storage::disk('public')->get($key)) !== $assetHash) {
                    $stats['protected_or_missing_skipped']++;
                    continue;
                }
                if (!Storage::disk('public')->exists($key)) {
                    if (!Storage::disk('public')->put($key, file_get_contents($asset))) {
                        throw new RuntimeException('Could not store Service photograph.');
                    }
                    $stats['assets_added']++;
                }
                if ($service->img !== $reference) {
                    // Delete only this Service's obsolete association, never a
                    // shared file or another Service's gallery.
                    $service->galleries()->where('path', $service->img)->delete();
                    $service->update(['img' => $reference]);
                    $stats['services_updated']++;
                } else {
                    $stats['already_correct']++;
                }
                if (!$service->galleries()->where('path', $reference)->exists()) {
                    $service->galleries()->create(['path' => $reference, 'title' => basename($key),
                        'type' => 'services', 'size' => filesize($asset), 'mime' => 'image/jpeg']);
                    $stats['associations_added']++;
                }
            }
        });
        $this->command?->info(json_encode($stats, JSON_UNESCAPED_SLASHES));
    }

    /** Replacements use content-versioned native names; old shared blobs stay intact. */
    public static function mediaReference(int $serviceId, int $shopId, ?string $revision = null): string
    {
        $hex = substr(hash('sha256', 'agendaally-demo-service-photo-' . $serviceId .
            ($revision ? '-' . $revision : '')), 0, 32);
        $uuid = substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
            . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
        return '/storage/images/services/shops/' . $shopId . '/' .
            ($revision ? '1000000001-' : '1000000000-') . $uuid . '.jpg';
    }

    public static function isKnownDemoMedia(?string $actual, ?string $original, string $legacy,
        string $final, ?string $storedHash, string $previousHash, string $finalHash): bool
    {
        if ($actual === null || $actual === '') return false;
        if ($actual === $original) return true;
        if ($actual === $final && ($storedHash === null || $storedHash === $finalHash)) return true;
        return $actual === $legacy && $storedHash === $previousHash;
    }
}