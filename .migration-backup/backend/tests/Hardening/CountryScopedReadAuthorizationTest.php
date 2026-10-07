<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Middleware\BlockIpMiddleware;
use App\Http\Middleware\CheckCountryPermission;
use App\Http\Middleware\CheckParentSeller;
use App\Http\Middleware\CheckSellerShop;
use App\Http\Middleware\CheckShopPermission;
use App\Http\Middleware\SanctumCheck;
use App\Models\Booking;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Providers\FormRequestServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleMiddleware;

final class CountryScopedReadAuthorizationTest extends IsolatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(FormRequestServiceProvider::class);
        $this->app['config']->set('view.paths', [dirname(__DIR__, 2) . '/resources/views']);
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->app->register(\Illuminate\View\ViewServiceProvider::class);
        $this->app->register(\Illuminate\Pagination\PaginationServiceProvider::class);
        $this->createReadFixturesSchema();
        $this->registerApplicationRoutes();
    }

    public function test_foreign_shop_detail_lookup_returns_not_found_instead_of_server_error(): void
    {
        $this->seedTenantRecords();

        $countryActor = $this->actor(500, ['manager'], 10, ['vendors.view']);
        $response = $this->getAs($countryActor, '/api/v1/dashboard/admin/shops/2?lang=en');

        self::assertSame(404, $response->getStatusCode());
    }

    public function test_country_finance_reads_require_the_existing_grant_and_hide_other_country_records(): void
    {
        $this->seedTenantRecords();
        $countryActor = $this->actor(500, ['manager'], 10, ['transactions.view']);

        $payouts = $this->getAs($countryActor, '/api/v1/dashboard/admin/payouts?country_id=20');
        self::assertSame(200, $payouts->getStatusCode());
        self::assertSame([1001], $this->responseIds($payouts));

        $subscriptions = $this->getAs($countryActor, '/api/v1/dashboard/admin/shop-subscriptions?country_id=20');
        self::assertSame(200, $subscriptions->getStatusCode());
        self::assertSame([2001], $this->responseIds($subscriptions));

        self::assertSame(
            404,
            $this->getAs($countryActor, '/api/v1/dashboard/admin/payouts/1002')->getStatusCode(),
            'Implicit model binding must apply the same country scope to a direct payout read.'
        );
        self::assertSame(
            404,
            $this->getAs($countryActor, '/api/v1/dashboard/admin/shop-subscriptions/2002')->getStatusCode(),
            'Implicit model binding must apply the same country scope to a direct subscription read.'
        );
        self::assertSame(
            404,
            $this->getAs($countryActor, '/api/v1/dashboard/admin/payouts/1006')->getStatusCode(),
            'Mixed-country shop payouts have no exclusive country allocation and must fail closed.'
        );
        self::assertSame(404, $this->getAs($countryActor, '/api/v1/dashboard/admin/payouts/1003')->getStatusCode());
        self::assertSame(404, $this->getAs($countryActor, '/api/v1/dashboard/admin/payouts/1007')->getStatusCode());
        self::assertSame(404, $this->getAs($countryActor, '/api/v1/dashboard/admin/payouts/1008')->getStatusCode());
        self::assertSame(
            404,
            $this->getAs($countryActor, '/api/v1/dashboard/admin/shop-subscriptions/2003')->getStatusCode(),
            'Mixed-country shop subscriptions have no exclusive country allocation and must fail closed.'
        );
        self::assertSame(404, $this->getAs($countryActor, '/api/v1/dashboard/admin/shop-subscriptions/2004')->getStatusCode());

        $noFinanceGrant = $this->actor(501, ['manager'], 10, []);
        self::assertSame(
            403,
            $this->getAs($noFinanceGrant, '/api/v1/dashboard/admin/payouts')->getStatusCode()
        );
        self::assertSame(
            403,
            $this->getAs($noFinanceGrant, '/api/v1/dashboard/admin/shop-subscriptions')->getStatusCode()
        );

        $otherCountryActor = $this->actor(502, ['manager'], 20, ['transactions.view']);
        self::assertSame(
            [1002],
            $this->responseIds($this->getAs($otherCountryActor, '/api/v1/dashboard/admin/payouts'))
        );
        self::assertSame(200, $this->getAs($otherCountryActor, '/api/v1/dashboard/admin/payouts/1002')->getStatusCode());
        self::assertSame(404, $this->getAs($otherCountryActor, '/api/v1/dashboard/admin/payouts/1003')->getStatusCode());
        self::assertSame(404, $this->getAs($otherCountryActor, '/api/v1/dashboard/admin/payouts/1007')->getStatusCode());
        self::assertSame(404, $this->getAs($otherCountryActor, '/api/v1/dashboard/admin/payouts/1008')->getStatusCode());
        self::assertSame(
            [2002],
            $this->responseIds($this->getAs($otherCountryActor, '/api/v1/dashboard/admin/shop-subscriptions'))
        );
        self::assertSame(404, $this->getAs($otherCountryActor, '/api/v1/dashboard/admin/payouts/1006')->getStatusCode());
        self::assertSame(404, $this->getAs($otherCountryActor, '/api/v1/dashboard/admin/shop-subscriptions/2003')->getStatusCode());
        self::assertSame(404, $this->getAs($otherCountryActor, '/api/v1/dashboard/admin/shop-subscriptions/2004')->getStatusCode());

        $countryStaff = $this->actor(507, ['manager'], 10, ['transactions.view']);
        $countryStaff->setRelation('countryAdmin', null);
        $this->database->table('country_invitations')->insert([
            'id' => 501,
            'country_id' => 10,
            'user_id' => 507,
            'created_by' => 500,
            'country_role_id' => 7,
            'status' => \App\Models\CountryInvitation::ACCEPTED,
        ]);
        self::assertSame(
            [1001],
            $this->responseIds($this->getAs($countryStaff, '/api/v1/dashboard/admin/payouts'))
        );
    }

    public function test_global_admin_role_can_read_all_country_finance_records(): void
    {
        $this->seedTenantRecords();
        $superAdmin = $this->actor(503, ['manager'], null, [], true);

        self::assertSame(
            [1001, 1002, 1003, 1006, 1007, 1008],
            $this->responseIds($this->getAs($superAdmin, '/api/v1/dashboard/admin/payouts'))
        );
        self::assertSame(
            [2001, 2002, 2003, 2004],
            $this->responseIds($this->getAs($superAdmin, '/api/v1/dashboard/admin/shop-subscriptions'))
        );
    }

    public function test_seller_payout_index_and_detail_reads_are_limited_to_the_authenticated_creator(): void
    {
        $this->seedTenantRecords();
        $this->database->table('payouts')->insert([
            ['id' => 1004, 'created_by' => 501, 'status' => 'pending', 'price' => 40],
            ['id' => 1005, 'created_by' => 502, 'status' => 'pending', 'price' => 50],
        ]);
        $seller = $this->actor(501, ['seller'], null, []);
        $seller->setRelation('shop', (new \App\Models\Shop())->forceFill(['id' => 1]));

        self::assertSame(
            [1004],
            $this->responseIds($this->getAs($seller, '/api/v1/dashboard/seller/payouts'))
        );
        self::assertSame(
            200,
            $this->getAs($seller, '/api/v1/dashboard/seller/payouts/1004')->getStatusCode()
        );
        self::assertNotSame(
            200,
            $this->getAs($seller, '/api/v1/dashboard/seller/payouts/1005')->getStatusCode()
        );
    }

    public function test_plan_catalog_is_shared_metadata_not_a_country_tenant_record(): void
    {
        $this->seedTenantRecords();
        $this->database->table('subscriptions')->insert([
            'id' => 3001,
            'type' => 'monthly',
            'price' => 12,
            'month' => 1,
            'active' => 1,
            'title' => 'Shared plan',
            'product_limit' => 5,
            'order_limit' => 10,
            'booking_limit' => 10,
            'with_report' => 0,
        ]);

        $countryActor = $this->actor(504, ['manager'], 10, []);
        $response = $this->getAs($countryActor, '/api/v1/dashboard/admin/subscriptions');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([3001], $this->responseIds($response));

        $anotherCountryActor = $this->actor(506, ['manager'], 20, []);
        self::assertSame(
            [3001],
            $this->responseIds($this->getAs($anotherCountryActor, '/api/v1/dashboard/admin/subscriptions'))
        );
    }

    public function test_staff_booking_reads_are_branch_scoped_and_master_reads_are_personal(): void
    {
        $this->seedBranchBookings();
        $staff = $this->actor(10, ['shop_manager'], null, []);
        $staff->setRelation('moderatorShop', (new \App\Models\Shop())->forceFill(['id' => 1]));
        $staff->setRelation('shop', null);

        $staffFilter = null;
        $staffVisibleBookings = [];
        $staffRepository = Mockery::mock(\App\Repositories\BookingRepository\BookingRepository::class);
        $staffRepository->shouldReceive('paginate')->once()->andReturnUsing(
            function (array $filter) use (&$staffFilter, &$staffVisibleBookings): LengthAwarePaginator {
                $staffFilter = $filter;
                $staffVisibleBookings = Booking::filter($filter)->orderBy('id')->pluck('id')->all();

                return new LengthAwarePaginator(collect(), 0, 10);
            }
        );
        $this->app->instance(\App\Repositories\BookingRepository\BookingRepository::class, $staffRepository);

        $assignedBranch = $this->getAs($staff, '/api/v1/dashboard/seller/bookings?branch_scope_location_ids[]=102');
        self::assertSame(200, $assignedBranch->getStatusCode());
        self::assertSame([101], $staffFilter['branch_scope_location_ids']);
        self::assertSame([4001], array_map('intval', $staffVisibleBookings));

        self::assertNotSame(
            200,
            $this->getAs($staff, '/api/v1/dashboard/seller/bookings/4002')->getStatusCode(),
            'A branch-scoped staff member must not read a booking assigned to a different branch.'
        );

        $unassignedStaff = $this->actor(13, ['shop_manager'], null, []);
        $unassignedStaff->setRelation('moderatorShop', (new \App\Models\Shop())->forceFill(['id' => 1]));
        $unassignedStaff->setRelation('shop', null);

        $unassignedFilter = null;
        $unassignedVisibleBookings = [];
        $unassignedRepository = Mockery::mock(\App\Repositories\BookingRepository\BookingRepository::class);
        $unassignedRepository->shouldReceive('paginate')->once()->andReturnUsing(
            function (array $filter) use (&$unassignedFilter, &$unassignedVisibleBookings): LengthAwarePaginator {
                $unassignedFilter = $filter;
                $unassignedVisibleBookings = Booking::filter($filter)->orderBy('id')->pluck('id')->all();

                return new LengthAwarePaginator(collect(), 0, 10);
            }
        );
        $this->app->instance(\App\Repositories\BookingRepository\BookingRepository::class, $unassignedRepository);
        self::assertSame(
            200,
            $this->getAs($unassignedStaff, '/api/v1/dashboard/seller/bookings')->getStatusCode()
        );
        self::assertSame([], $unassignedFilter['branch_scope_location_ids']);
        self::assertSame([], $unassignedVisibleBookings);

        $master = $this->actor(11, ['master'], null, []);
        $masterFilter = null;
        $masterVisibleBookings = [];
        $masterRepository = Mockery::mock(\App\Repositories\BookingRepository\BookingRepository::class);
        $masterRepository->shouldReceive('paginate')->once()->andReturnUsing(
            function (array $filter) use (&$masterFilter, &$masterVisibleBookings): LengthAwarePaginator {
                $masterFilter = $filter;
                $masterVisibleBookings = Booking::filter($filter)->orderBy('id')->pluck('id')->all();

                return new LengthAwarePaginator(collect(), 0, 10);
            }
        );
        $this->app->instance(\App\Repositories\BookingRepository\BookingRepository::class, $masterRepository);
        self::assertSame(
            200,
            $this->getAs($master, '/api/v1/dashboard/master/bookings?master_id=12')->getStatusCode()
        );
        self::assertSame(11, (int) $masterFilter['master_id']);
        self::assertSame([4001], array_map('intval', $masterVisibleBookings));
    }

    public function test_seller_location_reads_are_limited_to_accepted_assigned_branches(): void
    {
        $this->database->table('users')->insert([
            ['id' => 100, 'firstname' => 'Shop owner', 'active' => true],
            ['id' => 114, 'firstname' => 'Assigned branch manager', 'active' => true],
            ['id' => 115, 'firstname' => 'Unassigned branch manager', 'active' => true],
            ['id' => 120, 'firstname' => 'Foreign shop owner', 'active' => true],
        ]);
        $this->database->table('shops')->insert([
            ['id' => 1, 'user_id' => 100],
            ['id' => 2, 'user_id' => 120],
        ]);
        $this->database->table('shop_locations')->insert([
            ['id' => 1, 'shop_id' => 1, 'country_id' => 10, 'type' => 1, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 2, 'shop_id' => 1, 'country_id' => 10, 'type' => 2, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 3, 'shop_id' => 1, 'country_id' => 20, 'type' => 1, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 4, 'shop_id' => 1, 'country_id' => 20, 'type' => 2, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ['id' => 5, 'shop_id' => 2, 'country_id' => 10, 'type' => 1, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
        ]);
        $this->database->table('invitations')->insert([
            ['id' => 601, 'user_id' => 114, 'shop_id' => 1, 'created_by' => 100, 'shop_role_id' => 10, 'status' => Invitation::ACCEPTED],
            ['id' => 602, 'user_id' => 115, 'shop_id' => 1, 'created_by' => 100, 'shop_role_id' => 10, 'status' => Invitation::ACCEPTED],
        ]);
        $this->database->table('invitation_shop_locations')->insert([
            ['invitation_id' => 601, 'shop_location_id' => 2],
            ['invitation_id' => 601, 'shop_location_id' => 4],
        ]);

        $staff = $this->actor(114, ['shop_manager'], null, []);
        $staff->setRelation('moderatorShop', (new \App\Models\Shop())->forceFill(['id' => 1, 'uuid' => 'shop-1']));

        $profileShop = Mockery::mock(\App\Models\Shop::class)->makePartial();
        $profileShop->forceFill(['id' => 1, 'uuid' => 'shop-1', 'user_id' => 100]);
        $profileShop->setRelation(
            'locations',
            collect([
                (new \App\Models\ShopLocation())->forceFill([
                    'id' => 2,
                    'shop_id' => 1,
                    'country_id' => 10,
                    'type' => 2,
                    'created_at' => '2026-01-01 00:00:00',
                    'updated_at' => '2026-01-01 00:00:00',
                ]),
                (new \App\Models\ShopLocation())->forceFill([
                    'id' => 4,
                    'shop_id' => 1,
                    'country_id' => 20,
                    'type' => 2,
                    'created_at' => '2026-01-01 00:00:00',
                    'updated_at' => '2026-01-01 00:00:00',
                ]),
            ])
        );
        $profileShop->shouldReceive('load')->twice()->andReturnSelf();
        $shopRepository = Mockery::mock(\App\Repositories\ShopRepository\ShopRepository::class);
        $shopRepository->shouldReceive('shopDetails')->twice()->withArgs(
            fn (string $uuid, array $filter, ?array $locationIds) => $uuid === 'shop-1'
                && $filter === []
                && $locationIds === [2, 4]
        )->andReturn($profileShop);
        $this->app->instance(\App\Repositories\ShopRepository\ShopRepository::class, $shopRepository);
        $this->app->instance(
            \App\Services\ShopServices\ShopService::class,
            Mockery::mock(\App\Services\ShopServices\ShopService::class)
        );

        $unassignedShopProfile = $this->getAs(
            $staff,
            '/api/v1/dashboard/seller/shops?lang=en&country_id=10&location_type=1'
        );
        self::assertSame(200, $unassignedShopProfile->getStatusCode());
        $unassignedShopPayload = json_decode($unassignedShopProfile->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame([2, 4], array_column($unassignedShopPayload['data']['locations'], 'id'));
        self::assertNull(data_get($unassignedShopPayload, 'data.matched_location'));

        $assignedShopProfile = $this->getAs(
            $staff,
            '/api/v1/dashboard/seller/shops?lang=en&country_id=10&location_type=2'
        );
        self::assertSame(200, $assignedShopProfile->getStatusCode());
        self::assertSame(
            2,
            data_get(json_decode($assignedShopProfile->getContent(), true, flags: JSON_THROW_ON_ERROR), 'data.matched_location.id')
        );

        $workingDayRepository = Mockery::mock(\App\Repositories\ShopWorkingDayRepository\ShopWorkingDayRepository::class);
        $workingDayRepository->shouldReceive('show')->andReturn(collect());
        $this->app->instance(
            \App\Repositories\ShopWorkingDayRepository\ShopWorkingDayRepository::class,
            $workingDayRepository
        );
        $this->app->instance(
            \App\Services\ShopWorkingDayService\ShopWorkingDayService::class,
            Mockery::mock(\App\Services\ShopWorkingDayService\ShopWorkingDayService::class)
        );

        $unassignedProfile = $this->getAs(
            $staff,
            '/api/v1/dashboard/seller/shop-working-days?lang=en&country_id=10&location_type=1'
        );
        self::assertSame(200, $unassignedProfile->getStatusCode());
        self::assertNull(
            data_get(json_decode($unassignedProfile->getContent(), true, flags: JSON_THROW_ON_ERROR), 'data.shop.matched_location')
        );

        $assignedProfile = $this->getAs(
            $staff,
            '/api/v1/dashboard/seller/shop-working-days?lang=en&country_id=10&location_type=2'
        );
        self::assertSame(200, $assignedProfile->getStatusCode());
        self::assertSame(
            2,
            json_decode($assignedProfile->getContent(), true, flags: JSON_THROW_ON_ERROR)['data']['shop']['matched_location']['id']
        );

        $staffList = $this->getAs($staff, '/api/v1/dashboard/seller/shop-locations?lang=en');
        self::assertSame(200, $staffList->getStatusCode());
        self::assertSame([2, 4], $this->responseIds($staffList));
        self::assertSame(2, json_decode($staffList->getContent(), true, flags: JSON_THROW_ON_ERROR)['meta']['total']);
        self::assertSame(200, $this->getAs($staff, '/api/v1/dashboard/seller/shop-locations/2?lang=en')->getStatusCode());
        self::assertSame(200, $this->getAs($staff, '/api/v1/dashboard/seller/shop-locations/4?lang=en')->getStatusCode());
        self::assertSame(404, $this->getAs($staff, '/api/v1/dashboard/seller/shop-locations/1?lang=en')->getStatusCode());
        self::assertSame(404, $this->getAs($staff, '/api/v1/dashboard/seller/shop-locations/3?lang=en')->getStatusCode());
        self::assertSame(404, $this->getAs($staff, '/api/v1/dashboard/seller/shop-locations/5?lang=en')->getStatusCode());

        $unassignedStaff = $this->actor(115, ['shop_manager'], null, []);
        $unassignedStaff->setRelation('moderatorShop', (new \App\Models\Shop())->forceFill(['id' => 1]));
        self::assertSame([], $this->responseIds(
            $this->getAs($unassignedStaff, '/api/v1/dashboard/seller/shop-locations?lang=en')
        ));
        self::assertSame(404, $this->getAs($unassignedStaff, '/api/v1/dashboard/seller/shop-locations/2?lang=en')->getStatusCode());

        $owner = $this->actor(100, ['seller'], null, []);
        $owner->setRelation('shop', (new \App\Models\Shop())->forceFill(['id' => 1]));
        self::assertSame([1, 2, 3, 4], $this->responseIds(
            $this->getAs($owner, '/api/v1/dashboard/seller/shop-locations?lang=en')
        ));
        self::assertSame(200, $this->getAs($owner, '/api/v1/dashboard/seller/shop-locations/1?lang=en')->getStatusCode());
        self::assertSame(200, $this->getAs($owner, '/api/v1/dashboard/seller/shop-locations/3?lang=en')->getStatusCode());

        $this->database->table('users')->insert([
            ['id' => 200, 'firstname' => 'Assigned ordinary master', 'active' => true],
            ['id' => 201, 'firstname' => 'Other branch ordinary master', 'active' => true],
        ]);
        $this->database->table('invitations')->insert([
            ['id' => 603, 'user_id' => 200, 'shop_id' => 1, 'created_by' => 100, 'shop_role_id' => null, 'status' => Invitation::ACCEPTED],
            ['id' => 604, 'user_id' => 201, 'shop_id' => 1, 'created_by' => 100, 'shop_role_id' => null, 'status' => Invitation::ACCEPTED],
        ]);
        $this->database->table('invitation_shop_locations')->insert([
            ['invitation_id' => 603, 'shop_location_id' => 2],
            ['invitation_id' => 604, 'shop_location_id' => 1],
        ]);
        $this->database->table('shop_roles')->insert(['id' => 10, 'shop_id' => 1, 'name' => 'Branch staff']);
        $this->database->table('shop_permissions')->insert([
            'id' => 10,
            'key' => 'bookings.availability',
            'group' => 'bookings',
            'label' => 'Manage availability',
        ]);
        $this->database->table('shop_role_permissions')->insert([
            'shop_role_id' => 10,
            'shop_permission_id' => 10,
        ]);

        self::assertSame(
            [200],
            User::query()
                ->availableAtShopLocations(1, [2, 4])
                ->whereIn('users.id', [200, 201])
                ->orderBy('users.id')
                ->pluck('users.id')
                ->map(static fn ($id): int => (int) $id)
                ->all()
        );

        $visibleMasterIds = [];
        $recordMasterIds = static function (string $chain, array $ids) use (&$visibleMasterIds): bool {
            $visibleMasterIds[$chain] = $ids;

            return true;
        };

        $serviceMasters = Mockery::mock(\App\Repositories\ServiceMasterRepository\ServiceMasterRepository::class);
        $serviceMasters->shouldReceive('paginate')->once()->withArgs(
            fn (array $filter, array $ids) => $recordMasterIds('service_masters', $ids)
        )->andReturn(new LengthAwarePaginator(collect(), 0, 10));
        $this->app->instance(\App\Repositories\ServiceMasterRepository\ServiceMasterRepository::class, $serviceMasters);
        $this->app->instance(
            \App\Services\ServiceMasterService\ServiceMasterService::class,
            Mockery::mock(\App\Services\ServiceMasterService\ServiceMasterService::class)
        );

        $workingDays = Mockery::mock(\App\Repositories\UserWorkingDayRepository\UserWorkingDayRepository::class);
        $workingDays->shouldReceive('paginate')->once()->withArgs(
            fn (array $filter, array $ids) => $recordMasterIds('working_days', $ids)
        )->andReturn(new LengthAwarePaginator(collect(), 0, 10));
        $this->app->instance(\App\Repositories\UserWorkingDayRepository\UserWorkingDayRepository::class, $workingDays);
        $this->app->instance(
            \App\Services\UserWorkingDayService\UserWorkingDayService::class,
            Mockery::mock(\App\Services\UserWorkingDayService\UserWorkingDayService::class)
        );

        $closedDates = Mockery::mock(\App\Repositories\MasterClosedDateRepository\MasterClosedDateRepository::class);
        $closedDates->shouldReceive('paginate')->once()->withArgs(
            fn (array $filter, array $ids) => $recordMasterIds('closed_dates', $ids)
        )->andReturn(new LengthAwarePaginator(collect(), 0, 10));
        $this->app->instance(\App\Repositories\MasterClosedDateRepository\MasterClosedDateRepository::class, $closedDates);
        $this->app->instance(
            \App\Services\MasterClosedDateService\MasterClosedDateService::class,
            Mockery::mock(\App\Services\MasterClosedDateService\MasterClosedDateService::class)
        );

        $disabledTimes = Mockery::mock(\App\Repositories\MasterDisabledTimeRepository\MasterDisabledTimeRepository::class);
        $disabledTimes->shouldReceive('paginate')->once()->withArgs(
            fn (array $filter, array $ids) => $recordMasterIds('disabled_times', $ids)
        )->andReturn(new LengthAwarePaginator(collect(), 0, 10));
        $this->app->instance(\App\Repositories\MasterDisabledTimeRepository\MasterDisabledTimeRepository::class, $disabledTimes);
        $this->app->instance(
            \App\Services\MasterDisabledTimeService\MasterDisabledTimeService::class,
            Mockery::mock(\App\Services\MasterDisabledTimeService\MasterDisabledTimeService::class)
        );

        foreach ([
            '/api/v1/dashboard/seller/service-masters?lang=en',
            '/api/v1/dashboard/seller/user-working-days?lang=en',
            '/api/v1/dashboard/seller/master-closed-dates?lang=en',
            '/api/v1/dashboard/seller/master-disabled-times?lang=en',
        ] as $uri) {
            self::assertSame(200, $this->getAs($staff, $uri)->getStatusCode(), $uri);
        }

        self::assertSame(
            ['service_masters', 'working_days', 'closed_dates', 'disabled_times'],
            array_keys($visibleMasterIds)
        );
        foreach ($visibleMasterIds as $ids) {
            self::assertContains(200, $ids);
            self::assertNotContains(201, $ids);
        }
    }

    public function test_admin_endpoints_still_require_an_admin_or_manager_role(): void
    {
        $this->seedTenantRecords();
        $seller = $this->actor(505, ['seller'], 10, ['transactions.view']);

        try {
            $this->getAs($seller, '/api/v1/dashboard/admin/payouts');
            self::fail('A seller role must not enter the admin payout routes.');
        } catch (UnauthorizedException $exception) {
            self::assertSame(403, $exception->getStatusCode());
            self::assertSame(['admin', 'manager'], $exception->getRequiredRoles());
        }
    }

    private function createReadFixturesSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('uuid')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('img')->nullable();
            $table->boolean('active')->default(true);
        });
        Schema::create('shops', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('uuid')->nullable();
        });
        Schema::create('shop_locations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('area_id')->nullable();
            $table->unsignedInteger('type')->nullable();
            $table->string('address')->nullable();
            $table->string('alias')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
        Schema::create('regions', function (Blueprint $table): void {
            $table->increments('id');
            $table->boolean('active')->default(true);
        });
        Schema::create('countries', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->string('code')->nullable();
            $table->boolean('active')->default(true);
        });
        Schema::create('cities', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->boolean('active')->default(true);
        });
        Schema::create('areas', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->boolean('active')->default(true);
        });
        foreach (['region', 'country', 'city', 'area'] as $geography) {
            Schema::create($geography . '_translations', function (Blueprint $table): void {
                $table->increments('id');
                $table->unsignedInteger('region_id')->nullable();
                $table->unsignedInteger('country_id')->nullable();
                $table->unsignedInteger('city_id')->nullable();
                $table->unsignedInteger('area_id')->nullable();
                $table->string('locale')->nullable();
                $table->string('title')->nullable();
            });
        }
        Schema::create('payouts', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('approved_by')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->unsignedInteger('payment_id')->nullable();
            $table->string('status')->nullable();
            $table->string('cause')->nullable();
            $table->string('answer')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
        });
        Schema::create('invitations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('shop_role_id')->nullable();
            $table->unsignedInteger('status');
        });
        Schema::create('country_invitations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('country_role_id')->nullable();
            $table->unsignedInteger('status');
        });
        Schema::create('shop_subscriptions', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('subscription_id')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('type')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('month')->default(1);
            $table->boolean('active')->default(true);
            $table->string('title')->nullable();
            $table->unsignedInteger('product_limit')->default(0);
            $table->unsignedInteger('order_limit')->default(0);
            $table->unsignedInteger('booking_limit')->default(0);
            $table->boolean('with_report')->default(false);
            $table->timestamps();
        });
        Schema::create('transactions', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('payable_type')->nullable();
            $table->unsignedInteger('payable_id')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('payment_trx_id')->nullable();
            $table->text('note')->nullable();
            $table->string('perform_time')->nullable();
            $table->string('refund_time')->nullable();
            $table->string('status')->nullable();
            $table->text('status_description')->nullable();
            $table->timestamps();
        });
        Schema::create('shop_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->string('locale');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('address')->nullable();
        });
        Schema::create('currencies', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
            $table->string('symbol')->nullable();
            $table->decimal('rate', 12, 4)->default(1);
            $table->string('position')->default('before');
            $table->boolean('default')->default(true);
            $table->boolean('active')->default(true);
        });
        Schema::create('languages', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->boolean('default')->default(true);
            $table->boolean('active')->default(true);
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->string('key');
            $table->text('value');
        });
        Schema::create('payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tag')->nullable();
            $table->string('title')->nullable();
            $table->boolean('active')->default(true);
        });
        Schema::create('wallets', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
        });
        Schema::create('bookings', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('master_id');
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
        });
        Schema::create('shop_roles', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->string('name');
        });
        Schema::create('shop_permissions', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('key');
            $table->string('group')->nullable();
            $table->string('label')->nullable();
        });
        Schema::create('shop_role_permissions', function (Blueprint $table): void {
            $table->unsignedInteger('shop_role_id');
            $table->unsignedInteger('shop_permission_id');
        });
        Schema::create('invitation_shop_locations', function (Blueprint $table): void {
            $table->unsignedInteger('invitation_id');
            $table->unsignedInteger('shop_location_id');
        });
        $this->database->table('languages')->insert(['id' => 1, 'locale' => 'en', 'default' => true]);
        $this->database->table('currencies')->insert([
            'id' => 1,
            'title' => 'Test currency',
            'symbol' => 'T',
            'rate' => 1,
            'position' => 'before',
            'default' => true,
            'active' => true,
        ]);
    }

    private function registerApplicationRoutes(): void
    {
        $router = $this->app['router'];
        $router->middlewareGroup('api', [SubstituteBindings::class]);
        $router->aliasMiddleware('block.ip', BlockIpMiddleware::class);
        $router->aliasMiddleware('sanctum.check', SanctumCheck::class);
        $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->aliasMiddleware('check.shop', CheckSellerShop::class);
        $router->aliasMiddleware('check.parentSeller', CheckParentSeller::class);
        $router->aliasMiddleware('country.permission', CheckCountryPermission::class);
        $router->aliasMiddleware('shop.permission', CheckShopPermission::class);

        $this->app->bind(\App\Services\PayoutService\PayoutService::class, fn () => Mockery::mock(
            \App\Services\PayoutService\PayoutService::class
        ));
        $this->app->bind(\App\Services\SubscriptionService\SubscriptionService::class, fn () => Mockery::mock(
            \App\Services\SubscriptionService\SubscriptionService::class
        ));
        $this->app->bind(\App\Services\BookingService\BookingService::class, fn () => Mockery::mock(
            \App\Services\BookingService\BookingService::class
        ));

        Route::middleware('api')->prefix('api')->group(function (): void {
            require dirname(__DIR__, 2) . '/routes/api.php';
        });

    }

    private function seedTenantRecords(): void
    {
        $this->database->table('users')->insert([
            ['id' => 1, 'firstname' => 'Country 10 owner', 'active' => true],
            ['id' => 2, 'firstname' => 'Country 20 owner', 'active' => true],
            ['id' => 3, 'firstname' => 'Country 10 invited staff', 'active' => true],
            ['id' => 4, 'firstname' => 'Multi-country owner', 'active' => true],
            ['id' => 5, 'firstname' => 'Unallocated owner', 'active' => true],
            ['id' => 6, 'firstname' => 'Unallocated staff', 'active' => true],
        ]);
        $this->database->table('shops')->insert([
            ['id' => 1, 'user_id' => 1],
            ['id' => 2, 'user_id' => 2],
            ['id' => 3, 'user_id' => 4],
            ['id' => 4, 'user_id' => 5],
        ]);
        $this->database->table('shop_locations')->insert([
            ['id' => 101, 'shop_id' => 1, 'country_id' => 10, 'type' => 1],
            ['id' => 201, 'shop_id' => 2, 'country_id' => 20, 'type' => 1],
            ['id' => 301, 'shop_id' => 3, 'country_id' => 10, 'type' => 1],
            ['id' => 302, 'shop_id' => 3, 'country_id' => 20, 'type' => 1],
            ['id' => 401, 'shop_id' => 4, 'country_id' => null, 'type' => 1],
        ]);
        $this->database->table('invitations')->insert([
            ['id' => 300, 'user_id' => 3, 'shop_id' => 1, 'created_by' => 1, 'shop_role_id' => null, 'status' => Invitation::ACCEPTED],
            ['id' => 301, 'user_id' => 2, 'shop_id' => 1, 'created_by' => 1, 'shop_role_id' => null, 'status' => Invitation::ACCEPTED],
            ['id' => 302, 'user_id' => 3, 'shop_id' => 2, 'created_by' => 2, 'shop_role_id' => null, 'status' => Invitation::ACCEPTED],
            ['id' => 303, 'user_id' => 6, 'shop_id' => 4, 'created_by' => 5, 'shop_role_id' => null, 'status' => Invitation::ACCEPTED],
        ]);
        $this->database->table('payouts')->insert([
            ['id' => 1001, 'created_by' => 1, 'status' => 'pending', 'price' => 10],
            ['id' => 1002, 'created_by' => 2, 'status' => 'pending', 'price' => 20],
            ['id' => 1003, 'created_by' => 3, 'status' => 'pending', 'price' => 30],
            ['id' => 1006, 'created_by' => 4, 'status' => 'pending', 'price' => 60],
            ['id' => 1007, 'created_by' => 5, 'status' => 'pending', 'price' => 70],
            ['id' => 1008, 'created_by' => 6, 'status' => 'pending', 'price' => 80],
        ]);
        $this->database->table('shop_subscriptions')->insert([
            ['id' => 2001, 'shop_id' => 1, 'active' => true],
            ['id' => 2002, 'shop_id' => 2, 'active' => true],
            ['id' => 2003, 'shop_id' => 3, 'active' => true],
            ['id' => 2004, 'shop_id' => 4, 'active' => true],
        ]);
    }

    private function seedBranchBookings(): void
    {
        $this->database->table('users')->insert([
            ['id' => 10, 'firstname' => 'Shop staff', 'active' => true],
            ['id' => 11, 'firstname' => 'Assigned master', 'active' => true],
            ['id' => 12, 'firstname' => 'Other branch master', 'active' => true],
        ]);
        $this->database->table('shops')->insert(['id' => 1, 'user_id' => 99]);
        $this->database->table('shop_locations')->insert([
            ['id' => 101, 'shop_id' => 1, 'country_id' => 10, 'type' => 1],
            ['id' => 102, 'shop_id' => 1, 'country_id' => 10, 'type' => 1],
        ]);
        $this->database->table('shop_roles')->insert(['id' => 1, 'shop_id' => 1, 'name' => 'Branch staff']);
        $this->database->table('shop_permissions')->insert([
            'id' => 1,
            'key' => 'bookings.view',
            'group' => 'bookings',
            'label' => 'View bookings',
        ]);
        $this->database->table('shop_role_permissions')->insert([
            'shop_role_id' => 1,
            'shop_permission_id' => 1,
        ]);
        $this->database->table('invitations')->insert([
            ['id' => 301, 'user_id' => 10, 'shop_id' => 1, 'created_by' => 99, 'shop_role_id' => 1, 'status' => Invitation::ACCEPTED],
            ['id' => 302, 'user_id' => 11, 'shop_id' => 1, 'created_by' => 99, 'shop_role_id' => null, 'status' => Invitation::ACCEPTED],
            ['id' => 303, 'user_id' => 12, 'shop_id' => 1, 'created_by' => 99, 'shop_role_id' => null, 'status' => Invitation::ACCEPTED],
            ['id' => 304, 'user_id' => 13, 'shop_id' => 1, 'created_by' => 99, 'shop_role_id' => 1, 'status' => Invitation::ACCEPTED],
        ]);
        $this->database->table('invitation_shop_locations')->insert([
            ['invitation_id' => 301, 'shop_location_id' => 101],
            ['invitation_id' => 302, 'shop_location_id' => 101],
            ['invitation_id' => 303, 'shop_location_id' => 102],
        ]);
        $this->database->table('bookings')->insert([
            ['id' => 4001, 'shop_id' => 1, 'master_id' => 11],
            ['id' => 4002, 'shop_id' => 1, 'master_id' => 12],
        ]);
    }

    private function actor(int $id, array $roles, ?int $countryId, array $permissions, bool $superAdmin = false): ScopedReadActor
    {
        $user = new ScopedReadActor();
        $user->configure($roles, $countryId, $permissions, $superAdmin);
        $user->forceFill(['id' => $id]);
        $user->setRelation('countryAdmin', $countryId === null ? null : (object) ['country_id' => $countryId]);
        $user->setRelation('shop', null);
        $user->setRelation('moderatorShop', null);

        return $user;
    }

    private function getAs(ScopedReadActor $actor, string $uri): \Symfony\Component\HttpFoundation\Response
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($actor);
        $guard->shouldReceive('id')->andReturn($actor->getKey());
        $guard->shouldReceive('check')->andReturn(true);

        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->withAnyArgs()->andReturn($guard);
        $this->app->instance('auth', $auth);
        Facade::clearResolvedInstance('auth');

        $request = Request::create($uri, 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');
        foreach ($this->app['router']->getRoutes() as $route) {
            $route->flushController();
        }

        try {
            return $this->app['router']->dispatch($request);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            // The real HTTP kernel renders model-binding misses as 404; this
            // minimal isolated router harness applies the same translation.
            return new \Illuminate\Http\JsonResponse(['message' => 'Not Found'], 404);
        }
    }

    private function responseIds(\Symfony\Component\HttpFoundation\Response $response): array
    {
        $payload = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $items = data_get($payload, 'data.data', data_get($payload, 'data', []));

        $ids = array_map(
            'intval',
            isset($items[0]) && is_array($items[0]) ? array_column($items, 'id') : $items
        );
        sort($ids);

        return $ids;
    }
}

