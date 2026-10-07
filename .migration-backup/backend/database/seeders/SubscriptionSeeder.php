<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\ShopSubscription;
use App\Models\Subscription;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubscriptionSeeder extends Seeder
{
    use Loggable;

    // See DemoAfricaSeeder / UserSeeder — the Cameroon demo seller's shop.
    private const CAMEROON_SELLER_USER_ID = 107;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Prices are written as "real-world $ * 600" (600 = the currency:
        // rebase-to-xaf factor, see CurrencySeeder) - a bare 100.00 here
        // would mean 100 XAF (~$0.17) now that XAF is the base currency,
        // not $100.
        $data =  [
            [
                'id'            => 97,
                'title'         => 'Starter',
                'type'          => 'orders',
                'price'         => 100.00 * 600,
                'product_limit' => 1000,
                'order_limit'   => 1000,
                'booking_limit' => 1000,
                'with_report'   => 0,
                'month'         => 1,
                'active'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id'            => 98,
                'title'         => 'Growth',
                'type'          => 'orders',
                'price'         => 250.00 * 600,
                'product_limit' => 3000,
                'order_limit'   => 3000,
                'booking_limit' => 3000,
                'with_report'   => 1,
                'month'         => 3,
                'active'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id'            => 99,
                'title'         => 'Pro',
                'type'          => 'orders',
                'product_limit' => 6000,
                'order_limit'   => 6000,
                'booking_limit' => 6000,
                'with_report'   => 1,
                'price'         => 450.00 * 600,
                'month'         => 6,
                'active'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id'            => 100,
                'title'         => 'Enterprise',
                'product_limit' => 12000,
                'order_limit'   => 12000,
                'booking_limit' => 12000,
                'with_report'   => 1,
                'type'          => 'orders',
                'price'         => 800.00 * 600,
                'month'         => 12,
                'active'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ];

        foreach ($data as $item) {
            try {
                Subscription::updateOrCreate(['id' => $item['id']], $item);
            } catch (Throwable $e) {
                $this->error($e);
            }
        }
    }

    /**
     * Puts the Cameroon demo seller (shop 501, see DemoAfricaSeeder) on the
     * Growth plan, mirroring the exact fields ShopSubscriptionService::update()
     * sets for a real subscribe action rather than inventing a parallel shape.
     *
     * Called separately from DatabaseSeeder, after UserSeeder/DemoAfricaSeeder
     * have created the shop — SubscriptionSeeder itself runs before both, so
     * doing this from run() would silently no-op on a fresh migrate:fresh --seed.
     */
    public static function subscribeCameroonSeller(): void
    {
        $shop = Shop::where('user_id', self::CAMEROON_SELLER_USER_ID)->first();
        $growth = Subscription::find(98);

        if (!$shop || !$growth) {
            return;
        }

        try {
            ShopSubscription::updateOrCreate(['shop_id' => $shop->id], [
                'subscription_id' => $growth->id,
                'expired_at'      => now()->addMonths($growth->month),
                'price'           => $growth->price,
                'type'            => $growth->type,
                'active'          => true,
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['code' => $e->getCode(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }
}
