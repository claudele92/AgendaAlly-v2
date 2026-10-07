<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CountryPermission;
use App\Models\CountryRole;
use App\Models\Shop;
use App\Models\ShopPermission;
use App\Models\ShopRole;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Demo shop_roles for the seeded Cameroon shop (see DemoAfricaSeeder/
 * UserSeeder), covering common seller-side job functions, plus the one
 * country_role that isn't per-country (Main Accountant — see below). The
 * 3 per-country default roles (Country Manager, Country Accountant,
 * Support/Customer Service) are NOT seeded here; CountryObserver::created()
 * already gives every country those automatically (see DefaultCountryRoles).
 *
 * Runs after DemoAfricaSeeder (needs the shop to exist) and after
 * ShopPermissionSeeder/CountryPermissionSeeder (needs the permission
 * catalogs seeded). Every write is an updateOrCreate/sync, so this is safe
 * to run more than once.
 */
class DemoStaffRolesSeeder extends Seeder
{
    use Loggable;

    // See UserSeeder — the Cameroon demo seller's user id.
    private const CAMEROON_SELLER_USER_ID = 107;

    public function run(): void
    {
        try {
            $shop = Shop::where('user_id', self::CAMEROON_SELLER_USER_ID)->first();

            if ($shop) {
                $this->seedShopRole($shop->id, 'Moderator', ShopPermission::pluck('key')->all());
                $this->seedShopRole($shop->id, 'Receptionist', [
                    'bookings.view', 'bookings.manage', 'bookings.status', 'customers.view',
                ]);
                $this->seedShopRole($shop->id, 'Cashier', [
                    'orders.view', 'orders.manage', 'products.view', 'customers.view', 'payments.view',
                ]);
                $this->seedShopRole($shop->id, 'Branch Manager', [
                    'bookings.view', 'bookings.manage', 'bookings.status', 'bookings.availability',
                    'orders.view', 'orders.manage',
                    'payments.view', 'payments.refunds.manage',
                    'products.view', 'products.manage',
                    'marketing.view',
                ]);
            }

            // Country Manager/Country Accountant/Support/Customer Service
            // are NOT seeded here for Cameroon — CountryObserver::created()
            // already gives every new country those 3 roles automatically
            // (see DefaultCountryRoles), and DemoAfricaSeeder creates
            // Cameroon via Country::create() earlier in DatabaseSeeder, so
            // they already exist by the time this seeder runs. Seeding them
            // again here would just be a second, driftable copy of the same
            // definitions.

            // Platform-wide, not scoped to any one country — see the
            // 2026_09_07_030000 migration making country_id nullable.
            $this->seedCountryRole(null, 'Main Accountant', [
                'transactions.view', 'transactions.manage', 'reports.view',
                'currency.view', 'currency.manage',
            ]);
        } catch (Throwable $e) {
            $this->error($e);
        }
    }

    private function seedShopRole(int $shopId, string $name, array $permissionKeys): void
    {
        $role = ShopRole::updateOrCreate(['shop_id' => $shopId, 'name' => $name]);

        $ids = ShopPermission::whereIn('key', $permissionKeys)->pluck('id');
        $role->permissions()->sync($ids);
    }

    private function seedCountryRole(?int $countryId, string $name, array $permissionKeys): void
    {
        $role = CountryRole::updateOrCreate(['country_id' => $countryId, 'name' => $name]);

        $ids = CountryPermission::whereIn('key', $permissionKeys)->pluck('id');
        $role->permissions()->sync($ids);
    }
}
