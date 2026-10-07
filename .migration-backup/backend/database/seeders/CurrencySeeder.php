<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Throwable;

class CurrencySeeder extends Seeder
{
    use Loggable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // XAF is the platform's base/default currency (rate=1) - see the
        // currency:rebase-to-xaf command and its PR for the full rationale
        // and the one-time data migration this mirrors. Every other rate
        // below is written as "old USD-relative rate / 600" (600 being
        // XAF's own old USD-relative rate, i.e. the same rebase factor
        // that command used) rather than a pre-computed decimal, so the
        // ratio between any two non-base currencies stays provably
        // identical to what it was before the rebase - the exact math
        // already live-verified (a $15 service correctly round-trips to
        // 9000 XAF -> 15.00 USD -> 13.80 EUR).
        //
        // This seeder previously planted USD as rate=1/default, which
        // silently reverted the rebase on every migrate:fresh --seed -
        // the one-time command only ever updated rows that already
        // existed, never what a fresh install produces. DemoServiceCatalogSeeder's
        // SERVICES prices, SubscriptionSeeder's plan prices, and
        // DemoAfricaSeeder's delivery price are seeded pre-multiplied by
        // the same factor for the same reason - a fresh install must
        // produce XAF-denominated catalog data from the start, not
        // USD-scale numbers under an XAF-default label (the exact
        // relabel-without-converting mismatch the rebase itself was
        // fixing).
        $currencies = [
            [
                'id' => 2,
                'symbol' => '$',
                'title' => 'USD',
                'rate' => 1 / 600,
                'default' => 0,
                'active' => 1,
            ],
            // Demo data for the Cameroon/Burkina Faso seed (DemoAfricaSeeder)
            // — two distinct CFA franc zones, pegged to each other but not
            // interchangeable currencies. No hardcoded id: unlike USD above
            // there's no existing reference to preserve, so keying by title
            // avoids any risk of id collisions.
            [
                'symbol' => 'FCFA',
                'title' => 'XAF',
                'rate' => 1.0,
                'default' => 1,
                'active' => 1,
            ],
            [
                'symbol' => 'FCFA',
                'title' => 'XOF',
                'rate' => 600 / 600,
                'default' => 0,
                'active' => 1,
            ],
            // Additional demo currencies — rates are approximate demo
            // values, not live/maintained FX rates, same caveat as
            // XAF/XOF above.
            [
                'symbol' => '€',
                'title' => 'EUR',
                'rate' => 0.92 / 600,
                'default' => 0,
                'active' => 1,
            ],
            [
                'symbol' => '₦',
                'title' => 'NGN',
                'rate' => 1550 / 600,
                'default' => 0,
                'active' => 1,
            ],
            [
                'symbol' => 'GH₵',
                'title' => 'GHS',
                'rate' => 15 / 600,
                'default' => 0,
                'active' => 1,
            ],
            [
                'symbol' => 'CA$',
                'title' => 'CAD',
                'rate' => 1.37 / 600,
                'default' => 0,
                'active' => 1,
            ],
            [
                'symbol' => '£',
                'title' => 'GBP',
                'rate' => 0.79 / 600,
                'default' => 0,
                'active' => 1,
            ],
            [
                'symbol' => 'FC',
                'title' => 'CDF',
                'rate' => 2870 / 600,
                'default' => 0,
                'active' => 1,
            ],
            [
                'symbol' => 'KSh',
                'title' => 'KES',
                'rate' => 129 / 600,
                'default' => 0,
                'active' => 1,
            ],
        ];

        foreach ($currencies as $currency) {
            $key = array_key_exists('id', $currency) ? ['id' => $currency['id']] : ['title' => $currency['title']];

            try {
                Currency::updateOrCreate($key, $currency);
            } catch (Throwable $e) {
                $this->error($e);
            }
        }

    }
}
