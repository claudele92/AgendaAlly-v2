<?php
declare(strict_types=1);

namespace App\Repositories\StoryRepository;

use App\Models\Shop;
use App\Models\Story;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StoryRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Story::class;
    }

    /**
     * @param array $data
     * @return LengthAwarePaginator
     */
    public function index(array $data = []): LengthAwarePaginator
    {
        /** @var Story $stories */
        $stories = $this->model();

        return $stories
            ->filter($data)
            ->with([
                'model.translation' => fn ($q) => $q
                    ->where('locale', $this->language),
                'shop'                => fn ($q) => $q->select(['id', 'uuid', 'user_id', 'logo_img']),
                'shop.translation'    => fn ($q) => $q
                    ->select('id', 'shop_id', 'locale', 'title')
                    ->where('locale', $this->language),
            ])
            ->paginate($data['perPage'] ?? 15);
    }

    public function list(array $data = []): array
    {
        $shopsStories = Story::with([
            'shop:id,uuid,slug,user_id,logo_img,status',
            'shop.seller:id,firstname,lastname,img',
            'shop.translation' => fn ($q) => $q
                ->where('locale', $this->language)
                ->select('id', 'shop_id', 'locale', 'title'),
            'model.translation' => fn ($q) => $q
                ->where('locale', $this->language)
        ])
            ->publiclyVisible()
            ->simplePaginate($data['perPage'] ?? 100);

        $shops = [];

        foreach ($shopsStories as $shopStories) {

            /** @var Story $shopStories */

            if (!isset($shops[$shopStories->shop_id])) {
                $shops[$shopStories->shop_id] = [];
            }

            $model = $shopStories->model;

            if (!$shopStories->shop || $shopStories->shop->status !== Shop::APPROVED) {
                continue;
            }

            foreach ($shopStories->file_urls as $fileUrl) {

                $shopsStoriesTitle = $shopStories?->shop?->translation?->title;
                $modelTitle        = $model?->translation?->title;
                $createdAt         = $shopStories?->created_at;
                $updatedAt         = $shopStories?->updated_at;

                $shops[$shopStories->shop_id][] = [
                    'shop_id'       => $shopStories->shop_id,
                    'shop_uuid'     => $shopStories?->shop?->uuid,
                    'shop_slug'     => $shopStories?->shop?->slug,
                    'logo_img'      => $shopStories?->shop?->logo_img,
                    'title'         => $shopsStoriesTitle,
                    'firstname'     => $shopStories->shop?->seller?->firstname,
                    'lastname'      => $shopStories->shop?->seller?->lastname,
                    'avatar'        => $shopStories->shop?->seller?->img,
                    'model_uuid'    => data_get($model, 'uuid', $model?->id),
                    'model_slug'    => $model?->slug,
                    'model_title'   => $modelTitle,
                    'model_type'    => $shopStories->model_type,
                    'url'           => $fileUrl,
                    'created_at'    => !empty($createdAt) ? $createdAt->format('Y-m-d H:i:s') . 'Z' : null,
                    'updated_at'    => !empty($updatedAt) ? $updatedAt->format('Y-m-d H:i:s') . 'Z' : null,
                ];

            }

        }

        $shops = collect($shops);

        return $shops?->count() > 0 ? array_values($shops->reject(fn($items) => empty($items))->toArray()) : [];
    }

    public function show(Story $story): Story
    {
        return $story->load([
            'model.translation' => fn ($q) => $q
                ->where('locale', $this->language),
            'shop.translation'  => fn ($q) => $q
                ->select('id', 'shop_id', 'locale', 'title')
                ->where('locale', $this->language),
        ]);
    }
}
