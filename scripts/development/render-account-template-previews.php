<?php
declare(strict_types=1);

// Local proof only: no sender, live User, challenge, queue, or SMTP setup.
$base = $argv[1] ?? '';
require $base.'/vendor/autoload.php';
$app = require $base.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\Illuminate\Support\Facades\DB::connection()->getPdo()->exec('PRAGMA query_only = ON');
$proofs = [];
$directory = dirname(__DIR__, 2).'/.local/staging-mvp/account-template-preview';
if (!is_dir($directory)) mkdir($directory, 0700, true);
foreach (['verify', 'reset'] as $type) {
    $record = \Illuminate\Support\Facades\DB::table('email_templates')->where('type', $type)
        ->first(['type', 'subject', 'body', 'alt_body']);
    if (!$record) throw new RuntimeException('Required managed presentation record is missing.');
    $preview = \App\Support\ManagedEmailPresentation::preview((array) $record);
    file_put_contents($directory.'/'.$type.'.html', $preview['html']);
    file_put_contents($directory.'/'.$type.'.txt', $preview['text']);
    $proofs[$type] = $preview;
}
file_put_contents($directory.'/previews.json', json_encode($proofs, JSON_THROW_ON_ERROR));
// Keep evidence private. Temporary public browser proof copies are removed
// after local acceptance; the product preview remains Admin-authorized.
echo json_encode(['preview_types' => array_keys($proofs), 'mode' => 'synthetic_no_send',
    'challenge_created' => false, 'smtp_invoked' => false, 'database_read_only' => true]), "\n";
