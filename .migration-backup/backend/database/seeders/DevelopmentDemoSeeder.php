<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Helpers\Utility;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\CartDetailProduct;
use App\Models\Country;
use App\Models\CountryInvitation;
use App\Models\CountryRole;
use App\Models\Coupon;
use App\Models\ExtraGroup;
use App\Models\ExtraGroupTranslation;
use App\Models\ExtraValue;
use App\Models\Like;
use App\Models\MasterDisabledTime;
use App\Models\MasterDisabledTimeTranslation;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PlatformFeeLedgerEntry;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceMaster;
use App\Models\Shop;
use App\Models\Settings;
use App\Models\ShopLocation;
use App\Models\Stock;
use App\Models\StockExtra;
use App\Models\Transaction;
use App\Models\Translation;
use App\Models\User;
use App\Models\UserCart;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Services\UserServices\UserWalletService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Illuminate\Support\Str;

/**
 * Synthetic fixtures for an explicitly owned local SQLite database only.
 *
 * Historical seeders are invoked selectively: DatabaseSeeder also truncates
 * translations through a legacy SQL fallback and creates mail accounts, so it
 * is intentionally never called here. All fixture contacts use reserved test
 * values. Completed sales, bookkeeping, and wallet examples are explicitly
 * synthetic offline cash entries; no payment, mail, SMS, transfer, or provider
 * API is used.
 */
class DevelopmentDemoSeeder extends Seeder
{
    private const CAMEROON_SHOP_ID = 501;
    private const CAMEROON_MASTER_ID = 112;

    private const CAMEROON_PHYSICAL_SERVICE_NAMES = [
        'Haircut',
        'Hair Coloring',
        'Manicure',
        'Massage Therapy',
        'Beard Trim',
        'Bridal Makeup',
    ];

    private const LOCAL_ONLY_PASSWORD = 'AgendaAlly-Dev-Only-2026!';

    /**
     * Seed only the legacy catalogs that are idempotent and contain no
     * integration credentials. CategorySeeder and ShopTagSeeder are
     * intentionally excluded because they insert fresh rows on every run.
     *
     * @var list<class-string<Seeder>>
     */
    private const SAFE_LEGACY_SEEDERS = [
        LanguageSeeder::class,
        CurrencySeeder::class,
        RoleSeeder::class,
        ShopPermissionSeeder::class,
        CountryPermissionSeeder::class,
        OrderSeeder::class,
        SubscriptionSeeder::class,
        UnitSeeder::class,
        NotificationSeeder::class,
        UserSeeder::class,
        DemoAfricaSeeder::class,
        DemoServiceCatalogSeeder::class,
        DemoExpansionSeeder::class,
        DemoStaffRolesSeeder::class,
        DemoStaffInvitationSeeder::class,
        DemoCountryInvitationSeeder::class,
        CategoryCatalogExpansionSeeder::class,
        EducationTattooDemoSeeder::class,
        ProductCatalogDemoSeeder::class,
        ShopWorkingDaysDemoSeeder::class,
    ];

    public function run(): void
    {
        $this->assertOwnedDevelopmentDatabase();

        DB::transaction(function (): void {
            $this->seedOwnedDevelopmentFixtures();
        });
    }

    private function seedOwnedDevelopmentFixtures(): void
    {
        $walletsBeforeLegacySeeders = $this->snapshotExistingWallets();
        $stockQuantitiesBeforeLegacySeeders = $this->snapshotExistingStockQuantities();

        foreach (self::SAFE_LEGACY_SEEDERS as $seeder) {
            if ($seeder === CurrencySeeder::class) {
                // The legacy seeder matches USD by a guarded explicit ID.
                // On SQLite that ID can resolve to XAF on a repeat run;
                // use the local title-keyed catalog instead.
                $this->call(DevelopmentCurrencyCatalogSeeder::class);

                continue;
            }

            if ($seeder === UserSeeder::class) {
                // UserSeeder's explicit synthetic IDs are the keys used by
                // the reviewed branch/vendor fixtures, but User guards `id`.
                // Scope unguarding to this opted-in local fixture seeder only.
                EloquentModel::unguarded(fn () => $this->call($seeder));
                $this->ensureSqliteOriginalShopIds();

                continue;
            }

            if (in_array($seeder, [
                DemoServiceCatalogSeeder::class,
                DemoExpansionSeeder::class,
                EducationTattooDemoSeeder::class,
            ], true)) {
                // These reviewed local catalogs intentionally use explicit
                // user IDs, but User guards `id` everywhere else. Keep this
                // unguarding bounded to those specific development fixtures.
                EloquentModel::unguarded(fn () => $this->call($seeder));

                continue;
            }

            $this->call($seeder);
        }

        $this->call(DevelopmentPaymentCatalogSeeder::class);
        $this->restoreExistingWallets($walletsBeforeLegacySeeders);
        $this->restoreExistingStockQuantities($stockQuantitiesBeforeLegacySeeders);
        $this->ensureDemoShopCurrencies();
        $this->call(DevelopmentGeographyPickupSeeder::class);
        $this->seedProductsForDevelopmentBranches();
        $this->seedCanonicalTranslations();
        DevelopmentTranslationSeeder::seedTranslations();
        $this->normalizeDemoContacts();
        $this->seedAccounts();
        $this->seedCountryManagerAccount();
        $this->ensureCameroonPhysicalServicesAreInPerson();
        $this->seedLocalCatalogExamples();
        $this->seedScheduleFinanceAndNotificationExamples();
        $this->call(DevelopmentPreviewContentSeeder::class);
    }

