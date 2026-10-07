<?php
declare(strict_types=1);

// This suite never boots the original application's service providers, reads
// .env files. Default fixtures use process-local SQLite; the explicitly opted-in
// MySQL contention fixture accepts only an initially empty loopback disposable
// test database. Accounting fixtures apply their reviewed isolated migrations.
$vendor = getenv('HARDENING_VENDOR_AUTOLOAD') ?: __DIR__ . '/../../vendor/autoload.php';
if (!is_file($vendor)) {
    throw new RuntimeException('Restore reviewed dependencies with --no-scripts --no-plugins first.');
}
$loader = require $vendor;
$loader->addPsr4('App\\', __DIR__ . '/../../app');
$loader->addPsr4('Tests\\Hardening\\', __DIR__);
Illuminate\Foundation\AliasLoader::getInstance(
    Illuminate\Support\Facades\Facade::defaultAliases()->all()
)->register();
class_alias(Illuminate\Support\Facades\Schema::class, 'Schema');
class_alias(Illuminate\Database\Eloquent\Model::class, 'Eloquent');