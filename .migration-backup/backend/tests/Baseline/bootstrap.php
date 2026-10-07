<?php
declare(strict_types=1);

// Reuse the reviewed isolated container, aliases and source autoloading. This
// suite never boots the Laravel application or the original provider stack.
require __DIR__ . '/../Hardening/bootstrap.php';

$loader->addPsr4('Tests\\Baseline\\', __DIR__);