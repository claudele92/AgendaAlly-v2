<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopTranslation;
use App\Repositories\ProductRepository\RestProductRepository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache as CacheFacade;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

final class ProductCardShopProjectionTest extends TestCase
{
    private ?Container $previousContainer = null;

    private ?Container $previousFacadeApplication = null;

    protected function setUp(): void
    {
        $this->previousContainer = Container::getInstance();
        $this->previousFacadeApplication = Facade::getFacadeApplication();

        $app = new Container();
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        if (!class_exists('Cache', false)) {
            class_alias(CacheFacade::class, 'Cache');
        }

        $guard = \Mockery::mock(Guard::class);
        $guard->shouldReceive('check')->andReturn(false);
        $guard->shouldReceive('user')->andReturn(null);
        $auth = \Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $app->instance(AuthFactory::class, $auth);
        $app->instance('auth', $auth);
        $app->instance('cache', new class {
            public function get(string $key, mixed $default = null): mixed
            {
                return $default;
            }
        });
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousFacadeApplication);
        Container::setInstance($this->previousContainer);
    }

    public function test_product_discovery_requires_explicit_opt_in_for_public_shop_projection(): void
    {
        $repository = (new ReflectionClass(RestProductRepository::class))
            ->newInstanceWithoutConstructor();
        (new ReflectionProperty(RestProductRepository::class, 'language'))
            ->setValue($repository, 'en');

        $defaultRelations = $repository->with();
        self::assertArrayNotHasKey('shop', $defaultRelations);
        self::assertArrayNotHasKey('shop.translation', $defaultRelations);

        $detailRelations = $repository->showWith();
        self::assertArrayHasKey('shop.translation', $detailRelations);
        self::assertArrayNotHasKey('shop', $detailRelations);

        $relations = $repository->with(['seller_metadata' => '1']);
        self::assertArrayHasKey('shop', $relations);
        self::assertArrayHasKey('shop.translation', $relations);

        $shopQuery = new ProductShopProjectionQuery();
        $relations['shop']($shopQuery);
        self::assertSame(['id', 'uuid', 'logo_img'], $shopQuery->selected);
        self::assertSame(['1 as product_card_metadata_only'], $shopQuery->rawSelections);

        $translationQuery = new ProductShopProjectionQuery();
        $relations['shop.translation']($translationQuery);
        self::assertSame(['id', 'shop_id', 'locale', 'title'], $translationQuery->selected);
        self::assertSame([['locale', 'en']], $translationQuery->filters);

        foreach (['user_id', 'tax', 'percentage', 'collect_via_platform', 'ai_token_limit'] as $privateColumn) {
            self::assertNotContains($privateColumn, $shopQuery->selected);
            self::assertNotContains($privateColumn, $translationQuery->selected);
        }
    }

    public function test_metadata_only_shop_serialization_does_not_emit_shopresource_defaults_or_finance_fields(): void
    {
        $shop = $this->shop(['product_card_metadata_only' => 1]);
        $shopData = $this->serializeProductShop($shop);

        self::assertSame([
            'id' => 8,
            'uuid' => 'shop-uuid',
            'logo_img' => 'shops/logo.png',
            'translation' => [
                'id' => 18,
                'locale' => 'en',
                'title' => 'Seller Shop',
            ],
        ], $shopData);
        foreach (['user_id', 'tax', 'percentage', 'collect_via_platform', 'open'] as $nonMetadataField) {
            self::assertArrayNotHasKey($nonMetadataField, $shopData);
        }
    }

    public function test_product_resource_omits_unloaded_shop_by_default(): void
    {
        $product = new Product();
        $product->setRawAttributes(['id' => 5], true);
        $product->setRelation('galleries', collect());

        $resource = ProductResource::make($product)->resolve(Request::create('/api/v1/rest/products/paginate'));

        self::assertArrayNotHasKey('shop', $resource);
    }

    public function test_full_shop_resource_keeps_the_actual_platform_collection_value(): void
    {
        $shop = $this->shop([
            'user_id' => 41,
            'tax' => 12,
            'percentage' => 7,
            'collect_via_platform' => true,
            'open' => true,
            'visibility' => true,
            'verify' => true,
        ]);
        $shopData = $this->serializeProductShop($shop);

        self::assertTrue($shopData['collect_via_platform']);
        self::assertSame(41, $shopData['user_id']);
        self::assertSame(12, $shopData['tax']);
        self::assertSame(7, $shopData['percentage']);
    }

    private function shop(array $attributes): Shop
    {
        $shop = new Shop();
        $shop->setRawAttributes(array_merge([
            'id' => 8,
            'uuid' => 'shop-uuid',
            'logo_img' => 'shops/logo.png',
        ], $attributes), true);

        $translation = new ShopTranslation();
        $translation->setRawAttributes([
            'id' => 18,
            'shop_id' => 8,
            'locale' => 'en',
            'title' => 'Seller Shop',
        ], true);
        $shop->setRelation('translation', $translation);

        return $shop;
    }

    private function serializeProductShop(Shop $shop): array
    {
        $product = new Product();
        $product->setRawAttributes(['id' => 5, 'shop_id' => 8], true);
        $product->setRelation('shop', $shop);
        $product->setRelation('galleries', collect());

        $request = Request::create('/api/v1/rest/products/paginate');
        $shopData = ProductResource::make($product)->resolve($request)['shop'];

        return $shopData instanceof JsonResource ? $shopData->resolve($request) : $shopData;
    }
}

final class ProductShopProjectionQuery
{
    /** @var list<string> */
    public array $selected = [];

    /** @var list<array{string, mixed}> */
    public array $filters = [];

    /** @var list<string> */
    public array $rawSelections = [];

    /** @param list<string> $columns */
    public function select(array $columns): self
    {
        $this->selected = $columns;

        return $this;
    }

    public function selectRaw(string $expression): self
    {
        $this->rawSelections[] = $expression;

        return $this;
    }

    public function where(string $column, mixed $value): self
    {
        $this->filters[] = [$column, $value];

        return $this;
    }
}