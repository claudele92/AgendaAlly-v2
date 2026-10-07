<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Language;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Adds 6 new top-level (type=SERVICE) categories, each with their approved
 * subcategories, to the catalog started by DemoServiceCatalogSeeder's own
 * 6 (Hair Care, Nail Care, Spa & Massage, Makeup, Barbershop, Skin Care).
 * Unlike that seeder, this one does NOT wire any Service/ServiceMaster rows
 * to a demo shop - these categories exist so the catalog itself covers a
 * broader range of fixed-slot, appointment-based services, independent of
 * which (if any) demo seller currently offers them.
 *
 * Construction/project-based work was deliberately left out of this catalog
 * (proposed alongside these 6, not approved): this platform's booking model
 * is fixed-slot/appointment-based, and project or quote-based engagements
 * (a multi-day renovation, a custom build) don't fit that shape without a
 * feature this platform doesn't have yet.
 *
 * img on every category is a real icon, same reasoning and source
 * (remixicon, already a project dependency, served locally from
 * web/public/icons/categories/) as DemoServiceCatalogSeeder's own
 * CATEGORY_ICONS - see that class's docblock for why. remixicon has no
 * dedicated tooth/dental icon at all (confirmed by searching the full
 * package - "dent"/"tooth" match nothing); 'user-smile-line.svg' is used
 * for Dental Care as the closest honest visual proxy available, not a
 * literal dental icon.
 */
class CategoryCatalogExpansionSeeder extends Seeder
{
    use Loggable;

    // Top-level (type=SERVICE) category => its sub_service children.
    private const CATEGORY_TREE = [
        'Tailoring'              => ['Custom Suits', 'Alterations & Repairs', 'Traditional & Ethnic Wear', 'Bridal Wear'],
        'Dental Care'            => ['General Checkup', 'Teeth Whitening', 'Orthodontics'],
        'Healthcare'             => ['General Consultation', 'Home Nursing', 'Physiotherapy'],
        'Handyman'               => ['Plumbing', 'Electrical Repair', 'Furniture Assembly'],
        'Laundry & Dry Cleaning' => ['Wash & Fold', 'Dry Cleaning', 'Ironing'],
        'Home Cleaning'          => ['Standard Cleaning', 'Deep Cleaning', 'Move-in / Move-out'],
    ];

    private const CATEGORY_ICONS = [
        'Tailoring'              => '/icons/categories/tailoring.svg',
        'Dental Care'            => '/icons/categories/dental-care.svg',
        'Healthcare'             => '/icons/categories/healthcare.svg',
        'Handyman'               => '/icons/categories/handyman.svg',
        'Laundry & Dry Cleaning' => '/icons/categories/laundry-dry-cleaning.svg',
        'Home Cleaning'          => '/icons/categories/home-cleaning.svg',
    ];

    public function run(): void
    {
        try {
            $locale = data_get(Language::where('default', 1)->first(), 'locale', 'en');

            foreach (self::CATEGORY_TREE as $parentTitle => $children) {
                $icon = self::CATEGORY_ICONS[$parentTitle] ?? null;
                $parent = $this->category($parentTitle, Category::SERVICE, null, $locale, $icon);

                foreach ($children as $childTitle) {
                    $this->category($childTitle, Category::SUB_SERVICE, $parent, $locale, $icon);
                }
            }
        } catch (Throwable $e) {
            $this->error($e);
        }
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
}
