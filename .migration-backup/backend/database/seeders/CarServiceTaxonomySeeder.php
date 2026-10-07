<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Seeds only the approved Car Service category tree into the owned local
 * development database. It intentionally creates no marketplace supply.
 */
class CarServiceTaxonomySeeder extends Seeder
{
    private const ROOT_TITLE = 'Car Service';

    private const CHILD_TITLES = [
        'Auto Repair & Maintenance',
        'Oil Change',
        'Tire Service',
        'Car Wash & Detailing',
        'Auto Electrical',
        'Diagnostics',
        'Brake Service',
    ];

    private const ICON = '/icons/categories/car-service.svg';

    public function run(): void
    {
        $lock = null;

        try {
            DevelopmentDatabaseGuard::requireOptIn(
                (string) config('app.env', 'production'),
                env('AGENDAALLY_DEVELOPMENT_DATABASE'),
                env('DEVELOPMENT_MODE')
            );

            $basePath = base_path();
            $manifest = DevelopmentDatabaseGuard::reviewedManifest($basePath);
            $path = DevelopmentDatabaseGuard::resolveSqlitePath(
                $basePath,
                (string) config('database.default', ''),
                (array) config('database.connections.sqlite', [])
            );

            if (
                !is_file($path)
                || is_link($path)
                || !DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest)
            ) {
                throw new RuntimeException(
                    'Car Service taxonomy seeding requires the positively verified owned local development SQLite database.'
                );
            }

            if (!is_file(dirname($basePath) . '/web/public' . self::ICON)) {
                throw new RuntimeException('The local Car Service category icon is missing.');
            }

            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $path,
                'database.connections.sqlite.url' => null,
            ]);
            DB::purge('sqlite');

            $lock = @fopen($path, 'c+b');
            if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Unable to obtain an exclusive lock on the owned development database.');
            }

            if (
                DB::connection('sqlite')->getDatabaseName() !== $path
                || !DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest)
            ) {
                throw new RuntimeException('Development database ownership changed before taxonomy seeding.');
            }

            $relativePath = str_replace(
                '\\',
                '/',
                substr($path, strlen(rtrim($basePath, DIRECTORY_SEPARATOR)) + 1)
            );
            $marker = DB::connection('sqlite')->table('agendaally_development_environment')
                ->where('database_path', $relativePath)
                ->sole();

            if (
                $marker->environment !== 'local'
                || (int) $marker->schema_version !== (int) $manifest['schema_version']
                || !hash_equals(
                    (string) $manifest['migration_set_sha256'],
                    (string) $marker->migration_set_sha256
                )
            ) {
                throw new RuntimeException('The owned development database marker does not match the reviewed schema.');
            }

            if (!Schema::hasTable('categories') || !Schema::hasTable('category_translations')) {
                throw new RuntimeException('The native category hierarchy tables are unavailable.');
            }

            $locale = Language::query()->where('default', true)->value('locale');
            if (!is_string($locale) || trim($locale) === '') {
                throw new RuntimeException('A default locale is required to seed the Car Service taxonomy.');
            }

            $summary = DB::connection('sqlite')->transaction(
                fn (): array => $this->seedTaxonomy($locale)
            );

            $childIds = implode(', ', array_map(
                static fn (array $child): string => $child['title'] . '=' . $child['id'],
                $summary['children']
            ));
            $this->command?->info(sprintf(
                'Car Service taxonomy complete: %d categories added, %d translations added, %d media references set; root ID %d; children [%s].',
                $summary['categories_created'],
                $summary['translations_created'],
                $summary['media_references_set'],
                $summary['root_id'],
                $childIds
            ));
        } finally {
            if (is_resource($lock)) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    /**
     * Insert or safely reuse only the requested native taxonomy rows.
     *
     * Public for isolated, in-memory idempotency tests; production/development
     * execution must go through run(), which validates opt-in, marker,
     * fingerprint, path, and database lock first.
     *
     * @return array{
     *   root_id:int,
     *   children:list<array{title:string,id:int}>,
     *   categories_created:int,
     *   translations_created:int,
     *   media_references_set:int
     * }
     */
    public function seedTaxonomy(string $locale): array
    {
        $categoriesCreated = 0;
        $translationsCreated = 0;
        $mediaReferencesSet = 0;

        [$root, $created, $translated, $mediaSet] = $this->ensureCategory(
            self::ROOT_TITLE,
            Category::SERVICE,
            0,
            $locale
        );
        $categoriesCreated += $created ? 1 : 0;
        $translationsCreated += $translated ? 1 : 0;
        $mediaReferencesSet += $mediaSet ? 1 : 0;

        $children = [];
        foreach (self::CHILD_TITLES as $title) {
            [$child, $created, $translated, $mediaSet] = $this->ensureCategory(
                $title,
                Category::SUB_SERVICE,
                (int) $root->id,
                $locale
            );
            $categoriesCreated += $created ? 1 : 0;
            $translationsCreated += $translated ? 1 : 0;
            $mediaReferencesSet += $mediaSet ? 1 : 0;
            $children[] = ['title' => $title, 'id' => (int) $child->id];
        }

        return [
            'root_id' => (int) $root->id,
            'children' => $children,
            'categories_created' => $categoriesCreated,
            'translations_created' => $translationsCreated,
            'media_references_set' => $mediaReferencesSet,
        ];
    }

    /**
     * @return array{Category,bool,bool,bool}
     */
    private function ensureCategory(string $title, int $type, int $parentId, string $locale): array
    {
        $normalizedTitle = strtolower(trim($title));
        $matches = Category::withoutGlobalScopes()
            ->whereHas('translations', static fn ($query) => $query
                ->whereRaw('LOWER(TRIM(title)) = ?', [$normalizedTitle]))
            ->orderBy('id')
            ->get();

        if ($matches->count() > 1) {
            throw new RuntimeException("Multiple existing native categories conflict with '{$title}'.");
        }

        $created = false;
        $category = $matches->first();

        if ($category) {
            if (
                (int) $category->type !== $type
                || (int) $category->parent_id !== $parentId
                || $category->shop_id !== null
            ) {
                throw new RuntimeException(
                    "An existing category named '{$title}' has a different hierarchy or ownership; it was left unchanged."
                );
            }

            if (!(bool) $category->active || $category->status !== Category::PUBLISHED) {
                throw new RuntimeException(
                    "The existing '{$title}' category is not published; it was left unchanged."
                );
            }
        } else {
            $category = Category::withoutEvents(fn (): Category => Category::create([
                'uuid' => (string) Str::uuid(),
                'type' => $type,
                'parent_id' => $parentId,
                'active' => true,
                'status' => Category::PUBLISHED,
                'shop_id' => null,
                'img' => self::ICON,
            ]));
            $created = true;
        }

        $translation = $category->translations()->where('locale', $locale)->first();
        $translated = false;

        if ($translation) {
            if (strtolower(trim((string) $translation->title)) !== $normalizedTitle) {
                throw new RuntimeException(
                    "The existing '{$title}' category has a different '{$locale}' translation; it was left unchanged."
                );
            }
        } else {
            $category->translations()->create([
                'title' => $title,
                'locale' => $locale,
            ]);
            $translated = true;
        }

        $mediaSet = $created;
        if ($category->img !== self::ICON) {
            Category::withoutEvents(function () use ($category): void {
                $category->forceFill(['img' => self::ICON])->save();
            });
            $mediaSet = true;
        }

        return [$category, $created, $translated, $mediaSet];
    }
}