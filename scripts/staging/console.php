<?php
declare(strict_types=1);
require __DIR__.'/runtime.php';
$app=stagingApplication();
$kernel=$app->make(Illuminate\Contracts\Console\Kernel::class);
$status=$kernel->handle(new Symfony\Component\Console\Input\ArgvInput,new Symfony\Component\Console\Output\ConsoleOutput);
$kernel->terminate(new Symfony\Component\Console\Input\ArgvInput,$status);
exit($status);