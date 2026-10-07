<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * One-time rebase of the platform's base/default currency from USD to XAF.
 *
 * Every raw price-bearing column in this app is implicitly denominated in
 * whatever currency is flagged default=1 (see Currency::convert(): amount *
 * (toRate/fromRate), fromRate defaults to the default currency's rate).
 * Flipping the default flag alone would silently reinterpret every existing
 * price's real-world meaning (a service stored as "15" meant $15; the same
 * "15" under an XAF-default reading means ~$0.025) - the exact mislabeling
 * bug class already found and fixed once for Subscription/AdsPackage
 * display (see commit 7d3c2e7's "platform-wide price mislabeling" revert).
 *
 * So this multiplies every affected raw column by XAF's current rate
 * (600, read live rather than hardcoded, in case it's changed by the time
 * this runs) BEFORE rescaling the currencies table itself - preserving
 * every existing price's real-world value exactly: a service stored as 15
 * (meaning $15) becomes 9000 (meaning 9000 XAF, still $15 once XAF.rate=1
 * and USD.rate=1/600).
 *
 * Scope is every column identified in the currency-rebase investigation,
 * split into three groups:
 *
 * - Group A: live-converted per request today via SetCurrency/Currency::
 *   convert() (services, service_masters, stocks, subscriptions,
 *   ads_packages, auctions, auction_users, bonuses.value WHERE type=sum,
 *   wholesale_prices, wallets, wallet_histories, service_master_prices
 *   (+ its JSON smart.value WHERE smart.value_type=fix), coupons WHERE
 *   type=fix, cart_detail_products, carts.total_price, parcel_order_settings,
 *   transactions WHERE payable_type is one of the Group A/B model classes).
 * - Group B: raw catalog-like values, not currently live-converted anywhere,
 *   but the same "default currency units" convention (service_extras,
 *   delivery_prices, gift_carts, user_gift_carts, points WHERE type=fix
 *   (price) / unconditionally (value, compared directly against already-
 *   rebased order/booking totals), member_ships, user_member_ships,
 *   shop_deliveryman_settings WHERE type=fix,
 *   shop_subscriptions, point_histories).
 * - Denormalized caches: shops.min_price/max_price/service_min_price/
 *   service_max_price, products.min_price/max_price - multiplied directly
 *   by the same factor rather than recomputed from the (already-rebased)
 *   underlying tables, since uniform scaling preserves every MIN/MAX
 *   relationship exactly; recomputing via the app's own aggregate helpers
 *   risks picking up their own unrelated quirks (e.g. ProductAdditionalService
 *   sets Shop.min_price/max_price from whichever product was last touched,
 *   not a true shop-wide aggregate) as a side effect of this migration.
 *
 * Deliberately NEVER touched - historical/frozen records that carry their
 * own currency_id+rate captured at transaction time, and everything that
 * copies from them:
 * - orders, bookings, payouts (own currency_id/rate columns)
 * - booking_coupons, order_coupons, booking_extras, booking_extra_times
 *   (frozen line-items created once from Order/Booking's own rate context)
 * - transactions WHERE payable_type IN (Order::class, Booking::class)
 *
 * Everything in this list must move together in one pass (one DB
 * transaction) - a partial run would leave some raw values in old-USD
 * terms and others in new-XAF terms with no way to tell them apart later,
 * which is the same "different consumers reading disagreeing sources of
 * truth" failure PR #60 caused (see 7d3c2e7).
 *
 * --dry-run performs zero writes: it only counts affected rows per table
 * (and, for bonuses/discounts/points/shop_deliveryman_settings, prints the
 * type breakdown) so the exact scope can be reviewed before this is ever
 * run for real.
 */
class RebaseCurrencyToXaf extends Command
{
    protected $signature = 'currency:rebase-to-xaf {--dry-run : Report what would change without writing anything}';

    protected $description = 'One-time rebase of every raw price column from the current default currency to XAF';

    /** @var array<int, array{table: string, column: string, where?: array}> */
    private array $simpleColumns = [
        // Group A
        ['table' => 'services', 'column' => 'price'],
        ['table' => 'service_masters', 'column' => 'price'],
        ['table' => 'service_masters', 'column' => 'discount'],
        ['table' => 'stocks', 'column' => 'price'],
        ['table' => 'subscriptions', 'column' => 'price'],
        ['table' => 'ads_packages', 'column' => 'price'],
        ['table' => 'auctions', 'column' => 'min_price'],
        ['table' => 'auction_users', 'column' => 'price'],
        ['table' => 'bonuses', 'column' => 'value', 'where' => ['type' => 'sum']],
        ['table' => 'whole_sale_prices', 'column' => 'price'],
        ['table' => 'wallets', 'column' => 'price'],
        ['table' => 'wallet_histories', 'column' => 'price'],
        ['table' => 'service_master_prices', 'column' => 'price'],
        ['table' => 'coupons', 'column' => 'price', 'where' => ['type' => 'fix']],
        ['table' => 'cart_detail_products', 'column' => 'price'],
        ['table' => 'cart_detail_products', 'column' => 'discount'],
        ['table' => 'carts', 'column' => 'total_price'],
        ['table' => 'parcel_order_settings', 'column' => 'price'],
        ['table' => 'parcel_order_settings', 'column' => 'price_per_km'],
        ['table' => 'parcel_order_settings', 'column' => 'special_price'],
        ['table' => 'parcel_order_settings', 'column' => 'special_price_per_km'],

        // Group B
        ['table' => 'service_extras', 'column' => 'price'],
        ['table' => 'delivery_prices', 'column' => 'price'],
        ['table' => 'gift_carts', 'column' => 'price'],
        ['table' => 'user_gift_carts', 'column' => 'price'],
        ['table' => 'points', 'column' => 'price', 'where' => ['type' => 'fix']],
        ['table' => 'points', 'column' => 'value'],
        ['table' => 'member_ships', 'column' => 'price'],
        ['table' => 'user_member_ships', 'column' => 'price'],
        ['table' => 'shop_deliveryman_settings', 'column' => 'value', 'where' => ['type' => 'fix']],
        ['table' => 'shop_subscriptions', 'column' => 'price'],
        ['table' => 'point_histories', 'column' => 'price'],

        // Denormalized caches - see class docblock for why these are
        // multiplied directly rather than recomputed.
        ['table' => 'shops', 'column' => 'min_price'],
        ['table' => 'shops', 'column' => 'max_price'],
        ['table' => 'shops', 'column' => 'service_min_price'],
        ['table' => 'shops', 'column' => 'service_max_price'],
        ['table' => 'products', 'column' => 'min_price'],
        ['table' => 'products', 'column' => 'max_price'],
    ];

    /** payable_type values whose transactions.price must be rebased alongside their payable. */
    private const TRANSACTION_PAYABLE_TYPES_TO_REBASE = [
        'App\\Models\\Wallet',
        'App\\Models\\WalletHistory',
        'App\\Models\\ShopSubscription',
        'App\\Models\\ShopAdsPackage',
        'App\\Models\\UserMemberShip',
        'App\\Models\\UserGiftCart',
        'App\\Models\\AuctionUser',
    ];

    /** payable_type values that must NEVER be touched - frozen historical records. */
    private const TRANSACTION_PAYABLE_TYPES_TO_SKIP = [
        'App\\Models\\Order',
        'App\\Models\\Booking',
    ];

    public function handle(): int
    {
        $factor = (float) DB::table('currencies')->where('title', 'XAF')->value('rate');

        if ($factor <= 0) {
            $this->error('XAF currency row not found (or its rate is <= 0) - aborting.');

            return self::FAILURE;
        }

        $this->info("Rebase factor (XAF's current rate): $factor");

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->reportDryRun($factor);

            return self::SUCCESS;
        }

        try {
            DB::transaction(function () use ($factor) {
                foreach ($this->simpleColumns as $spec) {
                    $this->multiplyColumn($spec['table'], $spec['column'], $factor, $spec['where'] ?? []);
                }

                $this->rebaseTransactions($factor);
                $this->rebaseServiceMasterPriceSmart($factor);
                $this->rescaleCurrencies($factor);
            });
        } catch (Throwable $e) {
            $this->error('Rebase failed and was rolled back: ' . $e->getMessage());

            return self::FAILURE;
        }

        // Currency::currenciesList() (used by Currency::convert() and the
        // SetCurrency trait - i.e. every conversion in the app) caches its
        // result for 24h (Currency::TTL). Left alone, every conversion
        // would keep computing against the pre-rebase rates for up to a
        // day after this command reports success - a live-verified miss
        // caught while testing this exact command: Currency::convert()
        // returned an unconverted raw amount immediately after a real run,
        // until this cache was cleared.
        Cache::forget('currencies-list');

        $this->info('Currency rebase to XAF complete.');

        return self::SUCCESS;
    }

    private function multiplyColumn(string $table, string $column, float $factor, array $where): void
    {
        $query = DB::table($table);

        foreach ($where as $col => $value) {
            $query->where($col, $value);
        }

        $affected = $query->update([$column => DB::raw("`$column` * $factor")]);

        $this->line("  $table.$column" . ($where ? ' [' . json_encode($where) . ']' : '') . ": $affected row(s)");
    }

    private function rebaseTransactions(float $factor): void
    {
        $affected = DB::table('transactions')
            ->whereIn('payable_type', self::TRANSACTION_PAYABLE_TYPES_TO_REBASE)
            ->update(['price' => DB::raw("`price` * $factor")]);

        $this->line("  transactions.price [rebase-eligible payable_type]: $affected row(s)");

        $skipped = DB::table('transactions')
            ->whereIn('payable_type', self::TRANSACTION_PAYABLE_TYPES_TO_SKIP)
            ->count();

        $this->line("  transactions.price [Order/Booking payable_type, left untouched]: $skipped row(s)");

        $other = DB::table('transactions')
            ->whereNotIn('payable_type', array_merge(
                self::TRANSACTION_PAYABLE_TYPES_TO_REBASE,
                self::TRANSACTION_PAYABLE_TYPES_TO_SKIP
            ))
            ->distinct()
            ->pluck('payable_type');

        if ($other->isNotEmpty()) {
            $this->warn('  Unclassified payable_type values found in transactions (left untouched, review before relying on this): ' . $other->implode(', '));
        }
    }

    /**
     * service_master_prices.smart is a JSON column: {type, value_type, value}.
     * Only value_type='fix' rows carry a money delta in `value` - 'percent'
     * rows must never be scaled. Not expressible as a single SQL UPDATE, so
     * handled row-by-row.
     */
    private function rebaseServiceMasterPriceSmart(float $factor): void
    {
        $rows = DB::table('service_master_prices')->whereNotNull('smart')->get(['id', 'smart']);
        $touched = 0;

        foreach ($rows as $row) {
            $smart = json_decode($row->smart, true);

            if (!is_array($smart) || ($smart['value_type'] ?? null) !== 'fix' || !isset($smart['value'])) {
                continue;
            }

            $smart['value'] = (float) $smart['value'] * $factor;

            DB::table('service_master_prices')
                ->where('id', $row->id)
                ->update(['smart' => json_encode($smart)]);

            $touched++;
        }

        $this->line("  service_master_prices.smart.value [value_type=fix]: $touched row(s)");
    }

    /**
     * Every currency's rate must be divided by the same factor, not just
     * USD's - the ratio between any two non-base currencies (EUR, NGN,
     * GHS, CAD, GBP, CDF, KES, XOF, ...) must stay economically consistent.
     * Dividing every row uniformly by XAF's own current rate makes XAF's
     * new rate exactly 1.0 with no special case needed.
     */
    private function rescaleCurrencies(float $factor): void
    {
        DB::table('currencies')->update(['rate' => DB::raw("`rate` / $factor")]);
        DB::table('currencies')->update(['default' => false]);
        $flipped = DB::table('currencies')->where('title', 'XAF')->update(['default' => true]);

        $this->line("  currencies.rate: rescaled all rows by 1/$factor; default flipped to XAF ($flipped row updated)");
    }

    private function reportDryRun(float $factor): void
    {
        $this->info('--- DRY RUN: no rows will be written ---');

        foreach ($this->simpleColumns as $spec) {
            $query = DB::table($spec['table']);

            foreach ($spec['where'] ?? [] as $col => $value) {
                $query->where($col, $value);
            }

            $count = $query->count();
            $label = $spec['table'] . '.' . $spec['column'] . (isset($spec['where']) ? ' [' . json_encode($spec['where']) . ']' : '');
            $this->line("  $label: $count row(s) would be multiplied by $factor");
        }

        $rebase = DB::table('transactions')->whereIn('payable_type', self::TRANSACTION_PAYABLE_TYPES_TO_REBASE)->count();
        $skip = DB::table('transactions')->whereIn('payable_type', self::TRANSACTION_PAYABLE_TYPES_TO_SKIP)->count();
        $this->line("  transactions.price [rebase-eligible]: $rebase row(s) would be multiplied");
        $this->line("  transactions.price [Order/Booking, untouched]: $skip row(s) would be left alone");

        $smartFixCount = DB::table('service_master_prices')
            ->whereNotNull('smart')
            ->get(['smart'])
            ->filter(fn ($row) => (json_decode($row->smart, true)['value_type'] ?? null) === 'fix')
            ->count();
        $this->line("  service_master_prices.smart.value [value_type=fix]: $smartFixCount row(s) would be multiplied");

        $currencyCount = DB::table('currencies')->count();
        $this->line("  currencies.rate: all $currencyCount row(s) would be divided by $factor; default would flip to XAF");
    }
}
