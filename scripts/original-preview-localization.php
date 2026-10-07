<?php
declare(strict_types=1);

function originalPreviewLoadTranslationSources(string $root, string $runtime): array
{
    $catalogSource = $root . '/.migration-backup/backend/resources/lang/translations.php';
    if (!is_file($catalogSource)) {
        throw new RuntimeException('The original PHP translation catalog is missing.');
    }
    $canonicalRows = require $catalogSource;
    if (!is_array($canonicalRows)) {
        throw new RuntimeException('The original PHP translation catalog is invalid.');
    }

    $autoload = $runtime . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('The owned preview Composer autoloader is missing.');
    }
    require_once $autoload;

    $supplementalSource = $root . '/.migration-backup/backend/database/seeders/MissingTranslationsSeeder.php';
    if (!is_file($supplementalSource)) {
        throw new RuntimeException('The original supplemental translation seeder is missing.');
    }
    require_once $supplementalSource;

    $seederReflection = new ReflectionClass(\Database\Seeders\MissingTranslationsSeeder::class);
    $translationsConstant = $seederReflection->getReflectionConstant('TRANSLATIONS');
    if ($translationsConstant === false || !$translationsConstant->isPrivate()) {
        throw new RuntimeException('The original supplemental translation constant is unavailable.');
    }
    $supplementalValues = $translationsConstant->getValue();
    if (!is_array($supplementalValues)) {
        throw new RuntimeException('The original supplemental translation constant is invalid.');
    }

    $supplementalRows = [];
    foreach ($supplementalValues as $key => $value) {
        if (!is_string($key) || $key === '' || !is_string($value) || $value === '') {
            throw new RuntimeException('The original supplemental translation constant contains an invalid row.');
        }
        $supplementalRows[] = [
            'locale' => 'en',
            'group' => 'web',
            'key' => $key,
            'value' => $value,
            'status' => 1,
        ];
    }

    return [
        'canonical' => $canonicalRows,
        'supplemental' => $supplementalRows,
    ];
}

function originalPreviewTranslationIdentity(string $locale, string $group, string $key): string
{
    return json_encode(
        [$locale, $group, $key],
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
    );
}