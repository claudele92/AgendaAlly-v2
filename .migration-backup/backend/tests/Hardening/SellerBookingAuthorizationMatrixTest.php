<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\Seller\BookingClientController;
use App\Http\Controllers\API\v1\Dashboard\Seller\BookingController;
use App\Http\Controllers\API\v1\Dashboard\Seller\SellerBaseController;
use App\Models\Invitation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionClass;

final class SellerBookingAuthorizationMatrixTest extends IsolatedTestCase
{
    private const SHOP = 1;
    private const BRANCH = 11;
    private const OTHER_BRANCH = 12;
    private const FOREIGN_BRANCH = 21;
    private const OWNER = 100;
    private const BRANCH_MANAGER = 101;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->app->register(\Illuminate\Translation\TranslationServiceProvider::class);
        $this->app['config']->set('view', [
            'paths' => [],
            'compiled' => sys_get_temp_dir(),
        ]);
        $this->app->register(\Illuminate\View\ViewServiceProvider::class);
        $this->app['config']->set('auth.defaults.guard', 'sanctum');
        $this->app['config']->set('auth.guards.sanctum', [
            'driver' => 'sanctum',
            'provider' => 'users',
        ]);
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
            'cache' => [
                'expiration_time' => 3600,
                'key' => 'seller.booking.authorization.matrix',
                'store' => 'array',
            ],
            'events_enabled' => false,
        ]);
        $this->app->singleton(\Spatie\Permission\PermissionRegistrar::class);
        $this->createSchema();
        $this->seedMatrix();
    }

    public function test_real_client_search_is_shop_and_assigned_branch_scoped_for_registered_and_local_clients(): void
    {
        // A legacy worker may have a generic user role and no invitation row.
        // Its real service-master association must independently exclude it.
        $this->database->table('invitations')->where('user_id', 3)->delete();
        $manager = User::query()->findOrFail(self::BRANCH_MANAGER);
        $this->authenticateAs($manager);
        $controller = $this->controller(BookingClientController::class, self::SHOP);

        $response = $controller->index(Request::create('/api/v1/dashboard/seller/booking-clients', 'GET', [
            'search' => '',
            'perPage' => 50,
        ]))->getData(true);
        $clients = collect($response['data']);

        self::assertSame(
            [1],
            $clients->where('kind', 'registered')->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
        );
        self::assertSame(
            [1],
            $clients->where('kind', 'local')->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
        );
        foreach ([2, 3, 4, 5, 6, 7, 8, 100] as $protectedUserId) {
            self::assertNotContains(
                $protectedUserId,
                $clients->where('kind', 'registered')->pluck('id')->map(fn ($id) => (int) $id)->all(),
                "Protected or out-of-branch account {$protectedUserId} leaked into client discovery."
            );
        }
        self::assertNotContains(
            3,
            $clients->where('kind', 'local')->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'A local client belonging to another business must not be discoverable.'
        );

        $this->assertValidationField(
            fn () => $controller->index(Request::create('/api/v1/dashboard/seller/booking-clients', 'GET', [
                'shop_location_id' => self::OTHER_BRANCH,
            ])),
            'shop_location_id'
        );
        $this->assertValidationField(
            fn () => $controller->index(Request::create('/api/v1/dashboard/seller/booking-clients', 'GET', [
                'shop_location_id' => self::FOREIGN_BRANCH,
            ])),
            'shop_location_id'
        );

        $owner = User::query()->findOrFail(self::OWNER);
        $this->authenticateAs($owner);
        $ownerController = $this->controller(BookingClientController::class, self::SHOP);
        $ownerClients = collect($ownerController->index(Request::create(
            '/api/v1/dashboard/seller/booking-clients',
            'GET',
            ['perPage' => 50]
        ))->getData(true)['data']);

        self::assertEqualsCanonicalizing(
            [1, 9],
            $ownerClients->where('kind', 'registered')->pluck('id')->map(fn ($id) => (int) $id)->all()
        );
        self::assertEqualsCanonicalizing(
            [1, 2, 4],
            $ownerClients->where('kind', 'local')->pluck('id')->map(fn ($id) => (int) $id)->all()
        );
        self::assertNotContains(
            5,
            $ownerClients->where('kind', 'registered')->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'A customer whose only booking is at another business must not be discoverable.'
        );
        self::assertNotContains(
            3,
            $ownerClients->where('kind', 'local')->pluck('id')->map(fn ($id) => (int) $id)->all()
        );
    }

    public function test_forged_registered_customer_and_service_master_ids_are_rejected_but_valid_customer_is_accepted(): void
    {
        $manager = User::query()->findOrFail(self::BRANCH_MANAGER);
        $this->authenticateAs($manager);
        $controller = $this->controller(BookingController::class, self::SHOP);
        $valid = [
            'shop_id' => self::SHOP,
            'user_id' => 1,
            'data' => [[
                'service_master_id' => 31,
                'shop_location_id' => self::BRANCH,
            ]],
        ];

        $normalized = $this->validateSellerBooking($controller, $valid);
        self::assertSame(self::BRANCH, (int) $normalized['data'][0]['shop_location_id']);

        foreach ([2, 3, 4, 5, 7, 100] as $protectedUserId) {
            $this->assertValidationField(
                fn () => $this->validateSellerBooking($controller, array_replace($valid, [
                    'user_id' => $protectedUserId,
                ])),
                'user_id'
            );
        }

        $this->assertValidationField(
            fn () => $this->validateSellerBooking($controller, array_replace($valid, [
                'user_id' => 9,
                'data' => [[
                    'service_master_id' => 34,
                    'shop_location_id' => self::OTHER_BRANCH,
                ]],
            ])),
            'data'
        );

        $this->assertValidationField(
            fn () => $this->validateSellerBooking($controller, array_replace($valid, [
                'shop_id' => 2,
                'data' => [[
                    'service_master_id' => 32,
                    'shop_location_id' => self::BRANCH,
                ]],
            ])),
            'data'
        );
        $this->assertValidationField(
            fn () => $this->validateSellerBooking($controller, array_replace($valid, [
                'data' => [[
                    'service_master_id' => 33,
                    'shop_location_id' => self::BRANCH,
                ]],
            ])),
            'data'
        );

        $foreignLocationCustomer = array_replace($valid, [
            'user_id' => 8,
            'data' => [[
                'service_master_id' => 31,
                'shop_location_id' => self::FOREIGN_BRANCH,
            ]],
        ]);
        $this->assertValidationField(
            fn () => $this->validateSellerBooking($controller, $foreignLocationCustomer),
            'user_id'
        );
        $this->assertValidationField(
            fn () => $this->validateSellerBooking($controller, [
                'shop_id' => self::SHOP,
                'local_client_id' => 1,
                'data' => [[
                    'service_master_id' => 31,
                    'shop_location_id' => self::FOREIGN_BRANCH,
                ]],
            ]),
            'local_client_id'
        );
    }

    public function test_valid_local_client_works_and_forged_foreign_or_unauthorized_local_ids_fail(): void
    {
        $manager = User::query()->findOrFail(self::BRANCH_MANAGER);
        $this->authenticateAs($manager);
        $controller = $this->controller(BookingController::class, self::SHOP);
        $valid = [
            'shop_id' => self::SHOP,
            'local_client_id' => 1,
            'data' => [[
                'service_master_id' => 31,
                'shop_location_id' => self::BRANCH,
            ]],
        ];

        $normalized = $this->validateSellerBooking($controller, $valid);
        self::assertSame(self::BRANCH, (int) $normalized['data'][0]['shop_location_id']);

        $this->assertValidationField(
            fn () => $this->validateSellerBooking($controller, array_replace($valid, [
                'local_client_id' => 2,
                'user_id' => null,
            ])),
            'local_client_id'
        );
        $this->assertValidationField(
            fn () => $this->validateSellerBooking($controller, array_replace($valid, [
                'local_client_id' => 3,
                'user_id' => null,
            ])),
            'local_client_id'
        );
    }

    private function validateSellerBooking(BookingController $controller, array $data): array
    {
        $method = (new ReflectionClass(BookingController::class))->getMethod('validateSellerBooking');
        $method->setAccessible(true);

        return $method->invoke($controller, $data);
    }

    private function assertValidationField(callable $operation, string $field): void
    {
        try {
            $operation();
            self::fail("Expected validation failure for {$field}.");
        } catch (ValidationException $error) {
            self::assertArrayHasKey($field, $error->errors());
        }
    }

    private function controller(string $controllerClass, int $shopId): object
    {
        $reflection = new ReflectionClass($controllerClass);
        $controller = $reflection->newInstanceWithoutConstructor();
        $shopProperty = (new ReflectionClass(SellerBaseController::class))->getProperty('shop');
        $shopProperty->setAccessible(true);
        $shopProperty->setValue($controller, Shop::query()->findOrFail($shopId));

        return $controller;
    }

    private function authenticateAs(User $user): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        Facade::clearResolvedInstance('auth');
        $this->app->instance('request', Request::create('/api/v1/dashboard/seller/bookings', 'POST'));
    }

    private function createSchema(): void
    {
        $schema = $this->database->schema('hardening');
        $schema->create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('active')->default(true);
        });
        $schema->create('shops', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
        });
        $schema->create('country_admins', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('country_id')->nullable();
        });
        $schema->create('country_invitations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('status');
            $table->unsignedInteger('country_role_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
        });
        $schema->create('shop_locations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedTinyInteger('type');
        });
        $schema->create('roles', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
        });
        $schema->create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedInteger('role_id');
            $table->string('model_type');
            $table->unsignedInteger('model_id');
        });
        $schema->create('invitations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('status');
            $table->string('role')->nullable();
            $table->unsignedInteger('shop_role_id')->nullable();
        });
        $schema->create('invitation_shop_locations', function (Blueprint $table): void {
            $table->unsignedInteger('invitation_id');
            $table->unsignedInteger('shop_location_id');
        });
        $schema->create('shop_roles', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->string('name');
        });
        $schema->create('shop_permissions', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('key');
            $table->string('group')->nullable();
            $table->string('label')->nullable();
        });
        $schema->create('shop_role_permissions', function (Blueprint $table): void {
            $table->unsignedInteger('shop_role_id');
            $table->unsignedInteger('shop_permission_id');
        });
        $schema->create('services', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
        });
        $schema->create('service_masters', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('master_id');
            $table->unsignedInteger('shop_id');
            $table->boolean('active')->default(true);
        });
        $schema->create('bookings', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('shop_location_id')->nullable();
        });
        $schema->create('seller_booking_clients', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('shop_location_id')->nullable();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('dedupe_scope');
            $table->string('normalized_phone')->nullable();
            $table->string('normalized_email')->nullable();
        });
    }

    private function seedMatrix(): void
    {
        $users = [
            [1, 'Existing', 'Customer', 'existing@example.test'],
            [2, 'Staff', 'User', 'staff@example.test'],
            [3, 'Master', 'User', 'master@example.test'],
            [4, 'Admin', 'User', 'admin@example.test'],
            [5, 'Foreign', 'Customer', 'foreign@example.test'],
            [6, 'Other', 'Branch', 'other-branch@example.test'],
            [7, 'Business', 'Owner', 'business-owner@example.test'],
            [8, 'Forged', 'Branch', 'forged-branch@example.test'],
            [9, 'Another', 'Branch Customer', 'branch-customer@example.test'],
            [self::OWNER, 'Shop', 'Owner', 'owner@example.test'],
            [self::BRANCH_MANAGER, 'Branch', 'Manager', 'manager@example.test'],
            [200, 'Foreign', 'Owner', 'foreign-owner@example.test'],
        ];
        foreach ($users as [$id, $first, $last, $email]) {
            $this->database->table('users')->insert([
                'id' => $id,
                'firstname' => $first,
                'lastname' => $last,
                'email' => $email,
                'phone' => "+1555000{$id}",
            ]);
        }
        $this->database->table('shops')->insert([
            ['id' => self::SHOP, 'user_id' => self::OWNER],
            ['id' => 2, 'user_id' => 7],
            ['id' => 3, 'user_id' => 200],
        ]);
        $this->database->table('shop_locations')->insert([
            ['id' => self::BRANCH, 'shop_id' => self::SHOP, 'type' => 2],
            ['id' => self::OTHER_BRANCH, 'shop_id' => self::SHOP, 'type' => 2],
            ['id' => self::FOREIGN_BRANCH, 'shop_id' => 2, 'type' => 2],
        ]);

        $this->database->table('roles')->insert([
            ['id' => 1, 'name' => 'user', 'guard_name' => 'sanctum'],
            ['id' => 2, 'name' => 'admin', 'guard_name' => 'sanctum'],
            ['id' => 3, 'name' => 'seller', 'guard_name' => 'sanctum'],
        ]);
        foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, self::OWNER, 200] as $id) {
            $this->attachRole($id, 1);
        }
        $this->attachRole(4, 2);
        $this->attachRole(self::OWNER, 3);

        $this->database->table('shop_roles')->insert([
            ['id' => 501, 'shop_id' => self::SHOP, 'name' => 'Branch staff'],
        ]);
        $this->database->table('invitations')->insert([
            ['id' => 601, 'shop_id' => self::SHOP, 'user_id' => 2, 'status' => Invitation::ACCEPTED, 'shop_role_id' => 501],
            ['id' => 602, 'shop_id' => self::SHOP, 'user_id' => 3, 'status' => Invitation::ACCEPTED, 'shop_role_id' => null],
            ['id' => 603, 'shop_id' => self::SHOP, 'user_id' => 4, 'status' => Invitation::ACCEPTED, 'shop_role_id' => 501],
            ['id' => 604, 'shop_id' => self::SHOP, 'user_id' => 6, 'status' => Invitation::ACCEPTED, 'shop_role_id' => 501],
            ['id' => 605, 'shop_id' => self::SHOP, 'user_id' => 101, 'status' => Invitation::ACCEPTED, 'shop_role_id' => 501],
            ['id' => 606, 'shop_id' => self::SHOP, 'user_id' => 3, 'status' => Invitation::ACCEPTED, 'shop_role_id' => null],
            ['id' => 607, 'shop_id' => self::SHOP, 'user_id' => 4, 'status' => Invitation::ACCEPTED, 'shop_role_id' => null],
        ]);
        $this->database->table('invitations')->whereIn('user_id', [3, 4, 6])->update(['role' => 'master']);
        $this->database->table('invitation_shop_locations')->insert([
            ['invitation_id' => 601, 'shop_location_id' => self::BRANCH],
            ['invitation_id' => 602, 'shop_location_id' => self::BRANCH],
            ['invitation_id' => 603, 'shop_location_id' => self::BRANCH],
            ['invitation_id' => 604, 'shop_location_id' => self::OTHER_BRANCH],
            ['invitation_id' => 605, 'shop_location_id' => self::BRANCH],
            ['invitation_id' => 606, 'shop_location_id' => self::BRANCH],
            ['invitation_id' => 607, 'shop_location_id' => self::OTHER_BRANCH],
        ]);
        $this->database->table('services')->insert([
            ['id' => 101, 'shop_id' => self::SHOP],
            ['id' => 102, 'shop_id' => self::SHOP],
            ['id' => 201, 'shop_id' => 2],
        ]);
        $this->database->table('service_masters')->insert([
            ['id' => 31, 'service_id' => 101, 'master_id' => 3, 'shop_id' => self::SHOP, 'active' => true],
            ['id' => 32, 'service_id' => 201, 'master_id' => 4, 'shop_id' => 2, 'active' => true],
            ['id' => 33, 'service_id' => 201, 'master_id' => 3, 'shop_id' => self::SHOP, 'active' => true],
            ['id' => 34, 'service_id' => 102, 'master_id' => 6, 'shop_id' => self::SHOP, 'active' => true],
        ]);
        $this->database->table('bookings')->insert([
            ['id' => 1, 'user_id' => 1, 'shop_id' => self::SHOP, 'shop_location_id' => self::BRANCH],
            ['id' => 2, 'user_id' => 2, 'shop_id' => self::SHOP, 'shop_location_id' => self::BRANCH],
            ['id' => 3, 'user_id' => 3, 'shop_id' => self::SHOP, 'shop_location_id' => self::BRANCH],
            ['id' => 4, 'user_id' => 4, 'shop_id' => self::SHOP, 'shop_location_id' => self::BRANCH],
            ['id' => 5, 'user_id' => 5, 'shop_id' => 2, 'shop_location_id' => self::FOREIGN_BRANCH],
            ['id' => 6, 'user_id' => 6, 'shop_id' => self::SHOP, 'shop_location_id' => self::OTHER_BRANCH],
            ['id' => 7, 'user_id' => 7, 'shop_id' => self::SHOP, 'shop_location_id' => self::BRANCH],
            ['id' => 8, 'user_id' => 8, 'shop_id' => 2, 'shop_location_id' => self::FOREIGN_BRANCH],
            ['id' => 9, 'user_id' => self::OWNER, 'shop_id' => self::SHOP, 'shop_location_id' => self::BRANCH],
            ['id' => 10, 'user_id' => 9, 'shop_id' => self::SHOP, 'shop_location_id' => self::OTHER_BRANCH],
        ]);
        $this->database->table('seller_booking_clients')->insert([
            ['id' => 1, 'shop_id' => self::SHOP, 'shop_location_id' => self::BRANCH, 'name' => 'Branch Walk-in', 'dedupe_scope' => 'location:11'],
            ['id' => 2, 'shop_id' => self::SHOP, 'shop_location_id' => self::OTHER_BRANCH, 'name' => 'Other Branch Walk-in', 'dedupe_scope' => 'location:12'],
            ['id' => 3, 'shop_id' => 2, 'shop_location_id' => self::FOREIGN_BRANCH, 'name' => 'Foreign Walk-in', 'dedupe_scope' => 'location:21'],
            ['id' => 4, 'shop_id' => self::SHOP, 'shop_location_id' => null, 'name' => 'Shop-wide Walk-in', 'dedupe_scope' => 'shop'],
        ]);
    }

    private function attachRole(int $userId, int $roleId): void
    {
        $this->database->table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => User::class,
            'model_id' => $userId,
        ]);
    }
}