    private function assertOwnedDevelopmentDatabase(): void
    {
        DevelopmentDatabaseGuard::requireOptIn(
            (string) config('app.env'),
            env('AGENDAALLY_DEVELOPMENT_DATABASE'),
            env('DEVELOPMENT_MODE')
        );

        $manifest = DevelopmentDatabaseGuard::reviewedManifest(base_path());

        if (config('database.default') !== 'sqlite' || !Schema::hasTable('agendaally_development_environment')) {
            throw new RuntimeException('Refusing the demo seed: bootstrap the owned development SQLite database first.');
        }

        $path = DevelopmentDatabaseGuard::resolveSqlitePath(
            base_path(),
            (string) config('database.default'),
            (array) config('database.connections.sqlite')
        );
        $relativePath = str_replace('\\', '/', substr($path, strlen(rtrim(base_path(), DIRECTORY_SEPARATOR)) + 1));
        $row = DB::table('agendaally_development_environment')->sole();

        if (
            $row->environment !== 'local'
            || $row->database_path !== $relativePath
            || (int) $row->schema_version !== (int) $manifest['schema_version']
            || (int) $row->demo_seed_version < 0
            || (int) $row->demo_seed_version > (int) $manifest['demo_seed_version']
            || !hash_equals((string) $manifest['migration_set_sha256'], (string) $row->migration_set_sha256)
        ) {
            throw new RuntimeException('Refusing the demo seed: development database ownership does not match this file.');
        }
    }

    private function ensureSqliteOriginalShopIds(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        foreach ([107 => 501, 113 => 502] as $sellerId => $reviewedShopId) {
            $shop = Shop::query()->where('user_id', $sellerId)->first();

            if (!$shop) {
                throw new RuntimeException(
                    "The reviewed original seller shop for user {$sellerId} is missing after UserSeeder."
                );
            }

            if ((int) $shop->id === $reviewedShopId) {
                continue;
            }

            if (Shop::query()->whereKey($reviewedShopId)->exists()) {
                throw new RuntimeException(
                    "Cannot normalize the SQLite shop for seller {$sellerId} to reviewed id {$reviewedShopId}."
                );
            }

            // The historical migration uses id()->from(501), which MySQL
            // honors but SQLite ignores. Normalize both original shops before
            // any Africa/service/expansion seeder can claim their IDs.
            $updated = DB::table('shops')->where('id', $shop->id)->update(['id' => $reviewedShopId]);

            if ($updated !== 1) {
                throw new RuntimeException(
                    "Unable to normalize the SQLite shop for seller {$sellerId} to id {$reviewedShopId}."
                );
            }
        }
    }

    /**
     * @return array<int, array{uuid: string, price: string, currency_id: int}>
     */
    private function snapshotExistingWallets(): array
    {
        return DB::table('wallets')
            ->orderBy('id')
            ->get(['id', 'uuid', 'price', 'currency_id'])
            ->mapWithKeys(static fn (object $wallet): array => [
                (int) $wallet->id => [
                    'uuid' => (string) $wallet->uuid,
                    'price' => (string) $wallet->price,
                    'currency_id' => (int) $wallet->currency_id,
                ],
            ])
            ->all();
    }

    /**
     * Legacy account seeders call UserWalletService::create(), which resets
     * existing wallet UUIDs, balances, and currency assignments. Restore only
     * wallets that predated this guarded local seed; production behavior is
     * unchanged.
     *
     * @param array<int, array{uuid: string, price: string, currency_id: int}> $wallets
     */
    private function restoreExistingWallets(array $wallets): void
    {
        foreach ($wallets as $walletId => $wallet) {
            DB::table('wallets')
                ->where('id', $walletId)
                ->update($wallet);
        }
    }

    /**
     * @return array<int, int>
     */
    private function snapshotExistingStockQuantities(): array
    {
        return DB::table('stocks')
            ->orderBy('id')
            ->pluck('quantity', 'id')
            ->map(static fn ($quantity): int => (int) $quantity)
            ->all();
    }

    /**
     * Legacy product catalogs can reapply their original starting quantities
     * to already-sold stock. Preserve only existing local inventory balances;
     * production catalog seeding and inventory services remain unchanged.
     *
     * @param array<int, int> $stockQuantities
     */
    private function restoreExistingStockQuantities(array $stockQuantities): void
    {
        foreach ($stockQuantities as $stockId => $quantity) {
            DB::table('stocks')
                ->where('id', $stockId)
                ->update(['quantity' => $quantity]);
        }
    }

