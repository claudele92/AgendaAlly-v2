<?php

namespace Database\Seeders;

use App\Helpers\Utility;
use App\Models\Shop;
use App\Models\ShopWorkingDay;
use Illuminate\Database\Seeder;

class ShopWorkingDaysDemoSeeder extends Seeder
{
    /**
     * Seeds real business hours for every demo shop. Previously every shop
     * had 0 shop_working_days rows, so the storefront's working-hours
     * widget had nothing to render. updateOrCreate so a later reseed
     * doesn't clobber hours a seller has since edited via the UI.
     *
     * @return void
     */
    public function run(): void
    {
        $weekday = ['from' => '09:00', 'to' => '18:00', 'disabled' => false];
        $sunday  = ['from' => '09:00', 'to' => '18:00', 'disabled' => true];

        Shop::pluck('id')->each(function (int $shopId) use ($weekday, $sunday) {
            foreach (Utility::DAYS as $day) {
                ShopWorkingDay::updateOrCreate(
                    ['shop_id' => $shopId, 'day' => $day],
                    $day === Utility::SUNDAY ? $sunday : $weekday
                );
            }
        });
    }
}
