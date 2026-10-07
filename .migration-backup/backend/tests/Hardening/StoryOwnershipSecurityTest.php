<?php

declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\Seller\StoryController;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Story\UpdateRequest;
use App\Models\Shop;
use App\Models\Story;
use App\Models\User;
use App\Models\Invitation;
use App\Repositories\StoryRepository\StoryRepository;
use App\Services\StoryService\StoryService;
use App\Http\Controllers\API\v1\Dashboard\Admin\StoryController as AdminStoryController;
use App\Http\Middleware\CheckShopPermission;
use App\Models\Product;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;

final class StoryOwnershipSecurityTest extends IsolatedTestCase
{
    private StoryService $service;
    private string $storageRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->app->register(\Illuminate\Translation\TranslationServiceProvider::class);
        $this->storageRoot = sys_get_temp_dir() . '/hardening-stories-' . uniqid('', true);
        mkdir($this->storageRoot, 0700, true);
        $this->app['config']->set('app.url', 'https://cdn.example.test');
        $this->app['config']->set('filesystems', [
            'default' => 'local',
            'disks' => [
                'local' => ['driver' => 'local', 'root' => $this->storageRoot, 'throw' => true],
                'public' => ['driver' => 'local', 'root' => $this->storageRoot . '/public', 'throw' => true],
                's3' => ['driver' => 'local', 'root' => $this->storageRoot . '-s3', 'throw' => true],
            ],
        ]);
        $this->app->instance('request', Request::create('/', 'GET', ['lang' => 'en', 'currency_id' => 1]));
        $this->authenticateGuest();
        $this->createSchema();
        $this->database->table('shops')->insert([
            ['id' => 11, 'user_id' => 101],
            ['id' => 22, 'user_id' => 202],
        ]);
        $this->database->table('products')->insert([
            ['id' => 31, 'shop_id' => 11, 'deleted_at' => null],
            ['id' => 32, 'shop_id' => 22, 'deleted_at' => null],
        ]);
        $this->database->table('services')->insert([
            ['id' => 41, 'shop_id' => 11],
            ['id' => 42, 'shop_id' => 22],
        ]);
        $this->service = new StoryService();
    }

    protected function tearDown(): void
    {
        if (isset($this->storageRoot) && is_dir($this->storageRoot)) {
            (new Filesystem())->deleteDirectory($this->storageRoot);
        }
        if (isset($this->storageRoot) && is_dir($this->storageRoot . '-s3')) {
            (new Filesystem())->deleteDirectory($this->storageRoot . '-s3');
        }
        parent::tearDown();
    }

    public function test_create_and_update_require_same_shop_related_entity_and_shop_owned_media(): void
    {
        $ownMedia = $this->createUpload(11, '101-12345678-1234-1234-1234-123456789abc.jpg');
        $valid = [
            'shop_id' => 11,
            'model_type' => 'product',
            'model_id' => 31,
            'file_urls' => [$ownMedia],
        ];

        self::assertTrue($this->service->create($valid)['status']);
        self::assertTrue($this->service->create(array_replace($valid, ['model_type' => 'service', 'model_id' => 41]))['status']);
        self::assertTrue($this->service->create(array_replace($valid, ['model_type' => 'shop', 'model_id' => 11]))['status']);
        self::assertFalse($this->service->create(array_replace($valid, ['model_id' => 32]))['status']);
        $foreignMedia = $this->createUpload(22, '202-12345678-1234-1234-1234-123456789abc.jpg');
        self::assertFalse($this->service->create(array_replace($valid, ['file_urls' => [$foreignMedia]]))['status']);
        self::assertFalse($this->service->create(array_replace(
            $valid,
            ['file_urls' => ['https://attacker.example.test/storage/images/stories/shops/11/101-12345678-1234-1234-1234-123456789abc.jpg']]
        ))['status']);
        self::assertFalse($this->service->create(array_replace(
            $valid,
            ['file_urls' => ['https://cdn.example.test/storage/images/stories/shops/11/101-aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa.jpg']]
        ))['status'], 'A forged URL in the correct namespace is not owned unless the uploaded file exists.');
        self::assertFalse($this->service->create(array_replace(
            $valid,
            ['file_urls' => ['https://cdn.example.test/storage/images/stories/shops/11/%2e%2e/22/202.jpg']]
        ))['status']);
        self::assertFalse($this->service->create(array_replace($valid, ['model_type' => 'shop', 'model_id' => 22]))['status']);

        $story = Story::query()->where('shop_id', 11)->firstOrFail();
        self::assertFalse($this->service->update($story, array_replace($valid, ['shop_id' => 22, 'active' => false]))['status']);
        self::assertFalse($this->service->update($story, array_replace($valid, ['model_type' => 'service', 'model_id' => 42, 'active' => false]))['status']);
        self::assertFalse($this->service->update($story, array_replace(
            $valid,
            ['file_urls' => ['https://cdn.example.test/storage/images/stories/shops/22/202-12345678-1234-1234-1234-123456789abc.jpg'], 'active' => false]
        ))['status']);
        self::assertTrue($this->service->update($story, array_replace($valid, ['model_type' => 'service', 'model_id' => 41, 'active' => false]))['status']);
        self::assertFalse((bool) $story->fresh()->active);
        self::assertSame(3, Story::query()->where('shop_id', 11)->count());
    }

    public function test_owner_controller_create_update_active_and_bulk_delete_use_shop_scoped_contract(): void
    {
        $media = $this->createUpload(11, '101-12345678-1234-1234-1234-123456789abc.jpg');
        $this->authenticateShopOwner(11);
        $repository = Mockery::mock(StoryRepository::class);
        $repository->shouldReceive('index')
            ->once()
            ->withArgs(static fn (array $filters): bool => (int) ($filters['shop_id'] ?? 0) === 11)
            ->andReturn(new LengthAwarePaginator([], 0, 15));
        $controller = new StoryController($this->service, $repository);
        $listRequest = FilterParamsRequest::create('/?shop_id=22', 'GET');
        $controller->index($listRequest);

        $store = Mockery::mock(\App\Http\Requests\Story\StoreRequest::class);
        $store->shouldReceive('validated')->andReturn([
            'shop_id' => 22,
            'model_type' => 'product',
            'model_id' => 31,
            'file_urls' => [$media],
        ]);
        self::assertSame(200, $controller->store($store)->getStatusCode());

        $story = Story::query()->where('shop_id', 11)->firstOrFail();
        self::assertSame(Product::class, $story->model_type);
        self::assertSame(31, (int) $story->model_id);
        $update = Mockery::mock(UpdateRequest::class);
        $update->shouldReceive('validated')->andReturn([
            'shop_id' => 22,
            'model_type' => 'service',
            'model_id' => 41,
            'active' => false,
            'file_urls' => [$media],
        ]);
        self::assertSame(200, $controller->update($story, $update)->getStatusCode());
        self::assertSame(11, (int) $story->fresh()->shop_id);
        self::assertFalse((bool) $story->fresh()->active);

        $delete = Mockery::mock(FilterParamsRequest::class);
        $delete->shouldReceive('input')->with('ids', [])->andReturn([$story->id]);
        self::assertSame(200, $controller->destroy($delete)->getStatusCode());
        self::assertNull(Story::query()->find($story->id));
    }

    public function test_upload_returns_an_existing_shop_namespaced_url_that_create_accepts(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'story-upload-');
        file_put_contents($tempFile, 'isolated image upload');
        $upload = new UploadedFile($tempFile, 'story.jpg', 'image/jpeg', null, true);

        $uploaded = $this->service->uploadFiles(['files' => [$upload]], 11);

        self::assertTrue($uploaded['status']);
        $url = $uploaded['data'][0];
        self::assertStringContainsString('/storage/images/stories/shops/11/', $url);
        self::assertTrue($this->service->create([
            'shop_id' => 11,
            'model_type' => 'product',
            'model_id' => 31,
            'file_urls' => [$url],
        ])['status']);
    }

    public function test_cleanup_only_removes_unreferenced_owned_uploads_not_foreign_or_legacy_media(): void
    {
        $ownMedia = $this->createUpload(11, '101-12345678-1234-1234-1234-123456789abc.jpg');
        $foreignMedia = $this->createUpload(22, '202-12345678-1234-1234-1234-123456789abc.jpg');
        $storyWithMixedLegacyUrls = Story::query()->create([
            'shop_id' => 11,
            'model_type' => Shop::class,
            'model_id' => 11,
            'file_urls' => [$ownMedia, $foreignMedia, 'https://legacy.example.test/story.jpg'],
            'active' => true,
        ]);
        $sameShopReference = Story::query()->create([
            'shop_id' => 11,
            'model_type' => Shop::class,
            'model_id' => 11,
            'file_urls' => [$ownMedia],
            'active' => true,
        ]);

        $this->service->delete([$storyWithMixedLegacyUrls->id], 11);
        self::assertTrue(Storage::disk('public')->exists('images/stories/shops/11/101-12345678-1234-1234-1234-123456789abc.jpg'));
        self::assertTrue(Storage::disk('public')->exists('images/stories/shops/22/202-12345678-1234-1234-1234-123456789abc.jpg'));

        $this->service->delete([$sameShopReference->id], 11);
        self::assertFalse(Storage::disk('public')->exists('images/stories/shops/11/101-12345678-1234-1234-1234-123456789abc.jpg'));
        self::assertTrue(Storage::disk('public')->exists('images/stories/shops/22/202-12345678-1234-1234-1234-123456789abc.jpg'));
    }

    public function test_seller_route_bound_show_and_update_hide_foreign_story_ids(): void
    {
        $foreign = Story::query()->create([
            'shop_id' => 22,
            'model_type' => Shop::class,
            'model_id' => 22,
            'file_urls' => [],
            'active' => true,
        ]);
        $this->authenticateShopOwner(11);
        $repository = Mockery::mock(StoryRepository::class);
        $repository->shouldNotReceive('show');
        $controller = new StoryController($this->service, $repository);

        self::assertSame(404, $controller->show($foreign)->getStatusCode());

        $request = Mockery::mock(UpdateRequest::class);
        $request->shouldNotReceive('validated');
        self::assertSame(404, $controller->update($foreign, $request)->getStatusCode());
        $deleteRequest = Mockery::mock(FilterParamsRequest::class);
        $deleteRequest->shouldNotReceive('input');
        self::assertSame(404, $controller->destroy($deleteRequest, $foreign)->getStatusCode());
        $this->authenticateGuest();
        self::assertSame(22, (int) Story::query()->findOrFail($foreign->id)->shop_id);
    }

    public function test_shop_grants_gate_staff_and_admin_delete_stays_separate_and_unscoped(): void
    {
        $shop = Shop::query()->findOrFail(11);
        $staff = new User();
        $staff->id = 303;
        $staff->setRelation('shop', null);
        $staff->setRelation('moderatorShop', $shop);
        $this->database->table('shop_roles')->insert(['id' => 5, 'shop_id' => 11, 'name' => 'Story viewer']);
        $this->database->table('shop_permissions')->insert(['id' => 1, 'key' => 'stories.view', 'group' => 'stories', 'label' => 'View']);
        $this->database->table('shop_role_permissions')->insert(['shop_role_id' => 5, 'shop_permission_id' => 1]);
        $this->database->table('invitations')->insert([
            'id' => 71,
            'shop_id' => 11,
            'user_id' => 303,
            'role' => 'shop_manager',
            'status' => Invitation::ACCEPTED,
            'shop_role_id' => 5,
        ]);
        self::assertFalse($staff->hasShopPermission(22, 'stories.view'));
        $this->authenticateUser($staff);

        $permission = new CheckShopPermission();
        $passed = false;
        $granted = $permission->handle(
            Request::create('/', 'GET', ['lang' => 'en']),
            static function () use (&$passed): Response {
                $passed = true;
                return new Response('allowed', 200);
            },
            'stories.view'
        );
        self::assertTrue($passed);
        self::assertSame(200, $granted->getStatusCode());

        $denied = $permission->handle(
            Request::create('/', 'POST', ['lang' => 'en']),
            static fn () => new Response('must not pass', 200),
            'stories.manage'
        );
        self::assertSame(403, $denied->getStatusCode());

        $adminService = Mockery::mock(StoryService::class);
        $adminService->shouldReceive('delete')->once()->with([901])->andReturn(['status' => true]);
        $adminRequest = Mockery::mock(FilterParamsRequest::class);
        $adminRequest->shouldReceive('input')->with('ids', [])->andReturn([901]);
        $admin = new AdminStoryController($adminService, Mockery::mock(StoryRepository::class));
        self::assertSame(200, $admin->destroy($adminRequest)->getStatusCode());
    }

    public function test_authorized_shop_manager_can_manage_own_story_but_not_foreign_status_or_media(): void
    {
        $staff = new User();
        $staff->id = 303;
        $staff->setRelation('shop', null);
        $staff->setRelation('moderatorShop', Shop::query()->findOrFail(11));
        $staff->setRelation('countryAdmin', null);
        $this->database->table('shop_roles')->insert(['id' => 5, 'shop_id' => 11, 'name' => 'Story manager']);
        $this->database->table('shop_permissions')->insert(['id' => 1, 'key' => 'stories.manage', 'group' => 'stories', 'label' => 'Manage']);
        $this->database->table('shop_role_permissions')->insert(['shop_role_id' => 5, 'shop_permission_id' => 1]);
        $this->database->table('invitations')->insert([
            'id' => 71, 'shop_id' => 11, 'user_id' => 303, 'role' => 'shop_manager',
            'status' => Invitation::ACCEPTED, 'shop_role_id' => 5,
        ]);
        $this->authenticateUser($staff);
        self::assertTrue($staff->hasShopPermission(11, 'stories.manage'));
        self::assertFalse($staff->hasShopPermission(22, 'stories.manage'));
        $media = $this->createUpload(11, '401-12345678-1234-1234-1234-123456789abc.jpg');
        $own = Story::query()->create([
            'shop_id' => 11, 'model_id' => 11, 'model_type' => Shop::class, 'file_urls' => [$media],
        ]);
        $foreign = Story::query()->create([
            'shop_id' => 22, 'model_id' => 22, 'model_type' => Shop::class, 'file_urls' => ['https://foreign.example.test/media.jpg'],
        ]);
        $repository = Mockery::mock(StoryRepository::class);
        $repository->shouldReceive('show')->once()->with($own)->andReturn($own);
        $controller = new StoryController($this->service, $repository);
        self::assertSame(200, $controller->show($own)->getStatusCode());
        $request = Mockery::mock(UpdateRequest::class);
        $request->shouldReceive('validated')->once()->andReturn([
            'model_type' => 'shop', 'model_id' => 11, 'active' => false, 'file_urls' => [$media],
        ]);
        self::assertSame(200, $controller->update($own, $request)->getStatusCode());
        self::assertFalse($own->fresh()->active);
        foreach ([
            ['active' => false],
            ['file_urls' => [$media]],
            ['file_urls' => []],
        ] as $attack) {
            $forged = UpdateRequest::create('/stories/' . $foreign->id, 'PUT', $attack);
            self::assertSame(404, $controller->update($foreign, $forged)->getStatusCode());
        }
        self::assertSame(['https://foreign.example.test/media.jpg'], $foreign->fresh()->file_urls);
    }

    public function test_bulk_delete_only_removes_ids_from_the_authenticated_shop_scope(): void
    {
        $own = Story::query()->create([
            'shop_id' => 11,
            'model_type' => Shop::class,
            'model_id' => 11,
            'file_urls' => [],
            'active' => true,
        ]);
        $foreign = Story::query()->create([
            'shop_id' => 22,
            'model_type' => Shop::class,
            'model_id' => 22,
            'file_urls' => [],
            'active' => true,
        ]);

        $this->service->delete([$own->id, $foreign->id], 11);

        self::assertNull(Story::query()->find($own->id));
        self::assertNotNull(Story::query()->find($foreign->id));
    }

    private function authenticateShopOwner(int $shopId): void
    {
        $shop = Shop::query()->findOrFail($shopId);
        $user = new User();
        $user->setRelation('shop', $shop);
        $user->setRelation('countryAdmin', null);

        $this->authenticateUser($user);
    }

    public function test_public_visibility_requires_approved_active_and_strictly_unexpired(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-10-02 12:00:00');
        try {
            $this->database->table('shops')->where('id', 11)->update(['status' => Shop::APPROVED]);
            $this->database->table('shops')->where('id', 22)->update(['status' => 'new']);
            $base = [
                'shop_id' => 11, 'model_id' => 11, 'model_type' => Shop::class,
                'file_urls' => [], 'active' => true, 'created_at' => now()->subHours(23),
            ];
            $visible = Story::query()->create($base);
            Story::query()->create(array_replace($base, ['active' => false]));
            $boundary = Story::query()->create(array_replace($base, ['created_at' => now()->subHours(24)]));
            Story::query()->create(array_replace($base, ['created_at' => now()->subHours(25)]));
            Story::query()->create(array_replace($base, ['shop_id' => 22, 'model_id' => 22]));
            self::assertSame([$visible->id], Story::query()->publiclyVisible()->pluck('id')->all());
            self::assertTrue(Story::query()->expired()->whereKey($boundary->id)->exists());
            $visible->update(['active' => false]);
            $visible->update(['active' => true]);
            self::assertSame('2026-10-02T13:00:00+00:00', $visible->expires_at->toIso8601String());
            self::assertSame([], Story::query()->publiclyVisible()->where('active', false)->pluck('id')->all());
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }

    public function test_scheduled_expiry_uses_safe_media_cleanup_and_keeps_current_stories(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-10-02 12:00:00');
        try {
            $media = $this->createUpload(11, '301-12345678-1234-1234-1234-123456789abc.jpg');
            $base = ['shop_id' => 11, 'model_id' => 11, 'model_type' => Shop::class, 'file_urls' => [$media], 'active' => true];
            $expired = Story::query()->create($base + ['created_at' => now()->subHours(24)]);
            $current = Story::query()->create(array_replace($base, ['file_urls' => [], 'created_at' => now()->subHours(23)]));
            self::assertSame(0, (new \App\Console\Commands\RemoveExpiredStories())->handle($this->service));
            self::assertNull($expired->fresh());
            self::assertNotNull($current->fresh());
            self::assertFalse(Storage::disk('public')->exists('images/stories/shops/11/301-12345678-1234-1234-1234-123456789abc.jpg'));
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }

    private function authenticateUser(User $user): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        Facade::clearResolvedInstance('auth');
    }

    private function authenticateGuest(): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn(null);
        $guard->shouldReceive('check')->andReturn(false);
        $guard->shouldReceive('id')->andReturn(null);
        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        Facade::clearResolvedInstance('auth');
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
        Schema::create('country_invitations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('status')->nullable();
            $table->unsignedInteger('country_role_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
        });
        Schema::create('shop_roles', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->string('name');
        });
        Schema::create('shop_permissions', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('key');
            $table->string('group');
            $table->string('label');
        });
        Schema::create('shop_role_permissions', function (Blueprint $table): void {
            $table->unsignedInteger('shop_role_id');
            $table->unsignedInteger('shop_permission_id');
        });
        Schema::create('invitations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('user_id');
            $table->string('role')->nullable();
            $table->unsignedTinyInteger('status')->default(Invitation::NEW);
            $table->unsignedInteger('shop_role_id')->nullable();
        });
        Schema::create('shops', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('status')->default(Shop::APPROVED);
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->timestamp('deleted_at')->nullable();
        });
        Schema::create('services', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
        });
        Schema::create('stories', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('model_id');
            $table->string('model_type');
            $table->boolean('active')->default(true);
            $table->text('file_urls')->nullable();
            $table->timestamps();
        });
    }

    private function createUpload(int $shopId, string $filename): string
    {
        $path = "images/stories/shops/$shopId/$filename";
        Storage::disk('public')->put($path, 'isolated hardening test upload');

        return 'https://cdn.example.test/storage/' . $path;
    }
}