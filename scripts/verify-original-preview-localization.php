<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$expectedRuntime = $root . '/.local/agendaally-preview/backend';
$runtime = getenv('PREVIEW_RUNTIME') ?: $expectedRuntime;
$database = getenv('PREVIEW_DB_FILE') ?: $expectedRuntime . '/storage/preview.sqlite';

if (realpath($runtime) !== realpath($expectedRuntime) ||
    !is_file($expectedRuntime . '/.original-http-preview-owned') ||
    realpath(dirname($database)) !== realpath($expectedRuntime . '/storage') ||
    basename($database) !== 'preview.sqlite' ||
    realpath($database) !== realpath($expectedRuntime . '/storage/preview.sqlite')) {
    fwrite(STDERR, "Refusing to inspect anything outside the owned original-preview runtime.\n");
    exit(1);
}
require_once $root . '/scripts/original-preview-localization.php';
try {
    $translationSources = originalPreviewLoadTranslationSources($root, $runtime);
} catch (Throwable $exception) {
    fwrite(STDERR, "Could not load original translation sources: " . get_class($exception) . ": " . $exception->getMessage() . "\n");
    exit(1);
}
$sourceRows = $translationSources['canonical'];
$supplementalRows = $translationSources['supplemental'];

$expected = [];
$sourceRecordsByLocale = [];
$sourceKeysByLocale = [];
foreach ($sourceRows as $row) {
    if (!is_array($row) ||
        !is_string($row['locale'] ?? null) || $row['locale'] === '' ||
        !is_string($row['group'] ?? null) || $row['group'] === '' ||
        !is_string($row['key'] ?? null) || $row['key'] === '' ||
        (!is_string($row['value'] ?? null) && ($row['value'] ?? null) !== null) ||
        !is_numeric($row['status'] ?? null)) {
        fwrite(STDERR, "The original PHP translation catalog contains an invalid row.\n");
        exit(1);
    }

    $identity = originalPreviewTranslationIdentity(
        $row['locale'],
        $row['group'],
        $row['key']
    );
    $sourceRecordsByLocale[$row['locale']] = ($sourceRecordsByLocale[$row['locale']] ?? 0) + 1;
    $sourceKeysByLocale[$row['locale']][$row['group'] . "\0" . $row['key']] = true;

    // TranslationSeeder's firstOrCreate uses locale/group/key and keeps the
    // first source row if the exported array repeats that identity.
    if (!isset($expected[$identity])) {
        $expected[$identity] = [
            'locale' => $row['locale'],
            'group' => $row['group'],
            'key' => $row['key'],
            'status' => (int)$row['status'],
            'value' => $row['value'] ?? null,
        ];
    }
}
$canonicalUniqueRows = count($expected);
$supplementalIdentities = [];
$supplementalCanonicalOverlaps = 0;
$supplementalConflictingValues = 0;
foreach ($supplementalRows as $row) {
    $identity = originalPreviewTranslationIdentity(
        $row['locale'],
        $row['group'],
        $row['key']
    );
    $sourceRecordsByLocale[$row['locale']] = ($sourceRecordsByLocale[$row['locale']] ?? 0) + 1;
    $sourceKeysByLocale[$row['locale']][$row['group'] . "\0" . $row['key']] = true;
    $supplementalIdentities[$identity] = true;

    if (isset($expected[$identity])) {
        $supplementalCanonicalOverlaps++;
        if ($expected[$identity]['status'] !== $row['status'] ||
            $expected[$identity]['value'] !== $row['value']) {
            $supplementalConflictingValues++;
        }
        continue;
    }

    $expected[$identity] = $row;
}
$supplementalUniqueRows = count($supplementalIdentities);
$supplementalAddedRows = $supplementalUniqueRows - $supplementalCanonicalOverlaps;

