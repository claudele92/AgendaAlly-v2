<?php
declare(strict_types=1);
// Hash source only, never dotenv, vendor, normal data or connection material.
$root = dirname(__DIR__, 2);
$state = $root . '/.local/mysql-approved-bootstrap-rehearsal';
if (count($argv) !== 2 || !in_array($argv[1], ['before', 'after'], true)
    || is_link($state) || realpath($state) !== $state) {
    throw new RuntimeException('Owned source receipt arguments required.');
}
$trees = [
    'original' => $root . '/.migration-backup',
    'frozen' => $root . '/.local/agendaally-clean-repository/.migration-backup/backend',
    'validation' => $root . '/.local/clean-publication-validation/.migration-backup/backend',
];
$snapshot = [];
foreach ($trees as $label => $base) {
    $iterator = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
        static fn($file) => !in_array($file->getFilename(),
            ['vendor', 'node_modules', '.next', '.git', 'storage', 'cache', 'build', '.dart_tool'], true)
            && !str_starts_with($file->getFilename(), '.env')
            && !$file->isLink()
    ));
    foreach ($iterator as $file) {
        if (!$file->isFile() || !preg_match('/\.(php|json|lock|ts|tsx|js|jsx|dart|yaml|yml|css|scss|html|md)$/',
            $file->getFilename())) continue;
        $snapshot[$label][substr($file->getPathname(), strlen($base) + 1)] = hash_file('sha256', $file->getPathname());
    }
    ksort($snapshot[$label], SORT_STRING);
}
// Freeze all executed business/configuration/migration definitions, not just ledger count.
foreach ($snapshot['frozen'] as $path => $hash) {
    if (preg_match('#^(app/|config/|bootstrap/|database/|routes/|composer\.)#', $path)
        && ($snapshot['validation'][$path] ?? null) !== $hash) {
        throw new RuntimeException('Sanitized execution source differs: ' . $path);
    }
}
$destination = $state . '/source-' . $argv[1] . '.json';
if (file_exists($destination)) throw new RuntimeException('Source receipt overwrite refused.');
file_put_contents($destination, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
if ($argv[1] === 'after' && $snapshot !== json_decode(file_get_contents($state . '/source-before.json'), true, 512, JSON_THROW_ON_ERROR)) {
    throw new RuntimeException('Protected source bytes changed during the rehearsal.');
}
echo "Protected original/sanitized source bytes match.\n";
