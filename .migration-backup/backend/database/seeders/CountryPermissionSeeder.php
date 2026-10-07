<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CountryPermission;
use Illuminate\Database\Seeder;
use Throwable;

class CountryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Vendors/shops within the country
            ['key' => 'vendors.view',   'group' => 'vendors',   'label' => 'View vendors/shops'],
            ['key' => 'vendors.manage', 'group' => 'vendors',   'label' => 'Approve, reject, and edit shops'],

            // E-commerce orders
            ['key' => 'orders.view',    'group' => 'orders',    'label' => 'View orders'],
            ['key' => 'orders.manage',  'group' => 'orders',    'label' => 'Manage order status and refunds'],

            // Service bookings
            ['key' => 'bookings.view',   'group' => 'bookings',  'label' => 'View bookings'],
            ['key' => 'bookings.manage', 'group' => 'bookings',  'label' => 'Manage booking status'],

            // Money movement
            ['key' => 'transactions.view',   'group' => 'transactions', 'label' => 'View transactions and payouts'],
            ['key' => 'transactions.manage', 'group' => 'transactions', 'label' => 'Manage payouts and refunds'],

            // Reporting/analytics
            ['key' => 'reports.view', 'group' => 'reports', 'label' => 'View reports and dashboard analytics'],

            // Country-level staff/role management (the permission system itself)
            ['key' => 'staff.view',         'group' => 'staff', 'label' => 'View country staff and invitations'],
            ['key' => 'staff.invite',       'group' => 'staff', 'label' => 'Invite new country staff'],
            ['key' => 'staff.roles.manage', 'group' => 'staff', 'label' => 'Create, edit, and delete country roles'],

            // Product catalog moderation within the country
            ['key' => 'products.view',   'group' => 'products', 'label' => 'View products and catalog'],
            ['key' => 'products.manage', 'group' => 'products', 'label' => 'Moderate products, brands, categories'],

            // Customer reviews
            ['key' => 'reviews.view',   'group' => 'reviews', 'label' => 'View shop reviews'],
            ['key' => 'reviews.manage', 'group' => 'reviews', 'label' => 'Moderate shop reviews'],

            // Support tickets tied to an order in the country
            ['key' => 'tickets.view',   'group' => 'tickets', 'label' => 'View support tickets'],
            ['key' => 'tickets.manage', 'group' => 'tickets', 'label' => 'Respond to and resolve support tickets'],

            // Country logistics infrastructure (warehouses, delivery points)
            ['key' => 'geography.manage', 'group' => 'geography', 'label' => 'Manage warehouses, delivery points, cities, areas'],

            // Marketing (coupons, bonuses, memberships, ads) within the country
            ['key' => 'marketing.view',   'group' => 'marketing', 'label' => 'View marketing campaigns'],
            ['key' => 'marketing.manage', 'group' => 'marketing', 'label' => 'Manage coupons, bonuses, memberships, ads'],

            // Platform currency catalog — not actually country-scoped data
            // (Currency has no country_id), but reuses this same catalog/
            // middleware rather than building a parallel platform-permission
            // system for one feature area. Only ever granted to a
            // platform-wide role (country_id = NULL, e.g. Main Accountant —
            // see DemoStaffRolesSeeder) or held via the superadmin bypass.
            ['key' => 'currency.view',   'group' => 'currency', 'label' => 'View currencies'],
            ['key' => 'currency.manage', 'group' => 'currency', 'label' => 'Create, edit, and set default/rate for currencies'],
        ];

        foreach ($permissions as $permission) {
            try {
                CountryPermission::updateOrCreate(['key' => $permission['key']], $permission);
            } catch (Throwable) {
            }
        }
    }
}
