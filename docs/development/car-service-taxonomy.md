# Car Service taxonomy (Part B)

## Existing model and accepted development data

The original native catalog uses `categories.type = 11` (`SERVICE`) for
service roots and `type = 12` (`SUB_SERVICE`) for their direct children. A
root has `parent_id = 0`; a child has its actual root's ID. Category display
labels are stored in `category_translations`, and local category art is
referenced by the native `categories.img` path.

Before adding anything, the accepted development SQLite dataset was inspected
read-only. It contained 76 category rows (14 service roots, 40 service
subcategories, and 22 legacy product-category rows) and 76 translation rows.
No root or child translation matched Car Service, car/auto/vehicle automotive
labels, or any of the seven requested child labels. The existing category IDs
and all existing categories were left in place.

## Added native hierarchy

```text
Car Service (type 11, parent_id 0)
├── Auto Repair & Maintenance (type 12)
├── Oil Change (type 12)
├── Tire Service (type 12)
├── Car Wash & Detailing (type 12)
├── Auto Electrical (type 12)
├── Diagnostics (type 12)
└── Brake Service (type 12)
```

Each child points to the Car Service root ID; none is a root. All eight rows
are active/published and use the English default-locale translation. The root
and its children reference the local `/icons/categories/car-service.svg`
semantic automotive mark. The Stage 2 `CategoryPictogram` has a matching
locally authored car glyph and resolves the category by title or icon filename.
No photograph, vendor, service, booking, order, review, or financial record was
created.

## Narrow guarded development seed

Added `php artisan development:car-service-taxonomy`. It invokes only
`CarServiceTaxonomySeeder`, which requires the existing local development
opt-ins, the reviewed migration-set fingerprint, the owned development marker
and exact SQLite path, an exclusive database lock, and the local icon file.
It does not run `DevelopmentDemoSeeder`, `DatabaseSeeder`, a migration, or a
reset. The seeder transaction writes only this taxonomy's category/translation
rows and category image references. It avoids Category observer side effects,
and rejects conflicting names or hierarchy rather than silently reparenting or
changing existing taxonomy.

Executed the narrow command twice against the owned development database:

| Pass | New categories | New translations | Media refs set | Root / children |
| --- | ---: | ---: | ---: | --- |
| First | 8 | 8 | 8 | Car Service `77`; Auto Repair & Maintenance `78`; Oil Change `79`; Tire Service `80`; Car Wash & Detailing `81`; Auto Electrical `82`; Diagnostics `83`; Brake Service `84` |
| Second | 0 | 0 | 0 | Same root `77` and children `78`–`84` |

After both passes the database contained 84 categories and 84 translations.
The second pass reused all IDs and created no duplicate root, child,
translation, or media reference.

## Native discovery and filtering

No parallel category system or replacement endpoint was added. The existing
native endpoints and Stage 2 homepage remain in use:

- `GET v1/rest/categories/paginate` returns the active native category tree
  through `CategoryController::paginate` / `RestCategoryRepository`.
- `GET v1/rest/categories/children/{id}` returns the existing parent-wrapped
  children resource; the homepage uses this when nested children are absent.
- The Stage 2 service-category grid uses `getCategoryHierarchy`, which promotes
  only explicit roots (`parent_id` zero/null) to root cards. The seven children
  remain under Car Service and link through the existing `/search?category_id=`
  filter.
- Existing native service search and category filter contracts were not
  changed.

## Files and focused checks

Implementation:

- `.migration-backup/backend/database/seeders/CarServiceTaxonomySeeder.php`
- `.migration-backup/backend/app/Console/Commands/SeedDevelopmentCarServiceTaxonomy.php`
- `.migration-backup/web/public/icons/categories/car-service.svg`
- `.migration-backup/web/components/stage2/category-pictogram.tsx`

Tests:

- `.migration-backup/backend/tests/Development/CarServiceTaxonomySeederTest.php`
  — disposable in-memory SQLite; preserves an existing Handyman/Plumbing
  hierarchy; verifies exactly one Car Service root, seven correctly-parented
  children, existing rows unchanged, and an identical zero-addition second
  pass.
- `.migration-backup/web/scripts/category-hierarchy.regression.test.cjs`
  — verifies the Car Service children do not leak into the root grid and its
  pictogram uses the shared semantic icon grammar.

Commands and results:

```sh
cd .migration-backup/backend
vendor/bin/phpunit tests/Development/CarServiceTaxonomySeederTest.php
# PASS: 1 test, 18 assertions (PHPUnit reports one deprecation).

cd ../web
node scripts/category-hierarchy.regression.test.cjs
# PASS (exit 0).
```

PHP lint passed for the new seeder, command, and test. The authorized database
seed command was executed twice as reported above. No migration or migration
manifest/fingerprint was changed.

Focused native browser verification passed at 1280px desktop and 390px mobile.
Car Service appears once as a root card; selecting it shows exactly seven
children in the separate “Explore within” panel. Oil Change links to
`/search?category_id=79` and truthfully shows no results (no listings were
manufactured). Document widths: hierarchy 1265/1280 and 375/390; filtered search
1274/1280 and 375/390—no horizontal overflow. Evidence in this session:
desktop `4wkne9`, mobile `nxiz9t`. Only a fresh anonymous QA browser's initially
empty local cart/location preference was used; no existing customer cart or
marketplace record was changed. No broad acceptance pass was run.