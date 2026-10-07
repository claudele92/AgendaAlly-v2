<?php
declare(strict_types=1);
// Native facade aliases only: isolated tests still own every in-memory database.
// Do not boot the application's real environment/database/notification providers.
require dirname(__DIR__,2).'/.migration-backup/backend/vendor/autoload.php';
\Illuminate\Foundation\AliasLoader::getInstance(
    \Illuminate\Support\Facades\Facade::defaultAliases()->toArray()
)->register();
