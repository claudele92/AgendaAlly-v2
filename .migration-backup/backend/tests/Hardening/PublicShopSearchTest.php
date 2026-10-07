<?php

declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Rest\ShopController;
use App\Http\Requests\FilterParamsRequest;
use App\Helpers\PortableSqliteFunctions;
use App\Models\Service;
use App\Repositories\ShopRepository\ShopRepository;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use Mockery;

final class PublicShopSearchTest extends IsolatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(PaginationServiceProvider::class);
        $this->app['config']->set('view.paths', [dirname(__DIR__, 2) . '/resources/views']);
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->app->register(\Illuminate\View\ViewServiceProvider::class);
        $this->createPublicSearchSchema();
        PortableSqliteFunctions::register($this->database->getConnection()->getPdo());
        $this->configureUnauthenticatedPublicRequest();
    }

    public function test_public_shop_pagination_keeps_country_city_category_and_distance_ties_distinct(): void
    {
        $this->seedPublicSearchRows();

        $firstPage = $this->getShopPage(1, true);
        $secondPage = $this->getShopPage(2, true);

        self::assertSame(200, $firstPage->getStatusCode());
        self::assertSame(200, $secondPage->getStatusCode());

        $first = json_decode($firstPage->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $second = json_decode($secondPage->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $firstIds = array_column($first['data'], 'id');
        $secondIds = array_column($second['data'], 'id');

        self::assertSame([501], $firstIds);
        self::assertSame([502], $secondIds);
        self::assertSame(2, $first['meta']['total']);
        self::assertSame(2, $first['meta']['last_page']);
        self::assertEquals(0.0, $first['data'][0]['distance']);
        self::assertEquals(0.0, $second['data'][0]['distance']);
        foreach ([$first['data'][0], $second['data'][0]] as $shop) {
            self::assertArrayHasKey('translation', $shop);
            self::assertArrayHasKey('services', $shop);
            self::assertArrayHasKey('matched_location', $shop);
        }

        $countryOnly = $this->getShopPage(null, false, false);
        $countryResults = json_decode($countryOnly->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([501, 502, 503], array_column($countryResults['data'], 'id'));

        $withoutPosition = $this->getShopPage(null, false);
        self::assertSame(200, $withoutPosition->getStatusCode());
        $unlocated = json_decode($withoutPosition->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([501, 502], array_column($unlocated['data'], 'id'));
        foreach ($unlocated['data'] as $shop) {
            self::assertArrayHasKey('distance', $shop);
            self::assertNull($shop['distance']);
        }
    }

    private function getShopPage(
        ?int $page,
        bool $withPosition,
        bool $includeCity = true
    ): \Symfony\Component\HttpFoundation\Response
    {
        $query = [
            'category_ids' => [1],
            'country_id' => 10,
            'city_id' => 20,
            'location_type' => 2,
            'column' => 'distance',
            'sort' => 'asc',
            'perPage' => $page === null ? 20 : 1,
        ];

        if (!$includeCity) {
            unset($query['city_id']);
        }

        if ($page !== null) {
            $query['page'] = $page;
        }

        if ($withPosition) {
            $query['address'] = ['latitude' => 10, 'longitude' => 20];
        }

        $request = Request::create(
            '/api/v1/rest/shops/paginate?' . http_build_query($query),
            'GET',
            [],
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');

        $formRequest = FilterParamsRequest::createFrom($request);
        $resource = (new ShopController(new ShopRepository()))->paginate($formRequest);

        return $resource->toResponse($request);
    }

    private function configureUnauthenticatedPublicRequest(): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn(null);
        $guard->shouldReceive('check')->andReturn(false);

        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        Facade::clearResolvedInstance('auth');
    }

    private function createPublicSearchSchema(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('key');
            $table->text('value')->nullable();
        });
        Schema::create('languages', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->boolean('default')->default(false);
        });
        Schema::create('currencies', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
            $table->string('symbol')->nullable();
            $table->float('rate')->default(1);
            $table->string('position')->nullable();
            $table->boolean('default')->default(false);
            $table->boolean('active')->default(true);
        });
        Schema::create('shops', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->string('uuid')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('logo_img')->nullable();
            $table->string('background_img')->nullable();
            $table->string('status')->nullable();
            $table->unsignedTinyInteger('type')->nullable();
            $table->text('delivery_time')->nullable();
            $table->unsignedTinyInteger('delivery_type')->nullable();
            $table->boolean('open')->default(true);
            $table->boolean('visibility')->default(true);
            $table->boolean('verify')->default(false);
            $table->float('r_count')->nullable();
            $table->float('r_avg')->nullable();
            $table->float('r_sum')->nullable();
            $table->float('min_price')->nullable();
            $table->float('max_price')->nullable();
            $table->float('service_min_price')->nullable();
            $table->float('service_max_price')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
        });
        Schema::create('shop_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->string('locale');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('address')->nullable();
        });
        Schema::create('categories', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('parent_id')->nullable();
        });
        Schema::create('services', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('category_id');
            $table->string('status');
            $table->float('price')->nullable();
            $table->float('commission_fee')->nullable();
            $table->float('discount')->nullable();
        });
        Schema::create('service_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->string('locale');
            $table->string('title');
        });
        Schema::create('service_extras', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('service_id');
        });
        Schema::create('service_extra_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('service_extra_id');
            $table->string('locale');
            $table->string('title');
        });
        Schema::create('service_masters', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->float('price')->nullable();
            $table->float('commission_fee')->nullable();
            $table->float('discount')->nullable();
        });
        Schema::create('shop_working_days', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
        });
        Schema::create('shop_closed_dates', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
        });
        Schema::create('shop_locations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('region_id');
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('area_id')->nullable();
            $table->unsignedTinyInteger('type');
            $table->text('address')->nullable();
            $table->string('alias')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        $this->createLocationTables();

        $this->database->table('settings')->insert([
            'key' => 'by_subscription',
            'value' => '0',
        ]);
        $this->database->table('languages')->insert([
            'id' => 1,
            'locale' => 'en',
            'default' => true,
        ]);
        $this->database->table('currencies')->insert([
            'id' => 1,
            'title' => 'Test',
            'symbol' => 'T',
            'rate' => 1,
            'position' => 'before',
            'default' => true,
            'active' => true,
        ]);
    }

    private function createLocationTables(): void
    {
        Schema::create('regions', function (Blueprint $table): void {
            $table->increments('id');
            $table->boolean('active')->default(true);
        });
        Schema::create('region_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('region_id');
            $table->string('locale');
            $table->string('title');
        });
        Schema::create('countries', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable();
            $table->boolean('active')->default(true);
            $table->string('code')->nullable();
        });
        Schema::create('country_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('country_id');
            $table->string('locale');
            $table->string('title');
        });
        Schema::create('cities', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->boolean('active')->default(true);
        });
        Schema::create('city_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('city_id');
            $table->string('locale');
            $table->string('title');
        });
        Schema::create('areas', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
        });
        Schema::create('area_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('area_id');
            $table->string('locale');
            $table->string('title');
        });
    }

    private function seedPublicSearchRows(): void
    {
        $this->database->table('regions')->insert([
            ['id' => 1, 'active' => true],
            ['id' => 2, 'active' => true],
        ]);
        $this->database->table('countries')->insert([
            ['id' => 10, 'region_id' => 1, 'active' => true, 'code' => 'CM'],
            ['id' => 30, 'region_id' => 2, 'active' => true, 'code' => 'GA'],
        ]);
        $this->database->table('cities')->insert([
            ['id' => 20, 'region_id' => 1, 'country_id' => 10, 'active' => true],
            ['id' => 21, 'region_id' => 1, 'country_id' => 10, 'active' => true],
            ['id' => 31, 'region_id' => 2, 'country_id' => 30, 'active' => true],
        ]);
        $this->database->table('region_translations')->insert([
            ['region_id' => 1, 'locale' => 'en', 'title' => 'Littoral'],
            ['region_id' => 2, 'locale' => 'en', 'title' => 'Estuary'],
        ]);
        $this->database->table('country_translations')->insert([
            ['country_id' => 10, 'locale' => 'en', 'title' => 'Cameroon'],
            ['country_id' => 30, 'locale' => 'en', 'title' => 'Gabon'],
        ]);
        $this->database->table('city_translations')->insert([
            ['city_id' => 20, 'locale' => 'en', 'title' => 'Douala'],
            ['city_id' => 21, 'locale' => 'en', 'title' => 'Other CM city'],
            ['city_id' => 31, 'locale' => 'en', 'title' => 'Other country city'],
        ]);
        $this->database->table('categories')->insert([
            ['id' => 1, 'parent_id' => 0],
            ['id' => 2, 'parent_id' => 1],
            ['id' => 3, 'parent_id' => 0],
        ]);
        $this->database->table('shops')->insert([
            ['id' => 501, 'uuid' => 'wouri-501', 'slug' => 'wouri-one', 'user_id' => 1, 'status' => 'approved', 'type' => 1, 'r_avg' => 4.5],
            ['id' => 502, 'uuid' => 'wouri-502', 'slug' => 'wouri-two', 'user_id' => 2, 'status' => 'approved', 'type' => 1, 'r_avg' => 4.5],
            ['id' => 503, 'uuid' => 'other-city', 'slug' => 'other-city', 'user_id' => 3, 'status' => 'approved', 'type' => 1, 'r_avg' => 4.5],
            ['id' => 504, 'uuid' => 'other-country', 'slug' => 'other-country', 'user_id' => 4, 'status' => 'approved', 'type' => 1, 'r_avg' => 4.5],
            ['id' => 505, 'uuid' => 'other-category', 'slug' => 'other-category', 'user_id' => 5, 'status' => 'approved', 'type' => 1, 'r_avg' => 4.5],
        ]);
        $this->database->table('shop_translations')->insert([
            ['shop_id' => 501, 'locale' => 'en', 'title' => 'Wouri One'],
            ['shop_id' => 502, 'locale' => 'en', 'title' => 'Wouri Two'],
            ['shop_id' => 503, 'locale' => 'en', 'title' => 'Different city'],
            ['shop_id' => 504, 'locale' => 'en', 'title' => 'Different country'],
            ['shop_id' => 505, 'locale' => 'en', 'title' => 'Different category'],
        ]);
        $this->database->table('services')->insert([
            ['shop_id' => 501, 'category_id' => 1, 'status' => Service::STATUS_ACCEPTED],
            ['shop_id' => 501, 'category_id' => 2, 'status' => Service::STATUS_ACCEPTED],
            ['shop_id' => 502, 'category_id' => 1, 'status' => Service::STATUS_ACCEPTED],
            ['shop_id' => 502, 'category_id' => 2, 'status' => Service::STATUS_ACCEPTED],
            ['shop_id' => 503, 'category_id' => 2, 'status' => Service::STATUS_ACCEPTED],
            ['shop_id' => 504, 'category_id' => 2, 'status' => Service::STATUS_ACCEPTED],
            ['shop_id' => 505, 'category_id' => 3, 'status' => Service::STATUS_ACCEPTED],
        ]);
        $this->database->table('service_translations')->insert([
            ['service_id' => 1, 'locale' => 'en', 'title' => 'Service One'],
            ['service_id' => 2, 'locale' => 'en', 'title' => 'Service Two'],
            ['service_id' => 3, 'locale' => 'en', 'title' => 'Service Three'],
            ['service_id' => 4, 'locale' => 'en', 'title' => 'Service Four'],
            ['service_id' => 5, 'locale' => 'en', 'title' => 'Service Five'],
            ['service_id' => 6, 'locale' => 'en', 'title' => 'Service Six'],
            ['service_id' => 7, 'locale' => 'en', 'title' => 'Service Seven'],
        ]);

        $locationRows = [];
        foreach ([501, 502] as $shopId) {
            $locationRows[] = ['shop_id' => $shopId, 'region_id' => 1, 'country_id' => 10, 'city_id' => 20, 'type' => 2, 'latitude' => 10, 'longitude' => 20];
            $locationRows[] = ['shop_id' => $shopId, 'region_id' => 1, 'country_id' => 10, 'city_id' => 20, 'type' => 2, 'latitude' => 10, 'longitude' => 20];
            $locationRows[] = ['shop_id' => $shopId, 'region_id' => 1, 'country_id' => 10, 'city_id' => null, 'type' => 2, 'latitude' => 10, 'longitude' => 20];
        }
        $locationRows[] = ['shop_id' => 503, 'region_id' => 1, 'country_id' => 10, 'city_id' => 21, 'type' => 2, 'latitude' => 10, 'longitude' => 20];
        $locationRows[] = ['shop_id' => 503, 'region_id' => 1, 'country_id' => 10, 'city_id' => null, 'type' => 2, 'latitude' => 10, 'longitude' => 20];
        $locationRows[] = ['shop_id' => 504, 'region_id' => 2, 'country_id' => 30, 'city_id' => 31, 'type' => 2, 'latitude' => 10, 'longitude' => 20];
        $locationRows[] = ['shop_id' => 505, 'region_id' => 1, 'country_id' => 10, 'city_id' => 20, 'type' => 2, 'latitude' => 10, 'longitude' => 20];
        $locationRows[] = ['shop_id' => 505, 'region_id' => 1, 'country_id' => 10, 'city_id' => null, 'type' => 2, 'latitude' => 10, 'longitude' => 20];
        foreach ($locationRows as &$location) {
            $location['created_at'] = '2025-01-01 00:00:00';
            $location['updated_at'] = '2025-01-01 00:00:00';
        }
        unset($location);
        $this->database->table('shop_locations')->insert($locationRows);
    }
}