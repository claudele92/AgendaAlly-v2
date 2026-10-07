<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Invitation;
use App\Models\Language;
use App\Models\Region;
use App\Models\Service;
use App\Models\ServiceMaster;
use App\Models\ServiceTranslation;
use App\Models\Shop;
use App\Models\ShopLocation;
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
 * Two new top-level (type=SERVICE) categories - Education and Tattoo &
 * Piercing - each with real subcategories, plus one real demo shop per
 * category so both are actually bookable, not just catalog entries.
 *
 * Education: one multi-branch Cameroon shop (Douala/Bafoussam/Yaoundé,
 * reusing DemoAfricaSeeder's and DemoExpansionSeeder's existing cities -
 * this seeder creates no new geography), 5 masters split across the 3
 * branches. Tattoo & Piercing: one 2-branch shop (Douala/Yaoundé), 1
 * master per branch.
 *
 * "Branch" here means a ShopLocation (PRODUCT+SERVICE pair) per city, same
 * shape every other demo shop uses - see DemoAfricaSeeder/DemoExpansionSeeder.
 * Each master's Invitation is scoped to their own branch's ShopLocation rows
 * via shopLocations()->sync(), the same pivot DemoExpansionSeeder's staff
 * invitations already use, so "which master works where" is a real,
 * queryable relationship rather than only a naming convention. Service/
 * ServiceMaster rows themselves are shop-wide, not per-location (this
 * codebase has no location-scoped service - see Service::scopeFilter()),
 * so every master in a shop is attached as a ServiceMaster to every one of
 * that shop's services, matching how every other demo shop with more than
 * one master already works.
 *
 * Icons: remixicon (already a dependency, same source as every other
 * category icon here - see CategoryCatalogExpansionSeeder's own docblock)
 * has no dedicated graduation-cap/education icon or tattoo-needle icon.
 * Education uses Book2FillIcon's path (an open book) and Tattoo & Piercing
 * uses InkBottleFillIcon's path (an ink bottle) as the closest honest
 * proxies available, copied to web/public/icons/categories/ - same
 * tooth-substitute reasoning already used for Dental Care.
 *
 * Unlike DemoServiceCatalogSeeder/DemoExpansionSeeder (both written when
 * this sandbox couldn't reach unsplash.com), every photo below is a real,
 * verified Unsplash URL (free tier) sourced via the Unsplash MCP tool in
 * the session that wrote this seeder.
 */
class EducationTattooDemoSeeder extends Seeder
{
    use Loggable;
    use SetTranslations;

    private const EDUCATION_CATEGORY_TREE = [
        'Academic Tutoring',
        'Exam Preparation',
        'Language Lessons',
        'Music Lessons',
        'Computer & Digital Skills',
    ];

    private const TATTOO_CATEGORY_TREE = [
        'Custom Tattoo',
        'Tattoo Cover-Up & Touch-Up',
        'Ear Piercing',
        'Body Piercing',
    ];

    // Real Unsplash photos (id -> description), documented once here rather
    // than per-line below: all free tier, fetched via the Unsplash MCP tool.
    // Shop images:
    //   edu background: iQPr1XkF5F0 "woman carrying white and green textbook" by javier trueba
    //   edu logo:        yf61jhXNmYo "assorted-color pencil lot on white surface" by Copper and Wild
    //   tattoo background: Iskr6EBJQs0 "tattoo and piercing shop with ornate signage" by Dmitry Kropachev
    //   tattoo logo:        WmZPq-vgpNE "person holding black and silver hand tool" (tattoo machine) by Jonathan Cooper
    // Master portraits:
    //   jyBYPwb_Soo "smiling man in a dark collared shirt" by abdullah ali
    //   21ckukPU3qA "a woman with a smile on her face" by Abenezer Shewaga
    //   d9_v4rieKpU "a smiling man in a black suit and tie" by Makeen M.Alaa
    //   xc5gojDaxW8 "a woman standing with her arms crossed" by Abenezer Shewaga
    //   JoZ0Ni2xn2U "a black man in a black turtle neck shirt" by Oluwatobi
    //   k21zKdqBzUI "a man in a gray shirt is posing for a picture" by Oluwatobi
    //   jYGxN1vMWiw "a woman with curly hair wearing a striped shirt" by Abenezer Shewaga
    // Service images:
    //   6RTM8EsD1T8 "woman writing on book" by Kyle Gregory Devaras
    //   AE1XpXLxXSA "assorted-title book lot" by Annie Spratt
    //   o_jVJ_R3e50 "a group of people standing around talking to each other" by Alex Gallegos
    //   mm7-kDz4AY4 "man in white shirt playing piano" by Pietro Schellino
    //   mpmNrm_AWhY "a person typing on a laptop" by Alexander Polous
    //   C_MccHr6Q_4 "artist tattooing a hand with black ink" by Haberdoedas
    //   cqPTVG35W1I "man with extensive tattoos receiving new ink" by Haberdoedas
    //   n6bbbc3Oc-w "delicate hexagonal diamond stud earrings" by Bench Lewish
    //   lDY_iN70ddo "a close-up of a person with a nose piercing" by Joceline Painho

    public function run(): void
    {
        try {
            $locale = data_get(Language::where('default', 1)->first(), 'locale', 'en');

            $africa = Region::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', 'Africa'))->first();
            $cameroon = Country::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', 'Cameroon'))->first();

            if (!$africa || !$cameroon) {
                $this->command?->warn('EducationTattooDemoSeeder: Africa/Cameroon not found, skipping (run DemoAfricaSeeder first)');

                return;
            }

            $douala = $this->existingCity($cameroon, 'Douala', $locale);
            $bafoussam = $this->existingCity($cameroon, 'Bafoussam', $locale);
            $yaounde = $this->existingCity($cameroon, 'Yaoundé', $locale);

            $educationCategories = $this->categoryTree(
                'Education',
                self::EDUCATION_CATEGORY_TREE,
                '/icons/categories/education.svg',
                $locale
            );

            $tattooCategories = $this->categoryTree(
                'Tattoo & Piercing',
                self::TATTOO_CATEGORY_TREE,
                '/icons/categories/tattoo-piercing.svg',
                $locale
            );

            $this->seedEducationShop($africa, $cameroon, $douala, $bafoussam, $yaounde, $educationCategories, $locale);
            $this->seedTattooShop($africa, $cameroon, $douala, $yaounde, $tattooCategories, $locale);
        } catch (Throwable $e) {
            $this->error($e);
        }
    }

    private function existingCity(Country $country, string $title, string $locale): City
    {
        $city = City::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $title))
            ->where('country_id', $country->id)
            ->first();

        if (!$city) {
            throw new RuntimeException("EducationTattooDemoSeeder: city '$title' not found - did DemoAfricaSeeder/DemoExpansionSeeder run first?");
        }

        return $city;
    }

    /**
     * @param string[] $children
     * @return array<string, Category> child title => Category
     */
    private function categoryTree(string $parentTitle, array $children, string $icon, string $locale): array
    {
        $parent = $this->category($parentTitle, Category::SERVICE, null, $locale, $icon);

        $leaves = [];

        foreach ($children as $childTitle) {
            $leaves[$childTitle] = $this->category($childTitle, Category::SUB_SERVICE, $parent, $locale, $icon);
        }

        return $leaves;
    }

    private function category(string $title, int $type, ?Category $parent, string $locale, ?string $img = null): Category
    {
        $category = Category::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $title))
            ->where('type', $type)
            ->first();

        if (!$category) {
            $category = Category::create([
                'type'      => $type,
                'parent_id' => $parent?->id ?? 0,
                'active'    => true,
                'status'    => Category::PUBLISHED,
                'img'       => $img,
            ]);
            $category->translations()->create(['title' => $title, 'locale' => $locale]);
            $this->command?->info("category: $title");
        } elseif ($img && $category->img !== $img) {
            $category->update(['img' => $img]);
        }

        return $category;
    }

    /**
     * @param array<string, Category> $categoriesByTitle
     */
    private function seedEducationShop(
        Region $africa,
        Country $cameroon,
        City $douala,
        City $bafoussam,
        City $yaounde,
        array $categoriesByTitle,
        string $locale
    ): void {
        $seller = $this->user([
            'id' => 132, 'firstname' => 'sellers-edu', 'lastname' => 'sellers-edu',
            'email' => 'sellers-edu@githubit.com', 'phone' => '998911902714', 'password' => 'sellersedu', 'gender' => 'male',
        ]);
        $seller->syncRoles('seller');

        $shop = $this->shop($seller->id, [
            'title'       => 'AgendaAlly Learning Hub',
            'description' => 'Tutoring, exam prep, language, music, and computer lessons across Douala, Bafoussam, and Yaoundé.',
            'address'     => 'Rue de la Réunification, Douala, Cameroon',
            'latitude'    => 4.0483,
            'longitude'   => 9.7043,
            'phone'       => '+2370000000005',
            // "woman carrying white and green textbook" (photo id
            // iQPr1XkF5F0, by javier trueba).
            'background_img' => 'https://images.unsplash.com/photo-1571260899304-425eee4c7efc?auto=format&fit=crop&w=1200&q=80',
            // "assorted-color pencil lot on white surface" (photo id
            // yf61jhXNmYo, by Copper and Wild).
            'logo_img' => 'https://images.unsplash.com/photo-1568205612837-017257d2310a?auto=format&fit=crop&w=400&h=400&q=80',
        ], $locale);

        $this->shopLocation($shop, $africa, $cameroon, $douala, ShopLocation::PRODUCT);
        $this->shopLocation($shop, $africa, $cameroon, $douala, ShopLocation::SERVICE);
        $this->shopLocation($shop, $africa, $cameroon, $bafoussam, ShopLocation::PRODUCT);
        $this->shopLocation($shop, $africa, $cameroon, $bafoussam, ShopLocation::SERVICE);
        $this->shopLocation($shop, $africa, $cameroon, $yaounde, ShopLocation::PRODUCT);
        $this->shopLocation($shop, $africa, $cameroon, $yaounde, ShopLocation::SERVICE);

        $masters = [
            $this->masterFor($shop, $douala, [
                'id' => 133, 'firstname' => 'Serge', 'lastname' => 'Talla', 'email' => 'master-edu1@githubit.com',
                'phone' => '998911902715', 'password' => 'masteredu1', 'gender' => 'male',
                // "smiling man in a dark collared shirt sits indoors" (photo
                // id jyBYPwb_Soo, by abdullah ali).
                'img' => 'https://images.unsplash.com/photo-1765366574945-e2f1b4b1a5b3?auto=format&fit=crop&w=600&h=600&q=80',
            ]),
            $this->masterFor($shop, $douala, [
                'id' => 134, 'firstname' => 'Élise', 'lastname' => 'Fotso', 'email' => 'master-edu2@githubit.com',
                'phone' => '998911902716', 'password' => 'masteredu2', 'gender' => 'female',
                // "a woman with a smile on her face" (photo id
                // 21ckukPU3qA, by Abenezer Shewaga).
                'img' => 'https://images.unsplash.com/photo-1710777932534-2a58edf3603d?auto=format&fit=crop&w=600&h=600&q=80',
            ]),
            $this->masterFor($shop, $bafoussam, [
                'id' => 135, 'firstname' => 'Achille', 'lastname' => 'Nkeng', 'email' => 'master-edu3@githubit.com',
                'phone' => '998911902717', 'password' => 'masteredu3', 'gender' => 'male',
                // "a smiling man in a black suit and tie" (photo id
                // d9_v4rieKpU, by Makeen M.Alaa).
                'img' => 'https://images.unsplash.com/photo-1770452603217-89b4f03e8271?auto=format&fit=crop&w=600&h=600&q=80',
            ]),
            $this->masterFor($shop, $yaounde, [
                'id' => 136, 'firstname' => 'Solange', 'lastname' => 'Ateba', 'email' => 'master-edu4@githubit.com',
                'phone' => '998911902718', 'password' => 'masteredu4', 'gender' => 'female',
                // "a woman standing with her arms crossed" (photo id
                // xc5gojDaxW8, by Abenezer Shewaga).
                'img' => 'https://images.unsplash.com/photo-1710778044102-56a3a6b69a1b?auto=format&fit=crop&w=600&h=600&q=80',
            ]),
            $this->masterFor($shop, $yaounde, [
                'id' => 137, 'firstname' => 'Patrice', 'lastname' => 'Etoundi', 'email' => 'master-edu5@githubit.com',
                'phone' => '998911902719', 'password' => 'masteredu5', 'gender' => 'male',
                // "a black man in a black turtle neck shirt" (photo id
                // JoZ0Ni2xn2U, by Oluwatobi).
                'img' => 'https://images.unsplash.com/photo-1678282955795-200c1e18bc7d?auto=format&fit=crop&w=600&h=600&q=80',
            ]),
        ];

        $services = [
            [
                'category' => 'Academic Tutoring', 'price' => 20 * 600, 'interval' => 60,
                'description' => 'One-on-one tutoring across core school subjects, tailored to the student\'s level.',
                // "woman writing on book" (photo id 6RTM8EsD1T8, by Kyle
                // Gregory Devaras).
                'img' => 'https://images.unsplash.com/photo-1514369118554-e20d93546b30?auto=format&fit=crop&w=800&h=600&q=80',
            ],
            [
                'category' => 'Exam Preparation', 'price' => 30 * 600, 'interval' => 90,
                'description' => 'Focused, intensive sessions to prepare for upcoming school or entrance exams.',
                // "assorted-title book lot" (photo id AE1XpXLxXSA, by Annie
                // Spratt).
                'img' => 'https://images.unsplash.com/photo-1499332251574-a76a01d733fc?auto=format&fit=crop&w=800&h=600&q=80',
            ],
            [
                'category' => 'Language Lessons', 'price' => 15 * 600, 'interval' => 45,
                'description' => 'Conversational and grammar lessons in the language of your choice.',
                // "a group of people standing around talking to each other"
                // (photo id o_jVJ_R3e50, by Alex Gallegos).
                'img' => 'https://images.unsplash.com/photo-1730875644858-7a312de90752?auto=format&fit=crop&w=800&h=600&q=80',
            ],
            [
                'category' => 'Music Lessons', 'price' => 25 * 600, 'interval' => 60,
                'description' => 'Piano, guitar, or voice lessons for beginners through advanced students.',
                // "man in white shirt playing piano" (photo id
                // mm7-kDz4AY4, by Pietro Schellino).
                'img' => 'https://images.unsplash.com/photo-1626541650317-6ac1c2325995?auto=format&fit=crop&w=800&h=600&q=80',
            ],
            [
                'category' => 'Computer & Digital Skills', 'price' => 18 * 600, 'interval' => 60,
                'description' => 'Practical computer literacy, office software, and everyday digital skills.',
                // "a person typing on a laptop" (photo id mpmNrm_AWhY, by
                // Alexander Polous).
                'img' => 'https://images.unsplash.com/photo-1667058014960-b4d3c76047a2?auto=format&fit=crop&w=800&h=600&q=80',
            ],
        ];

        $this->servicesForShop($shop, $masters, $categoriesByTitle, $services, $locale);
    }

    /**
     * @param array<string, Category> $categoriesByTitle
     */
    private function seedTattooShop(
        Region $africa,
        Country $cameroon,
        City $douala,
        City $yaounde,
        array $categoriesByTitle,
        string $locale
    ): void {
        $seller = $this->user([
            'id' => 138, 'firstname' => 'sellers-tattoo', 'lastname' => 'sellers-tattoo',
            'email' => 'sellers-tattoo@githubit.com', 'phone' => '998911902720', 'password' => 'sellerstattoo', 'gender' => 'male',
        ]);
        $seller->syncRoles('seller');

        $shop = $this->shop($seller->id, [
            'title'       => 'Wouri Ink & Piercing Studio',
            'description' => 'Custom tattoos, cover-ups, and professional piercing across Douala and Yaoundé.',
            'address'     => 'Boulevard de la Liberté, Douala, Cameroon',
            'latitude'    => 4.0511,
            'longitude'   => 9.7679,
            'phone'       => '+2370000000006',
            // "tattoo and piercing shop with ornate signage" (photo id
            // Iskr6EBJQs0, by Dmitry Kropachev).
            'background_img' => 'https://images.unsplash.com/photo-1760877611905-0f885a3ce551?auto=format&fit=crop&w=1200&q=80',
            // "person holding black and silver hand tool" (a tattoo
            // machine, photo id WmZPq-vgpNE, by Jonathan Cooper).
            'logo_img' => 'https://images.unsplash.com/photo-1597100569812-3508fa87bdc7?auto=format&fit=crop&w=400&h=400&q=80',
        ], $locale);

        $this->shopLocation($shop, $africa, $cameroon, $douala, ShopLocation::PRODUCT);
        $this->shopLocation($shop, $africa, $cameroon, $douala, ShopLocation::SERVICE);
        $this->shopLocation($shop, $africa, $cameroon, $yaounde, ShopLocation::PRODUCT);
        $this->shopLocation($shop, $africa, $cameroon, $yaounde, ShopLocation::SERVICE);

        $masters = [
            $this->masterFor($shop, $douala, [
                'id' => 139, 'firstname' => 'Junior', 'lastname' => 'Mvondo', 'email' => 'master-tattoo1@githubit.com',
                'phone' => '998911902721', 'password' => 'mastertattoo1', 'gender' => 'male',
                // "a man in a gray shirt is posing for a picture" (photo id
                // k21zKdqBzUI, by Oluwatobi).
                'img' => 'https://images.unsplash.com/photo-1678282956162-f4d7e699f135?auto=format&fit=crop&w=600&h=600&q=80',
            ]),
            $this->masterFor($shop, $yaounde, [
                'id' => 140, 'firstname' => 'Carine', 'lastname' => 'Essomba', 'email' => 'master-tattoo2@githubit.com',
                'phone' => '998911902722', 'password' => 'mastertattoo2', 'gender' => 'female',
                // "a woman with curly hair wearing a striped shirt" (photo
                // id jYGxN1vMWiw, by Abenezer Shewaga).
                'img' => 'https://images.unsplash.com/photo-1710777915903-a7d7f159f2c0?auto=format&fit=crop&w=600&h=600&q=80',
            ]),
        ];

        $services = [
            [
                'category' => 'Custom Tattoo', 'price' => 80 * 600, 'interval' => 120,
                'description' => 'A custom-designed tattoo, sized and placed to your specification.',
                // "artist tattooing a hand with black ink" (photo id
                // C_MccHr6Q_4, by Haberdoedas).
                'img' => 'https://images.unsplash.com/photo-1775135786116-2a587f46a64a?auto=format&fit=crop&w=800&h=600&q=80',
            ],
            [
                'category' => 'Tattoo Cover-Up & Touch-Up', 'price' => 50 * 600, 'interval' => 90,
                'description' => 'Refresh faded ink or rework an existing tattoo into something new.',
                // "man with extensive tattoos receiving new ink" (photo id
                // cqPTVG35W1I, by Haberdoedas).
                'img' => 'https://images.unsplash.com/photo-1775135679609-125e8cb67de5?auto=format&fit=crop&w=800&h=600&q=80',
            ],
            [
                'category' => 'Ear Piercing', 'price' => 15 * 600, 'interval' => 20,
                'description' => 'Professional, sterile ear piercing with jewelry included.',
                // "delicate hexagonal diamond stud earrings on white
                // background" (photo id n6bbbc3Oc-w, by Bench Lewish).
                'img' => 'https://images.unsplash.com/photo-1769151591224-2eee6793b885?auto=format&fit=crop&w=800&h=600&q=80',
            ],
            [
                'category' => 'Body Piercing', 'price' => 25 * 600, 'interval' => 30,
                'description' => 'Piercing for nose, eyebrow, navel, and other body placements.',
                // "a close-up of a person with a nose piercing" (photo id
                // lDY_iN70ddo, by Joceline Painho).
                'img' => 'https://images.unsplash.com/photo-1735954221100-1cb3447a3396?auto=format&fit=crop&w=800&h=600&q=80',
            ],
        ];

        $this->servicesForShop($shop, $masters, $categoriesByTitle, $services, $locale);
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
            'img'               => $def['img'] ?? null,
            'email_verified_at' => now(),
            'password'          => bcrypt($def['password']),
        ]);

        (new UserWalletService)->create($user);

        return $user;
    }

    private function shop(int $sellerId, array $def, string $locale): Shop
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
            'background_img'  => $def['background_img'],
            'logo_img'        => $def['logo_img'],
        ]);

        ShopTranslation::updateOrCreate(['shop_id' => $shop->id], [
            'description' => $def['description'],
            'title'       => $def['title'],
            'locale'      => $locale,
            'address'     => $def['address'],
        ]);

        try {
            $this->setSlug($shop, [$locale => $def['title']], $locale);
        } catch (Throwable $e) {
            $this->error($e);
        }

        $shop->tags()->sync(ShopTag::pluck('id')->toArray());

        return $shop;
    }

    private function shopLocation(Shop $shop, Region $region, Country $country, City $city, int $type): ShopLocation
    {
        return ShopLocation::updateOrCreate([
            'shop_id' => $shop->id,
            'city_id' => $city->id,
            'type'    => $type,
        ], [
            'region_id'  => $region->id,
            'country_id' => $country->id,
        ]);
    }

    /**
     * Creates/updates the master user, invites them to the shop, gives
     * them default working days, and scopes their invitation to the
     * specific branch (both PRODUCT and SERVICE ShopLocation rows at that
     * city) they work at - same shopLocations() pivot DemoExpansionSeeder's
     * staff invitations already use.
     */
    private function masterFor(Shop $shop, City $branchCity, array $def): User
    {
        $master = $this->user($def);
        $master->syncRoles('master');

        $invitation = Invitation::updateOrCreate([
            'shop_id' => $shop->id,
            'user_id' => $master->id,
        ], [
            'role'   => 'master',
            'status' => Invitation::ACCEPTED,
        ]);

        $branchLocationIds = ShopLocation::where('shop_id', $shop->id)
            ->where('city_id', $branchCity->id)
            ->pluck('id');

        $invitation->shopLocations()->sync($branchLocationIds);

        if (!$master->workingDays()->exists()) {
            (new UserService)->createDefaultWorkingDays($master);
        }

        return $master;
    }

    /**
     * @param User[] $masters
     * @param array<string, Category> $categoriesByTitle
     * @param array<int, array{category: string, price: int, interval: int, description: string, img: string}> $services
     */
    private function servicesForShop(Shop $shop, array $masters, array $categoriesByTitle, array $services, string $locale): void
    {
        foreach ($services as $definition) {
            $category = $categoriesByTitle[$definition['category']];

            $service = Service::where('shop_id', $shop->id)
                ->where('category_id', $category->id)
                ->first();

            if (!$service) {
                $service = Service::create([
                    'category_id' => $category->id,
                    'shop_id'     => $shop->id,
                    'status'      => Service::STATUS_ACCEPTED,
                    'img'         => $definition['img'],
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
                $this->command?->info("service: {$definition['category']} (shop {$shop->id})");
            } elseif ($service->img !== $definition['img']) {
                $service->update(['img' => $definition['img']]);
            }

            foreach ($masters as $master) {
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
    }
}
