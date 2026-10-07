<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Invitation;
use App\Models\DeliveryManSetting;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Events\Order\SendDeliveryManLocationByOrder;
use App\Http\Controllers\API\v1\Dashboard\Seller\DeliveryManSettingController;
use App\Http\Controllers\API\v1\Dashboard\Seller\SellerBaseController;
use App\Http\Controllers\API\v1\Dashboard\Seller\UserController as SellerUserController;
use App\Http\Controllers\API\v1\Dashboard\Deliveryman\OrderController as DriverOrderController;
use App\Http\Requests\DeliveryManSetting\AdminRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Order\StatusUpdateRequest;
use App\Http\Middleware\CheckShopPermission;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Listeners\Order\SendDeliveryManLocationByOrderListener;
use App\Repositories\DeliveryManSettingRepository\DeliveryManSettingRepository;
use App\Repositories\OrderRepository\DeliveryMan\OrderRepository as DriverOrderRepository;
use App\Repositories\UserRepository\UserRepository;
use App\Services\DeliveryDriver\DriverMembership;
use App\Services\DeliveryManSettingService\DeliveryManSettingService;
use App\Services\OrderService\OrderService;
use App\Services\UserServices\UserService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Mockery;
use Spatie\Permission\PermissionRegistrar;

final class DeliveryDriverMembershipAuthorizationTest extends IsolatedTestCase
{
    private const SHOP_A = 11;
    private const SHOP_B = 22;