    private function ensureDemoShopCurrencies(): void
    {
        foreach (Shop::query()->with('seller')->orderBy('id')->get() as $shop) {
            $currency = $shop->displayCurrency();

            if (!$currency) {
                throw new RuntimeException(
                    "Demo shop {$shop->id} has no country-configured currency for its product or service locations."
                );
            }

            $seller = $shop->seller;

            if (!$seller) {
                throw new RuntimeException("Demo shop {$shop->id} has no seller to receive its country currency.");
            }

            if ((int) $seller->currency_id !== (int) $currency->id) {
                EloquentModel::withoutEvents(fn () => $seller->forceFill([
                    'currency_id' => $currency->id,
                ])->save());
            }
        }
    }

    /**
     * The shared service catalog leaves Service.type at its online default.
     * Only the six physical salon services assigned to the Cameroon master
     * are in-person; other services and the Burkina Faso catalog stay online.
     */
    private function ensureCameroonPhysicalServicesAreInPerson(): void
    {
        $serviceIds = Service::withoutGlobalScopes()
            ->where('shop_id', self::CAMEROON_SHOP_ID)
            ->whereHas(
                'translation',
                fn ($query) => $query->whereIn('title', self::CAMEROON_PHYSICAL_SERVICE_NAMES)
            )
            ->whereHas(
                'serviceMasters',
                fn ($query) => $query->withoutGlobalScopes()
                    ->where('shop_id', self::CAMEROON_SHOP_ID)
                    ->where('master_id', self::CAMEROON_MASTER_ID)
            )
            ->pluck('id');

        if ($serviceIds->isEmpty()) {
            throw new RuntimeException(
                'The guarded development catalog did not produce any physical Cameroon salon services.'
            );
        }

        Service::withoutGlobalScopes()
            ->whereIn('id', $serviceIds)
            ->where('type', '!=', Service::OFFLINE_IN)
            ->update(['type' => Service::OFFLINE_IN]);
    }

    private function currencyIdForShopLocation(Shop $shop, int $locationType): int
    {
        $country = $shop->checkoutCountry($locationType);
        $currencyId = (int) ($country?->currency?->id ?? 0);

        if ($currencyId < 1) {
            $locationName = $locationType === ShopLocation::PRODUCT ? 'product' : 'service';

            throw new RuntimeException(
                "Demo shop {$shop->id} has no valid country currency for its {$locationName} location."
            );
        }

        return $currencyId;
    }

    private function seedCanonicalTranslations(): void
    {
        $source = resource_path('lang/translations.php');

        if (!is_file($source)) {
            throw new RuntimeException(
                'The canonical PHP translation catalog is missing; the development seed refuses the legacy SQL truncate fallback.'
            );
        }

        $translations = require $source;

        if (!is_array($translations)) {
            throw new RuntimeException('The canonical PHP translation catalog did not return an array.');
        }

        foreach ($translations as $translation) {
            if (
                !is_array($translation)
                || !isset($translation['locale'], $translation['group'], $translation['key'], $translation['value'])
            ) {
                throw new RuntimeException('A row in the canonical PHP translation catalog is incomplete.');
            }

            Translation::firstOrCreate(
                [
                    'locale' => $translation['locale'],
                    'group' => $translation['group'],
                    'key' => $translation['key'],
                ],
                ['value' => $translation['value']]
            );
        }
    }

    private function normalizeDemoContacts(): void
    {
        User::query()
            ->where('email', 'like', '%@githubit.com')
            ->orderBy('id')
            ->get()
            ->each(function (User $user): void {
                $localPart = preg_replace('/[^a-z0-9._+-]/i', '.', (string) strtok($user->email, '@'));
                $localPart = trim((string) $localPart, '.');
                $email = substr($localPart ?: 'demo-user-' . $user->id, 0, 140) . '@agendaally.test';

                if (User::query()->where('email', $email)->whereKeyNot($user->getKey())->exists()) {
                    $email = 'demo-user-' . $user->id . '@agendaally.test';
                }

                $user->forceFill([
                    'email' => $email,
                    'phone' => sprintf('+1202555%04d', 100 + ((int) $user->id % 100)),
                ])->save();
            });

        User::query()
            ->where('email', 'like', '%@agendaally.test')
            ->orderBy('id')
            ->get()
            ->each(function (User $user): void {
                if (Hash::check(self::LOCAL_ONLY_PASSWORD, (string) $user->password)) {
                    return;
                }

                EloquentModel::withoutEvents(fn () => $user->forceFill([
                    'password' => Hash::make(self::LOCAL_ONLY_PASSWORD),
                ])->save());
            });

        Shop::query()->orderBy('id')->get()->each(function (Shop $shop): void {
            $phone = sprintf('+1202555%04d', 100 + ((int) $shop->id % 100));

            if ((string) $shop->phone !== $phone) {
                EloquentModel::withoutEvents(fn () => $shop->forceFill(['phone' => $phone])->save());
            }
        });

    }

