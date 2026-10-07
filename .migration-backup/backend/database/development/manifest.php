<?php

declare(strict_types=1);

return [
    'schema_version' => 3,
    'migration_count' => 229,
    'migration_set_sha256' => '37954929be028d7e487a7720f2108d3f300b2862af85f46b724b3c9f63d3f896',
    'accepted_previous_migration_set_sha256' => [
        // Manual finance adds one reviewed migration; historical files unchanged.
        '7102bbf790eb27f2262901cb605b0ec27c57bbc49dba4562c4bda10a7a68e918',
        // Owner-approved Subscription default; data-only, prior files unchanged.
        'e7644e771af0141becc984ff7d3e533d773e9613e19f86bf3b4540688752064b',
        // Bounded presentation-default provisioning; no historical/schema changes.
        'f70ab7e3a086c95ab69c0bf28cad6502cd7c4d2541ce630aceff79ae64ebbdb3',
        // Approved additive directory-save replay evidence; historical source unchanged.
        'cee7850f2d399c2f10d469a00fbfb1dc3d6e33180a5a37bd239576b443c3e8ba',
        // The selected-email outbox is additive and reviewed; ordinary serving
        // must accept the existing marker without running any owned migration.
        'fa928a7554e342818b989b2c41dc00129970c25fe652b435771b6e99c43aea9a',
        'fdde0c303d46c6034a6b34fdcb28476173e236f54a869cff59aca00ee2635389',
        '69f670efcff4c783d60a0f92ee936bdd075a63e9a9ac608b860487c0e773327b',
        '2af599cfb1df35fe623c77352838416fd5a6d4bea1e1d7a247bdac5f1d10bf16',
        '844b60d4a89331546ee4b49377297e1ed6cd158a1790c04da0727266a692c83c',
        '9fe33f974eb6083ffd10a8dbe35dd4e4dca1f172b0863ba92edbe053599a7824',
        'c098312f5b37d2c3683549bad04d2773e0dfd0e41c013f05666eef80852e1be5',
        '8d2dbaceeee2ecf3c4a242a12828d418a6019b44b127c88ee337495a9f764a50',
        '1d77166d58b32d45c0179098b0271c537448c72989aa18e75292f95db5770d03',
        'e99cd20671527290ae416aa416f92c5802c7341de182c63e05b272e1c7c01a8c',
        'e18c3009bc46b32ccbb2dbb5a3d5ed8c56e65276b0bdb7d14bea50324444b471',
        'bf994251e0cb8ef0724587fffda0e5e62ec3dca5b4fb4cc327a625c7c6554e46',
        '6bcdce02129c7c870cc44bc34f58b8266c0c27fb213195023c61dfad087f6b5a',
    ],
    'latest_reviewed_migration' => '2026_09_27_010000_add_collect_via_platform_to_bookings_table',
    'approved_incremental_migrations' => [
        '2026_10_04_010000_add_seller_client_save_intents',
        '2026_09_28_010000_add_shop_scoped_booking_clients',
        '2026_10_02_010000_add_driver_active_to_invitations',
        '2026_10_03_010000_extend_invitations_for_delivery_driver_onboarding',
        '2026_10_04_010000_add_product_fulfillment_methods_to_shops',
        '2026_10_05_010000_add_native_product_pickup_scheduling',
        '2026_10_06_010000_add_product_fulfillment_financial_state',
        '2026_10_03_100000_create_commerce_payment_allocations',
        '2026_10_03_100100_create_payment_collection_contexts',
        '2026_10_03_100200_link_payment_accounting_evidence',
        '2026_10_03_100300_add_mtn_attempt_identity',
        '2026_10_03_100400_add_payment_completion_identity',
        // Owner-approved bounded account-email closure; operational authority only.
        '2026_10_07_010000_add_selected_email_deliveries',
        '2026_10_08_010000_provision_account_email_templates',
        '2026_10_09_010000_provision_subscription_email_template',
        // Owner-approved additive manual Refund / Vendor Payout workflow.
        '2026_10_10_010000_add_manual_financial_workflows',
    ],
    'demo_seed_version' => 1,
];