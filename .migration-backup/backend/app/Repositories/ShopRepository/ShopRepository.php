<?php
declare(strict_types=1);

namespace App\Repositories\ShopRepository;

use App\Models\Shop;
use App\Models\Stock;
use App\Models\Product;
use App\Models\ShopTag;
use App\Helpers\Utility;
use App\Models\Category;
use App\Traits\ByLocation;
use Illuminate\Support\Facades\DB;
use App\Repositories\CoreRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShopRepository extends CoreRepository
{
    use ByLocation;

    protected function getModelClass(): string
    {
        return Shop::class;
    }

    private function with(): array
    {
        $with = [
            'translation' => fn($query) => $query
                ->where('locale', $this->language),
            'services' => fn($q) => $q, // ->take(3)
            'services.translation' => fn($query) => $query
                ->where('locale', $this->language),
            'services.serviceExtras.translation' => fn($query) => $query
                ->where('locale', $this->language)
                ->select('id', 'service_extra_id', 'title', 'locale'),
            'workingDays',
            'closedDates',
            'bonus' => fn($q) => $q->where('expired_at', '>=', now())
                ->select([
                    'stock_id',
                    'bonus_quantity',
                    'bonus_stock_id',
                    'expired_at',
                    'value',
                    'type',
                ]),
            'bonus.stock.product' => fn($q) => $q->select('id', 'uuid'),
            'bonus.stock.product.translation' => fn($q) => $q
                ->where('locale', $this->language)
                ->select('id', 'locale', 'title', 'product_id'),
            'discounts' => fn($q) => $q->where('end', '>=', now())->where('active', 1)
                ->select('id', 'shop_id', 'end', 'active'),
            'shopPayments:id,payment_id,shop_id,status,client_id,secret_id',
            'shopPayments.payment:id,tag,input,sandbox,active',
            'socials',
            'memberShips',
            'locations' => fn($q) => $q->with($this->getWith())
        ];

        if (!request()->is('api/v1/rest/*')) {
            $with[] = 'documents';
        }

        return $with;
    }
    /**
     * Get one Shop by UUID
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function shopsPaginate(array $filter): LengthAwarePaginator
    {
        /** @var Shop $shop */
        $shop      = $this->model();
        $latitude  = data_get($filter, 'address.latitude');
        $longitude = data_get($filter, 'address.longitude');

        return $shop
            ->filter($filter)
            ->with([
                'translation' => function ($query) use ($filter) {

                    $query->when(data_get($filter, 'not_lang'),
                        fn($q, $notLang) => $q->where('locale', '!=', data_get($filter, 'not_lang')),
                        fn($query) => $query->where('locale', $this->language),
                    );

                },
                'services.translation' => fn($query) => $query
                    ->where('locale', $this->language),
                'services.serviceExtras.translation' => fn($query) => $query
                    ->where('locale', $this->language)->select('id', 'service_extra_id', 'title', 'locale'),
                'closedDates',
                'workingDays' => fn($q) => $q->when(data_get($filter, 'work_24_7'),
                    fn($b) => $b->where('from', '01-00')->where('to', '>=', '23-00')
                ),
            ])
            ->whereHas('translation', function ($query) use ($filter) {

                $query->when(data_get($filter, 'not_lang'),
                    fn($q, $notLang) => $q->where('locale', '!=', data_get($filter, 'not_lang')),
                    fn($query) => $query->where('locale', $this->language),
                );

            })
            ->select([
                'id',
                'uuid',
                'slug',
                'logo_img',
                'background_img',
                'status',
                'type',
                'delivery_time',
                'delivery_type',
                'open',
                'visibility',
                'verify',
                'r_count',
                'r_avg',
                'b_count',
                'min_price',
                'max_price',
                'service_min_price',
                'service_max_price',
                'latitude',
                'longitude',
            ])
            ->distinct()
            ->when($this->hasValidCoordinates($latitude, $longitude), function (Builder $query) use ($latitude, $longitude, $filter) {
                $query
//                    ->where('latitude', '>', 0)
//                    ->where('longitude', '>', 0)
                    ->addSelect([
                        DB::raw($this->distanceSelectRaw($filter, (float)$longitude, (float)$latitude) . ' AS distance'),
                    ])
                    ->when(data_get($filter, 'column') === 'distance', function ($q) use ($filter) {
                        $q->orderBy('distance', $filter['sort'] ?? 'desc');
                    });
            })
            // Distance and other user-selected sorts commonly tie. A stable
            // unique tiebreaker prevents offset pagination from returning a
            // shop twice (or skipping one) between adjacent pages.
            ->orderBy('shops.id')
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * Get one Shop by UUID
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function selectPaginate(array $filter): LengthAwarePaginator
    {
        /** @var Shop $shop */
        $shop = $this->model();

        $latitude   = data_get($filter, 'address.latitude');
        $longitude  = data_get($filter, 'address.longitude');

        return $shop
            ->filter($filter)
            ->with([
                'translation' => fn($q) => $q
                    ->where('locale', $this->language)
                    ->select('id', 'locale', 'title', 'shop_id'),
            ])
            ->whereHas(
                'translation',
                fn($query) => $query->where('locale', $this->language)
            )
            ->select([
                'id',
                'uuid',
                'slug',
                'logo_img'
            ])
            ->when($this->hasValidCoordinates($latitude, $longitude), function (Builder $query) use ($latitude, $longitude, $filter) {
                $query
                    ->select('*')
//                    ->where('latitude', '>', 0)
//                    ->where('longitude', '>', 0)
                    ->addSelect([
                        'latitude',
                        'longitude',
                        DB::raw($this->distanceSelectRaw($filter, (float)$longitude, (float)$latitude) . ' AS distance'),
                        //(6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))) AS distance
                    ])
                    ->when(data_get($filter, 'column') === 'distance', function ($q) use ($filter) {
                        $q->orderBy('distance', $filter['sort'] ?? 'desc');
                    });
            })
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * @param string $uuid
     * @param array|null $filter
     * @return Model|Builder|null
     */
    public function shopDetails(
        string $uuid,
        ?array $filter = [],
        ?array $branchLocationIds = null
    ): Model|Builder|null
    {
        $latitude       = data_get($filter, 'address.latitude');
        $longitude      = data_get($filter, 'address.longitude');
        $locationExists = $this->hasValidCoordinates($latitude, $longitude);

        $shop = Shop::where('uuid', $uuid)
            ->select('*')
            ->when($locationExists, function (Builder $query) use ($latitude, $longitude, $filter) {
                $query
                    ->addSelect([
                        DB::raw($this->distanceSelectRaw($filter ?? [], (float)$longitude, (float)$latitude) . ' AS distance'),
                    ]);
            })
            ->first();

        if (empty($shop) || $shop->uuid !== $uuid) {
            $shop = Shop::where('id', (int)$uuid)
                ->when($locationExists, function (Builder $query) use ($latitude, $longitude, $filter) {
                    $query
                        ->select('*')
                        ->addSelect([
                            DB::raw($this->distanceSelectRaw($filter ?? [], (float)$longitude, (float)$latitude) . ' AS distance'),
                        ]);
                })
                ->first();
        }

        // Null (not a fake 1) when no distance was actually computed -
        // matches AdminShopRepository's own shopDetails(), and see
        // ShopResource's own 'distance' field for why 0 was the same
        // kind of mistake on the list side.
        $distance = $shop?->distance;

        $with = $this->with();

        if ($branchLocationIds !== null) {
            $with['locations'] = fn ($query) => $query
                ->whereIn('shop_locations.id', $branchLocationIds)
                ->with($this->getWith());
        }

        return $shop->fresh($with)->setAttribute('distance', $distance);
    }

    /**
     * @param string $slug
     * @param array|null $filter
     * @return Model|Builder|null
     */
    public function shopDetailsBySlug(string $slug, ?array $filter = []): Model|Builder|null
    {
        /** @var Shop $shop */
        $shop = $this->model();

        $latitude       = data_get($filter, 'address.latitude');
        $longitude      = data_get($filter, 'address.longitude');
        $locationExists = $this->hasValidCoordinates($latitude, $longitude);

        return $shop->with($this->with())
            ->where(fn($q) => $q->where('slug', $slug))
            ->when($locationExists, function (Builder $query) use ($latitude, $longitude, $filter) {
                $query
                    ->addSelect([
                        DB::raw($this->distanceSelectRaw($filter ?? [], (float)$longitude, (float)$latitude) . ' AS distance'),
                    ]);
            })
            ->first();
    }

    /**
     * @return Collection|array
     */
    public function takes(): Collection|array
    {
        return ShopTag::with([
                'translation' => fn($query) => $query->where('locale', $this->language),
            ])
            ->get();
    }

    /**
     * @return float[]|int[]
     */
    public function productsAvgPrices(): array
    {
        $min = Stock::where('price', '>=', 0)
            ->where('quantity', '>', 0)
            ->whereHas('product', fn($q) => $q->actual($this->language))
            ->min('price');

        $max = Stock::where('price', '>=', 0)
            ->where('quantity', '>', 0)
            ->whereHas('product', fn($q) => $q->actual($this->language))
            ->max('price');

        return [
            'min' => $min * $this->currency(),
            'max' => ($min === $max ? $max + 1 : $max) * $this->currency(),
        ];
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function shopsSearch(array $filter): LengthAwarePaginator
    {
        /** @var Shop $shop */
        $shop      = $this->model();
        $latitude  = data_get($filter, 'address.latitude');
        $longitude = data_get($filter, 'address.longitude');

        return $shop
            ->filter($filter)
            ->with([
                'translation' => fn($query) => $query
                    ->where('locale', $this->language),
                'discounts' => fn($q) => $q->where('end', '>=', now())->where('active', 1)
                    ->select('id', 'shop_id', 'end', 'active'),
            ])
            ->whereHas('translation', fn($query) => $query->where('locale', $this->language))
            ->latest()
            ->select([
                'id',
                'slug',
                'logo_img',
                'status',
            ])
            ->distinct()
            ->when($this->hasValidCoordinates($latitude, $longitude), function (Builder $query) use ($latitude, $longitude, $filter) {
                $query
//                    ->where('latitude', '>', 0)
//                    ->where('longitude', '>', 0)
                    ->addSelect([
                        'latitude',
                        'longitude',
                        DB::raw($this->distanceSelectRaw($filter, (float)$longitude, (float)$latitude) . ' AS distance'),
                    ])
                    ->when(data_get($filter, 'column') === 'distance', function ($q) use ($filter) {
                        $q->orderBy('distance', $filter['sort'] ?? 'desc');
                    });
            })
            ->orderBy('shops.id')
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * @param array $filter
     * @return mixed
     */
    public function shopsByIDs(array $filter): mixed
    {
        /** @var Shop $shop */
        $shop   = $this->model();

        return $shop->with([
            'translation' => fn($query) => $query->where(
                fn($q) => $q->where('locale', $this->language)
            ),
            'discounts' => fn($q) => $q
                ->where('end', '>=', now())
                ->where('active', 1)
                ->select('id', 'shop_id', 'end', 'active'),
            'tags:id,img',
            'tags.translation' => fn($query) => $query->where(
                fn($q) => $q->where('locale', $this->language)
            ),
        ])
            ->when(data_get($filter, 'status'), fn($q, $status) => $q->where('status', $status))
            ->find(data_get($filter, 'shops', []));
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function categories(array $filter): LengthAwarePaginator
    {
        $shopId = data_get($filter, 'shop_id');

        return Category::where([
            ['type', Category::MAIN],
            ['active', true],
        ])
            ->with([
                'translation' => fn($q) => $q
                    ->where('locale', $this->language)
                    ->select('id', 'locale', 'title', 'category_id'),
            ])
            ->whereHas(
                'translation',
                fn($query) => $query->where('locale', $this->language),
            )
            ->whereHas('products', fn($q) => $q
                ->where('active', true)
                ->where('status', Product::PUBLISHED)
                ->where('shop_id', $shopId)
            )
            ->select([
                'id',
                'uuid',
                'keywords',
                'type',
                'active',
                'img',
            ])
            ->orderBy('id')
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * @param int $id
     * @return array
     */
    public function reviewsGroupByRating(int $id): array
    {
        return Utility::reviewsGroupRating([
            'reviewable_type' => Shop::class,
            'assignable_type' => Shop::class,
            'assignable_id'   => $id,
        ]);
    }
}