    private function seedAccounts(): void
    {
        $accounts = [
            102 => ['email' => 'customer@agendaally.test', 'role' => 'user'],
            103 => ['email' => 'admin@agendaally.test', 'role' => 'admin'],
            104 => ['email' => 'manager@agendaally.test', 'role' => 'manager'],
            107 => ['email' => 'owner@agendaally.test', 'role' => 'seller'],
            112 => ['email' => 'master@agendaally.test', 'role' => 'master'],
            114 => ['email' => 'staff@agendaally.test', 'role' => 'shop_manager'],
            115 => ['email' => 'finance@agendaally.test', 'role' => 'manager'],
        ];

        foreach ($accounts as $id => $account) {
            $user = User::query()->find($id);

            if (!$user) {
                throw new RuntimeException(
                    "The reviewed demo user {$id} is absent; the original UserSeeder did not produce the expected demo accounts."
                );
            }

            $user->forceFill([
                'email' => $account['email'],
                'phone' => sprintf('+1202555%04d', 100 + ((int) $id % 100)),
                'active' => 1,
                'email_verified_at' => $user->email_verified_at ?? now(),
                'password' => Hash::check(self::LOCAL_ONLY_PASSWORD, (string) $user->password)
                    ? $user->password
                    : Hash::make(self::LOCAL_ONLY_PASSWORD),
            ])->save();

            if ($id === 114) {
                // This identity logs into the staff portal, not the generic
                // customer portal. The accepted invitation supplies its
                // branch-scoped Branch Manager permission set separately.
                $user->syncRoles([$account['role']]);
            } elseif (!$user->hasRole($account['role'])) {
                $user->assignRole($account['role']);
            }
        }
    }

    private function seedCountryManagerAccount(): void
    {
        $country = Country::query()->where('code', 'cm')->firstOrFail();
        $role = CountryRole::query()
            ->where('country_id', $country->id)
            ->where('name', 'Country Manager')
            ->first();

        if (!$role) {
            throw new RuntimeException(
                'The Cameroon Country Manager role is missing; the original country-role seeding did not create it.'
            );
        }

        $manager = User::query()->where('email', 'country-manager@agendaally.test')->first();

        if (!$manager) {
            $manager = EloquentModel::withoutEvents(fn () => User::factory()
                ->developmentAccount(
                    'country-manager@agendaally.test',
                    self::LOCAL_ONLY_PASSWORD,
                    [
                        'firstname' => 'Development',
                        'lastname' => 'Country Manager',
                    ]
                )
                ->create());
        }

        if (!$manager->hasRole('manager')) {
            $manager->assignRole('manager');
        }

        EloquentModel::withoutEvents(fn () => CountryInvitation::query()->updateOrCreate(
            ['user_id' => $manager->id, 'country_id' => $country->id],
            [
                'created_by' => 103,
                'country_role_id' => $role->id,
                'status' => CountryInvitation::ACCEPTED,
            ]
        ));

        // This account is created after the legacy user seeders. Initialize
        // its wallet during the first guarded demo seed so the subsequent
        // login does not add a wallet between the seeder's repeat snapshots.
        if (!$manager->wallet()->exists()) {
            (new UserWalletService)->create($manager);
        }
    }

    private function seedProductsForDevelopmentBranches(): void
    {
        $source = Product::query()->where('shop_id', 501)->with(['translations', 'galleries', 'stocks'])->first();

        if (!$source || !$source->stocks->first()) {
            throw new RuntimeException(
                'The reviewed Cameroon product catalog is missing; branch retail catalog copies cannot be created.'
            );
        }

        $sourceTranslation = $source->translations->firstWhere('locale', 'en');

        if (!$sourceTranslation) {
            throw new RuntimeException('The Cameroon demo product has no English name to copy into its branches.');
        }

        $sourceStock = $source->stocks->first();

        foreach (Shop::query()->whereHas('productLocation')->orderBy('id')->get() as $shop) {
            if ((int) $shop->id === 501) {
                continue;
            }

            $locale = (string) $sourceTranslation->locale;
            $title = (string) $sourceTranslation->title;
            $exists = Product::query()
                ->where('shop_id', $shop->id)
                ->whereHas('translation', fn ($query) => $query->where('locale', $locale)->where('title', $title))
                ->exists();

            if ($exists) {
                continue;
            }

            $product = Product::factory()->developmentCatalog(
                (int) $shop->id,
                (int) $source->category_id,
                (int) $source->brand_id,
                $source->unit_id ? (int) $source->unit_id : null,
                [
                    'uuid' => (string) Str::uuid(),
                    'img' => $source->img,
                    'tax' => $source->tax,
                    'min_qty' => $source->min_qty,
                    'max_qty' => $source->max_qty,
                    'min_price' => $sourceStock->price,
                    'max_price' => $sourceStock->price,
                ]
            )->make();
            EloquentModel::withoutEvents(fn () => $product->save());

            foreach ($source->translations as $translation) {
                $product->translations()->firstOrCreate(
                    ['locale' => $translation->locale],
                    ['title' => $translation->title, 'description' => $translation->description]
                );
            }

            foreach ($source->galleries as $gallery) {
                $product->galleries()->firstOrCreate(
                    ['type' => $gallery->type, 'path' => $gallery->path],
                    ['title' => $gallery->title]
                );
            }

            EloquentModel::withoutEvents(fn () => $product->stocks()->create([
                'price' => $sourceStock->price,
                'quantity' => max((int) $sourceStock->quantity, 10),
                'sku' => sprintf('AAG-DEMO-SHOP-%d', $shop->id),
                'img' => $sourceStock->img,
                'tax' => $sourceStock->tax,
            ]));

            $shop->update([
                'min_price' => $shop->min_price
                    ? min((float) $shop->min_price, (float) $sourceStock->price)
                    : (float) $sourceStock->price,
                'max_price' => max((float) $shop->max_price, (float) $sourceStock->price),
            ]);
        }
    }