final class ScopedReadActor extends User
{
    protected $table = 'users';

    private array $testRoles = [];
    private ?int $testCountryId = null;
    private array $testCountryPermissions = [];
    private bool $testSuperAdmin = false;

    public function getForeignKey(): string
    {
        return 'user_id';
    }

    public function configure(array $roles, ?int $countryId, array $permissions, bool $superAdmin): void
    {
        $this->testRoles = $roles;
        $this->testCountryId = $countryId;
        $this->testCountryPermissions = $permissions;
        $this->testSuperAdmin = $superAdmin;
    }

    public function hasAnyRole(...$roles): bool
    {
        $flattenedRoles = [];
        array_walk_recursive($roles, static function ($role) use (&$flattenedRoles): void {
            $flattenedRoles[] = (string) $role;
        });

        foreach ($flattenedRoles as $role) {
            if (in_array((string) $role, $this->testRoles, true)) {
                return true;
            }
        }

        return false;
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        return $this->hasAnyRole($roles);
    }

    public function getRoleAttribute(): string
    {
        return $this->testRoles[0] ?? 'no role';
    }

    public function isSuperAdmin(): bool
    {
        return $this->testSuperAdmin;
    }

    public function hasCountryPermission(int $countryId, string $permissionKey): bool
    {
        return $this->testCountryId === $countryId
            && in_array($permissionKey, $this->testCountryPermissions, true);
    }
}