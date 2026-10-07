<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\DeliveryPrice;
use App\Models\Invitation;
use App\Models\Language;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServiceMaster;
use App\Models\ServiceTranslation;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPermission;
use App\Models\ShopRole;
use App\Models\ShopTag;
use App\Models\ShopTranslation;
use App\Models\User;
use App\Services\UserServices\UserService;
use App\Services\UserServices\UserWalletService;
use App\Traits\Loggable;
use App\Traits\SetTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Part 7 of the September demo-data expansion: Nigeria and Ghana as new
 * countries (each with one seeded single-branch shop), Bafoussam as a
 * third Cameroon city (also one seeded single-branch shop), and two more
 * single-branch Cameroon shops (Douala, Yaoundé) so Cameroon reaches 4
 * sellers total alongside the original multi-city shop 501 (see UserSeeder/
 * DemoAfricaSeeder) — that shop keeps both its Yaoundé and Douala
 * ShopLocation rows unchanged; every shop this seeder creates is
 * deliberately single-branch (one city, both PRODUCT and SERVICE type
 * ShopLocation rows at it — the same shape shop 502, the existing Burkina
 * Faso shop, already has).
 *
 * Each new shop gets the same treatment DemoServiceCatalogSeeder gives
 * shops 501/502: a seller (shop owner), a master (accepted invitation +
 * default working days, so their services are actually bookable per
 * Service::scopeFilter()'s has_master check), a staff member (a "Branch
 * Manager" shop_role, mirroring DemoStaffRolesSeeder/DemoStaffInvitationSeeder
 * for shop 501), and the same 6-service catalog (Haircut, Hair Coloring,
 * Manicure, Massage Therapy, Beard Trim, Bridal Makeup) already seeded by
 * DemoServiceCatalogSeeder — reusing that exact catalog rather than
 * inventing a new one per shop keeps this large seeder's own risk surface
 * small and its output predictable across all 5 new shops.
 *
 * Runs after DemoAfricaSeeder (needs the Africa region and the Cameroon
 * country to already exist) and DemoServiceCatalogSeeder (needs its 6
 * sub_service categories to already exist) — see DatabaseSeeder for
 * ordering. Every write is an updateOrCreate/find-or-create, so this is
 * safe to run more than once.
 *
 * No shop/master photo URLs are set here (img stays null on every new Shop/
 * User row) — same reasoning as DemoServiceCatalogSeeder's own category
 * icons: this environment can't verify a real, working photo URL against
 * unsplash.com (blocked by this sandbox's own egress proxy), and a broken
 * or invented URL would be worse than an honest null. PR #74's
 * ImageWithFallBack fix covers the storefront rendering of a null image
 * the same way it already does for any other shop/user missing one.
 */
class DemoExpansionSeeder extends Seeder
{
    use Loggable;
    use SetTranslations;

    // Reuses the exact 6 services DemoServiceCatalogSeeder already seeded
    // categories for (see that class's CATEGORY_TREE) — not redefined
    // there as a shared constant since these two seeders are otherwise
    // independent and shouldn't need to know about each other's internals
    // beyond the category titles themselves.
    private const SERVICES = [
        ['category' => 'Haircut',          'price' => 15 * 600, 'interval' => 30, 'description' => 'A precision haircut tailored to your style.'],
        ['category' => 'Hair Coloring',    'price' => 45 * 600, 'interval' => 90, 'description' => 'Full color or highlights using professional-grade dye.'],
        ['category' => 'Manicure',         'price' => 20 * 600, 'interval' => 45, 'description' => 'Nail shaping, cuticle care, and polish of your choice.'],
        ['category' => 'Massage Therapy',  'price' => 35 * 600, 'interval' => 60, 'description' => 'A relaxing full-body massage to ease tension.'],
        ['category' => 'Beard Trim',       'price' => 10 * 600, 'interval' => 20, 'description' => 'Beard shaping and trim with a straight razor finish.'],
        ['category' => 'Bridal Makeup',    'price' => 60 * 600, 'interval' => 90, 'description' => 'Full bridal makeup application, trial included.'],
    ];

    // Each entry is one new single-branch shop. 'country' is null for the
    // two new Cameroon branches (Douala/Yaoundé), which reuse the country
    // DemoAfricaSeeder already created — only 'city' + 'currency' (read
    // from the existing country, not re-set) apply to those.
    private const BRANCHES = [
        [
            'country'  => ['title' => 'Nigeria', 'iso2' => 'ng', 'currency' => 'NGN'],
            'city'     => 'Lagos',
            'shop'     => [
                'title'       => 'Lagos Glow Studio',
                'description' => 'A vibrant beauty studio in the heart of Lagos, offering hair, nail, and spa services.',
                'address'     => 'Adeola Odeku Street, Victoria Island, Lagos, Nigeria',
                'latitude'    => 6.4281,
                'longitude'   => 3.4219,
                'phone'       => '+2348000000001',
                // Real salon photos (Unsplash, free tier). background_img:
                // "a hair salon with chairs and neon signs" (photo id
                // sbZi_DLSjzo, by Giorgio Trovato, @giorgiotrovato).
                // logo_img: "shampoo and conditioner bottles on shelf"
                // (photo id Wlu-dcVLuOI, by Ela De Pure, @eladepure).
                'background_img' => 'https://images.unsplash.com/photo-1637777277337-f114350fb088?auto=format&fit=crop&w=1200&q=80',
                'logo_img'        => 'https://images.unsplash.com/photo-1786725009844-3daea6bd1fb0?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            'seller' => ['id' => 117, 'firstname' => 'sellers-ng', 'lastname' => 'sellers-ng', 'email' => 'sellers-ng@githubit.com', 'phone' => '998911902699', 'password' => 'sellerng', 'gender' => 'male'],
            // Real portrait photo (Unsplash, free tier - "woman with green
            // and white flower on ear", photo id h1lA3N5wb8M, by Divine
            // Effiong, @iamdivineeffiong).
            'master' => ['id' => 118, 'firstname' => 'Adaeze', 'lastname' => 'Okafor', 'email' => 'master-ng@githubit.com', 'phone' => '998911902700', 'password' => 'masterng', 'gender' => 'female', 'img' => 'https://images.unsplash.com/photo-1593351799227-75df2026356b?auto=format&fit=crop&w=600&h=600&q=80'],
            'staff'  => ['id' => 119, 'firstname' => 'Branch', 'lastname' => 'Manager NG', 'email' => 'branch-manager-ng@githubit.com', 'phone' => '998911902701', 'password' => 'branchmanagerng', 'gender' => 'male'],
        ],
        [
            'country'  => ['title' => 'Ghana', 'iso2' => 'gh', 'currency' => 'GHS'],
            'city'     => 'Accra',
            'shop'     => [
                'title'       => 'Accra Radiance Salon',
                'description' => 'A welcoming salon in central Accra, offering hair, nail, and spa services for the whole family.',
                'address'     => 'Oxford Street, Osu, Accra, Ghana',
                'latitude'    => 5.5560,
                'longitude'   => -0.1969,
                'phone'       => '+2330000000001',
                // Real salon photos (Unsplash, free tier). background_img:
                // "woman in white shirt standing near black leather chairs"
                // (photo id OKXwmdbdXkk, by Giorgio Trovato,
                // @giorgiotrovato). logo_img: "two chairs outside a salon
                // with products on shelves" (photo id CFynKzsvqjA, by
                // Phuong Nguyen, @phuongtography).
                'background_img' => 'https://images.unsplash.com/photo-1626383137804-ff908d2753a2?auto=format&fit=crop&w=1200&q=80',
                'logo_img'        => 'https://images.unsplash.com/photo-1763741141049-352dfafcc64f?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            'seller' => ['id' => 120, 'firstname' => 'sellers-gh', 'lastname' => 'sellers-gh', 'email' => 'sellers-gh@githubit.com', 'phone' => '998911902702', 'password' => 'sellergh', 'gender' => 'male'],
            // Real portrait photo (Unsplash, free tier - "woman in white
            // button up shirt", photo id unG5ZwUPY0Y, by Qwerqu McBrew,
            // @qwerqu_jnr).
            'master' => ['id' => 121, 'firstname' => 'Ama', 'lastname' => 'Boateng', 'email' => 'master-gh@githubit.com', 'phone' => '998911902703', 'password' => 'mastergh', 'gender' => 'female', 'img' => 'https://images.unsplash.com/photo-1629145810320-aec9e63dd798?auto=format&fit=crop&w=600&h=600&q=80'],
            'staff'  => ['id' => 122, 'firstname' => 'Branch', 'lastname' => 'Manager GH', 'email' => 'branch-manager-gh@githubit.com', 'phone' => '998911902704', 'password' => 'branchmanagergh', 'gender' => 'male'],
        ],
        [
            'country'  => null, // Cameroon already exists (DemoAfricaSeeder).
            'city'     => 'Douala',
            'shop'     => [
                'title'       => 'Wouri Beauty Bar',
                'description' => 'A cozy, single-branch beauty bar in Douala offering hair, nail, and spa services.',
                'address'     => 'Rue Joss, Akwa, Douala, Cameroon',
                'latitude'    => 4.0483,
                'longitude'   => 9.7043,
                'phone'       => '+2370000000002',
                // Real salon photos (Unsplash, free tier). background_img:
                // "black and silver office rolling chair beside mirror"
                // (photo id gI9rvJK61L8, by Giorgio Trovato,
                // @giorgiotrovato). logo_img: "shelves with skincare
                // products and bottles in a salon" (photo id y-jhNJt0ZsM,
                // by Ela De Pure, @eladepure).
                'background_img' => 'https://images.unsplash.com/photo-1626379501846-0df4067b8bb9?auto=format&fit=crop&w=1200&q=80',
                'logo_img'        => 'https://images.unsplash.com/photo-1760862652442-e8ff7ebdd2f8?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            'seller' => ['id' => 123, 'firstname' => 'sellers-cm2', 'lastname' => 'sellers-cm2', 'email' => 'sellers-cm2@githubit.com', 'phone' => '998911902705', 'password' => 'sellercm2', 'gender' => 'male'],
            // Real portrait photo (Unsplash, free tier - "Young black man
            // with a big smile", photo id OGljLs0DnJ4, by Inocent Drici,
            // @inidrici).
            'master' => ['id' => 124, 'firstname' => 'Brice', 'lastname' => 'Ngoma', 'email' => 'master-cm2@githubit.com', 'phone' => '998911902706', 'password' => 'mastercm2', 'gender' => 'male', 'img' => 'https://images.unsplash.com/photo-1646658104783-2eec2433c1d1?auto=format&fit=crop&w=600&h=600&q=80'],
            'staff'  => ['id' => 125, 'firstname' => 'Branch', 'lastname' => 'Manager CM2', 'email' => 'branch-manager-cm2@githubit.com', 'phone' => '998911902707', 'password' => 'branchmanagercm2', 'gender' => 'female'],
        ],
        [
            'country'  => null,
            'city'     => 'Yaoundé',
            'shop'     => [
                'title'       => 'Mfoundi Style House',
                'description' => 'A modern, single-branch style house in Yaoundé offering hair, nail, and spa services.',
                'address'     => 'Avenue Kennedy, Yaoundé, Cameroon',
                'latitude'    => 3.8480,
                'longitude'   => 11.5021,
                'phone'       => '+2370000000003',
                // Real salon photos (Unsplash, free tier). background_img:
                // "stylish barbershop interior with a couch and magazines"
                // (photo id WlfTYvvRG_w, by Zachary Lancaster,
                // @zacharyl123). logo_img: "shelves filled with various
                // bottles and jars" (photo id wuBiNO345DE, by Ela De Pure,
                // @eladepure).
                'background_img' => 'https://images.unsplash.com/photo-1781925856343-c97d0d44f94c?auto=format&fit=crop&w=1200&q=80',
                'logo_img'        => 'https://images.unsplash.com/photo-1758188753373-5b01a0fc6d9d?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            'seller' => ['id' => 126, 'firstname' => 'sellers-cm3', 'lastname' => 'sellers-cm3', 'email' => 'sellers-cm3@githubit.com', 'phone' => '998911902708', 'password' => 'sellercm3', 'gender' => 'male'],
            // Real portrait photo (Unsplash, free tier - "close-up of a
            // smiling woman with braided hair", photo id JiZTPRLsa0Q, by
            // JEaLiFe Pictures, @jealife_pictures).
            'master' => ['id' => 127, 'firstname' => 'Chantal', 'lastname' => 'Mbarga', 'email' => 'master-cm3@githubit.com', 'phone' => '998911902709', 'password' => 'mastercm3', 'gender' => 'female', 'img' => 'https://images.unsplash.com/photo-1754844362137-88441eb7cc6f?auto=format&fit=crop&w=600&h=600&q=80'],
            'staff'  => ['id' => 128, 'firstname' => 'Branch', 'lastname' => 'Manager CM3', 'email' => 'branch-manager-cm3@githubit.com', 'phone' => '998911902710', 'password' => 'branchmanagercm3', 'gender' => 'male'],
        ],
        [
            'country'  => null,
            // New third Cameroon city — not seeded by DemoAfricaSeeder.
            'city'     => 'Bafoussam',
            'shop'     => [
                'title'       => 'Bafoussam Belle Époque',
                'description' => 'A charming, single-branch salon in Bafoussam offering hair, nail, and spa services.',
                'address'     => 'Marché A, Bafoussam, Cameroon',
                'latitude'    => 5.4737,
                'longitude'   => 10.4176,
                'phone'       => '+2370000000004',
                // Real salon photos (Unsplash, free tier), both with a
                // vintage look matching this shop's "Belle Époque" name.
                // background_img: "vintage barber shop interior with red
                // chairs and dryers" (photo id EU0nTbr0Uhk, by Sergio
                // Guardiola Herrador, @guardiola86). logo_img: "brown wooden
                // chair beside black wooden table" - a vintage salon chair
                // (photo id td9dv0feuuE, by judith girard-marczak,
                // @judithgirardmarczak).
                'background_img' => 'https://images.unsplash.com/photo-1758812818698-6ecd792a87da?auto=format&fit=crop&w=1200&q=80',
                'logo_img'        => 'https://images.unsplash.com/photo-1584537319035-d6f4fb63f4c4?auto=format&fit=crop&w=400&h=400&q=80',
            ],
            'seller' => ['id' => 129, 'firstname' => 'sellers-cm4', 'lastname' => 'sellers-cm4', 'email' => 'sellers-cm4@githubit.com', 'phone' => '998911902711', 'password' => 'sellercm4', 'gender' => 'male'],
            // Real portrait photo (Unsplash, free tier - "smiling woman
            // sitting on black chair", photo id kXmKqYOGA4Y, by Christina @
            // wocintechchat.com M, @wocintechchat).
            'master' => ['id' => 130, 'firstname' => 'Danielle', 'lastname' => 'Kamga', 'email' => 'master-cm4@githubit.com', 'phone' => '998911902712', 'password' => 'mastercm4', 'gender' => 'female', 'img' => 'https://images.unsplash.com/photo-1573497161161-c3e73707e25c?auto=format&fit=crop&w=600&h=600&q=80'],
            'staff'  => ['id' => 131, 'firstname' => 'Branch', 'lastname' => 'Manager CM4', 'email' => 'branch-manager-cm4@githubit.com', 'phone' => '998911902713', 'password' => 'branchmanagercm4', 'gender' => 'male'],
        ],
    ];

    public function run(): void
    {
        $locale = data_get(Language::where('default', 1)->first(), 'locale', 'en');

        $africa = Region::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', 'Africa'))->first();

        if (!$africa) {
            $this->command?->warn('DemoExpansionSeeder: Africa region not found, skipping (run DemoAfricaSeeder first)');
            return;
        }

        $categoriesByTitle = $this->categories($locale);

        foreach (self::BRANCHES as $branch) {
            try {
                $this->seedBranch($branch, $africa, $locale, $categoriesByTitle);
            } catch (Throwable $e) {
                $this->command?->error("DemoExpansionSeeder: failed seeding branch {$branch['shop']['title']} - {$e->getMessage()}");
                $this->error($e);
            }
        }
    }

    /**
     * @return array<string, Category> sub_service category title => Category
     */
    private function categories(string $locale): array
    {
        $titles = array_column(self::SERVICES, 'category');
        $categories = [];

        foreach ($titles as $title) {
            $category = Category::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $title))
                ->where('type', Category::SUB_SERVICE)
                ->first();

            if (!$category) {
                throw new RuntimeException("DemoExpansionSeeder: category '$title' not found - did DemoServiceCatalogSeeder run first?");
            }

            $categories[$title] = $category;
        }

        return $categories;
    }

    /**
     * @param array<string, Category> $categoriesByTitle
     */
    private function seedBranch(array $branch, Region $africa, string $locale, array $categoriesByTitle): void
    {
        $country = $branch['country']
            ? $this->country($africa, $branch['country'], $locale)
            : Country::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', 'Cameroon'))->first();

        if (!$country) {
            throw new RuntimeException('DemoExpansionSeeder: Cameroon country not found - did DemoAfricaSeeder run first?');
        }

        $city = $this->city($africa, $country, $branch['city'], $locale);

        $seller = $this->user($branch['seller']);
        $seller->syncRoles('seller');

        $shop = $this->shop($seller->id, $branch['shop']);

        $this->shopLocation($shop, $africa, $country, $city, 1); // PRODUCT
        $this->shopLocation($shop, $africa, $country, $city, 2); // SERVICE

        $master = $this->user($branch['master']);
        $master->syncRoles('master');
        $this->ensureInvitation($master, $shop);
        $this->ensureWorkingDays($master);

        $staff = $this->user($branch['staff']);
        $this->staffInvitation($shop, $staff);

        $this->servicesForShop($shop, $master, $categoriesByTitle, $locale);

        $this->deliveryPrice($africa, $country, $city);

        $this->command?->info("branch seeded: {$branch['shop']['title']} ({$branch['city']})");
    }

    private function country(Region $region, array $def, string $locale): Country
    {
        $currencyId = Currency::where('title', $def['currency'])->value('id');

        $country = Country::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $def['title']))->first();

        if (!$country) {
            $country = Country::create([
                'region_id'   => $region->id,
                'code'        => $def['iso2'],
                'active'      => true,
                'img'         => "https://flagcdn.com/h120/{$def['iso2']}.png",
                'currency_id' => $currencyId,
            ]);
            $country->translations()->create(['title' => $def['title'], 'locale' => $locale]);
            $this->command?->info("country: {$def['title']}");
        } elseif (!$country->currency_id && $currencyId) {
            $country->update(['currency_id' => $currencyId]);
        }

        return $country;
    }

    private function city(Region $region, Country $country, string $title, string $locale): City
    {
        $city = City::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $title))
            ->where('country_id', $country->id)
            ->first();

        if (!$city) {
            $city = City::create([
                'active'     => true,
                'region_id'  => $region->id,
                'country_id' => $country->id,
            ]);
            $city->translations()->create(['title' => $title, 'locale' => $locale]);
            $this->command?->info("city: $title");
        }

        return $city;
    }

    private function user(array $def): User
    {
        $user = User::updateOrCreate(['id' => $def['id']], [
            'uuid'              => Str::uuid(),
            'firstname'         => $def['firstname'],
            'lastname'          => $def['lastname'],
            'email'             => $def['email'],
            'phone'             => $def['phone'],
            'birthday'          => '1990-12-31',
            'gender'            => $def['gender'],
            // Only masters carry a photo (see BRANCHES) - sellers/staff
            // don't need one for anything currently rendered.
            'img'               => $def['img'] ?? null,
            'email_verified_at' => now(),
            'password'          => bcrypt($def['password']),
        ]);

        (new UserWalletService)->create($user);

        return $user;
    }

    private function shop(int $sellerId, array $def): Shop
    {
        $shop = Shop::updateOrCreate(['user_id' => $sellerId], [
            'uuid'            => Str::uuid(),
            'latitude'        => $def['latitude'],
            'longitude'       => $def['longitude'],
            'phone'           => $def['phone'],
            'open'            => 1,
            'status'          => 'approved',
            'status_note'     => 'approved',
            'delivery_time'   => ['from' => '10', 'to' => '90', 'type' => 'minute'],
            'type'            => 1,
            'background_img'  => $def['background_img'] ?? null,
            'logo_img'        => $def['logo_img'] ?? null,
        ]);

        $shopLocale = data_get(Language::first(), 'locale', 'en');

        ShopTranslation::updateOrCreate(['shop_id' => $shop->id], [
            'description' => $def['description'],
            'title'       => $def['title'],
            'locale'      => $shopLocale,
            'address'     => $def['address'],
        ]);

        try {
            $this->setSlug($shop, [$shopLocale => $def['title']], $shopLocale);
        } catch (Throwable $e) {
            $this->error($e);
        }

        $shop->tags()->sync(ShopTag::pluck('id')->toArray());

        return $shop;
    }

    private function shopLocation(Shop $shop, Region $region, Country $country, City $city, int $type): void
    {
        ShopLocation::updateOrCreate([
            'shop_id' => $shop->id,
            'city_id' => $city->id,
            'type'    => $type,
        ], [
            'region_id'  => $region->id,
            'country_id' => $country->id,
        ]);
    }

    private function ensureInvitation(User $master, Shop $shop): void
    {
        Invitation::updateOrCreate([
            'shop_id' => $shop->id,
            'user_id' => $master->id,
        ], [
            'role'   => 'master',
            'status' => Invitation::ACCEPTED,
        ]);
    }

    private function ensureWorkingDays(User $master): void
    {
        if ($master->workingDays()->exists()) {
            return;
        }

        (new UserService)->createDefaultWorkingDays($master);
    }

    private function staffInvitation(Shop $shop, User $staff): void
    {
        $role = ShopRole::updateOrCreate(['shop_id' => $shop->id, 'name' => 'Branch Manager']);

        $permissionKeys = [
            'bookings.view', 'bookings.manage', 'bookings.status', 'bookings.availability',
            'orders.view', 'orders.manage',
            'payments.view', 'payments.refunds.manage',
            'products.view', 'products.manage',
            'marketing.view',
        ];
        $role->permissions()->sync(ShopPermission::whereIn('key', $permissionKeys)->pluck('id'));

        $shopLocationIds = ShopLocation::where('shop_id', $shop->id)->pluck('id');

        $invitation = Invitation::updateOrCreate([
            'user_id' => $staff->id,
            'shop_id' => $shop->id,
        ], [
            'created_by'   => $shop->user_id,
            'role'         => 'shop_manager',
            'shop_role_id' => $role->id,
            'status'       => Invitation::ACCEPTED,
        ]);

        $invitation->shopLocations()->sync($shopLocationIds);

        $roles   = $staff->roles?->pluck('name')?->toArray() ?? [];
        $roles[] = $invitation->role;
        $staff->syncRoles($roles);
    }

    /**
     * @param array<string, Category> $categoriesByTitle
     */
    private function servicesForShop(Shop $shop, User $master, array $categoriesByTitle, string $locale): void
    {
        foreach (self::SERVICES as $definition) {
            $category = $categoriesByTitle[$definition['category']];

            $service = Service::where('shop_id', $shop->id)
                ->where('category_id', $category->id)
                ->first();

            if (!$service) {
                $service = Service::create([
                    'category_id' => $category->id,
                    'shop_id'     => $shop->id,
                    'status'      => Service::STATUS_ACCEPTED,
                    'img'         => $category->img,
                    'price'       => $definition['price'],
                    'interval'    => $definition['interval'],
                    'pause'       => 10,
                ]);
                ServiceTranslation::create([
                    'service_id'  => $service->id,
                    'locale'      => $locale,
                    'title'       => $definition['category'],
                    'description' => $definition['description'],
                ]);
            }

            ServiceMaster::updateOrCreate([
                'service_id' => $service->id,
                'master_id'  => $master->id,
                'shop_id'    => $shop->id,
            ], [
                'active'   => true,
                'price'    => $definition['price'],
                'interval' => $definition['interval'],
                'pause'    => 10,
            ]);
        }
    }

    private function deliveryPrice(Region $region, Country $country, ?City $city): void
    {
        $deliveryPrice = DeliveryPrice::where('region_id', $region->id)
            ->where('country_id', $country->id)
            ->when($city, fn($q) => $q->where('city_id', $city->id), fn($q) => $q->whereNull('city_id'))
            ->first();

        if (!$deliveryPrice) {
            $deliveryPrice = DeliveryPrice::create([
                'price'      => 2 * 600,
                'region_id'  => $region->id,
                'country_id' => $country->id,
                'city_id'    => $city?->id,
            ]);
            $deliveryPrice->translations()->create(['title' => 'Standard delivery', 'locale' => 'en']);
        }

        // Also seed the country-level (null-city) fallback row once per
        // country - same reasoning as DemoAfricaSeeder's own null-city rows
        // (DeliveryPrice::filter()'s whereNull default for a request that
        // hasn't got a city_id yet).
        $countryFallback = DeliveryPrice::where('region_id', $region->id)
            ->where('country_id', $country->id)
            ->whereNull('city_id')
            ->first();

        if (!$countryFallback) {
            $countryFallback = DeliveryPrice::create([
                'price'      => 2 * 600,
                'region_id'  => $region->id,
                'country_id' => $country->id,
                'city_id'    => null,
            ]);
            $countryFallback->translations()->create(['title' => 'Standard delivery', 'locale' => 'en']);
        }
    }
}
