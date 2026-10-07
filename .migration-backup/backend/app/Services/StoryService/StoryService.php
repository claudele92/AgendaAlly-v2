<?php
declare(strict_types=1);

namespace App\Services\StoryService;

use App\Helpers\FileHelper;
use App\Helpers\ResponseError;
use App\Models\Product;
use App\Models\Service;
use App\Models\Shop;
use App\Models\Story;
use App\Models\Settings;
use App\Services\CoreService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StoryService extends CoreService
{
    protected function getModelClass(): string
    {
        return Story::class;
    }

    public function create(array $data): array
    {
        if (!$this->hasOwnedRelatedModel($data) || !$this->hasOwnedMedia($data['file_urls'] ?? [], (int) ($data['shop_id'] ?? 0))) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        try {
            $data['model_type'] = Story::TYPES[$data['model_type']];
            $this->model()->create($data);

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
                'data' => [],
            ];
        } catch (Throwable $e) {
            $this->error($e);
        }

        return [
            'status' => false,
            'code' => ResponseError::ERROR_501,
        ];
    }

    public function update(Story $story, array $data): array
    {
        if ((int) ($data['shop_id'] ?? 0) !== (int) $story->shop_id
            || !$this->hasOwnedRelatedModel($data)
            || !$this->hasOwnedMedia($data['file_urls'] ?? [], (int) $story->shop_id, $story)) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        $oldFiles = is_array($story->file_urls) ? $story->file_urls : [];

        try {
            $data['model_type'] = Story::TYPES[$data['model_type']];
            $story->update($data);
            $this->removeFiles(
                array_values(array_diff($oldFiles, $data['file_urls'])),
                (int) $story->shop_id,
                true
            );

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
                'data' => [],
            ];
        } catch (Throwable $e) {
            $this->error($e);
        }

        return [
            'status' => false,
            'code' => ResponseError::ERROR_501,
        ];
    }

    public function delete(?array $ids = [], ?int $shopId = null): array
    {
        $stories = Story::whereIn('id', is_array($ids) ? $ids : [])
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->get();

        foreach ($stories as $story) {
            /** @var Story $story */
            $fileUrls = is_array($story->file_urls) ? $story->file_urls : [];
            $storyShopId = (int) $story->shop_id;
            $story->delete();
            $this->removeFiles($fileUrls, $storyShopId, true);
        }

        return [
            'status' => true,
            'code' => ResponseError::NO_ERROR,
        ];
    }

    public function uploadFiles(array $data, int $shopId): array
    {
        if ($shopId < 1) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        $fileUrls = [];

        foreach (data_get($data, 'files') as $file) {

            try {
                // Shop-scoped path ownership prevents another seller from
                // reusing or removing this upload by submitting its URL.
                $result = FileHelper::uploadFile($file, "stories/shops/$shopId");

                if (!data_get($result, 'status')) {
                    throw new Exception($result['message'] ?? 'message');
                }

                $fileUrls[] = $result['data'];

            } catch (Throwable $e) {
                $message = $e->getMessage();

                if ($message === "Class \"finfo\" not found") {
                    $message = 'You need on php file info extension';
                }

                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $message];
            }

        }

        if (count($fileUrls) === 0) {
            return [
                'status' => false,
                'code'   => ResponseError::ERROR_508,
            ];
        }

        return [
            'status' => true,
            'code'   => ResponseError::NO_ERROR,
            'data'   => $fileUrls,
        ];
    }

    public function removeFiles(array $fileUrls, ?int $shopId = null, bool $skipReferenced = false): void
    {
        if (!$shopId) {
            return;
        }

        foreach ($fileUrls as $fileUrl) {
            try {
                if ($skipReferenced && $this->isReferencedByShop($fileUrl, $shopId)) {
                    continue;
                }

                $ownedMedia = $this->ownedMediaStorageTarget((string) $fileUrl, $shopId);
                if (!$ownedMedia) {
                    continue;
                }

                Storage::disk($ownedMedia['disk'])->delete($ownedMedia['path']);
            } catch (Throwable $e) {
                $this->error($e);
            }
        }

    }

    /**
     * A Story may link only a related entity belonging to the same shop.
     */
    private function hasOwnedRelatedModel(array $data): bool
    {
        $shopId = filter_var($data['shop_id'] ?? null, FILTER_VALIDATE_INT);
        $modelId = filter_var($data['model_id'] ?? null, FILTER_VALIDATE_INT);
        $type = $data['model_type'] ?? null;

        if (!$shopId || !$modelId || !is_string($type) || !isset(Story::TYPES[$type])) {
            return false;
        }

        $relatedModel = match ($type) {
            'shop' => Shop::query()->find($modelId),
            'product' => Product::query()->find($modelId),
            'service' => Service::query()->find($modelId),
            default => null,
        };

        if (!$relatedModel) {
            return false;
        }

        return $type === 'shop'
            ? (int) $relatedModel->getKey() === $shopId
            : (int) $relatedModel->shop_id === $shopId;
    }

    /**
     * New media must have been uploaded into this shop's namespace. Existing
     * URLs may be retained/reused only if already attached to this shop.
     */
    private function hasOwnedMedia(array $fileUrls, int $shopId, ?Story $story = null): bool
    {
        if ($shopId < 1 || $fileUrls === []) {
            return false;
        }

        $existingUrls = Story::query()
            ->where('shop_id', $shopId)
            ->when($story, fn ($query) => $query->where('id', '!=', $story->id))
            ->pluck('file_urls')
            ->flatMap(static function ($urls): array {
                if (is_string($urls)) {
                    $urls = json_decode($urls, true);
                }

                return is_array($urls) ? $urls : [];
            })
            ->all();
        if ($story && is_array($story->file_urls)) {
            $existingUrls = array_merge($existingUrls, $story->file_urls);
        }

        foreach ($fileUrls as $url) {
            if (!is_string($url) || $url === '') {
                return false;
            }

            if (!$this->ownedMediaStorageTarget($url, $shopId) && !in_array($url, $existingUrls, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve only an actual Story upload created by this app for this shop.
     * Merely forging the correct-looking URL/path is insufficient: its origin,
     * generated filename, namespace, and backing file must all match.
     *
     * @return array{disk: string, path: string}|null
     */
    private function ownedMediaStorageTarget(string $fileUrl, int $shopId): ?array
    {
        $url = parse_url($fileUrl);
        if (!is_array($url)
            || !isset($url['scheme'], $url['host'], $url['path'])
            || array_intersect(['user', 'pass', 'query', 'fragment'], array_keys($url)) !== []
            || !in_array(strtolower($url['scheme']), ['http', 'https'], true)
            || rawurldecode($url['path']) !== $url['path']) {
            return null;
        }

        $mediaBase = config('app.img_host')
            ?: (config('app.url') !== 'http://localhost' ? config('app.url') : null)
            ?: request()?->getSchemeAndHttpHost();
        $base = parse_url(rtrim((string) $mediaBase, '/'));
        if (!is_array($base)
            || strtolower($url['scheme']) !== strtolower($base['scheme'] ?? '')
            || strtolower($url['host']) !== strtolower($base['host'] ?? '')
            || ($url['port'] ?? null) !== ($base['port'] ?? null)) {
            return null;
        }

        $basePath = rtrim((string) ($base['path'] ?? ''), '/');
        if ($basePath !== '' && !str_starts_with($url['path'], $basePath . '/')) {
            return null;
        }
        $path = substr($url['path'], strlen($basePath));
        $isAws = (bool) Settings::query()->where('key', 'aws')->value('value');
        $prefix = $isAws ? '/public/images/stories/shops/' : '/storage/images/stories/shops/';
        $pattern = '#^' . preg_quote($prefix . $shopId . '/', '#')
            . '([0-9]+-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.[A-Za-z0-9]+)$#iD';
        if (!preg_match($pattern, $path, $matches)) {
            return null;
        }

        $storagePath = ($isAws ? 'public/' : '')
            . 'images/stories/shops/' . $shopId . '/' . $matches[1];
        $disk = $isAws ? 's3' : 'public';
        if (!Storage::disk($disk)->exists($storagePath)) {
            return null;
        }

        return ['disk' => $disk, 'path' => $storagePath];
    }

    private function isReferencedByShop(string $fileUrl, int $shopId): bool
    {
        return Story::query()
            ->where('shop_id', $shopId)
            ->get(['file_urls'])
            ->contains(static function (Story $story) use ($fileUrl): bool {
                $urls = is_array($story->file_urls) ? $story->file_urls : [];

                return in_array($fileUrl, $urls, true);
            });
    }

}
