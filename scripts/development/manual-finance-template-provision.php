<?php
declare(strict_types=1);
// Called only through the native local launcher with network transports disabled.
$root=realpath($argv[1]??'');
if ($root!==realpath(__DIR__.'/../../.migration-backup/backend')) throw new RuntimeException('Native backend required.');
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!in_array($app->environment(),['local','testing'],true)
    || config('database.default')!=='sqlite'
    || realpath(config('database.connections.sqlite.database'))!==realpath($root.'/database/development/agendaally.sqlite')
    || config('manual_finance.smtp_enabled')!==false || config('manual_finance.provider_execution_enabled')!==false) {
    throw new RuntimeException('Bounded local database and disabled execution required.');
}
$count=\App\Support\FinancialEmailTemplates::ensure();
echo json_encode(['library_only_templates_added'=>$count,'smtp_enabled'=>false,'provider_execution_enabled'=>false]),"\n";
