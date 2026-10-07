<?php

declare(strict_types=1);

namespace Tests\Development;

use Database\Seeders\CarServiceTaxonomySeeder;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;

/**
 * Exercises only the Car Service tree against disposable in-memory SQLite.
 */
class CarServiceTaxonomySeederTest extends TestCase
{
    private ?\Illuminate\Foundation\Application $app = null;
    private array $originalEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();

        $backendPath = dirname(__DIR__, 2);
        $this->setEnvironment([
            'APP_ENV' => 'local',
            'APP_KEY' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DATABASE_URL' => '',
            'DB_URL' => '',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
        ]);

        $this->app = require $backendPath . '/bootstrap/app.php';
        $this->app->make(ConsoleKernel::class)->bootstrap();
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        $this->app['config']->set('database.connections.sqlite.url', null);
        DB::purge('sqlite');

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();
            $table->string('keywords')->nullable();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->unsignedInteger('type')->default(1);
            $table->unsignedInteger('input')->nullable();
            $table->string('img')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('age_limit')->default(0);
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });
        Schema::create('category_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('locale');
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->unique(['category_id', 'locale']);
        });

        $this->insertExistingCategory('Handyman', 11, 0);
        $handymanId = (int) DB::table('categories')->value('id');
        $this->insertExistingCategory('Plumbing', 12, $handymanId);
    }

    protected function tearDown(): void
    {
        if ($this->app !== null) {
            DB::disconnect('sqlite');
            DB::purge('sqlite');
            $this->app->flush();
        }

        foreach ($this->originalEnvironment as $name => $values) {
            foreach (['server' => '_SERVER', 'env' => '_ENV'] as $key => $superglobal) {
                if ($values[$key] === null) {
                    unset($GLOBALS[$superglobal][$name]);
                } else {
                    $GLOBALS[$superglobal][$name] = $values[$key];
                }
            }

            if ($values['process'] === false) {
                putenv($name);
            } else {
                putenv($name . '=' . $values['process']);
            }
        }

        parent::tearDown();
    }

    public function test_category_hierarchy_is_idempotent_and_preserves_existing_taxonomy(): void
    {
        $existingCategoryIds = DB::table('categories')->orderBy('id')->pluck('id')->all();
        $existingCategories = $this->rows('categories');
        $existingTranslations = $this->rows('category_translations');
        $seeder = new CarServiceTaxonomySeeder();

        $first = $seeder->seedTaxonomy('en');
        $categoriesAfterFirst = $this->rows('categories');
        $translationsAfterFirst = $this->rows('category_translations');
        $firstIds = [
            'root_id' => $first['root_id'],
            'children' => $first['children'],
        ];

        self::assertSame(8, $first['categories_created']);
        self::assertSame(8, $first['translations_created']);
        self::assertSame(8, $first['media_references_set']);
        self::assertSame([
            'Auto Repair & Maintenance',
            'Oil Change',
            'Tire Service',
            'Car Wash & Detailing',
            'Auto Electrical',
            'Diagnostics',
            'Brake Service',
        ], array_column($first['children'], 'title'));
        self::assertSame(
            $existingCategories,
            DB::table('categories')->whereIn('id', $existingCategoryIds)->orderBy('id')
                ->get()->map(static fn (object $row): array => (array) $row)->all()
        );
        self::assertSame(
            $existingTranslations,
            DB::table('category_translations')->whereIn('category_id', $existingCategoryIds)
                ->orderBy('id')->get()->map(static fn (object $row): array => (array) $row)->all()
        );

        $root = DB::table('categories')->where('id', $first['root_id'])->first();
        self::assertSame(11, (int) $root->type);
        self::assertSame(0, (int) $root->parent_id);
        self::assertSame('/icons/categories/car-service.svg', $root->img);
        self::assertSame(
            1,
            DB::table('categories')
                ->join('category_translations', 'category_translations.category_id', '=', 'categories.id')
                ->where('categories.type', 11)
                ->where('categories.parent_id', 0)
                ->where('category_translations.title', 'Car Service')
                ->count()
        );
        self::assertSame(2, DB::table('categories')->where('type', 11)->where('parent_id', 0)->count());
        self::assertSame(7, DB::table('categories')->where('type', 12)->where('parent_id', $root->id)->count());

        $second = $seeder->seedTaxonomy('en');
        self::assertSame(0, $second['categories_created']);
        self::assertSame(0, $second['translations_created']);
        self::assertSame(0, $second['media_references_set']);
        self::assertSame($firstIds, [
            'root_id' => $second['root_id'],
            'children' => $second['children'],
        ]);
        self::assertSame($categoriesAfterFirst, $this->rows('categories'));
        self::assertSame($translationsAfterFirst, $this->rows('category_translations'));
    }

    private function rows(string $table): array
    {
        return DB::table($table)->orderBy('id')
            ->get()->map(static fn (object $row): array => (array) $row)->all();
    }

    private function insertExistingCategory(string $title, int $type, int $parentId): void
    {
        $id = DB::table('categories')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'type' => $type,
            'parent_id' => $parentId,
            'active' => true,
            'status' => 'published',
            'shop_id' => null,
            'img' => null,
        ]);
        DB::table('category_translations')->insert([
            'category_id' => $id,
            'locale' => 'en',
            'title' => $title,
        ]);
    }

    private function setEnvironment(array $environment): void
    {
        foreach ($environment as $name => $value) {
            $this->originalEnvironment[$name] = [
                'server' => $_SERVER[$name] ?? null,
                'env' => $_ENV[$name] ?? null,
                'process' => getenv($name),
            ];

            $_SERVER[$name] = $value;
            $_ENV[$name] = $value;
            putenv($name . '=' . $value);
        }
    }
}