    private function seedLocalCatalogExamples(): void
    {
        $customer = User::query()->where('email', 'customer@agendaally.test')->firstOrFail();
        $shop = Shop::query()->where('user_id', 107)->firstOrFail();
        $productCurrencyId = $this->currencyIdForShopLocation($shop, ShopLocation::PRODUCT);
        $shopLocation = ShopLocation::query()
            ->where('shop_id', $shop->id)
            ->where('type', ShopLocation::PRODUCT)
            ->first();
        $stocks = Stock::query()
            ->whereHas('product', fn ($query) => $query->where('shop_id', $shop->id))
            ->orderBy('id')
            ->get();
        $stock = $stocks->first();

        if (!$stock || $stocks->count() < 3) {
            throw new RuntimeException(
                'At least three real product stock variants are required for development cart and sales fixtures.'
            );
        }

        $this->ensureRetailVariant($stock, $shop);

        if (!$shopLocation) {
            throw new RuntimeException('The Cameroon demo shop has no product location for its linked demo cart.');
        }

        $cart = Cart::query()->firstOrCreate(
            [
                'owner_id' => $customer->id,
                'region_id' => $shopLocation->region_id,
                'status' => true,
                'group' => false,
            ],
            [
                'currency_id' => $productCurrencyId,
                'country_id' => $shopLocation->country_id,
                'city_id' => $shopLocation->city_id,
                'area_id' => $shopLocation->area_id,
                'total_price' => $stock->price,
                'rate' => 1,
            ]
        );

        $userCart = UserCart::query()->firstOrCreate(
            ['cart_id' => $cart->id, 'user_id' => $customer->id],
            ['status' => true, 'name' => 'Development demo cart']
        );

        $cartDetail = CartDetail::query()->firstOrCreate(
            ['user_cart_id' => $userCart->id, 'shop_id' => $shop->id]
        );

        CartDetailProduct::query()->firstOrCreate(
            ['cart_detail_id' => $cartDetail->id, 'stock_id' => $stock->id],
            [
                'quantity' => 1,
                'price' => $stock->price,
                'bonus' => false,
                'discount' => 0,
            ]
        );

        $this->seedDeliveredProductSales($customer, $shop, $stocks->take(3)->all());

        if ($stock) {
            $order = Order::query()->firstOrCreate(
                ['track_id' => 'AGENDAALLY-DEVELOPMENT-UNPAID-001'],
                [
                    'type' => (string) Order::SELLER,
                    'user_id' => $customer->id,
                    'shop_id' => $shop->id,
                    'currency_id' => $productCurrencyId,
                    'status' => Order::STATUS_NEW,
                    'total_price' => $stock->price,
                    'commission_fee' => 0,
                    'total_tax' => 0,
                    'rate' => 1,
                    'note' => 'Synthetic development order; awaiting seller acceptance, no payment attempted.',
                    'location' => 'Douala, Cameroon (synthetic)',
                    'address' => 'Development demo address only',
                    'phone' => $customer->phone,
                    'username' => $customer->fullname,
                    'delivery_type' => Order::POINT,
                ]
            );

            OrderDetail::query()->firstOrCreate(
                ['order_id' => $order->id, 'stock_id' => $stock->id],
                [
                    'origin_price' => $stock->price,
                    'total_price' => $stock->price,
                    'quantity' => 1,
                    'bonus' => false,
                    'note' => 'Synthetic, unpaid development fixture.',
                ]
            );

            Like::query()->firstOrCreate([
                'user_id' => $customer->id,
                'likable_type' => \App\Models\Product::class,
                'likable_id' => $stock->product_id,
            ]);

            Review::query()->firstOrCreate(
                [
                    'user_id' => $customer->id,
                    'reviewable_type' => Shop::class,
                    'reviewable_id' => $shop->id,
                ],
                [
                    'assignable_type' => User::class,
                    'assignable_id' => 107,
                    'rating' => 5,
                    'comment' => 'Synthetic development-only review.',
                ]
            );
        }

        $coupon = Coupon::query()->firstOrCreate(
            ['name' => 'LOCAL-DEMO-10', 'shop_id' => $shop->id],
            [
                'type' => 'percent',
                'for' => Coupon::totalPrice,
                'price' => 10,
                'qty' => 100,
                'expired_at' => '2030-12-31 23:59:59',
            ]
        );

        $coupon->translations()->firstOrCreate(
            ['locale' => 'en'],
            ['title' => 'Local demo discount', 'description' => 'Synthetic development-only coupon.']
        );

        $serviceMaster = ServiceMaster::query()
            ->where('shop_id', $shop->id)
            ->where('active', true)
            ->first();
        $serviceCurrencyId = $this->currencyIdForShopLocation($shop, ShopLocation::SERVICE);

        if ($serviceMaster) {
            $start = CarbonImmutable::now()->addDays(14)->setTime(11, 0, 0);
            $demoNote = 'Synthetic unpaid development appointment; no payment attempted.';

            Booking::query()->firstOrCreate(
                ['note' => $demoNote],
                [
                    'user_id' => $customer->id,
                    'service_master_id' => $serviceMaster->id,
                    'master_id' => $serviceMaster->master_id,
                    'currency_id' => $serviceCurrencyId,
                    'start_date' => $start,
                    'end_date' => $start->addMinutes((int) $serviceMaster->interval),
                    'price' => $serviceMaster->price,
                    'rate' => 1,
                    'status' => Booking::STATUS_NEW,
                    'data' => ['demo' => true],
                ]
            );
        }
    }

