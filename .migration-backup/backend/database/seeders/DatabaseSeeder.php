<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityTranslation;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Region;
use App\Models\RegionTranslation;
use Database\Seeders\Support\CountryDefaultsBackfiller;
use Database\Seeders\Support\CountryRoleDefaultsBackfiller;
use Database\Seeders\Support\SellerCurrencyBackfiller;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(LanguageSeeder::class);
        $this->call(CurrencySeeder::class);
        // Was never wired in here at all - the settings table had zero rows
        // on every fresh install (is_demo/google_map_key included), despite
        // this seeder existing and being kept up to date.
        $this->call(SettingsSeeder::class);
        // Terms & Conditions / Privacy Policy / About pages had no seeder
        // at all until now - every fresh install had zero rows for any of
        // them (see ContentPagesSeeder's own docblock).
        $this->call(ContentPagesSeeder::class);
        $this->call(NotificationSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(ShopPermissionSeeder::class);
        $this->call(CountryPermissionSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ShopTagSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(SubscriptionSeeder::class);
        $this->call(TranslationSeeder::class);
        $this->call(EmailSettingSeeder::class);
        $this->call(SmsGatewaySeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(OrderSeeder::class);
//        $this->call(RegionSeeder::class);

        // Cameroon/Burkina Faso demo geography + the two demo sellers'
        // ShopLocation rows — needs UserSeeder's shops to already exist,
        // and must run before CountryDefaultsBackfiller below so its two
        // new countries get backfilled country_payments too.
        $this->call(DemoAfricaSeeder::class);

        // Demo shop_roles/country_roles for the seeded Cameroon shop and
        // country — needs the shop/country from DemoAfricaSeeder above and
        // the permission catalogs from ShopPermissionSeeder/CountryPermissionSeeder.
        $this->call(DemoStaffRolesSeeder::class);

        // A real demo staff member (Invitation) exercising a shop_role +
        // shop_location together — needs both of the above.
        $this->call(DemoStaffInvitationSeeder::class);

        // A real demo assignment of the platform-wide Main Accountant
        // country_role — needs the Cameroon country (DemoAfricaSeeder), the
        // role itself (DemoStaffRolesSeeder), and the demo user (UserSeeder).
        $this->call(DemoCountryInvitationSeeder::class);

        // A real, bookable service catalog (categories + services + master
        // working days/assignments) for both demo shops — needs the shops
        // from DemoAfricaSeeder and the 'master' role from RoleSeeder.
        $this->call(DemoServiceCatalogSeeder::class);

        // Nigeria + Ghana (new countries, one single-branch shop each),
        // Bafoussam (new third Cameroon city, one single-branch shop), and
        // two more single-branch Cameroon shops (Douala, Yaoundé) - needs
        // DemoAfricaSeeder's Africa region/Cameroon country and
        // DemoServiceCatalogSeeder's 6 sub_service categories to already
        // exist. Must run before CountryDefaultsBackfiller below so
        // Nigeria/Ghana get backfilled country_payments too.
        $this->call(DemoExpansionSeeder::class);

        // 6 more top-level categories (Tailoring, Dental Care, Healthcare,
        // Handyman, Laundry & Dry Cleaning, Home Cleaning) with their
        // subcategories - catalog-only, no demo shop/service/master wiring
        // (unlike DemoServiceCatalogSeeder's 6). Independent of the seeders
        // above; only needs the default Language.
        $this->call(CategoryCatalogExpansionSeeder::class);

        // Education and Tattoo & Piercing - two new top-level categories,
        // each with a real bookable demo shop (not catalog-only like the
        // one above) - needs DemoAfricaSeeder's Cameroon/Douala/Yaoundé and
        // DemoExpansionSeeder's Bafoussam to already exist.
        $this->call(EducationTattooDemoSeeder::class);

        // Enables products_enabled and seeds a minimal real product catalog
        // (5 products, 1 brand) on shop 501 - needs UserSeeder/
        // DemoAfricaSeeder's shop 501 (with its PRODUCT-type ShopLocation)
        // to already exist.
        $this->call(ProductCatalogDemoSeeder::class);

        // A handful of admin-panel/storefront translation keys the
        // original template's dump never included - independent of every
        // other seeder, only needs the default Language to exist.
        $this->call(MissingTranslationsSeeder::class);

        // 3 example blog posts (spanning the full category range above,
        // not just beauty) and a handful of example stories - needs
        // UserSeeder's owner@githubit.com and DemoAfricaSeeder's/
        // DemoExpansionSeeder's shops to already exist for author/shop
        // attribution (see BlogStorySeeder's own docblock).
        $this->call(BlogStorySeeder::class);

        // Real business hours for every demo shop - shop_working_days had
        // 0 rows for all 9 shops before this, so the storefront's working-
        // hours widget had nothing to render. Needs every shop-creating
        // seeder above (UserSeeder, DemoAfricaSeeder, DemoExpansionSeeder,
        // EducationTattooDemoSeeder) to already have run.
        $this->call(ShopWorkingDaysDemoSeeder::class);

        // Subscribes the Cameroon demo seller (shop 501) to the Growth plan —
        // needs both SubscriptionSeeder's plans and DemoAfricaSeeder's shop to
        // already exist, so it can't live inside SubscriptionSeeder::run()
        // itself (that runs before UserSeeder/DemoAfricaSeeder create the shop).
        SubscriptionSeeder::subscribeCameroonSeller();

        // Re-runs the country currency/payment-gateway backfill from the
        // 2026_09_05_030000 migration now that seed data exists. On
        // `migrate:fresh --seed`, that migration ran before any of the
        // seeders above, so currencies/countries/payments were all still
        // empty at the time and it backfilled nothing — this call is what
        // actually populates country defaults for a fresh install. It's a
        // no-op against anything already backfilled (e.g. a real deploy's
        // `php artisan migrate`, where the migration already found data).
        CountryDefaultsBackfiller::run();

        // Same reasoning, for the default country_roles backfill — a no-op
        // in practice here since DemoAfricaSeeder's Country::create() calls
        // already triggered CountryObserver::created() for both demo
        // countries; kept for symmetry and as a safety net (see
        // CountryRoleDefaultsBackfiller's own docblock).
        CountryRoleDefaultsBackfiller::run();

        // Belt-and-suspenders alongside ShopLocation::booted()'s saved()
        // sync: that event should already have set currency_id for every
        // seller seeded above the moment their ShopLocation row was
        // written, but this catches anything it might have missed (e.g. a
        // shop whose location's country had no currency_id yet at the time
        // it was saved). A no-op against any seller currency_id it's
        // already set.
        SellerCurrencyBackfiller::run();

//        if (app()->environment() == 'local') {
//            Category::factory()->hasTranslations(1)->count(10)->create();
//            Brand::factory()->count(10)->create();
//            ExtraGroup::factory()->hasTranslation(1)->hasExtraValues(3)->count(5)->create();
//            User::factory()->has(
//                Shop::factory()->hasTranslation(1)->has(
//                    Product::factory()->hasTranslation(1)->hasExtras(2)->hasProperties(10)->count(rand(10,30))
//                )->count(1)
//            )->count(100)->create();
//            Order::factory()->has(OrderDetail::factory()->hasProducts(2)->count(3))->count(10)->create();
//        }
    }
}
