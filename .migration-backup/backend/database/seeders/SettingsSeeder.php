<?php

namespace Database\Seeders;

use App\Models\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $items = [
            [
                // This is a real, single-tenant deployment, not a public
                // demo instance - true here silently disables Save buttons
                // and other write actions across ~33 admin screens (see
                // admin/src/helpers/useDemo.js), which is exactly the
                // "Save not clickable" bug this flag caused.
                'key'   => 'is_demo',
                'value' => false
            ]
        ];

        foreach ($items as $item) {
            Settings::updateOrCreate([
                'key'   => data_get($item, 'key'),
            ], [
                'value' => data_get($item, 'value')
            ]);
        }

        // Hero/footer copy for the storefront (web/components/footer)
        // and platform lat/long (used as the search page's fallback
        // "where am I" center when no explicit address is set - see
        // shops.tsx/marker-cluster.tsx). Previously blank, which made that
        // fallback resolve to (0,0) ("null island") instead of anywhere
        // near the demo shops. firstOrCreate, same reasoning as
        // google_map_key below: once a superadmin edits these via the UI,
        // a later reseed must not silently overwrite their value.
        $firstOrCreateItems = [
            'title'       => 'AgendaAlly',
            'description' => 'Discover trusted local businesses, book services, and shop products with AgendaAlly.',
            'footer_text' => '© ' . date('Y') . ' AgendaAlly. All rights reserved.',
            // Also previously blank - web/app/.../contact/page.tsx renders
            // these directly (phone as a tel: link, address as the map
            // link's label), so a fresh install showed an empty Contact
            // page even after that page's own null-safety crash was fixed.
            // Same Yaoundé market as the default country/city below.
            // These owner-supplied values remain configurable. Never seed
            // fabricated contact details or unofficial social/store URLs.
            'phone'       => '',
            'address'     => '',
            'instagram'   => '',
            'facebook'    => '',
            'twitter'     => '',
            'linkedin'    => '',
            'customer_app_ios'     => '',
            'customer_app_android' => '',
            // Douala, Cameroon - same coordinates as the Cameroon demo shop
            // (see UserSeeder), a reasonable "platform home base" default
            // given this seed's demo geography is Cameroon/Burkina Faso.
            'latitude'    => '4.0511',
            'longitude'   => '9.7679',
        ];

        foreach ($firstOrCreateItems as $key => $value) {
            Settings::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // Default country/city the storefront falls back to for a visitor
        // with no country selected yet (see web/app/layout.tsx, which reads
        // these 5 keys to seed the zustand address store on first render).
        // Cameroon/Yaoundé - the platform's most-established demo market
        // (shop 501, the original multi-branch shop). firstOrCreate: once a
        // superadmin picks a different default via Settings -> General
        // Settings -> Default Country/City, a later reseed must not
        // silently revert it.
        $defaultCountryItems = [
            'default_region_id'     => '1',
            'default_country_id'    => '1',
            'default_country_title' => 'Cameroon',
            'default_city_id'       => '2',
            'default_city_title'    => 'Yaoundé',
        ];

        foreach ($defaultCountryItems as $key => $value) {
            Settings::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // The General Settings screen's remaining fields are ALL marked
        // required (see admin/src/views/settings/general-settings/
        // setting.jsx) - every single one of them was completely unseeded,
        // meaning the form couldn't actually be saved at all (antd blocks
        // submission until every required field validates), regardless of
        // which of these a superadmin actually cares about changing.
        // Reasonable starting defaults, all in XAF (the platform's base
        // currency - see CurrencySeeder) where a monetary amount is called
        // for; firstOrCreate for the same reason as everything else in this
        // method - a superadmin's own edit must survive a later reseed.
        $requiredGeneralSettings = [
            // A flat per-booking platform fee.
            'service_fee'                        => (string) (5 * 600),
            // Minimum order amount (this screen is shared with the
            // template's product/e-commerce side - see min_amount's use
            // alongside deliveryman_order_acceptance_time below).
            'min_amount'                          => (string) (10 * 600),
            // How many days ahead a customer can book an appointment.
            'max_day_booking'                     => '30',
            // Hours before a booking's start time within which cancelling
            // still qualifies for a refund.
            'booking_refund_canceled_hour'        => '24',
            // A flat per-booking fee, distinct from the general service_fee
            // above (this one's specific to the booking flow).
            'booking_service_fee'                 => (string) (2 * 600),
            // Percentage commission withheld on a canceled booking - the
            // only "commission" concept this settings screen exposes; it's
            // scoped to cancellations, not a blanket cut on every
            // transaction.
            'booking_canceled_commission'         => '10',
            // '0' = 24-hour format, '1' = 12-hour - see setting.jsx's
            // Select options. 24-hour matches regional convention across
            // this platform's seeded countries (Cameroon, Burkina Faso,
            // Nigeria, Ghana).
            'using_12_hour_format'                => '0',
            // Minutes a deliveryman has to accept an order before it's
            // reassigned - must be one of deliveryman_time.js's Time
            // options (5-55 in steps of 5).
            'deliveryman_order_acceptance_time'   => '15',
        ];

        foreach ($requiredGeneralSettings as $key => $value) {
            Settings::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // ai_api_key is also required on this same form (setting.jsx's own
        // validator rejects an empty/whitespace value outright, unlike the
        // Upload fields below it, which are visually marked required but
        // don't actually block form submission) - so it was ALSO silently
        // blocking Save, on top of the 8 fields above. No real key to seed
        // here (this isn't a business default like service_fee - it's a
        // literal external API credential), so this follows the same
        // obviously-a-placeholder convention already used in web/.env
        // (NEXT_PUBLIC_API_KEY=NEXT_PUBLIC_API_KEY) rather than inventing
        // something that looks like a real key. Set AI_API_KEY in this
        // app's real .env to have a fresh `db:seed` populate a real value;
        // firstOrCreate so a superadmin's own entry survives a later reseed.
        Settings::firstOrCreate([
            'key' => 'ai_api_key',
        ], [
            'value' => env('AI_API_KEY', 'ai_api_key'),
        ]);

        // google_map_key is read by admin/, web/ and the mobile app straight off
        // this same key/value settings table (no dedicated column - see
        // Settings::class) to init their Google Maps SDK/geocoding widgets; the
        // backend itself never calls Google's API with it. Historically this
        // row only ever got created by a superadmin filling in Settings ->
        // General Settings -> Location, which means it's blank on every fresh
        // deploy until someone remembers to re-enter it by hand.
        //
        // Set GOOGLE_MAP_KEY in this app's real .env (there's no backend
        // .env.example to add it to - this comment is the documentation) to
        // have a fresh `db:seed` populate it automatically. Deliberately
        // firstOrCreate, not updateOrCreate like the loop above: once a
        // superadmin has edited this via the UI, a later reseed must not
        // silently overwrite their value back to the env var (or blank it,
        // if the env var isn't set on that later run).
        Settings::firstOrCreate([
            'key' => 'google_map_key',
        ], [
            'value' => env('GOOGLE_MAP_KEY', ''),
        ]);
    }
}