    private function ensureRetailVariant(Stock $stock, Shop $shop): void
    {
        $product = Product::query()->findOrFail($stock->product_id);
        $group = ExtraGroup::query()
            ->where('shop_id', $shop->id)
            ->whereHas(
                'translation',
                fn ($query) => $query->where('locale', 'en')->where('title', 'Demo Volume')
            )
            ->first();

        if (!$group) {
            $group = EloquentModel::withoutEvents(fn () => ExtraGroup::query()->create([
                'shop_id' => $shop->id,
                'type' => 'text',
                'active' => true,
            ]));
        }

        ExtraGroupTranslation::query()->firstOrCreate(
            ['extra_group_id' => $group->id, 'locale' => 'en'],
            ['title' => 'Demo Volume']
        );
        $value = ExtraValue::query()->firstOrCreate(
            ['extra_group_id' => $group->id, 'value' => '100 ml'],
            ['active' => true]
        );
        $variant = EloquentModel::withoutEvents(fn () => Stock::query()->firstOrCreate(
            ['product_id' => $product->id, 'sku' => 'AGENDAALLY-DEMO-SERUM-100ML'],
            [
                'price' => round((float) $stock->price * 1.5, 2),
                'quantity' => 20,
                'img' => $stock->img,
            ]
        ));

        StockExtra::query()->firstOrCreate(
            ['stock_id' => $variant->id, 'extra_group_id' => $group->id],
            ['extra_value_id' => $value->id]
        );

        $product->update([
            'min_price' => (float) $product->stocks()->min('price'),
            'max_price' => (float) $product->stocks()->max('price'),
        ]);
        $shop->update([
            'min_price' => $shop->min_price
                ? min((float) $shop->min_price, (float) $product->min_price)
                : (float) $product->min_price,
            'max_price' => max((float) $shop->max_price, (float) $product->max_price),
        ]);
    }

    /**
     * Seed actual delivered Order and OrderDetail rows used by the original
     * DashboardRepository::productsStatistic query. Inventory is decremented
     * once when its corresponding line item is first created.
     *
     * @param list<Stock> $stocks
     */
    private function seedDeliveredProductSales(User $customer, Shop $shop, array $stocks): void
    {
        if (count($stocks) < 3) {
            throw new RuntimeException('Three catalog stock rows are required for the top-selling demo fixture.');
        }

        $productCurrencyId = $this->currencyIdForShopLocation($shop, ShopLocation::PRODUCT);
        $salesByStock = [
            $stocks[0]->id => 5,
            $stocks[1]->id => 3,
            $stocks[2]->id => 2,
        ];
        $index = 0;

        foreach ($salesByStock as $stockId => $saleCount) {
            for ($sale = 1; $sale <= $saleCount; $sale++) {
                $index++;
                $trackId = sprintf('AGENDAALLY-DEVELOPMENT-DELIVERED-%03d', $index);
                $createdAt = now()->subDays(10 - $index)->subMinutes($index);
                $lineNote = 'SYNTHETIC DEVELOPMENT ONLY: offline cash order; no payment provider submitted.';

                DB::transaction(function () use (
                    $trackId,
                    $createdAt,
                    $customer,
                    $shop,
                    $stockId,
                    $lineNote,
                    $productCurrencyId
                ): void {
                    EloquentModel::withoutEvents(function () use (
                        $trackId,
                        $createdAt,
                        $customer,
                        $shop,
                        $stockId,
                        $lineNote,
                        $productCurrencyId
                    ): void {
                        $stock = Stock::query()->findOrFail($stockId);
                        $order = Order::query()->firstOrCreate(
                            ['track_id' => $trackId],
                            [
                                'type' => (string) Order::SELLER,
                                'user_id' => $customer->id,
                                'shop_id' => $shop->id,
                                'currency_id' => $productCurrencyId,
                                'status' => Order::STATUS_DELIVERED,
                                'total_price' => $stock->price,
                                'commission_fee' => 0,
                                'total_tax' => 0,
                                'rate' => 1,
                                'note' => $lineNote,
                                'location' => 'Douala, Cameroon (synthetic)',
                                'address' => 'Development demo address only',
                                'phone' => $customer->phone,
                                'username' => $customer->fullname,
                                'delivery_type' => Order::POINT,
                                'created_at' => $createdAt,
                                'updated_at' => $createdAt,
                            ]
                        );
                        $orderDetail = OrderDetail::query()->firstOrCreate(
                            ['order_id' => $order->id, 'stock_id' => $stock->id],
                            [
                                'origin_price' => $stock->price,
                                'total_price' => $stock->price,
                                'quantity' => 1,
                                'bonus' => false,
                                'note' => $lineNote,
                                'created_at' => $createdAt,
                                'updated_at' => $createdAt,
                            ]
                        );

                        if ($orderDetail->wasRecentlyCreated) {
                            if ($stock->quantity < 1) {
                                throw new RuntimeException(
                                    "Insufficient on-hand stock for synthetic delivered order {$trackId}."
                                );
                            }

                            $stock->decrement('quantity', 1);
                        }

                        Transaction::query()->firstOrCreate(
                            [
                                'payable_type' => Order::class,
                                'payable_id' => $order->id,
                                'note' => $lineNote,
                            ],
                            [
                                'price' => $stock->price,
                                'user_id' => $customer->id,
                                'payment_sys_id' => null,
                                'payment_trx_id' => null,
                                'status' => Transaction::STATUS_PAID,
                                'status_description' => 'Synthetic offline cash sale for local development only.',
                                'perform_time' => $createdAt,
                                'created_at' => $createdAt,
                                'updated_at' => $createdAt,
                            ]
                        );
                    });
                });
            }
        }
    }