    private const DRIVER_A = 101;
    private const DRIVER_B = 102;
    private const UNRELATED_DRIVER = 103;
    private const PENDING_DRIVER = 104;
    private const SUSPENDED_DRIVER = 105;
    private const INACTIVE_DRIVER = 106;
    private const STAFF = 201;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->configureRoles();
        $this->authenticateAs(null);
        $this->createSchema();
        $this->seedUsersAndMemberships();
    }

    public function test_members_and_eligible_users_use_the_exact_shop_relationship_and_global_account_state(): void
    {
        self::assertSame(
            [self::DRIVER_A, self::PENDING_DRIVER, self::SUSPENDED_DRIVER, self::INACTIVE_DRIVER],
            DriverMembership::members(self::SHOP_A)
                ->orderBy('users.id')
                ->pluck('users.id')
                ->map(static fn ($id): int => (int) $id)
                ->all()
        );
        self::assertSame(
            [self::DRIVER_A],
            DriverMembership::eligibleUsers(self::SHOP_A)
                ->orderBy('users.id')
                ->pluck('users.id')
                ->map(static fn ($id): int => (int) $id)
                ->all()
        );

        self::assertTrue(DriverMembership::isEligible(self::SHOP_A, self::DRIVER_A));
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, self::DRIVER_B));
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, self::UNRELATED_DRIVER));
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, self::PENDING_DRIVER));
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, self::SUSPENDED_DRIVER));
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, self::INACTIVE_DRIVER));
        self::assertFalse(
            DeliveryManSetting::query()->where('user_id', self::PENDING_DRIVER)->exists(),
            'Pending invitations must not provision driver profile settings.'
        );

        // Shop-specific suspension is independent; another accepted active
        // shop relationship remains eligible and does not toggle users.active.
        $this->database->table('invitations')
            ->where('user_id', self::DRIVER_A)
            ->where('shop_id', self::SHOP_A)
            ->update(['driver_active' => false]);
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, self::DRIVER_A));
        self::assertTrue(DriverMembership::isEligible(self::SHOP_B, self::DRIVER_A));
        self::assertTrue((bool) User::query()->findOrFail(self::DRIVER_A)->active);
    }

    public function test_relationship_is_exactly_one_driver_invitation_and_fails_closed_on_ambiguous_rows(): void
    {
        $relation = DriverMembership::relationship(self::SHOP_A, self::DRIVER_A);
        self::assertInstanceOf(Invitation::class, $relation);
        self::assertSame(self::SHOP_A, (int) $relation->shop_id);
        self::assertSame(self::DRIVER_A, (int) $relation->user_id);

        $this->database->table('invitations')->insert([
            'id' => 901,
            'shop_id' => self::SHOP_A,
            'user_id' => self::DRIVER_A,
            'role' => 'deliveryman',
            'status' => Invitation::ACCEPTED,
            'driver_active' => true,
        ]);
        self::assertNull(DriverMembership::relationship(self::SHOP_A, self::DRIVER_A));
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, self::DRIVER_A));

        // A second row of a conflicting role must not let selection order
        // choose the apparently valid delivery-driver invitation.
        $this->database->table('invitations')->where('id', 901)->update(['role' => 'shop_manager']);
        self::assertNull(DriverMembership::relationship(self::SHOP_A, self::DRIVER_A));
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, self::DRIVER_A));
        self::assertNotContains(
            self::SHOP_A,
            DriverMembership::eligibleShopIds(self::DRIVER_A)->pluck('shop_id')->map(static fn ($id): int => (int) $id)->all()
        );
    }

    public function test_assignment_requires_shop_membership_but_legacy_admin_null_shop_still_accepts_active_driver(): void
    {
        $service = new class extends OrderService {
            public function sendNotification(
                mixed $model = null,
                array|null $receivers = [],
                string|int|null $message = '',
                string|int|null $title = null,
                mixed $data = [],
                array $userIds = [],
            ): void {
                // Assignment tests assert persistence, not external messaging.
            }
        };

        $assigned = $service->updateDeliveryMan(301, self::DRIVER_A, self::SHOP_A);
        self::assertTrue($assigned['status']);
        self::assertSame(self::DRIVER_A, (int) Order::query()->findOrFail(301)->deliveryman_id);

        foreach ([
            self::DRIVER_B,
            self::UNRELATED_DRIVER,
            self::PENDING_DRIVER,
            self::SUSPENDED_DRIVER,
            self::INACTIVE_DRIVER,
        ] as $ineligibleDriver) {
            $result = $service->updateDeliveryMan(302, $ineligibleDriver, self::SHOP_A);
            self::assertFalse($result['status'], "Driver {$ineligibleDriver} was assigned outside active Shop A membership.");
            self::assertNull(Order::query()->findOrFail(302)->deliveryman_id);
        }

        self::assertFalse($service->updateDeliveryMan(303, self::DRIVER_A, self::SHOP_A)['status']);
        self::assertFalse($service->updateDeliveryMan(304, self::DRIVER_A, self::SHOP_A)['status']);
        self::assertFalse($service->updateDeliveryMan(305, self::DRIVER_A, self::SHOP_A)['status']);

        $adminAssignment = $service->updateDeliveryMan(302, self::DRIVER_B, null);
        self::assertTrue($adminAssignment['status']);
        self::assertSame(self::DRIVER_B, (int) Order::query()->findOrFail(302)->deliveryman_id);
    }

    public function test_driver_provisioning_rejects_missing_password_without_creating_a_predictable_account(): void
    {
        $before = User::query()->count();

        $result = (new UserService())->create([
            'role' => 'deliveryman',
            'firstname' => 'No',
            'lastname' => 'Password',
            'email' => 'no-password@example.test',
        ]);

        self::assertFalse($result['status']);
        self::assertSame($before, User::query()->count());
        self::assertFalse(User::query()->where('email', 'no-password@example.test')->exists());
    }

    public function test_vendor_cannot_directly_provision_driver_even_with_password_or_verification_values(): void
    {
        $service = Mockery::mock(UserService::class);
        $service->shouldNotReceive('create');
        $controller = $this->sellerController(SellerUserController::class);
        $this->setControllerProperty($controller, 'service', $service);
        $request = Mockery::mock(UserCreateRequest::class);
        $request->shouldReceive('validated')->once()->andReturn([
            'role' => 'deliveryman',
            'firstname' => 'Direct',
            'email' => 'direct-driver@example.test',
            'phone' => '5551234567',
            'password' => 'vendor-supplied-secret',
            'password_confirmation' => 'vendor-supplied-secret',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $before = User::query()->count();

        self::assertSame(400, $controller->store($request)->getStatusCode());
        self::assertSame($before, User::query()->count());
        self::assertFalse(User::query()->where('email', 'direct-driver@example.test')->exists());
    }

    public function test_vendor_cannot_edit_a_driver_or_convert_non_driver_roles_via_role_flags(): void
    {
        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldReceive('userByUUID')->times(4)->andReturnUsing(
            static fn (string $uuid): ?User => User::query()->where('uuid', $uuid)->first()
        );
        $service = Mockery::mock(UserService::class);
        $service->shouldNotReceive('update');
        $controller = $this->sellerController(SellerUserController::class);
        $this->setControllerProperty($controller, 'repository', $repository);
        $this->setControllerProperty($controller, 'service', $service);

        $cases = [
            ['driver-a', 'user', [], []],
            ['shop-staff', 'deliveryman', [], []],
            ['shop-staff', 'user', ['deliveryman'], []],
            ['shop-staff', 'user', [], ['deliveryman']],
        ];
        foreach ($cases as [$uuid, $role, $roles, $roleIds]) {
            $request = Mockery::mock(UserUpdateRequest::class);
            $request->shouldReceive('input')->with('roles')->twice()->andReturn($roles);
            $request->shouldReceive('input')->with('role_ids')->twice()->andReturn($roleIds);
            $request->shouldReceive('input')->with('role')->zeroOrMoreTimes()->andReturn($role);
            $request->shouldNotReceive('validated');

            self::assertSame(400, $controller->update($request, $uuid)->getStatusCode());
        }
    }

    public function test_non_driver_shop_staff_keeps_the_legacy_global_status_toggle(): void
    {
        $staff = User::query()->where('uuid', 'shop-staff')->firstOrFail();
        self::assertTrue((bool) $staff->active);
        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldReceive('userByUUID')->once()->with('shop-staff')->andReturn($staff);
        $controller = $this->sellerController(SellerUserController::class);
        $this->setControllerProperty($controller, 'repository', $repository);
        $this->setControllerProperty($controller, 'service', new UserService());

        self::assertSame(200, $controller->setUserActive('shop-staff')->getStatusCode());
        self::assertFalse((bool) User::query()->findOrFail(self::STAFF)->active);
        self::assertSame(
            Invitation::ACCEPTED,
            (int) $this->database->table('invitations')->where('id', 80)->value('status')
        );
    }

    public function test_customer_and_staff_profiles_preserve_identity_without_foreign_driver_settings(): void
    {
        $this->database->table('roles')->insert([
            'id' => 3,
            'name' => 'user',
            'guard_name' => 'sanctum',
        ]);
        $this->database->table('model_has_roles')->insert([
            'role_id' => 3,
            'model_type' => User::class,
            'model_id' => self::DRIVER_B,
        ]);
        $this->database->table('deliveryman_settings')->where('id', 501)->update([
            'brand' => 'Shop B private vehicle',
        ]);

        $this->authenticateAs(User::query()->findOrFail(1));
        $actualRepository = new UserRepository();
        $foreignDriverProfile = $actualRepository->userByUUID('driver-b');
        self::assertInstanceOf(User::class, $foreignDriverProfile);
        self::assertTrue($foreignDriverProfile->relationLoaded('deliveryManSetting'));
        self::assertSame('Shop B private vehicle', $foreignDriverProfile->deliveryManSetting->brand);

        $showController = $this->sellerController(SellerUserController::class);
        $showRepository = Mockery::mock(UserRepository::class);
        $showRepository->shouldReceive('userByUUID')->once()->with('driver-b')->andReturn($foreignDriverProfile);
        $this->setControllerProperty($showController, 'repository', $showRepository);
        $showData = $showController->show('driver-b')->getData(true)['data'];
        self::assertSame('driver-b', $showData['uuid']);
        self::assertSame('Driver B', $showData['firstname']);
        self::assertArrayNotHasKey('delivery_man_setting', $showData);
        self::assertArrayNotHasKey('deliveryman_orders', $showData);
        self::assertArrayNotHasKey('invitations', $showData);
        self::assertArrayNotHasKey('wallet', $showData);
        self::assertStringNotContainsString('Shop B private vehicle', json_encode($showData));

        // The global customer list also eagerly loads vehicle settings from
        // UserRepository, but must strip them without dropping the customer
        // identity or their non-driver role.
        $customerController = $this->sellerController(SellerUserController::class);
        $this->setControllerProperty($customerController, 'repository', $actualRepository);
        $customerRequest = FilterParamsRequest::create('/customers', 'GET', ['perPage' => 100]);
        $customerCollection = $customerController->paginate($customerRequest);
        $customerData = $customerCollection->resolve($customerRequest);
        $customerRow = collect($customerData)->firstWhere('uuid', 'driver-b');
        self::assertIsArray($customerRow);
        self::assertSame('Driver B', $customerRow['firstname']);
        self::assertSame('user', $customerRow['role']);
        self::assertArrayNotHasKey('delivery_man_setting', $customerRow);
        self::assertStringNotContainsString('Shop B private vehicle', json_encode($customerRow));

        // A real accepted staff relationship for the dual-role identity remains
        // visible in Shop A, but never carries the Shop B delivery profile.
        $this->database->table('invitations')->insert([
            'id' => 81,
            'shop_id' => self::SHOP_A,
            'user_id' => self::DRIVER_B,
            'role' => 'shop_manager',
            'status' => Invitation::ACCEPTED,
            'shop_role_id' => 31,
        ]);
        $staffController = $this->sellerController(SellerUserController::class);
        $this->setControllerProperty($staffController, 'repository', $actualRepository);
        $staffData = $staffController->shopUserShow('driver-b')->getData(true)['data'];
        self::assertSame('driver-b', $staffData['uuid']);
        self::assertSame('user', $staffData['role']);
        self::assertArrayNotHasKey('delivery_man_setting', $staffData);
        self::assertStringNotContainsString('Shop B private vehicle', json_encode($staffData));
        self::assertSame([self::SHOP_A], array_values(array_unique(array_map(
            static fn (array $invitation): int => (int) $invitation['shop_id'],
            $staffData['invitations']
        ))));

        // The dedicated staff profile route still permits an eligible driver
        // to view the vehicle belonging to this same vendor/shop.
        $this->database->table('deliveryman_settings')->where('id', 502)->update([
            'brand' => 'Shop A private vehicle',
        ]);
        $ownDriverController = $this->sellerController(SellerUserController::class);
        $this->setControllerProperty($ownDriverController, 'repository', $actualRepository);
        $ownDriverData = $ownDriverController->shopUserShow('driver-a')->getData(true)['data'];
        self::assertSame('Shop A private vehicle', $ownDriverData['delivery_man_setting']['brand']);
    }

    public function test_vendor_driver_picker_ignores_forged_filters_and_only_returns_eligible_shop_members(): void
    {
        $controller = $this->sellerController(SellerUserController::class);
        $request = FilterParamsRequest::create('/?shop_id=' . self::SHOP_B . '&role=admin', 'GET', [
            'shop_id' => self::SHOP_B,
            'role' => 'admin',
            'perPage' => 100,
        ]);

        $eligible = $controller->getDeliveryman($request)->collection;
        self::assertSame([self::DRIVER_A], $eligible->pluck('id')->map(static fn ($id): int => (int) $id)->all());

        $includeInactive = FilterParamsRequest::create('/?include_inactive_memberships=1', 'GET', [
            'include_inactive_memberships' => '1',
            'perPage' => 100,
        ]);
        $members = $controller->getDeliveryman($includeInactive)->collection;
        self::assertSame(
            [self::DRIVER_A, self::SUSPENDED_DRIVER, self::INACTIVE_DRIVER],
            $members->pluck('id')->map(static fn ($id): int => (int) $id)->all()
        );
    }

    public function test_driver_remote_picker_search_uses_approved_identity_fields_within_eligible_shop_scope(): void
    {
        $this->database->table('users')->where('id', self::DRIVER_A)->update([
            'firstname' => 'Jean Courier',
            'lastname' => 'Smith',
            'email' => 'private-driver-email@example.test',
            'phone' => '5550101010',
        ]);
        $controller = $this->sellerController(SellerUserController::class);

        foreach ([
            'Jean Courier',
            'Smith',
            'private-driver-email@example.test',
            '5550101010',
        ] as $approvedSearch) {
            $request = FilterParamsRequest::create('/drivers', 'GET', [
                'search' => $approvedSearch,
                'perPage' => 10,
            ]);
            self::assertSame(
                [self::DRIVER_A],
                $controller->getDeliveryman($request)->collection
                    ->pluck('id')->map(static fn ($id): int => (int) $id)->all()
            );
        }

    }

    public function test_seller_settings_read_and_update_establish_persisted_owner_before_payload(): void
    {
        $foreignSetting = DeliveryManSetting::query()->findOrFail(501);
        $repository = Mockery::mock(DeliveryManSettingRepository::class);
        $repository->shouldReceive('detail')->once()->with(501)->andReturn($foreignSetting);
        $repository->shouldReceive('detail')->once()->with(502)->andReturn(DeliveryManSetting::query()->findOrFail(502));
        $controller = $this->sellerController(DeliveryManSettingController::class);
        $this->setControllerProperty($controller, 'repository', $repository);

        $forgedFilters = FilterParamsRequest::create('/?shop_id=22&user_id=102&role=admin', 'GET', [
            'shop_id' => self::SHOP_B,
            'user_id' => self::DRIVER_B,
            'role' => 'admin',
            'perPage' => 100,
        ]);
        self::assertSame(
            [502],
            $controller->paginate($forgedFilters)->collection
                ->pluck('id')->map(static fn ($id): int => (int) $id)->all()
        );

        $ownSettingResponse = $controller->show(502);
        self::assertSame(200, $ownSettingResponse->getStatusCode());
        self::assertSame(502, (int) $ownSettingResponse->getData(true)['data']['id']);
        self::assertSame(404, $controller->show(501)->getStatusCode());

        $foreignUpdate = Mockery::mock(AdminRequest::class);
        $foreignUpdate->shouldNotReceive('validated');
        self::assertSame(404, $controller->update(501, $foreignUpdate)->getStatusCode());
        self::assertSame(self::DRIVER_B, (int) DeliveryManSetting::query()->findOrFail(501)->user_id);

        $ownUpdate = Mockery::mock(AdminRequest::class);
        $ownUpdate->shouldReceive('validated')->once()->andReturn([
            'user_id' => self::DRIVER_B,
            'brand' => 'forged reassignment',
        ]);
        self::assertSame(400, $controller->update(502, $ownUpdate)->getStatusCode());
        self::assertSame(self::DRIVER_A, (int) DeliveryManSetting::query()->findOrFail(502)->user_id);
    }

    public function test_shared_settings_bulk_and_single_deletes_fail_without_mutating_global_row(): void
    {
        $controller = $this->sellerController(DeliveryManSettingController::class);

        self::assertSame(400, $controller->destroyOne(502)->getStatusCode());
        self::assertSame(
            400,
            $controller->destroy(FilterParamsRequest::create('/settings', 'DELETE', ['ids' => [502]]))->getStatusCode()
        );
        self::assertSame(
            [502],
            DeliveryManSetting::query()->where('user_id', self::DRIVER_A)->pluck('id')->map(static fn ($id): int => (int) $id)->all()
        );
    }

    public function test_driver_active_migration_adds_default_and_reverses_on_minimal_sqlite_schema(): void
    {
        Schema::dropIfExists('invitations');
        Schema::create('invitations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('user_id');
            $table->string('role');
            $table->unsignedTinyInteger('status')->default(Invitation::NEW);
        });

        $migrationPath = dirname(__DIR__, 2) . '/database/migrations/2026_10_02_010000_add_driver_active_to_invitations.php';
        $migration = require $migrationPath;
        $migration->up();

        self::assertTrue(Schema::hasColumn('invitations', 'driver_active'));
        $this->database->table('invitations')->insert([
            'shop_id' => self::SHOP_A,
            'user_id' => self::DRIVER_A,
            'role' => 'deliveryman',
            'status' => Invitation::ACCEPTED,
        ]);
        self::assertTrue((bool) $this->database->table('invitations')->value('driver_active'));

        $migration->down();
        self::assertFalse(Schema::hasColumn('invitations', 'driver_active'));
    }

    public function test_vendor_suspension_changes_only_accepted_shop_membership_not_global_account_or_other_shop(): void
    {
        $driver = User::query()->findOrFail(self::DRIVER_A);
        $repository = Mockery::mock(UserRepository::class);
        $repository->shouldReceive('userByUUID')->once()->with('driver-a')->andReturn($driver);
        $controller = $this->sellerController(SellerUserController::class);
        $this->setControllerProperty($controller, 'repository', $repository);

        self::assertSame(200, $controller->setUserActive('driver-a')->getStatusCode());
        self::assertFalse((bool) $this->database->table('invitations')
            ->where('shop_id', self::SHOP_A)->where('user_id', self::DRIVER_A)->value('driver_active'));
        self::assertTrue((bool) $this->database->table('invitations')
            ->where('shop_id', self::SHOP_B)->where('user_id', self::DRIVER_A)->value('driver_active'));
        self::assertTrue((bool) User::query()->findOrFail(self::DRIVER_A)->active);
    }

    public function test_staff_permissions_gate_assignment_reads_settings_and_driver_status_separately(): void
    {
        $staff = User::query()->findOrFail(self::STAFF);
        $this->authenticateAs($staff);
        $middleware = new CheckShopPermission();
        $next = static fn (): Response => new Response('allowed', 200);

        self::assertSame(200, $middleware->handle(Request::create('/', 'POST'), $next, 'orders.manage')->getStatusCode());
        self::assertSame(200, $middleware->handle(Request::create('/', 'GET'), $next, 'staff.view')->getStatusCode());
        self::assertSame(403, $middleware->handle(Request::create('/', 'PUT'), $next, 'orders.delivery_settings')->getStatusCode());
        self::assertSame(403, $middleware->handle(Request::create('/', 'POST'), $next, 'staff.invite')->getStatusCode());

        $owner = User::query()->findOrFail(1);
        $this->authenticateAs($owner);
        self::assertSame(200, $middleware->handle(Request::create('/', 'POST'), $next, 'orders.manage')->getStatusCode());
        self::assertSame(200, $middleware->handle(Request::create('/', 'PUT'), $next, 'orders.delivery_settings')->getStatusCode());
    }

    public function test_assigned_order_query_and_current_selection_cannot_cross_membership_or_claim_unassigned_orders(): void
    {
        $this->authenticateAs(User::query()->findOrFail(self::DRIVER_B));
        self::assertNull((new DriverOrderRepository())->show(301));

        $this->authenticateAs(User::query()->findOrFail(self::DRIVER_A));
        $this->database->table('orders')->insert([
            ['id' => 306, 'shop_id' => self::SHOP_A, 'deliveryman_id' => self::DRIVER_A, 'delivery_type' => Order::DELIVERY, 'type' => Order::SELLER, 'current' => true],
            ['id' => 307, 'shop_id' => self::SHOP_B, 'deliveryman_id' => self::DRIVER_B, 'delivery_type' => Order::DELIVERY, 'type' => Order::SELLER, 'current' => true],
            ['id' => 308, 'shop_id' => self::SHOP_A, 'deliveryman_id' => null, 'delivery_type' => Order::DELIVERY, 'type' => Order::SELLER, 'current' => false],
            ['id' => 310, 'shop_id' => self::SHOP_A, 'deliveryman_id' => self::DRIVER_A, 'delivery_type' => Order::DELIVERY, 'type' => Order::IN_HOUSE, 'current' => false],
        ]);
        $visibleIds = DriverMembership::constrainAssignedOrders(Order::query(), self::DRIVER_A)
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
        self::assertSame([306, 310], $visibleIds);

        $service = new OrderService();
        $forgedForeignOrder = $service->setCurrent(307, self::DRIVER_A);
        self::assertFalse($forgedForeignOrder['status']);
        self::assertTrue((bool) Order::query()->findOrFail(306)->current);
        self::assertTrue((bool) Order::query()->findOrFail(307)->current);

        $unassignedClaim = $service->attachDeliveryMan(308);
        self::assertFalse($unassignedClaim['status']);
        self::assertNull(Order::query()->findOrFail(308)->deliveryman_id);

        self::assertTrue($service->setCurrent(306, self::DRIVER_A)['status']);
        self::assertTrue((bool) Order::query()->findOrFail(306)->current);
        self::assertTrue((bool) Order::query()->findOrFail(307)->current);
    }

    public function test_driver_order_list_overwrites_identity_and_removes_unassigned_pool_and_type_filters(): void
    {
        $driver = User::query()->findOrFail(self::DRIVER_A);
        $this->authenticateAs($driver);
        $repository = Mockery::mock(DriverOrderRepository::class);
        $capturedFilter = null;
        $repository->shouldReceive('paginate')
            ->once()
            ->andReturnUsing(static function (array $filter) use (&$capturedFilter): LengthAwarePaginator {
                $capturedFilter = $filter;

                return new LengthAwarePaginator([], 0, 10);
            });

        $controller = (new \ReflectionClass(DriverOrderController::class))->newInstanceWithoutConstructor();
        $this->setControllerProperty($controller, 'repository', $repository);
        $request = FilterParamsRequest::create('/api/v1/dashboard/deliveryman/orders', 'GET', [
            'deliveryman_id' => self::UNRELATED_DRIVER,
            'empty-deliveryman' => 1,
            'isset-deliveryman' => 1,
            'type' => Order::IN_HOUSE,
            'shop_id' => self::SHOP_B,
        ]);

        $controller->paginate($request);
        self::assertSame(self::DRIVER_A, (int) $capturedFilter['deliveryman_id']);
        self::assertArrayNotHasKey('empty-deliveryman', $capturedFilter);
        self::assertArrayNotHasKey('isset-deliveryman', $capturedFilter);
        self::assertArrayNotHasKey('type', $capturedFilter);
    }

    public function test_driver_status_update_denies_other_assignee_even_inside_the_actor_shop(): void
    {
        $this->database->table('orders')->insert([
            'id' => 311,
            'shop_id' => self::SHOP_A,
            'user_id' => null,
            'deliveryman_id' => self::DRIVER_B,
            'delivery_type' => Order::DELIVERY,
            'type' => Order::SELLER,
            'status' => Order::STATUS_READY,
            'current' => false,
        ]);
        $this->authenticateAs(User::query()->findOrFail(self::DRIVER_A));
        $controller = (new \ReflectionClass(DriverOrderController::class))->newInstanceWithoutConstructor();
        (new \ReflectionClass(\App\Http\Controllers\Controller::class))
            ->getConstructor()
            ->invoke($controller);
        $request = Mockery::mock(StatusUpdateRequest::class);
        $request->shouldReceive('input')->with('status')->andReturn(Order::STATUS_ON_A_WAY);
        $request->shouldNotReceive('validated');

        $response = $controller->orderStatusUpdate(311, $request);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame(Order::STATUS_READY, $this->database->table('orders')->where('id', 311)->value('status'));
    }

    public function test_location_update_dispatches_only_authorized_ids_with_actor_and_listener_rechecks_live_scope(): void
    {
        $this->database->table('orders')->insert([
            ['id' => 308, 'shop_id' => self::SHOP_A, 'user_id' => null, 'deliveryman_id' => null, 'delivery_type' => Order::DELIVERY, 'type' => Order::SELLER, 'status' => Order::STATUS_READY, 'current' => false],
            ['id' => 312, 'shop_id' => self::SHOP_A, 'user_id' => null, 'deliveryman_id' => self::DRIVER_A, 'delivery_type' => Order::POINT, 'type' => Order::SELLER, 'status' => Order::STATUS_READY, 'current' => false],
        ]);
        $this->database->table('orders')->where('id', 301)->update(['deliveryman_id' => self::DRIVER_A]);
        self::assertFalse(Schema::hasColumn('orders', 'order_ids'));

        $this->authenticateAs(User::query()->findOrFail(self::DRIVER_A));
        $events = [];
        $this->app['events']->listen(
            SendDeliveryManLocationByOrder::class,
            static function (SendDeliveryManLocationByOrder $event) use (&$events): void {
                $events[] = $event;
            }
        );

        $this->authenticateAs(null);
        $missingActorResult = (new DeliveryManSettingService())->updateLocation([
            'location' => ['latitude' => 1, 'longitude' => 2],
            'order_ids' => [301],
        ]);
        self::assertFalse($missingActorResult['status']);
        self::assertSame([], $events);

        $this->authenticateAs(User::query()->findOrFail(self::DRIVER_A));
        $result = (new DeliveryManSettingService())->updateLocation([
            'location' => ['latitude' => 51.5007, 'longitude' => -0.1246],
            'order_ids' => [301, 305, 308, 312, 'invalid'],
        ]);

        self::assertTrue($result['status']);
        self::assertSame(
            ['latitude' => 51.5007, 'longitude' => -0.1246],
            DeliveryManSetting::query()->findOrFail(502)->location
        );
        self::assertCount(1, $events);
        self::assertSame(self::DRIVER_A, $events[0]->actorId);
        self::assertSame([301], $events[0]->orderIds);
        self::assertSame(self::DRIVER_A, (int) Order::query()->findOrFail(301)->deliveryman_id);
        self::assertSame(Order::STATUS_NEW, Order::query()->findOrFail(301)->status);

        $listener = new class extends SendDeliveryManLocationByOrderListener {
            public array $sent = [];

            protected function sendToSocket(array $payload): void
            {
                $this->sent[] = $payload;
            }
        };

        // Queue workers need no ambient login: the event carries the actor,
        // and the listener evaluates current assignment/membership itself.
        $this->authenticateAs(null);
        $listener->handle($events[0]);
        self::assertCount(1, $listener->sent);
        self::assertSame('301', $listener->sent[0]['target']);
        self::assertSame(
            ['latitude' => 51.5007, 'longitude' => -0.1246],
            $listener->sent[0]['data']['location']
        );

        // A queued event cannot keep broadcasting after reassignment.
        $this->database->table('orders')->where('id', 301)->update(['deliveryman_id' => self::DRIVER_B]);
        $listener->handle($events[0]);
        self::assertCount(1, $listener->sent);

        // Foreign-assigned and unassigned IDs are removed by the live scope.
        $listener->handle(new SendDeliveryManLocationByOrder([305, 308, 312], 'en', self::DRIVER_A));
        self::assertCount(1, $listener->sent);

        // Old two-argument producers fail closed even if a request happens to
        // have an authenticated driver in ambient context.
        $this->authenticateAs(User::query()->findOrFail(self::DRIVER_A));
        $listener->handle(new SendDeliveryManLocationByOrder([301], 'en'));
        self::assertCount(1, $listener->sent);
        $this->authenticateAs(null);
        $listener->handle(new SendDeliveryManLocationByOrder([301], 'en'));
        self::assertCount(1, $listener->sent);

        // Actor eligibility is rechecked for both global disablement and the
        // shop-specific suspension branch.
        $this->database->table('orders')->where('id', 301)->update(['deliveryman_id' => self::DRIVER_A]);
        $this->database->table('users')->where('id', self::DRIVER_A)->update(['active' => false]);
        $listener->handle(new SendDeliveryManLocationByOrder([301], 'en', self::DRIVER_A));
        self::assertCount(1, $listener->sent);
        $this->database->table('users')->where('id', self::DRIVER_A)->update(['active' => true]);
        $this->database->table('invitations')
            ->where('shop_id', self::SHOP_A)->where('user_id', self::DRIVER_A)
            ->update(['driver_active' => false]);
        $listener->handle(new SendDeliveryManLocationByOrder([301], 'en', self::DRIVER_A));
        self::assertCount(1, $listener->sent);
    }

    public function test_driver_review_rejects_other_assignee_without_mutating_an_order(): void
    {
        $this->authenticateAs(User::query()->findOrFail(self::DRIVER_A));
        $result = (new \App\Services\OrderService\OrderReviewService())
            ->addReviewByDeliveryman(305, ['rating' => 5, 'comment' => 'should not persist']);

        self::assertFalse($result['status']);
        self::assertSame(self::DRIVER_B, (int) Order::query()->findOrFail(305)->deliveryman_id);
    }

    public function test_vendor_assignment_modal_keeps_the_deliveryman_id_api_contract(): void
    {
        $modal = dirname(__DIR__, 3) . '/admin/src/views/seller-views/order/orderDeliveryman.jsx';
        self::assertFileExists($modal);
        $source = file_get_contents($modal);
        self::assertIsString($source);
        self::assertMatchesRegularExpression(
            '/const\s+params\s*=\s*\{\s*deliveryman_id\s*:\s*values\.deliveryman\.value\s*\}\s*;/',
            $source
        );
        self::assertDoesNotMatchRegularExpression('/const\s+params\s*=\s*\{\s*deliveryman\s*:/', $source);
    }

    public function test_offboarding_keeps_historical_assignment_but_removes_order_from_active_driver_scope(): void
    {
        $this->database->table('orders')->where('id', 301)->update(['deliveryman_id' => self::DRIVER_A]);
        $this->database->table('invitations')
            ->where('shop_id', self::SHOP_A)
            ->where('user_id', self::DRIVER_A)
            ->update(['driver_active' => false]);

        self::assertSame(self::DRIVER_A, (int) Order::query()->findOrFail(301)->deliveryman_id);
        self::assertSame(
            [],
            DriverMembership::constrainAssignedOrders(Order::query(), self::DRIVER_A)->pluck('id')->all()
        );
    }

    private function configureRoles(): void
    {
        $this->app['config']->set('auth.defaults.guard', 'sanctum');
        $this->app['config']->set('auth.guards.sanctum', ['driver' => 'sanctum', 'provider' => 'users']);
        $this->app['config']->set('permission', [
            'models' => [
                'permission' => \Spatie\Permission\Models\Permission::class,
                'role' => \Spatie\Permission\Models\Role::class,
            ],
            'table_names' => [
                'roles' => 'roles',
                'model_has_roles' => 'model_has_roles',
                'model_has_permissions' => 'model_has_permissions',
                'role_has_permissions' => 'role_has_permissions',
                'permissions' => 'permissions',
            ],
            'column_names' => [
                'role_pivot_key' => 'role_id',
                'permission_pivot_key' => 'permission_id',
                'model_morph_key' => 'model_id',
            ],
            'teams' => false,
            'cache' => ['expiration_time' => 3600, 'key' => 'hardening.delivery-driver', 'store' => 'array'],
            'events_enabled' => false,
        ]);
        $this->app->singleton(PermissionRegistrar::class);
    }

    private function authenticateAs(?User $user): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($user?->id);
        $guard->shouldReceive('check')->andReturn($user !== null);
        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        Facade::clearResolvedInstance('auth');
        $this->app->instance('request', Request::create('/api/v1', 'GET', ['lang' => 'en']));
    }

    private function sellerController(string $class): object
    {
        $controller = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $shopProperty = (new \ReflectionClass(SellerBaseController::class))->getProperty('shop');
        $shopProperty->setAccessible(true);
        $shopProperty->setValue($controller, Shop::query()->findOrFail(self::SHOP_A));
        (new \ReflectionClass(\App\Http\Controllers\Controller::class))
            ->getConstructor()
            ->invoke($controller);

        return $controller;
    }

    private function setControllerProperty(object $controller, string $name, mixed $value): void
    {
        $property = (new \ReflectionClass($controller))->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($controller, $value);
    }

    private function createSchema(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->increments('id');
            $table->boolean('default')->default(false);
        });
        Schema::create('languages', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->boolean('default')->default(false);
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
        });
        Schema::create('settings', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('key');
            $table->text('value')->nullable();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('uuid')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->boolean('active')->default(true);
            $table->string('lang')->nullable();
            $table->text('firebase_token')->nullable();
            $table->timestamps();
        });
        Schema::create('shops', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->text('location')->nullable();
            $table->decimal('tax', 8, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('price_per_km', 12, 2)->nullable();
            $table->string('background_img')->nullable();
            $table->string('logo_img')->nullable();
            $table->string('uuid')->nullable();
            $table->string('phone')->nullable();
            $table->string('delivery_time')->nullable();
        });
        Schema::create('shop_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->string('locale');
            $table->string('title')->nullable();
        });
        Schema::create('shop_working_days', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->string('day')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
        });
        Schema::create('reviews', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('assignable_id');
            $table->string('assignable_type');
            $table->decimal('rating', 4, 2)->nullable();
        });
        Schema::create('order_details', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('stock_id')->nullable();
        });
        Schema::create('country_admins', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('country_id');
        });
        Schema::create('country_invitations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('country_role_id')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
        });
        Schema::create('invitations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('user_id');
            $table->string('role')->nullable();
            $table->unsignedTinyInteger('status')->default(Invitation::NEW);
            $table->boolean('driver_active')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('shop_role_id')->nullable();
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
        });
        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedInteger('role_id');
            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
        Schema::create('shop_roles', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->string('name');
        });
        Schema::create('wallets', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('uuid')->nullable();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('currency_id')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('user_points', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->decimal('price', 12, 2)->default(0);
        });
        Schema::create('user_working_days', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('day');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
        });
        Schema::create('user_translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('locale');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
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
        Schema::create('deliveryman_settings', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('type_of_technique')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('number')->nullable();
            $table->string('color')->nullable();
            $table->boolean('online')->default(false);
            $table->text('location')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('deliveryman_id')->nullable();
            $table->string('delivery_type')->default(Order::DELIVERY);
            $table->unsignedTinyInteger('type')->default(Order::SELLER);
            $table->string('status')->default(Order::STATUS_NEW);
            $table->boolean('current')->default(false);
            $table->timestamps();
        });
        $this->database->table('languages')->insert(['locale' => 'en', 'default' => true]);
        $this->database->table('currencies')->insert(['default' => true]);
        $this->database->table('shops')->insert([
            ['id' => self::SHOP_A, 'user_id' => 1],
            ['id' => self::SHOP_B, 'user_id' => 2],
        ]);
        $this->database->table('shop_roles')->insert([
            ['id' => 31, 'shop_id' => self::SHOP_A, 'name' => 'Assigned driver staff'],
        ]);
        $this->database->table('shop_permissions')->insert([
            ['id' => 41, 'key' => 'staff.view', 'group' => 'staff', 'label' => 'View staff'],
            ['id' => 42, 'key' => 'orders.manage', 'group' => 'orders', 'label' => 'Manage orders'],
            ['id' => 43, 'key' => 'orders.delivery_settings', 'group' => 'orders', 'label' => 'Manage delivery settings'],
            ['id' => 44, 'key' => 'staff.invite', 'group' => 'staff', 'label' => 'Invite staff'],
        ]);
        $this->database->table('shop_role_permissions')->insert([
            ['shop_role_id' => 31, 'shop_permission_id' => 41],
            ['shop_role_id' => 31, 'shop_permission_id' => 42],
        ]);
        $this->database->table('invitations')->insert([
            [
                'id' => 80,
                'shop_id' => self::SHOP_A,
                'user_id' => self::STAFF,
                'role' => 'shop_manager',
                'status' => Invitation::ACCEPTED,
                'shop_role_id' => 31,
            ],
        ]);
        $this->database->table('deliveryman_settings')->insert([
            ['id' => 501, 'user_id' => self::DRIVER_B],
            ['id' => 502, 'user_id' => self::DRIVER_A],
        ]);
    }

    private function seedUsersAndMemberships(): void
    {
        $driverIds = [
            self::DRIVER_A,
            self::DRIVER_B,
            self::UNRELATED_DRIVER,
            self::PENDING_DRIVER,
            self::SUSPENDED_DRIVER,
            self::INACTIVE_DRIVER,
        ];
        $this->database->table('users')->insert([
            ['id' => 1, 'uuid' => null, 'firstname' => 'Vendor A', 'active' => true],
            ['id' => 2, 'uuid' => null, 'firstname' => 'Vendor B', 'active' => true],
            ['id' => self::DRIVER_A, 'uuid' => 'driver-a', 'firstname' => 'Driver A', 'active' => true],
            ['id' => self::DRIVER_B, 'uuid' => 'driver-b', 'firstname' => 'Driver B', 'active' => true],
            ['id' => self::UNRELATED_DRIVER, 'uuid' => null, 'firstname' => 'Unrelated', 'active' => true],
            ['id' => self::PENDING_DRIVER, 'uuid' => null, 'firstname' => 'Pending', 'active' => true],
            ['id' => self::SUSPENDED_DRIVER, 'uuid' => null, 'firstname' => 'Suspended', 'active' => true],
            ['id' => self::INACTIVE_DRIVER, 'uuid' => null, 'firstname' => 'Inactive account', 'active' => false],
            ['id' => self::STAFF, 'uuid' => 'shop-staff', 'firstname' => 'Shop staff', 'active' => true],
        ]);
        $this->database->table('roles')->insert([
            ['id' => 1, 'name' => 'deliveryman', 'guard_name' => 'sanctum'],
            ['id' => 2, 'name' => 'shop_manager', 'guard_name' => 'sanctum'],
        ]);
        foreach ($driverIds as $driverId) {
            $this->database->table('model_has_roles')->insert([
                'role_id' => 1,
                'model_type' => User::class,
                'model_id' => $driverId,
            ]);
        }
        $this->database->table('model_has_roles')->insert([
            'role_id' => 2,
            'model_type' => User::class,
            'model_id' => self::STAFF,
        ]);
        $this->database->table('invitations')->insert([
            ['id' => 11, 'shop_id' => self::SHOP_A, 'user_id' => self::DRIVER_A, 'role' => 'deliveryman', 'status' => Invitation::ACCEPTED, 'driver_active' => true],
            ['id' => 12, 'shop_id' => self::SHOP_B, 'user_id' => self::DRIVER_A, 'role' => 'deliveryman', 'status' => Invitation::ACCEPTED, 'driver_active' => true],
            ['id' => 13, 'shop_id' => self::SHOP_B, 'user_id' => self::DRIVER_B, 'role' => 'deliveryman', 'status' => Invitation::ACCEPTED, 'driver_active' => true],
            ['id' => 14, 'shop_id' => self::SHOP_A, 'user_id' => self::PENDING_DRIVER, 'role' => 'deliveryman', 'status' => Invitation::NEW, 'driver_active' => true],
            ['id' => 15, 'shop_id' => self::SHOP_A, 'user_id' => self::SUSPENDED_DRIVER, 'role' => 'deliveryman', 'status' => Invitation::ACCEPTED, 'driver_active' => false],
            ['id' => 16, 'shop_id' => self::SHOP_A, 'user_id' => self::INACTIVE_DRIVER, 'role' => 'deliveryman', 'status' => Invitation::ACCEPTED, 'driver_active' => true],
        ]);
        $this->database->table('orders')->insert([
            ['id' => 301, 'shop_id' => self::SHOP_A, 'deliveryman_id' => null, 'delivery_type' => Order::DELIVERY, 'type' => Order::SELLER, 'current' => false],
            ['id' => 302, 'shop_id' => self::SHOP_A, 'deliveryman_id' => null, 'delivery_type' => Order::DELIVERY, 'type' => Order::SELLER, 'current' => false],
            ['id' => 303, 'shop_id' => self::SHOP_A, 'deliveryman_id' => null, 'delivery_type' => Order::POINT, 'type' => Order::SELLER, 'current' => false],
            ['id' => 304, 'shop_id' => self::SHOP_A, 'deliveryman_id' => null, 'delivery_type' => Order::DIGITAL, 'type' => Order::SELLER, 'current' => false],
            ['id' => 305, 'shop_id' => self::SHOP_B, 'deliveryman_id' => self::DRIVER_B, 'delivery_type' => Order::DELIVERY, 'type' => Order::SELLER, 'current' => false],
        ]);
    }
}