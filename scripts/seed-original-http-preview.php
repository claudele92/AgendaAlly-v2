<?php
declare(strict_types=1);

/**
 * Builds only the reviewed SQLite subset needed by the original Laravel HTTP
 * routes. This intentionally does not invoke Laravel migrations or seeders.
 */

$runtime = getenv('PREVIEW_RUNTIME');
$database = getenv('PREVIEW_DB_FILE');
$credentialFile = getenv('PREVIEW_CREDENTIALS');
$expectedRuntime = dirname(__DIR__) . '/.local/agendaally-preview/backend';

if (!$runtime || realpath($runtime) !== realpath($expectedRuntime) ||
    !is_file($runtime . '/.original-http-preview-owned') ||
    !$database || realpath(dirname($database)) !== realpath($runtime . '/storage') ||
    basename($database) !== 'preview.sqlite' ||
    !$credentialFile || realpath($credentialFile) !== realpath($runtime . '/.preview-credentials')) {
    fwrite(STDERR, "Refusing to seed outside the owned original-preview runtime.\n");
    exit(1);
}

$credentials = [];
foreach (file($credentialFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
    $credentials[$key] = $value;
}
foreach (['ADMIN_EMAIL', 'ADMIN_PASSWORD', 'CUSTOMER_EMAIL', 'CUSTOMER_PASSWORD', 'MASTER_EMAIL', 'MASTER_PASSWORD'] as $key) {
    if (empty($credentials[$key])) {
        fwrite(STDERR, "Synthetic login credentials are incomplete.\n");
        exit(1);
    }
}

$pdo = new PDO('sqlite:' . $database, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec('PRAGMA journal_mode = WAL');

$schema = [
    "CREATE TABLE IF NOT EXISTS currencies (id INTEGER PRIMARY KEY, symbol TEXT, title TEXT NOT NULL, rate REAL NOT NULL DEFAULT 1, position TEXT NOT NULL DEFAULT 'after', \"default\" INTEGER NOT NULL DEFAULT 0, active INTEGER NOT NULL DEFAULT 1, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS languages (id INTEGER PRIMARY KEY, title TEXT, locale TEXT NOT NULL UNIQUE, backward INTEGER NOT NULL DEFAULT 0, \"default\" INTEGER NOT NULL DEFAULT 0, active INTEGER NOT NULL DEFAULT 1, img TEXT)",
    "CREATE TABLE IF NOT EXISTS translations (id INTEGER PRIMARY KEY, status INTEGER NOT NULL DEFAULT 1, locale TEXT NOT NULL, \"group\" TEXT NOT NULL, \"key\" TEXT NOT NULL, value TEXT, created_at TEXT, updated_at TEXT, UNIQUE(\"group\", \"key\", locale))",
    "CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY, \"key\" TEXT NOT NULL UNIQUE, value TEXT, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS regions (id INTEGER PRIMARY KEY, active INTEGER NOT NULL DEFAULT 0)",
    "CREATE TABLE IF NOT EXISTS region_translations (id INTEGER PRIMARY KEY, region_id INTEGER, locale TEXT NOT NULL, title TEXT NOT NULL, UNIQUE(region_id, locale))",
    "CREATE TABLE IF NOT EXISTS countries (id INTEGER PRIMARY KEY, region_id INTEGER, currency_id INTEGER, active INTEGER NOT NULL DEFAULT 0, img TEXT, code TEXT)",
    "CREATE TABLE IF NOT EXISTS country_translations (id INTEGER PRIMARY KEY, country_id INTEGER, locale TEXT NOT NULL, title TEXT NOT NULL, UNIQUE(country_id, locale))",
    "CREATE TABLE IF NOT EXISTS cities (id INTEGER PRIMARY KEY, active INTEGER NOT NULL DEFAULT 0, region_id INTEGER, country_id INTEGER)",
    "CREATE TABLE IF NOT EXISTS city_translations (id INTEGER PRIMARY KEY, city_id INTEGER, locale TEXT NOT NULL, title TEXT NOT NULL, UNIQUE(city_id, locale))",
    "CREATE TABLE IF NOT EXISTS areas (id INTEGER PRIMARY KEY, active INTEGER NOT NULL DEFAULT 0, region_id INTEGER, country_id INTEGER, city_id INTEGER)",
    "CREATE TABLE IF NOT EXISTS area_translations (id INTEGER PRIMARY KEY, area_id INTEGER, locale TEXT NOT NULL, title TEXT NOT NULL, UNIQUE(area_id, locale))",
    "CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, uuid TEXT NOT NULL UNIQUE, firstname TEXT NOT NULL DEFAULT 'firstname', lastname TEXT, email TEXT UNIQUE, phone TEXT UNIQUE, birthday TEXT, gender TEXT NOT NULL DEFAULT 'male', email_verified_at TEXT, phone_verified_at TEXT, ip_address TEXT, active INTEGER NOT NULL DEFAULT 1, img TEXT, password TEXT, verify_token TEXT, my_referral TEXT, referral TEXT, firebase_token TEXT, location TEXT, r_count REAL DEFAULT 0, r_avg REAL DEFAULT 0, r_sum REAL DEFAULT 0, o_count REAL DEFAULT 0, o_sum REAL DEFAULT 0, remember_token TEXT, currency_id INTEGER, lang TEXT, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS roles (id INTEGER PRIMARY KEY, name TEXT NOT NULL, guard_name TEXT NOT NULL DEFAULT 'web', created_at TEXT, updated_at TEXT, UNIQUE(name, guard_name))",
    "CREATE TABLE IF NOT EXISTS permissions (id INTEGER PRIMARY KEY, name TEXT NOT NULL, guard_name TEXT NOT NULL DEFAULT 'web', created_at TEXT, updated_at TEXT, UNIQUE(name, guard_name))",
    "CREATE TABLE IF NOT EXISTS model_has_roles (role_id INTEGER NOT NULL, model_type TEXT NOT NULL, model_id INTEGER NOT NULL, PRIMARY KEY(role_id, model_id, model_type))",
    "CREATE INDEX IF NOT EXISTS model_has_roles_model_index ON model_has_roles(model_id, model_type)",
    "CREATE TABLE IF NOT EXISTS model_has_permissions (permission_id INTEGER NOT NULL, model_type TEXT NOT NULL, model_id INTEGER NOT NULL, PRIMARY KEY(permission_id, model_id, model_type))",
    "CREATE INDEX IF NOT EXISTS model_has_permissions_model_index ON model_has_permissions(model_id, model_type)",
    "CREATE TABLE IF NOT EXISTS role_has_permissions (permission_id INTEGER NOT NULL, role_id INTEGER NOT NULL, PRIMARY KEY(permission_id, role_id))",
    "CREATE TABLE IF NOT EXISTS personal_access_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, tokenable_type TEXT NOT NULL, tokenable_id INTEGER NOT NULL, name TEXT NOT NULL, token TEXT NOT NULL UNIQUE, abilities TEXT, last_used_at TEXT, created_at TEXT, updated_at TEXT)",
    "CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_index ON personal_access_tokens(tokenable_type, tokenable_id)",
    "CREATE TABLE IF NOT EXISTS wallets (id INTEGER PRIMARY KEY, uuid TEXT NOT NULL, user_id INTEGER NOT NULL, currency_id INTEGER NOT NULL, price REAL NOT NULL DEFAULT 0, created_at TEXT, updated_at TEXT, UNIQUE(uuid, user_id))",
    "CREATE TABLE IF NOT EXISTS country_admins (id INTEGER PRIMARY KEY, user_id INTEGER, country_id INTEGER)",
    "CREATE TABLE IF NOT EXISTS country_invitations (id INTEGER PRIMARY KEY, user_id INTEGER, country_id INTEGER, country_role_id INTEGER, status INTEGER NOT NULL DEFAULT 1)",
    "CREATE TABLE IF NOT EXISTS country_payments (id INTEGER PRIMARY KEY, country_id INTEGER NOT NULL, payment_id INTEGER NOT NULL, active INTEGER NOT NULL DEFAULT 1, created_at TEXT, updated_at TEXT, UNIQUE(country_id, payment_id))",
    "CREATE TABLE IF NOT EXISTS shops (id INTEGER PRIMARY KEY, slug TEXT, uuid TEXT NOT NULL UNIQUE, user_id INTEGER NOT NULL, tax REAL NOT NULL DEFAULT 0, percentage REAL NOT NULL DEFAULT 0, lat_long TEXT, phone TEXT, open INTEGER NOT NULL DEFAULT 1, visibility INTEGER NOT NULL DEFAULT 1, background_img TEXT, logo_img TEXT, min_amount REAL NOT NULL DEFAULT 0.1, status TEXT NOT NULL DEFAULT 'new', status_note TEXT, delivery_time TEXT, delivery_type INTEGER NOT NULL DEFAULT 2, type INTEGER NOT NULL DEFAULT 1, verify INTEGER NOT NULL DEFAULT 0, r_count REAL DEFAULT 0, r_avg REAL DEFAULT 0, r_sum REAL DEFAULT 0, o_count REAL DEFAULT 0, od_count REAL DEFAULT 0, b_count INTEGER DEFAULT 0, b_sum REAL DEFAULT 0, min_price REAL DEFAULT 0, max_price REAL DEFAULT 0, service_min_price REAL DEFAULT 0, service_max_price REAL DEFAULT 0, latitude REAL, longitude REAL, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS shop_translations (id INTEGER PRIMARY KEY, shop_id INTEGER NOT NULL, locale TEXT NOT NULL, title TEXT NOT NULL, description TEXT, address TEXT, UNIQUE(shop_id, locale))",
    "CREATE TABLE IF NOT EXISTS shop_locations (id INTEGER PRIMARY KEY, shop_id INTEGER NOT NULL, region_id INTEGER NOT NULL, country_id INTEGER, city_id INTEGER, area_id INTEGER, address TEXT, alias TEXT, latitude REAL, longitude REAL, type INTEGER NOT NULL DEFAULT 1, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS shop_working_days (id INTEGER PRIMARY KEY, shop_id INTEGER NOT NULL, day TEXT NOT NULL, \"from\" TEXT NOT NULL DEFAULT '9:00', \"to\" TEXT NOT NULL DEFAULT '21:00', disabled INTEGER NOT NULL DEFAULT 0, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS shop_closed_dates (id INTEGER PRIMARY KEY, shop_id INTEGER NOT NULL, date TEXT NOT NULL, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS invitations (id INTEGER PRIMARY KEY, shop_id INTEGER NOT NULL, user_id INTEGER NOT NULL, role TEXT, status INTEGER NOT NULL DEFAULT 1, created_by INTEGER, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS categories (id INTEGER PRIMARY KEY, uuid TEXT NOT NULL UNIQUE, slug TEXT, keywords TEXT, parent_id INTEGER NOT NULL DEFAULT 0, type INTEGER NOT NULL DEFAULT 1, input INTEGER, img TEXT, active INTEGER NOT NULL DEFAULT 1, age_limit INTEGER NOT NULL DEFAULT 0, status TEXT NOT NULL DEFAULT 'pending', shop_id INTEGER, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS category_translations (id INTEGER PRIMARY KEY, category_id INTEGER NOT NULL, locale TEXT NOT NULL, title TEXT NOT NULL, description TEXT, UNIQUE(category_id, locale))",
    "CREATE TABLE IF NOT EXISTS brands (id INTEGER PRIMARY KEY, uuid TEXT NOT NULL, title TEXT NOT NULL, active INTEGER NOT NULL DEFAULT 1, img TEXT, shop_id INTEGER, created_at TEXT, updated_at TEXT)",
    "CREATE INDEX IF NOT EXISTS brands_uuid_index ON brands(uuid)",
    "CREATE TABLE IF NOT EXISTS payments (id INTEGER PRIMARY KEY, tag TEXT, input INTEGER NOT NULL DEFAULT 2, sandbox INTEGER NOT NULL DEFAULT 0, active INTEGER NOT NULL DEFAULT 1, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS stories (id INTEGER PRIMARY KEY, product_id INTEGER NOT NULL, shop_id INTEGER NOT NULL, active INTEGER NOT NULL DEFAULT 1, file_urls TEXT, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS banners (id INTEGER PRIMARY KEY, url TEXT, type TEXT NOT NULL DEFAULT 'banner', img TEXT, active INTEGER NOT NULL DEFAULT 1, clickable INTEGER NOT NULL DEFAULT 1, input INTEGER, created_at TEXT, updated_at TEXT, shop_id INTEGER)",
    "CREATE TABLE IF NOT EXISTS banner_translations (id INTEGER PRIMARY KEY, banner_id INTEGER NOT NULL, locale TEXT NOT NULL, title TEXT NOT NULL, description TEXT, button_text TEXT, UNIQUE(banner_id, locale))",
    "CREATE TABLE IF NOT EXISTS banner_products (id INTEGER PRIMARY KEY, product_id INTEGER NOT NULL, banner_id INTEGER NOT NULL, interval REAL NOT NULL DEFAULT 1, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS likes (id INTEGER PRIMARY KEY, likable_type TEXT NOT NULL, likable_id INTEGER NOT NULL, user_id INTEGER NOT NULL, created_at TEXT, updated_at TEXT)",
    "CREATE INDEX IF NOT EXISTS likes_likable_id_index ON likes(likable_id)",
    "CREATE INDEX IF NOT EXISTS likes_likable_type_index ON likes(likable_type)",
    "CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY, slug TEXT, uuid TEXT NOT NULL UNIQUE, shop_id INTEGER NOT NULL, category_id INTEGER, brand_id INTEGER, unit_id INTEGER, keywords TEXT, img TEXT, qr_code TEXT, tax REAL, active INTEGER NOT NULL DEFAULT 0, status TEXT NOT NULL DEFAULT 'pending', min_qty INTEGER NOT NULL DEFAULT 1, max_qty INTEGER NOT NULL DEFAULT 2147483647, digital INTEGER NOT NULL DEFAULT 0, age_limit INTEGER NOT NULL DEFAULT 0, visibility INTEGER NOT NULL DEFAULT 1, interval REAL NOT NULL DEFAULT 1, status_note TEXT, r_count REAL DEFAULT 0, r_avg REAL DEFAULT 0, r_sum REAL DEFAULT 0, o_count REAL DEFAULT 0, od_count REAL DEFAULT 0, min_price REAL DEFAULT 0, max_price REAL DEFAULT 0, created_at TEXT, updated_at TEXT, deleted_at TEXT)",
    "CREATE TABLE IF NOT EXISTS product_translations (id INTEGER PRIMARY KEY, product_id INTEGER NOT NULL, locale TEXT NOT NULL, title TEXT NOT NULL, description TEXT, UNIQUE(product_id, locale))",
    "CREATE TABLE IF NOT EXISTS stocks (id INTEGER PRIMARY KEY, product_id INTEGER NOT NULL, price REAL NOT NULL DEFAULT 0, quantity INTEGER NOT NULL DEFAULT 0, bonus_expired_at TEXT, discount_expired_at TEXT, sku TEXT, discount_id INTEGER, tax REAL, img TEXT, o_count REAL DEFAULT 0, od_count REAL DEFAULT 0, created_at TEXT, updated_at TEXT, deleted_at TEXT)",
    "CREATE TABLE IF NOT EXISTS discounts (id INTEGER PRIMARY KEY, shop_id INTEGER, type TEXT, price REAL, start TEXT, end TEXT, active INTEGER NOT NULL DEFAULT 0)",
    "CREATE TABLE IF NOT EXISTS bonuses (id INTEGER PRIMARY KEY, shop_id INTEGER, stock_id INTEGER, bonus_stock_id INTEGER, bonus_quantity INTEGER, expired_at TEXT, value REAL, type TEXT, status INTEGER NOT NULL DEFAULT 1, active INTEGER NOT NULL DEFAULT 1)",
    "CREATE TABLE IF NOT EXISTS stock_extras (id INTEGER PRIMARY KEY, stock_id INTEGER NOT NULL, extra_group_id INTEGER NOT NULL, extra_value_id INTEGER)",
    "CREATE TABLE IF NOT EXISTS extra_groups (id INTEGER PRIMARY KEY, active INTEGER NOT NULL DEFAULT 1)",
    "CREATE TABLE IF NOT EXISTS extra_group_translations (id INTEGER PRIMARY KEY, extra_group_id INTEGER NOT NULL, locale TEXT NOT NULL, title TEXT NOT NULL)",
    "CREATE TABLE IF NOT EXISTS extra_values (id INTEGER PRIMARY KEY, extra_group_id INTEGER, active INTEGER NOT NULL DEFAULT 1)",
    "CREATE TABLE IF NOT EXISTS tags (id INTEGER PRIMARY KEY, product_id INTEGER NOT NULL, active INTEGER NOT NULL DEFAULT 1, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS tag_translations (id INTEGER PRIMARY KEY, tag_id INTEGER NOT NULL, locale TEXT NOT NULL, title TEXT NOT NULL)",
    "CREATE TABLE IF NOT EXISTS digital_files (id INTEGER PRIMARY KEY, active INTEGER NOT NULL DEFAULT 0, product_id INTEGER, path TEXT, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS galleries (id INTEGER PRIMARY KEY, loadable_type TEXT NOT NULL, loadable_id INTEGER NOT NULL, type TEXT NOT NULL DEFAULT 'other', title TEXT, path TEXT, size INTEGER, mime TEXT, preview TEXT, created_at TEXT, updated_at TEXT)",
    "CREATE INDEX IF NOT EXISTS galleries_loadable_index ON galleries(loadable_type, loadable_id)",
    "CREATE TABLE IF NOT EXISTS units (id INTEGER PRIMARY KEY, active INTEGER NOT NULL DEFAULT 1, position TEXT NOT NULL DEFAULT 'after', created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS unit_translations (id INTEGER PRIMARY KEY, unit_id INTEGER NOT NULL, locale TEXT NOT NULL, title TEXT NOT NULL, UNIQUE(unit_id, locale))",
    "CREATE TABLE IF NOT EXISTS services (id INTEGER PRIMARY KEY, slug TEXT, category_id INTEGER NOT NULL, shop_id INTEGER, status TEXT NOT NULL DEFAULT 'new', status_note TEXT, img TEXT, price REAL NOT NULL DEFAULT 0, commission_fee REAL NOT NULL DEFAULT 0, interval INTEGER NOT NULL DEFAULT 30, pause INTEGER NOT NULL DEFAULT 10, type TEXT NOT NULL DEFAULT 'online', gender INTEGER NOT NULL DEFAULT 3, data TEXT, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS service_translations (id INTEGER PRIMARY KEY, service_id INTEGER NOT NULL, locale TEXT NOT NULL, title TEXT NOT NULL, description TEXT, UNIQUE(service_id, locale))",
    "CREATE TABLE IF NOT EXISTS service_extras (id INTEGER PRIMARY KEY, service_id INTEGER NOT NULL, shop_id INTEGER NOT NULL, active INTEGER NOT NULL DEFAULT 0, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS service_extra_translations (id INTEGER PRIMARY KEY, service_extra_id INTEGER NOT NULL, locale TEXT NOT NULL, title TEXT NOT NULL, description TEXT, UNIQUE(service_extra_id, locale))",
    "CREATE TABLE IF NOT EXISTS service_faqs (id INTEGER PRIMARY KEY, slug TEXT, service_id INTEGER NOT NULL, type TEXT NOT NULL DEFAULT 'web', active INTEGER NOT NULL DEFAULT 1, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS service_faq_translations (id INTEGER PRIMARY KEY, service_faq_id INTEGER NOT NULL, locale TEXT NOT NULL, question TEXT NOT NULL, answer TEXT, UNIQUE(service_faq_id, locale))",
    "CREATE TABLE IF NOT EXISTS service_masters (id INTEGER PRIMARY KEY, service_id INTEGER NOT NULL, master_id INTEGER NOT NULL, shop_id INTEGER NOT NULL, commission_fee REAL NOT NULL DEFAULT 0, price REAL NOT NULL DEFAULT 0, discount REAL, active INTEGER NOT NULL DEFAULT 0, interval INTEGER NOT NULL DEFAULT 30, pause INTEGER NOT NULL DEFAULT 10, type TEXT NOT NULL DEFAULT 'online', data TEXT, gender INTEGER NOT NULL DEFAULT 3, pricing TEXT, extra_time INTEGER NOT NULL DEFAULT 0, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS user_working_days (id INTEGER PRIMARY KEY, user_id INTEGER NOT NULL, day TEXT NOT NULL, \"from\" TEXT NOT NULL DEFAULT '9:00', \"to\" TEXT NOT NULL DEFAULT '21:00', disabled INTEGER NOT NULL DEFAULT 0, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS bookings (id INTEGER PRIMARY KEY, service_master_id INTEGER, master_id INTEGER NOT NULL, user_id INTEGER NOT NULL, currency_id INTEGER NOT NULL, start_date TEXT NOT NULL, end_date TEXT NOT NULL, price REAL NOT NULL DEFAULT 0, rate REAL NOT NULL DEFAULT 1, discount REAL NOT NULL DEFAULT 0, commission_fee REAL NOT NULL DEFAULT 0, service_fee REAL NOT NULL DEFAULT 0, status TEXT NOT NULL DEFAULT 'new', canceled_note TEXT, note TEXT, notes TEXT, type TEXT, data TEXT, parent_id INTEGER, shop_id INTEGER, shop_location_id INTEGER, collect_via_platform INTEGER NOT NULL DEFAULT 0, service_id INTEGER, category_id INTEGER, user_member_ship_id INTEGER, gift_cart_id INTEGER, gift_cart_price REAL, extra_price REAL DEFAULT 0, extra_time_price REAL, coupon_price REAL, tips REAL, total_price REAL, gender INTEGER DEFAULT 3, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS booking_extras (id INTEGER PRIMARY KEY, booking_id INTEGER NOT NULL, service_extra_id INTEGER NOT NULL, price REAL DEFAULT 0, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS booking_activities (id INTEGER PRIMARY KEY, booking_id INTEGER NOT NULL, user_id INTEGER NOT NULL, note TEXT NOT NULL, type TEXT NOT NULL, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS booking_extra_times (id INTEGER PRIMARY KEY, booking_id INTEGER NOT NULL, price REAL, duration REAL, duration_type TEXT, created_at TEXT, updated_at TEXT)",
    "CREATE TABLE IF NOT EXISTS transactions (id INTEGER PRIMARY KEY, payable_type TEXT NOT NULL, payable_id INTEGER NOT NULL, price REAL NOT NULL, user_id INTEGER, payment_sys_id INTEGER, payment_trx_id TEXT, note TEXT, perform_time TEXT, refund_time TEXT, status TEXT NOT NULL DEFAULT 'progress', status_description TEXT NOT NULL DEFAULT '', created_at TEXT, updated_at TEXT)",
];

$pdo->beginTransaction();
try {
    foreach ($schema as $statement) {
        $pdo->exec($statement);
    }

    $tokenColumns = [];
    foreach ($pdo->query('PRAGMA table_info("personal_access_tokens")') as $column) {
        $tokenColumns[$column['name']] = true;
    }
    if (!isset($tokenColumns['expires_at'])) {
        $pdo->exec('ALTER TABLE "personal_access_tokens" ADD COLUMN "expires_at" TEXT');
    }

    // The persistent preview database may predate an original Laravel
    // migration represented in the reviewed schema above. Apply only these
    // non-destructive, explicitly enumerated SQLite additions to the owned
    // bookings table; never run the historical migration chain.
    $bookingColumns = [];
    foreach ($pdo->query('PRAGMA table_info("bookings")') as $column) {
        $bookingColumns[$column['name']] = true;
    }
    foreach ([
        'notes' => 'TEXT',
        'shop_location_id' => 'INTEGER',
        'collect_via_platform' => 'INTEGER NOT NULL DEFAULT 0',
        'service_id' => 'INTEGER',
        'category_id' => 'INTEGER',
        'user_member_ship_id' => 'INTEGER',
        'gift_cart_id' => 'INTEGER',
        'gift_cart_price' => 'REAL',
        'extra_price' => 'REAL DEFAULT 0',
        'extra_time_price' => 'REAL',
        'coupon_price' => 'REAL',
        'tips' => 'REAL',
        'total_price' => 'REAL',
        'gender' => 'INTEGER DEFAULT 3',
    ] as $column => $definition) {
        if (!isset($bookingColumns[$column])) {
            $pdo->exec('ALTER TABLE "bookings" ADD COLUMN "' . $column . '" ' . $definition);
        }
    }

    $insert = static function (string $table, array $row) use ($pdo): void {
        $columns = array_keys($row);
        $quoted = array_map(static fn(string $column): string => '"' . str_replace('"', '""', $column) . '"', $columns);
        $sql = 'INSERT OR IGNORE INTO "' . $table . '" (' . implode(',', $quoted) . ') VALUES (' .
            implode(',', array_fill(0, count($columns), '?')) . ')';
        $pdo->prepare($sql)->execute(array_values($row));
    };

    require_once dirname(__DIR__) . '/scripts/original-preview-localization.php';
    $translationSources = originalPreviewLoadTranslationSources(dirname(__DIR__), $runtime);

    // TranslationSeeder prefers translations.php when both it and the SQL
    // dump exist. Match its firstOrCreate identity (locale/group/key), not
    // the dump's unreliable IDs: many exported array rows have id=0, and
    // duplicate identities are intentionally left to firstOrCreate's
    // first-row-wins behavior. DatabaseSeeder runs MissingTranslationsSeeder
    // after TranslationSeeder; read only its private literal constant through
    // Reflection. Never invoke run(), which also contains delete statements.
    $originalTranslations = $translationSources['canonical'];
    if (!is_array($originalTranslations)) {
        throw new RuntimeException('The original PHP translation catalog is invalid.');
    }
    $translationInsert = $pdo->prepare(
        'INSERT OR IGNORE INTO translations ' .
        '(status, locale, "group", "key", value, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $originalTranslationSourceRows = 0;
    $originalTranslationIdentities = [];
    foreach ($originalTranslations as $translation) {
        if (!is_array($translation) ||
            !is_string($translation['locale'] ?? null) || $translation['locale'] === '' ||
            !is_string($translation['group'] ?? null) || $translation['group'] === '' ||
            !is_string($translation['key'] ?? null) || $translation['key'] === '' ||
            (!is_string($translation['value'] ?? null) && ($translation['value'] ?? null) !== null) ||
            !is_numeric($translation['status'] ?? null)) {
            throw new RuntimeException('The original PHP translation catalog contains an invalid row.');
        }

        $createdAt = $translation['created_at'] ?? null;
        $updatedAt = $translation['updated_at'] ?? null;
        if (($createdAt !== null && !is_string($createdAt)) ||
            ($updatedAt !== null && !is_string($updatedAt))) {
            throw new RuntimeException('The original PHP translation catalog contains invalid timestamps.');
        }

        $identity = json_encode(
            [$translation['locale'], $translation['group'], $translation['key']],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
        );
        $originalTranslationIdentities[$identity] = true;
        $translationInsert->execute([
            (int)$translation['status'],
            $translation['locale'],
            $translation['group'],
            $translation['key'],
            $translation['value'] ?? null,
            $createdAt,
            $updatedAt,
        ]);
        $originalTranslationSourceRows++;
    }
    $canonicalTranslationUniqueRows = count($originalTranslationIdentities);

    // Mirror MissingTranslationsSeeder::run()'s firstOrCreate inserts without
    // executing the seeder. Canonical TranslationSeeder rows have already been
    // inserted, so INSERT OR IGNORE preserves their first-row-wins values.
    $supplementalTranslations = $translationSources['supplemental'];
    $supplementalTranslationSourceRows = 0;
    $supplementalTranslationIdentities = [];
    $supplementalTranslationTimestamp = gmdate('Y-m-d H:i:s');
    foreach ($supplementalTranslations as $translation) {
        $identity = originalPreviewTranslationIdentity(
            $translation['locale'],
            $translation['group'],
            $translation['key']
        );
        $supplementalTranslationIdentities[$identity] = true;
        $translationInsert->execute([
            $translation['status'],
            $translation['locale'],
            $translation['group'],
            $translation['key'],
            $translation['value'],
            $supplementalTranslationTimestamp,
            $supplementalTranslationTimestamp,
        ]);
        $supplementalTranslationSourceRows++;
    }
    $supplementalTranslationUniqueRows = count($supplementalTranslationIdentities);
    $supplementalTranslationExistingRows = count(array_intersect_key(
        $supplementalTranslationIdentities,
        $originalTranslationIdentities
    ));
    $supplementalTranslationAddedRows = $supplementalTranslationUniqueRows - $supplementalTranslationExistingRows;
    $originalTranslationUniqueRows = $canonicalTranslationUniqueRows + $supplementalTranslationAddedRows;

    $now = gmdate('Y-m-d H:i:s');
    $passwordHash = static fn(string $password): string => password_hash($password, PASSWORD_BCRYPT);

    $insert('currencies', ['id' => 1, 'symbol' => 'XAF', 'title' => 'Central African CFA franc', 'rate' => 1, 'position' => 'after', 'default' => 1, 'active' => 1, 'created_at' => $now, 'updated_at' => $now]);
    $insert('languages', ['id' => 1, 'title' => 'English', 'locale' => 'en', 'default' => 1, 'active' => 1]);
    $insert('regions', ['id' => 1, 'active' => 1]);
    $insert('region_translations', ['id' => 1, 'region_id' => 1, 'locale' => 'en', 'title' => 'Littoral']);
    $insert('countries', ['id' => 1, 'region_id' => 1, 'currency_id' => 1, 'active' => 1, 'code' => 'CM']);
    $insert('country_translations', ['id' => 1, 'country_id' => 1, 'locale' => 'en', 'title' => 'Cameroon']);
    $insert('cities', ['id' => 1, 'region_id' => 1, 'country_id' => 1, 'active' => 1]);
    $insert('city_translations', ['id' => 1, 'city_id' => 1, 'locale' => 'en', 'title' => 'Douala']);

    foreach ([
        ['title', 'AgendaAlly Local Preview'],
        ['ui_type', '1'],
        ['products_enabled', '1'],
        ['default_country_id', '1'],
        ['default_city_id', '1'],
        ['default_currency_id', '1'],
        ['default_lang', 'en'],
        ['by_subscription', '0'],
        ['demo', '0'],
    ] as [$key, $value]) {
        $insert('settings', ['key' => $key, 'value' => $value]);
    }
    // Repair only earlier preview-created settings to the original UI's
    // numeric View 1 choice. Services are not gated by a services_enabled
    // setting in the original application.
    $pdo->exec("UPDATE settings SET value = '1' WHERE \"key\" = 'ui_type' AND value = 'home'");
    $pdo->exec("DELETE FROM settings WHERE \"key\" = 'services_enabled' AND value = '1'");

    $users = [
        [1, 'Original Preview Administrator', $credentials['ADMIN_EMAIL'], $credentials['ADMIN_PASSWORD'], 'admin'],
        [2, 'Preview Customer', $credentials['CUSTOMER_EMAIL'], $credentials['CUSTOMER_PASSWORD'], 'user'],
        [3, 'Preview Service Professional', $credentials['MASTER_EMAIL'], $credentials['MASTER_PASSWORD'], 'master'],
    ];
    $roleCatalog = [
        1 => 'user',
        11 => 'seller',
        12 => 'moderator',
        13 => 'deliveryman',
        14 => 'shop_manager',
        21 => 'manager',
        22 => 'master',
        99 => 'admin',
    ];
    $knownRoleNames = array_values($roleCatalog);
    $existingRoleRows = $pdo->query('SELECT id, name, guard_name FROM roles')->fetchAll();
    foreach ($roleCatalog as $roleId => $roleName) {
        foreach ($existingRoleRows as $existingRole) {
            if ((int)$existingRole['id'] === $roleId &&
                ($existingRole['name'] !== $roleName || $existingRole['guard_name'] !== 'web') &&
                !in_array($existingRole['name'], $knownRoleNames, true)) {
                throw new RuntimeException('An unrelated runtime role blocks an original fixture role ID.');
            }
        }
    }

    $existingRolesByName = [];
    foreach ($existingRoleRows as $existingRole) {
        if ($existingRole['guard_name'] === 'web' && in_array($existingRole['name'], $knownRoleNames, true)) {
            $existingRolesByName[$existingRole['name']] = (int)$existingRole['id'];
        }
    }
    $temporaryRoleIds = [];
    foreach ($roleCatalog as $roleId => $roleName) {
        $currentRoleId = $existingRolesByName[$roleName] ?? null;
        if ($currentRoleId !== null && $currentRoleId !== $roleId) {
            $temporaryRoleId = -100000 - $roleId;
            $pdo->prepare('UPDATE model_has_roles SET role_id = ? WHERE role_id = ?')
                ->execute([$temporaryRoleId, $currentRoleId]);
            $pdo->prepare('UPDATE role_has_permissions SET role_id = ? WHERE role_id = ?')
                ->execute([$temporaryRoleId, $currentRoleId]);
            $pdo->prepare('UPDATE roles SET id = ? WHERE id = ?')
                ->execute([$temporaryRoleId, $currentRoleId]);
            $temporaryRoleIds[$roleName] = $temporaryRoleId;
        }
    }

    foreach ($roleCatalog as $roleId => $roleName) {
        if (!isset($existingRolesByName[$roleName])) {
            $insert('roles', [
                'id' => $roleId, 'name' => $roleName, 'guard_name' => 'web',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        } elseif (isset($temporaryRoleIds[$roleName])) {
            $temporaryRoleId = $temporaryRoleIds[$roleName];
            $pdo->prepare('UPDATE roles SET id = ? WHERE id = ?')
                ->execute([$roleId, $temporaryRoleId]);
            $pdo->prepare('UPDATE model_has_roles SET role_id = ? WHERE role_id = ?')
                ->execute([$roleId, $temporaryRoleId]);
            $pdo->prepare('UPDATE role_has_permissions SET role_id = ? WHERE role_id = ?')
                ->execute([$roleId, $temporaryRoleId]);
        }
    }
    $roleIds = array_flip($roleCatalog);
    foreach ($users as [$id, $name, $email, $password, $role]) {
        [$first, $last] = array_pad(explode(' ', $name, 2), 2, '');
        $insert('users', [
            'id' => $id, 'uuid' => sprintf('10000000-0000-4000-8000-%012d', $id),
            'firstname' => $first, 'lastname' => $last, 'email' => $email,
            'gender' => 'male', 'active' => 1, 'password' => $passwordHash($password),
            'my_referral' => sprintf('preview%02d', $id), 'currency_id' => 1, 'lang' => 'en',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $insert('model_has_roles', [
            'role_id' => $roleIds[$role], 'model_type' => 'App\\Models\\User', 'model_id' => $id,
        ]);
    }

    $insert('wallets', ['id' => 1, 'uuid' => '20000000-0000-4000-8000-000000000001', 'user_id' => 1, 'currency_id' => 1, 'price' => 0, 'created_at' => $now, 'updated_at' => $now]);
    $insert('shops', [
        'id' => 501, 'slug' => 'douala-local-studio', 'uuid' => '30000000-0000-4000-8000-000000000501',
        'user_id' => 3, 'status' => 'approved', 'delivery_time' => '{"type":"minute","from":30,"to":60}',
        'type' => 1, 'delivery_type' => 2, 'open' => 1, 'visibility' => 1, 'verify' => 1,
        'latitude' => 4.0511, 'longitude' => 9.7679, 'min_price' => 0, 'max_price' => 0,
        'service_min_price' => 5000, 'service_max_price' => 5000, 'created_at' => $now, 'updated_at' => $now,
    ]);
    $insert('shop_translations', ['id' => 1, 'shop_id' => 501, 'locale' => 'en', 'title' => 'Bonanjo Local Studio', 'description' => 'Synthetic local-only preview studio.', 'address' => 'Bonanjo, Douala']);
    $insert('shop_locations', [
        'id' => 1, 'shop_id' => 501, 'region_id' => 1, 'country_id' => 1, 'city_id' => 1,
        'address' => 'Bonanjo, Douala', 'alias' => 'Bonanjo Studio', 'latitude' => 4.0511,
        'longitude' => 9.7679, 'type' => 1, 'created_at' => $now, 'updated_at' => $now,
    ]);
    $insert('shop_locations', [
        'id' => 2, 'shop_id' => 501, 'region_id' => 1, 'country_id' => 1, 'city_id' => 1,
        'address' => 'Bonanjo, Douala', 'alias' => 'Bonanjo Studio', 'latitude' => 4.0511,
        'longitude' => 9.7679, 'type' => 2, 'created_at' => $now, 'updated_at' => $now,
    ]);
    $insert('invitations', ['id' => 1, 'shop_id' => 501, 'user_id' => 3, 'role' => 'master', 'status' => 2, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now]);
    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $offset => $day) {
        $insert('shop_working_days', [
            'id' => $offset + 1, 'shop_id' => 501, 'day' => $day,
            'from' => '09:00', 'to' => '18:00', 'disabled' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $insert('user_working_days', [
            'id' => $offset + 1, 'user_id' => 3, 'day' => $day,
            'from' => '09:00', 'to' => '18:00', 'disabled' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    $insert('categories', [
        'id' => 11, 'uuid' => '40000000-0000-4000-8000-000000000011', 'slug' => 'wellness',
        'parent_id' => 0, 'type' => 11, 'active' => 1, 'status' => 'published',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    $insert('category_translations', ['id' => 11, 'category_id' => 11, 'locale' => 'en', 'title' => 'Wellness']);
    $insert('services', [
        'id' => 1, 'slug' => 'relaxing-massage', 'category_id' => 11, 'shop_id' => 501,
        'status' => 'accepted', 'price' => 5000, 'interval' => 60, 'pause' => 10,
        'type' => 'online', 'gender' => 3, 'created_at' => $now, 'updated_at' => $now,
    ]);
    $insert('service_translations', ['id' => 1, 'service_id' => 1, 'locale' => 'en', 'title' => 'Relaxing massage', 'description' => 'A local synthetic service listing for HTTP integration testing.']);
    $insert('service_masters', [
        'id' => 1, 'service_id' => 1, 'master_id' => 3, 'shop_id' => 501,
        'commission_fee' => 0, 'price' => 5000, 'active' => 1, 'interval' => 60,
        'pause' => 10, 'discount' => 0, 'type' => 'online', 'gender' => 3,
        'created_at' => $now, 'updated_at' => $now,
    ]);
    $bookingStart = new DateTimeImmutable('tomorrow 10:00:00', new DateTimeZone('Africa/Douala'));
    $bookingEnd = $bookingStart->modify('+60 minutes');
    $insert('bookings', [
        'id' => 1, 'service_master_id' => 1, 'master_id' => 3, 'user_id' => 2,
        'currency_id' => 1, 'start_date' => $bookingStart->format('Y-m-d H:i:s'),
        'end_date' => $bookingEnd->format('Y-m-d H:i:s'), 'price' => 5000,
        'rate' => 1, 'discount' => 0, 'commission_fee' => 0, 'service_fee' => 0,
        'status' => 'booked', 'type' => 'online', 'total_price' => 5000,
        'gender' => 3, 'shop_id' => 501, 'shop_location_id' => 2, 'service_id' => 1,
        'category_id' => 11, 'collect_via_platform' => 0, 'created_at' => $now,
        'updated_at' => $now,
    ]);

    $insert('products', [
        'id' => 1, 'slug' => 'preview-cocoa', 'uuid' => '50000000-0000-4000-8000-000000000001',
        'shop_id' => 501, 'category_id' => null, 'active' => 1, 'status' => 'published',
        'min_qty' => 1, 'max_qty' => 100, 'visibility' => 1, 'min_price' => 2500,
        'max_price' => 2500, 'created_at' => $now, 'updated_at' => $now,
    ]);
    $insert('product_translations', ['id' => 1, 'product_id' => 1, 'locale' => 'en', 'title' => 'Preview cocoa', 'description' => 'Synthetic local-only product listing.']);
    $insert('stocks', ['id' => 1, 'product_id' => 1, 'price' => 2500, 'quantity' => 8, 'sku' => 'PREVIEW-COCOA', 'created_at' => $now, 'updated_at' => $now]);

    if (!$pdo->inTransaction()) {
        throw new RuntimeException('Synthetic fixture transaction was lost.');
    }
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Synthetic SQLite provisioning failed: " . get_class($exception) . ": " . $exception->getMessage() . "\n");
    exit(1);
}

printf(
    "Reviewed original-route SQLite schema and synthetic fixtures are ready; canonical translation rows=%d identities=%d; supplemental literal rows=%d identities=%d canonical overlaps=%d; total original identities=%d.\n",
    $originalTranslationSourceRows,
    $canonicalTranslationUniqueRows,
    $supplementalTranslationSourceRows,
    $supplementalTranslationUniqueRows,
    $supplementalTranslationExistingRows,
    $originalTranslationUniqueRows
);