    private function seedScheduleFinanceAndNotificationExamples(): void
    {
        $customer = User::query()->where('email', 'customer@agendaally.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@agendaally.test')->firstOrFail();
        $shop = Shop::query()->where('user_id', $owner->id)->firstOrFail();
        $serviceCurrencyId = $this->currencyIdForShopLocation($shop, ShopLocation::SERVICE);
        $productCurrencyId = $this->currencyIdForShopLocation($shop, ShopLocation::PRODUCT);

        if ((int) $owner->currency_id !== $productCurrencyId) {
            throw new RuntimeException(
                'The Cameroon seller currency does not match the currency configured for its product country.'
            );
        }

        $serviceMaster = ServiceMaster::query()
            ->where('shop_id', $shop->id)
            ->where('active', true)
            ->firstOrFail();
        $master = User::query()->findOrFail($serviceMaster->master_id);
        $this->ensureMasterAvailabilityBlock(
            $master,
            'Synthetic lunch break',
            'A development-only availability break.',
            7,
            '12:00',
            '13:00'
        );
        $this->ensureMasterAvailabilityBlock(
            $master,
            'Synthetic personal time off',
            'A development-only specialist absence.',
            10,
            '09:00',
            '12:00'
        );

        $completedAt = now()->subDays(5);
        $servicePrice = (float) $serviceMaster->price;
        Settings::query()->firstOrCreate(
            ['key' => 'service_fee_type'],
            ['value' => Utility::SERVICE_FEE_TYPE_FIXED]
        );
        Settings::query()->firstOrCreate(
            ['key' => 'booking_service_fee'],
            ['value' => (string) (2 * 600)]
        );

        $commission = (float) $serviceMaster->commission_fee;

        if ($commission <= 0) {
            $commission = round($servicePrice * 0.10, 2);
            EloquentModel::withoutEvents(fn () => $serviceMaster->update(['commission_fee' => $commission]));
        }

        $serviceFee = Utility::resolveServiceFee('booking_service_fee', $servicePrice);
        $booking = EloquentModel::withoutEvents(fn () => Booking::query()->firstOrCreate(
            ['note' => 'SYNTHETIC DEVELOPMENT ONLY: completed offline-cash appointment.'],
            [
                'user_id' => $customer->id,
                'service_master_id' => $serviceMaster->id,
                'master_id' => $master->id,
                'currency_id' => $serviceCurrencyId,
                'start_date' => $completedAt,
                'end_date' => $completedAt->copy()->addMinutes((int) $serviceMaster->interval),
                'price' => $servicePrice,
                'rate' => 1,
                'commission_fee' => $commission,
                'service_fee' => $serviceFee,
                'status' => Booking::STATUS_ENDED,
                'data' => ['demo' => true, 'payment_method' => 'offline_cash'],
            ]
        ));
        $bookingTransaction = EloquentModel::withoutEvents(fn () => Transaction::query()->firstOrCreate(
            [
                'payable_type' => Booking::class,
                'payable_id' => $booking->id,
                'note' => 'SYNTHETIC DEVELOPMENT ONLY: offline cash appointment settlement.',
            ],
            [
                'price' => $servicePrice + $serviceFee,
                'user_id' => $customer->id,
                'payment_sys_id' => null,
                'payment_trx_id' => null,
                'status' => Transaction::STATUS_PAID,
                'status_description' => 'Synthetic offline cash example; no payment provider submitted.',
                'perform_time' => $completedAt,
            ]
        ));
        EloquentModel::withoutEvents(fn () => PlatformFeeLedgerEntry::query()->firstOrCreate(
            [
                'transaction_id' => $bookingTransaction->id,
                'entry_type' => PlatformFeeLedgerEntry::ENTRY_TYPE_FEE,
            ],
            [
                'payable_type' => Booking::class,
                'payable_id' => $booking->id,
                'shop_id' => $shop->id,
                'currency_id' => $serviceCurrencyId,
                'amount' => $serviceFee,
                'status' => PlatformFeeLedgerEntry::STATUS_PENDING,
                'note' => 'Synthetic pending fee reconciliation; local demo only.',
            ]
        ));

        $wallet = EloquentModel::withoutEvents(fn () => Wallet::query()->firstOrCreate(
            ['user_id' => $owner->id, 'currency_id' => $productCurrencyId],
            ['uuid' => (string) Str::uuid(), 'price' => 0]
        ));
        $walletTransaction = EloquentModel::withoutEvents(fn () => Transaction::query()->firstOrCreate(
            [
                'payable_type' => Wallet::class,
                'payable_id' => $wallet->id,
                'note' => 'SYNTHETIC DEVELOPMENT ONLY: offline cash wallet ledger example.',
            ],
            [
                'price' => 100,
                'user_id' => $owner->id,
                'payment_sys_id' => null,
                'payment_trx_id' => null,
                'status' => Transaction::STATUS_PAID,
                'status_description' => 'Synthetic offline wallet entry; no payment provider submitted.',
                'perform_time' => $completedAt,
            ]
        ));
        $walletHistory = EloquentModel::withoutEvents(fn () => WalletHistory::query()->firstOrCreate(
            ['wallet_uuid' => $wallet->uuid, 'transaction_id' => $walletTransaction->id],
            [
                'uuid' => (string) Str::uuid(),
                'type' => 'topup',
                'price' => 100,
                'note' => 'Synthetic offline demo opening balance; no external payment.',
                'status' => WalletHistory::PAID,
                'created_by' => $owner->id,
            ]
        ));

        if ($walletHistory->wasRecentlyCreated) {
            $wallet->increment('price', 100);
        }

        EloquentModel::withoutEvents(fn () => Payout::query()->firstOrCreate(
            [
                'created_by' => $owner->id,
                'cause' => 'SYNTHETIC DEVELOPMENT ONLY: offline payout request example.',
            ],
            [
                'approved_by' => null,
                'currency_id' => $productCurrencyId,
                'payment_id' => null,
                'price' => 25,
                'status' => Payout::STATUS_PENDING,
                'answer' => 'Pending demo review only; no funds transferred or payment provider called.',
            ]
        ));

        $notification = Notification::query()->updateOrCreate(
            ['type' => Notification::PUSH],
            ['payload' => ['demo' => true, 'message' => 'Synthetic development order notification.']]
        );
        NotificationUser::query()->firstOrCreate(
            ['notification_id' => $notification->id, 'user_id' => $owner->id],
            ['active' => true]
        );
    }

