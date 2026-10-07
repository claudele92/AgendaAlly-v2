<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ShopPermission;
use Illuminate\Database\Seeder;
use Throwable;

class ShopPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Bookings — appointment lifecycle + master availability
            ['key' => 'bookings.view',         'group' => 'bookings', 'label' => 'View bookings'],
            ['key' => 'bookings.manage',        'group' => 'bookings', 'label' => 'Create and edit bookings'],
            ['key' => 'bookings.status',        'group' => 'bookings', 'label' => 'Change booking status (accept/cancel)'],
            ['key' => 'bookings.availability',  'group' => 'bookings', 'label' => 'Manage master availability and working hours'],
            ['key' => 'bookings.view_all_branches', 'group' => 'bookings', 'label' => 'View bookings across all branches, not just an assigned one'],

            // Payments — payouts, gateway credentials, transactions, refunds
            ['key' => 'payments.view',              'group' => 'payments', 'label' => 'View transactions and payout history'],
            ['key' => 'payments.payouts.manage',     'group' => 'payments', 'label' => 'Request and edit payouts'],
            ['key' => 'payments.gateways.manage',    'group' => 'payments', 'label' => 'Manage payment gateway credentials'],
            ['key' => 'payments.refunds.manage',     'group' => 'payments', 'label' => 'Manage order refunds'],

            // Services — bookable-service catalog
            ['key' => 'services.view',           'group' => 'services', 'label' => 'View services'],
            ['key' => 'services.manage',          'group' => 'services', 'label' => 'Create and edit services, extras, FAQs, masters'],
            ['key' => 'services.auctions.manage', 'group' => 'services', 'label' => 'Manage service auctions'],

            // Stories — shop-attributed business updates
            ['key' => 'stories.view',   'group' => 'stories', 'label' => 'View shop Stories'],
            ['key' => 'stories.manage', 'group' => 'stories', 'label' => 'Create, edit, upload and delete shop Stories'],

            // Staff — the permission system itself
            ['key' => 'staff.view',         'group' => 'staff', 'label' => 'View staff and invitations'],
            ['key' => 'staff.invite',       'group' => 'staff', 'label' => 'Invite new staff'],
            ['key' => 'staff.roles.manage', 'group' => 'staff', 'label' => 'Create, edit, and delete shop roles'],

            // Reports — analytics/reporting (read-only)
            ['key' => 'reports.view', 'group' => 'reports', 'label' => 'View reports and dashboard analytics'],

            // Products — e-commerce catalog
            ['key' => 'products.view',   'group' => 'products', 'label' => 'View products and catalog'],
            ['key' => 'products.manage', 'group' => 'products', 'label' => 'Create and edit products, brands, categories, tags'],

            // Orders — e-commerce order fulfillment
            ['key' => 'orders.view',              'group' => 'orders', 'label' => 'View orders'],
            ['key' => 'orders.manage',             'group' => 'orders', 'label' => 'Create and edit orders'],
            ['key' => 'orders.delivery_settings',  'group' => 'orders', 'label' => 'Manage delivery pricing and deliveryman settings'],

            // Marketing — discounts, coupons, bonuses, ads
            ['key' => 'marketing.view',   'group' => 'marketing', 'label' => 'View marketing campaigns'],
            ['key' => 'marketing.manage', 'group' => 'marketing', 'label' => 'Create and edit discounts, coupons, bonuses, ads'],

            // Shop settings — shop profile, locations, hours
            ['key' => 'shop_settings.view',    'group' => 'shop_settings', 'label' => 'View shop settings'],
            ['key' => 'shop_settings.manage',  'group' => 'shop_settings', 'label' => 'Edit shop profile, locations, and hours'],

            // Customers — read-only view of the shop's customer records
            ['key' => 'customers.view', 'group' => 'customers', 'label' => "View customers' addresses, gift cards, memberships"],
        ];

        foreach ($permissions as $permission) {
            try {
                ShopPermission::updateOrCreate(['key' => $permission['key']], $permission);
            } catch (Throwable) {
            }
        }
    }
}