try {
    $pdo = new PDO('sqlite:' . $database, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $actual = [];
    foreach ($pdo->query('SELECT status, locale, "group", "key", value FROM translations') as $row) {
        $identity = originalPreviewTranslationIdentity(
            $row['locale'],
            $row['group'],
            $row['key']
        );
        $actual[$identity] = $row;
    }
    $languageRows = $pdo->query(
        'SELECT locale, title, active, "default" FROM languages ORDER BY locale'
    )->fetchAll();
} catch (Throwable $exception) {
    fwrite(STDERR, "Could not inspect localization tables: " . get_class($exception) . "\n");
    exit(1);
}

$missingSourceRows = [];
$mismatchedSourceRows = [];
foreach ($expected as $identity => $source) {
    if (!isset($actual[$identity])) {
        $missingSourceRows[] = $source;
        continue;
    }
    if ((int)$actual[$identity]['status'] !== $source['status'] ||
        $actual[$identity]['value'] !== $source['value']) {
        $mismatchedSourceRows[] = $source;
    }
}

$englishKeys = $sourceKeysByLocale['en'] ?? [];
$localeCoverage = [];
$missingKeysByLocale = [];
foreach ($sourceKeysByLocale as $locale => $keys) {
    $missing = array_diff_key($englishKeys, $keys);
    $localeCoverage[$locale] = [
        'source_records' => $sourceRecordsByLocale[$locale],
        'unique_keys' => count($keys),
        'missing_vs_en' => count($missing),
        'extra_vs_en' => count(array_diff_key($keys, $englishKeys)),
    ];
    if ($locale !== 'en' && $missing) {
        foreach (array_keys($missing) as $groupAndKey) {
            [$group, $key] = explode("\0", $groupAndKey, 2);
            $missingKeysByLocale[$locale][] = ['group' => $group, 'key' => $key];
        }
    }
}
ksort($localeCoverage);

$configuredLocales = array_map(
    static fn(array $language): string => (string)$language['locale'],
    $languageRows
);
sort($configuredLocales);
$expectedConfiguredLocales = ['en'];
$unconfiguredSourceLocales = array_values(array_diff(array_keys($sourceKeysByLocale), $configuredLocales));
sort($unconfiguredSourceLocales);
$unexpectedConfiguredLocales = array_values(array_diff($configuredLocales, $expectedConfiguredLocales));
sort($unexpectedConfiguredLocales);
$hasDefaultEnglish = false;
foreach ($languageRows as $language) {
    if ($language['locale'] === 'en' && (int)$language['active'] === 1 && (int)$language['default'] === 1) {
        $hasDefaultEnglish = true;
    }
}

printf(
    "Original sources: PHP catalog rows=%d; catalog identities=%d; repeated catalog rows=%d; private supplemental literal rows=%d; supplemental identities=%d; canonical overlaps=%d; new supplemental identities=%d; combined identities=%d.\n",
    count($sourceRows),
    $canonicalUniqueRows,
    count($sourceRows) - $canonicalUniqueRows,
    count($supplementalRows),
    $supplementalUniqueRows,
    $supplementalCanonicalOverlaps,
    $supplementalAddedRows,
    count($expected)
);
printf(
    "Owned preview translation rows=%d; expected original rows missing=%d; source rows with changed status/value=%d; supplemental/canonical conflicts=%d.\n",
    count($actual),
    count($missingSourceRows),
    count($mismatchedSourceRows),
    $supplementalConflictingValues
);
printf(
    "Original LanguageSeeder locales: en only; runtime configured locales=%s; original source locales not configured=%s.\n",
    $configuredLocales ? implode(',', $configuredLocales) : '(none)',
    $unconfiguredSourceLocales ? implode(',', $unconfiguredSourceLocales) : '(none)'
);
foreach ($localeCoverage as $locale => $coverage) {
    printf(
        "locale=%s source_records=%d unique_keys=%d missing_vs_en=%d extra_vs_en=%d\n",
        $locale,
        $coverage['source_records'],
        $coverage['unique_keys'],
        $coverage['missing_vs_en'],
        $coverage['extra_vs_en']
    );
}

if (in_array('--missing-keys', $argv, true)) {
    echo "Exact English keys absent from each source locale (JSON Lines):\n";
    foreach ($missingKeysByLocale as $locale => $keys) {
        foreach ($keys as $missing) {
            echo json_encode(
                ['locale' => $locale, 'group' => $missing['group'], 'key' => $missing['key']],
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
            ) . "\n";
        }
    }
}

if ($missingSourceRows || $mismatchedSourceRows || $supplementalConflictingValues || !$hasDefaultEnglish ||
    $configuredLocales !== $expectedConfiguredLocales || $unexpectedConfiguredLocales) {
    fwrite(STDERR, "FAIL original preview localization differs from its reviewed source/provisioning.\n");
    exit(1);
}

echo "PASS original PHP catalog, supplemental literal values, and seeded English default are present in the owned preview.\n";