    private function ensureMasterAvailabilityBlock(
        User $master,
        string $title,
        string $description,
        int $daysAhead,
        string $from,
        string $to
    ): MasterDisabledTime {
        $block = MasterDisabledTime::query()
            ->where('master_id', $master->id)
            ->whereHas(
                'translation',
                fn ($query) => $query->where('locale', 'en')->where('title', $title)
            )
            ->first();

        $date = $block && (string) $block->date >= now()->toDateString()
            ? (string) $block->date
            : now()->addDays($daysAhead)->toDateString();
        $attributes = [
            'master_id' => $master->id,
            'repeats' => MasterDisabledTime::DONT_REPEAT,
            'custom_repeat_type' => MasterDisabledTime::DAY,
            'custom_repeat_value' => 1,
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'end_type' => MasterDisabledTime::NEVER,
            'can_booking' => false,
        ];

        if (!$block) {
            $block = EloquentModel::withoutEvents(fn () => MasterDisabledTime::query()->create($attributes));
        } else {
            $changed = false;

            foreach ($attributes as $key => $value) {
                $current = $block->getAttribute($key);

                if ($key === 'custom_repeat_value') {
                    $current = is_array($current) ? array_values($current) : [$current];
                    $value = is_array($value) ? array_values($value) : [$value];

                    if ($current === $value) {
                        continue;
                    }
                } elseif ((string) $current === (string) $value) {
                    continue;
                }

                if ($block->getAttribute($key) !== $value) {
                    $block->setAttribute($key, $value);
                    $changed = true;
                }
            }

            if ($changed) {
                EloquentModel::withoutEvents(fn () => $block->save());
            }
        }

        MasterDisabledTimeTranslation::query()->updateOrCreate(
            ['disabled_time_id' => $block->id, 'locale' => 'en'],
            ['title' => $title, 'description' => $description]
        );

        return $block;
    }
}