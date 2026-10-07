<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

/**
 * A handful of keys the original template's translations.php/translations_en.sql
 * dump never included, found by auditing real screens rather than the dump
 * itself: three admin-panel Shop-edit step titles (confirmed via a live
 * click-through of Settings > Shop edit - they rendered as their raw keys)
 * and one storefront hero subheading (confirmed the same way on /home-2).
 * Both apps read from this one shared `translations` table (see the
 * existing 'shop'/'shop.edit'/'delivery' rows, group=web, which already
 * back admin-panel strings), so every key here uses the same group.
 */
class MissingTranslationsSeeder extends Seeder
{
    private const TRANSLATIONS = [
        // admin: seller-views/my-shop/edit.jsx step titles
        'product.locations' => 'Product locations',
        'service.locations' => 'Service locations',
        'shop.social'       => 'Social links',
        // web: (with-footer)/(home-2)/home-2/page.tsx hero subheading
        'home-2.hero.description' => 'Find trusted salons and book your next appointment in minutes.',
        // admin: views/welcome/welcome.jsx button - the /welcome screen a
        // fresh install redirects to (see checkInitFile()'s .catch() in
        // context/path-logout.jsx), confirmed rendering as its raw key on
        // a deployment whose translations table lacks this row entirely.
        'Go.to.installation' => 'Go to installation',
        // admin: views/dashboard/bookings-revenue-chart.jsx - revenue-over-time
        // widget added to the admin/seller dashboard
        'revenue.over.time' => 'Revenue over time',
        'total.revenue'     => 'Total revenue',
        // web: shops/[id]/components/location/location.tsx - branch
        // switcher heading, shown on multi-branch shops' pages. Renamed
        // from 'other.locations' (seeded the same day) once the switcher
        // itself started reading it as 'our.locations' - these are a
        // shop's own branches, not somebody else's.
        'our.locations'     => 'Our locations',
        // admin: components/shop/location-select.jsx - the branch alias
        // field on Service/Product Location forms
        'location.name'             => 'Location name',
        'location.name.placeholder' => 'e.g. Bonanjo - Main Studio',
        // admin: views/settings/general-settings/setting.jsx - optional
        // logo variant for dark backgrounds/dark mode, newly wired up on
        // both admin (sidebar) and web (header, footer)
        'dark.logo' => 'Dark mode logo',
        // admin: views/settings/general-settings/setting.jsx - the shared
        // flat/percentage toggle governing both service_fee and
        // booking_service_fee
        'service_fee_type' => 'Service fee type',
        'fixed'             => 'Fixed',
        'percentage'        => 'Percentage',
        // web: shops/[id]/components/branch-gate/branch-gate.tsx - required
        // branch picker shown when a multi-branch shop is entered with no
        // location context (backend's LOCATION_AMBIGUOUS case)
        'choose.a.branch'             => 'Choose a branch',
        'choose.a.branch.description' => 'This shop has multiple locations - please select one to continue booking.',
    ];

    public function run(): void
    {
        foreach (self::TRANSLATIONS as $key => $value) {
            Translation::firstOrCreate(
                ['locale' => 'en', 'group' => 'web', 'key' => $key],
                ['value' => $value]
            );
        }

        // The 'other.locations' row this seeder used to create is no
        // longer read anywhere - clean it up rather than leave a stale,
        // unused row once a deploy re-runs this seeder.
        Translation::where(['locale' => 'en', 'group' => 'web', 'key' => 'other.locations'])->delete();

        // 'error.descriptoin' was a misspelled duplicate seeded alongside
        // the correctly-spelled 'error.description' to paper over a typo
        // in app/error.tsx and its gallery-page copy (both now fixed to
        // read the correct key) - drop the stale duplicate on a reseed.
        Translation::where(['locale' => 'en', 'group' => 'web', 'key' => 'error.descriptoin'])->delete();
    }
}
