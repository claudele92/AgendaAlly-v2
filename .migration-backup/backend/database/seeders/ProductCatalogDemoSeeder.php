<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\Stock;
use App\Models\Unit;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Enables the platform-wide products_enabled toggle and seeds a minimal,
 * real product catalog behind it - before this, turning the flag on would
 * have exposed a fully-functional-looking POS/product-management/brands
 * surface (see admin sidebar's products_enabled gate) with zero products,
 * zero brands, and zero orders behind it.
 *
 * Reuses shop 501 (the original Cameroon demo shop, seeded by UserSeeder/
 * DemoAfricaSeeder and used throughout every other seeder this session)
 * rather than creating a new shop - it already has a PRODUCT-type
 * ShopLocation at both Douala and Yaoundé (see DemoAfricaSeeder), which is
 * the one precondition the seller-side "Product location" wizard step
 * checks for, so no new seller/location bootstrapping is needed.
 *
 * A real product taxonomy: this template's product categories
 * (Category::MAIN/SUB_MAIN/CHILD - a completely separate 3-level tree from
 * the service categories used everywhere else this session) had exactly
 * one placeholder row of each level ("main"/"sub_main"/"child") before this,
 * matching the same "boilerplate, never replaced" state products_enabled
 * itself was in. Builds out two real MAIN branches reflecting the two
 * service domains with an obvious retail-product angle (Beauty & Personal
 * Care, Tailoring & Apparel Supplies) - deliberately not forcing categories
 * onto service-only domains like Education/Dental/Handyman/Laundry, which
 * have no natural product counterpart. Only the 2 leaves the 5 demo
 * products actually use have products in them; the rest are catalog-only
 * breadth (same pattern as CategoryCatalogExpansionSeeder's service
 * categories), all shop_id=null/active/published as if admin-created and
 * pre-approved (see CategoryService::create()'s category_auto_approve
 * gate - shop_id=null keeps these visible/selectable by every seller, not
 * just shop 501).
 *
 * Prices are written as "real-world $ * 600" (600 = the currency:rebase-to
 * -xaf factor, see CurrencySeeder), same convention as every other price in
 * this session's seeders.
 *
 * Photos are real, verified Unsplash URLs (free tier), mostly Ela De Pure's
 * own real product photography (a genuine skincare/haircare brand) -
 * fetched via the Unsplash MCP tool in the session that wrote this seeder.
 *
 * Critical, easy-to-miss step: Product.min_price/max_price (and the shop's
 * own min_price/max_price) are NOT kept in sync by any model observer -
 * confirmed by reading app/Observers/ProductObserver.php, which has no
 * price logic at all. The real seller-facing flow
 * (ProductAdditionalService::update()) recomputes and writes both after
 * every stock change; skip that step here and Product::scopeFilter()'s
 * price-range filter query (`min_price >= ... AND max_price <= ...`) would
 * silently exclude every one of these products from any price-filtered
 * storefront listing, and the shop's own displayed price range would stay
 * at 0. This seeder replicates that exact step after creating each stock.
 */
class ProductCatalogDemoSeeder extends Seeder
{
    use Loggable;

    // See UserSeeder/DemoAfricaSeeder - the original Cameroon demo shop,
    // already used by every other seeder this session.
    private const SHOP_ID = 501;

    private const PRODUCTS = [
        [
            'category' => 'Hair Care Essentials',
            'title' => 'Moroccan Argan Oil Hair Serum',
            'description' => 'Lightweight argan oil serum that smooths frizz and adds shine without weighing hair down.',
            'price' => 16 * 600,
            'quantity' => 45,
            // "Hair serum bottle resting on a stack of elegant books"
            // (photo id eLm-P_CEdw0, by Ela De Pure).
            'img' => 'https://images.unsplash.com/photo-1779492907379-0894f8807533?auto=format&fit=crop&w=800&h=800&q=80',
        ],
        [
            'category' => 'Hair Care Essentials',
            'title' => 'Moisturizing Shampoo',
            'description' => 'Sulfate-free shampoo that cleanses gently while restoring moisture to dry or damaged hair.',
            'price' => 14 * 600,
            'quantity' => 60,
            // "Moisturizing shampoo bottle on a neutral background" (photo
            // id K1k8M_bb2bM, by Ela De Pure).
            'img' => 'https://images.unsplash.com/photo-1747858989102-cca0f4dc4a11?auto=format&fit=crop&w=800&h=800&q=80',
        ],
        [
            'category' => 'Hair Care Essentials',
            'title' => 'Moisturizing Conditioner',
            'description' => 'Rich conditioner that detangles and locks in moisture, formulated to pair with our Moisturizing Shampoo.',
            'price' => 14 * 600,
            'quantity' => 55,
            // "Two pump bottles of moisturizing shampoo and conditioner"
            // (photo id 5eoYsqzmDW4, by Ela De Pure).
            'img' => 'https://images.unsplash.com/photo-1755655618085-e0267f9fbd81?auto=format&fit=crop&w=800&h=800&q=80',
        ],
        [
            'category' => 'Skincare Serums',
            'title' => 'Vitamin C Brightening Serum',
            'description' => 'Antioxidant-rich vitamin C serum that brightens skin tone and evens texture with daily use.',
            'price' => 22 * 600,
            'quantity' => 30,
            // "A bottle of vitamin c on top of lemon slices" (photo id
            // rqK_baq_XgI, by Ela De Pure).
            'img' => 'https://images.unsplash.com/photo-1723951174326-2a97221d3b7f?auto=format&fit=crop&w=800&h=800&q=80',
        ],
        [
            'category' => 'Hair Care Essentials',
            'title' => 'Wide-Tooth Detangling Comb',
            'description' => 'A gentle wooden wide-tooth comb for detangling wet or dry hair without snagging or breakage.',
            'price' => 6 * 600,
            'quantity' => 100,
            // "brown hair comb on white wooden table" (photo id
            // kkQdRnasB98, by Apothecary 87).
            'img' => 'https://images.unsplash.com/photo-1587477444258-096b2d514c12?auto=format&fit=crop&w=800&h=800&q=80',
        ],
    ];

    public function run(): void
    {
        try {
            Settings::updateOrCreate(['key' => 'products_enabled'], ['value' => '1']);
            $this->command?->info('setting: products_enabled = 1');

            $locale = data_get(Language::where('default', 1)->first(), 'locale', 'en');

            $shop = Shop::find(self::SHOP_ID);

            if (!$shop) {
                $this->command?->warn('ProductCatalogDemoSeeder: shop ' . self::SHOP_ID . ' not found, skipping (run UserSeeder/DemoAfricaSeeder first)');

                return;
            }

            $unitId = Unit::query()->value('id');

            $brand = $this->brand($locale);
            $categoriesByTitle = $this->categories($locale);

            foreach (self::PRODUCTS as $definition) {
                $this->product($shop, $brand, $unitId, $categoriesByTitle[$definition['category']], $definition, $locale);
            }
        } catch (Throwable $e) {
            $this->error($e);
        }
    }

    private function brand(string $locale): Brand
    {
        $brand = Brand::where('title', 'Ela De Pure')->first();

        if (!$brand) {
            $brand = Brand::create([
                'title' => 'Ela De Pure',
                'active' => true,
                // "Three skincare products arranged on a shelf" (photo id
                // RJgh0UW0HbU, by Ela De Pure) - a real Unsplash
                // photographer whose actual product line these photos are.
                'img' => 'https://images.unsplash.com/photo-1768483018807-bd0b9ab86539?auto=format&fit=crop&w=400&h=400&q=80',
            ]);
            $this->command?->info('brand: Ela De Pure');
        }

        return $brand;
    }

    /**
     * @return array<string, Category> leaf (CHILD) category title => Category
     */
    private function categories(string $locale): array
    {
        // Renamed in place (not recreated) so the 5 existing products'
        // category_id chain, and the shop's already-verified price range,
        // stay attached to the same MAIN row rather than forking onto a
        // second, orphaned "Beauty Products" tree.
        $main = $this->category('Beauty Products', Category::MAIN, null, $locale, renameTo: 'Beauty & Personal Care');

        $hairCareSubMain = $this->category('Hair Care', Category::SUB_MAIN, $main, $locale);
        $skinCareSubMain = $this->category('Skin Care', Category::SUB_MAIN, $main, $locale);
        $makeupSubMain = $this->category('Makeup', Category::SUB_MAIN, $main, $locale);
        $bathBodySubMain = $this->category('Bath & Body', Category::SUB_MAIN, $main, $locale);

        $tailoringMain = $this->category('Tailoring & Apparel Supplies', Category::MAIN, null, $locale);

        $fabricsSubMain = $this->category('Fabrics', Category::SUB_MAIN, $tailoringMain, $locale);
        $notionsSubMain = $this->category('Sewing Notions', Category::SUB_MAIN, $tailoringMain, $locale);
        $accessoriesSubMain = $this->category('Ready-to-Wear Accessories', Category::SUB_MAIN, $tailoringMain, $locale);

        return [
            // Existing leaves - the 5 seeded products already point here.
            'Hair Care Essentials' => $this->category('Hair Care Essentials', Category::CHILD, $hairCareSubMain, $locale),
            'Skincare Serums'      => $this->category('Skincare Serums', Category::CHILD, $skinCareSubMain, $locale),

            // New catalog-only leaves - real breadth for the category
            // picker/filters, no demo products forced into them (that's a
            // separate ask; see class docblock).
            'Combs & Brushes'          => $this->category('Combs & Brushes', Category::CHILD, $hairCareSubMain, $locale),
            'Lipstick'                 => $this->category('Lipstick', Category::CHILD, $makeupSubMain, $locale),
            'Foundation & Concealer'   => $this->category('Foundation & Concealer', Category::CHILD, $makeupSubMain, $locale),
            'Body Wash & Soap'         => $this->category('Body Wash & Soap', Category::CHILD, $bathBodySubMain, $locale),
            'Body Lotion & Moisturizer' => $this->category('Body Lotion & Moisturizer', Category::CHILD, $bathBodySubMain, $locale),
            'Cotton & Linen Fabrics'   => $this->category('Cotton & Linen Fabrics', Category::CHILD, $fabricsSubMain, $locale),
            'Embroidered & Lace Fabrics' => $this->category('Embroidered & Lace Fabrics', Category::CHILD, $fabricsSubMain, $locale),
            'Thread & Needles'         => $this->category('Thread & Needles', Category::CHILD, $notionsSubMain, $locale),
            'Buttons & Zippers'        => $this->category('Buttons & Zippers', Category::CHILD, $notionsSubMain, $locale),
            'Headwraps & Scarves'      => $this->category('Headwraps & Scarves', Category::CHILD, $accessoriesSubMain, $locale),
            'Belts & Bags'             => $this->category('Belts & Bags', Category::CHILD, $accessoriesSubMain, $locale),
        ];
    }

    private function category(string $title, int $type, ?Category $parent, string $locale, ?string $renameTo = null): Category
    {
        $category = Category::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $title))
            ->where('type', $type)
            ->first();

        if (!$category && $renameTo) {
            // Already renamed on a previous run - look it up by the new
            // title instead of creating a second, duplicate MAIN row.
            $category = Category::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $renameTo))
                ->where('type', $type)
                ->first();
        }

        if (!$category) {
            $category = Category::create([
                'type'      => $type,
                'parent_id' => $parent?->id ?? 0,
                'active'    => true,
                'status'    => Category::PUBLISHED,
            ]);
            $category->translations()->create(['title' => $renameTo ?? $title, 'locale' => $locale]);
            $this->command?->info('category: ' . ($renameTo ?? $title));
        } elseif ($renameTo) {
            $category->translation?->update(['title' => $renameTo]);
            $this->command?->info("category: $title -> $renameTo");
        }

        return $category;
    }

    private function product(Shop $shop, Brand $brand, ?int $unitId, Category $category, array $definition, string $locale): void
    {
        $product = Product::where('shop_id', $shop->id)
            ->whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $definition['title']))
            ->first();

        if (!$product) {
            $product = Product::create([
                'shop_id'     => $shop->id,
                'category_id' => $category->id,
                'brand_id'    => $brand->id,
                'unit_id'     => $unitId,
                'img'         => $definition['img'],
                'active'      => true,
                'visibility'  => true,
                'status'      => Product::PUBLISHED,
                'min_qty'     => 1,
                'max_qty'     => 20,
            ]);
            ProductTranslation::create([
                'product_id'  => $product->id,
                'locale'      => $locale,
                'title'       => $definition['title'],
                'description' => $definition['description'],
            ]);
            $this->command?->info("product: {$definition['title']}");
        }

        // Real seller-created products always have at least one Gallery
        // row - the "Gallery" step is a required part of the product-add
        // wizard (see admin/src/views/seller-views/products/steps.js).
        // Without one, web's product-detail gallery swiper (which only
        // ever reads Product.galleries/Stock.galleries, never the plain
        // Product.img/Stock.img this seeder sets) renders nothing at all -
        // confirmed live: a genuinely blank space, not even the usual
        // broken-image fallback.
        if ($product->galleries()->count() === 0) {
            $product->galleries()->create([
                'title' => $definition['title'],
                'path'  => $definition['img'],
                'type'  => 'products',
            ]);
        }

        $stock = Stock::where('product_id', $product->id)->first();

        if (!$stock) {
            $stock = Stock::create([
                'product_id' => $product->id,
                'price'      => $definition['price'],
                'quantity'   => $definition['quantity'],
                'img'        => $definition['img'],
            ]);
        }

        // Not kept in sync by any observer - see class docblock. Mirrors
        // ProductAdditionalService::update()'s own min()/max() over the
        // product's stocks (here just the one) rather than hardcoding the
        // single stock's price, so this stays correct if a second stock
        // (a variant) is ever added by hand later.
        $minPrice = max($product->stocks()->min('price'), 0);
        $maxPrice = max($product->stocks()->max('price'), 0);

        $product->update(['min_price' => $minPrice, 'max_price' => $maxPrice]);

        $shop->update([
            'min_price' => min($shop->min_price ?: $minPrice, $minPrice),
            'max_price' => max($shop->max_price ?: $maxPrice, $maxPrice),
        ]);
    }
}
