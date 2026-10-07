<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\Service;
use Illuminate\Support\Facades\Storage;

/** Native Service media, not a new upload/storage subsystem. */
final class ServiceMedia
{
    /** Resolve portable local references; leave native S3 URLs unchanged. */
    public static function publicUrl(?string $reference): ?string
    {
        if ($reference && str_starts_with($reference, '/storage/images/services/')) {
            return Storage::disk('public')->url(substr($reference, strlen('/storage/')));
        }
        return $reference;
    }

    public static function scopeGallery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        $actor = auth('sanctum')->user();
        if (!$actor) return $query->whereRaw('1 = 0');
        if ($actor && self::isNativeAdmin($actor)) return $query;
        $shop = $actor?->shop ?? $actor?->moderatorShop;
        $type = (new Service)->getMorphClass();
        $userType = (new \App\Models\User)->getMorphClass();
        $shopType = (new \App\Models\Shop)->getMorphClass();
        $owned = Service::query()->where('shop_id', $shop?->id ?? 0)->pluck('id');
        $shops = \App\Models\Shop::query()->where('user_id', $actor->id)->pluck('id');
        return $query->where(fn ($q) => $q->whereNull('loadable_type')
            ->orWhereNotIn('loadable_type', [$type, $userType, $shopType])
            ->orWhere(fn ($q) => $q->where('loadable_type', $userType)->where('loadable_id', $actor->id))
            ->orWhere(fn ($q) => $q->where('loadable_type', $shopType)->whereIn('loadable_id', $shops))
            ->orWhere(fn ($q) => $q->where('loadable_type', $type)->whereIn('loadable_id', $owned)));
    }

    private static function isNativeAdmin($actor): bool
    {
        return $actor->hasRole('admin') || $actor->hasRole('manager');
    }

    public static function uploadPath(): string
    {
        $actor = auth('sanctum')->user();
        if (!$actor) abort(403);
        if (self::isNativeAdmin($actor)) return 'services/admin/' . $actor->id;
        $shop = $actor->shop ?? $actor->moderatorShop;
        if (!$shop || !$actor->hasShopPermission((int) $shop->id, 'services.manage')) abort(403);
        return 'services/shops/' . $shop->id;
    }

    public static function allows(array $images, int $shopId, ?Service $service = null): bool
    {
        $actor = auth('sanctum')->user();
        if (!$actor || $shopId < 1) return false;
        if (!self::isNativeAdmin($actor) && !$actor->hasShopPermission($shopId, 'services.manage')) return false;
        $existing = $service ? array_filter([$service->img, ...$service->galleries()->pluck('path')->all()]) : [];
        $roots = [['disk' => 'public', 'prefix' => 'images/', 'url' => Storage::disk('public')->url('images/')]];
        foreach ([config('app.img_host'), config('app.url')] as $origin) {
            if ($origin) $roots[] = ['disk' => 'public', 'prefix' => 'images/',
                'url' => rtrim($origin, '/') . '/storage/images/'];
        }
        if (filter_var(\App\Models\Settings::where('key', 'aws')->value('value'), FILTER_VALIDATE_BOOLEAN)) {
            $roots[] = ['disk' => 's3', 'prefix' => 'public/images/', 'url' => Storage::disk('s3')->url('public/images/')];
        }
        foreach ($images as $image) {
            if (!is_string($image) || $image === '') return false;
            // Only the same Service's old media may be retained without a new
            // namespace check. Another shop's (or another service's) URL isn't
            // proof of upload ownership.
            if (in_array($image, $existing, true)) continue;
            $url = parse_url($image);
            if (!$url || !isset($url['scheme'], $url['host'], $url['path']) ||
                array_intersect(['user', 'pass', 'query', 'fragment'], array_keys($url))) return false;
            $prefix = 'services/shops/' . $shopId;
            $admin = self::isNativeAdmin($actor) ? '|services/admin/' . (int) $actor->id : '';
            $owned = false;
            foreach ($roots as $root) {
                $base = parse_url($root['url']);
                $basePath = rtrim($base['path'] ?? '', '/') . '/';
                if (($base['scheme'] ?? '') !== $url['scheme'] || ($base['host'] ?? '') !== $url['host']
                    || ($base['port'] ?? null) !== ($url['port'] ?? null) || !str_starts_with($url['path'], $basePath)) continue;
                $path = substr($url['path'], strlen($basePath));
                if (preg_match('#^(' . preg_quote($prefix, '#') . $admin .
                    ')/[0-9]+-[a-f0-9-]{36}\.(png|jpe?g|webp|gif|avif)$#i', $path)
                    && Storage::disk($root['disk'])->exists($root['prefix'] . $path)) {
                    $owned = true;
                    break;
                }
            }
            if (!$owned) return false;
        }
        return true;
    }
}