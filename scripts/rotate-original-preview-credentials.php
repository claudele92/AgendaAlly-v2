<?php
declare(strict_types=1);

/**
 * Rotate only the synthetic credentials and application key in the owned
 * original HTTP preview runtime. This script never displays secret values.
 */

$root = dirname(__DIR__);
$runtime = $root . '/.local/agendaally-preview/backend';
$marker = $runtime . '/.original-http-preview-owned';
$credentialFile = $runtime . '/.preview-credentials';
$appKeyFile = $runtime . '/.preview-app-key';
$database = $runtime . '/storage/preview.sqlite';
$expectedRuntime = realpath($root . '/.local/agendaally-preview/backend');
umask(0077);

$fail = static function (): never {
    fwrite(STDERR, "Credential rotation refused or failed; no secret values were reported.\n");
    exit(1);
};

if ($expectedRuntime === false || realpath($runtime) !== $expectedRuntime ||
    !is_file($marker) || is_link($marker) ||
    realpath($marker) !== $expectedRuntime . '/.original-http-preview-owned' ||
    !is_file($credentialFile) || is_link($credentialFile) ||
    realpath($credentialFile) !== $expectedRuntime . '/.preview-credentials' ||
    !is_file($appKeyFile) || is_link($appKeyFile) ||
    realpath($appKeyFile) !== $expectedRuntime . '/.preview-app-key' ||
    !is_file($database) || is_link($database) ||
    realpath(dirname($database)) !== $expectedRuntime . '/storage' ||
    realpath($database) !== $expectedRuntime . '/storage/preview.sqlite') {
    $fail();
}

$credentials = [];
$lines = file($credentialFile, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
    $fail();
}
foreach ($lines as $line) {
    if ($line === '') {
        continue;
    }
    $separator = strpos($line, '=');
    if ($separator === false) {
        $fail();
    }
    $name = substr($line, 0, $separator);
    $value = substr($line, $separator + 1);
    if (array_key_exists($name, $credentials)) {
        $fail();
    }
    $credentials[$name] = $value;
}

$accounts = [
    ['id' => 1, 'emailKey' => 'ADMIN_EMAIL', 'passwordKey' => 'ADMIN_PASSWORD', 'email' => 'admin@agendaally.local'],
    ['id' => 2, 'emailKey' => 'CUSTOMER_EMAIL', 'passwordKey' => 'CUSTOMER_PASSWORD', 'email' => 'customer@agendaally.local'],
    ['id' => 3, 'emailKey' => 'MASTER_EMAIL', 'passwordKey' => 'MASTER_PASSWORD', 'email' => 'master@agendaally.local'],
];
foreach ($accounts as $account) {
    if (($credentials[$account['emailKey']] ?? null) !== $account['email'] ||
        !is_string($credentials[$account['passwordKey']] ?? null) ||
        $credentials[$account['passwordKey']] === '') {
        $fail();
    }
}

$rotatedCredentials = [];
foreach ($accounts as $account) {
    $rotatedCredentials[$account['passwordKey']] = bin2hex(random_bytes(24));
}
$newCredentialContents = implode("\n", [
    'ADMIN_EMAIL=' . $credentials['ADMIN_EMAIL'],
    'ADMIN_PASSWORD=' . $rotatedCredentials['ADMIN_PASSWORD'],
    'CUSTOMER_EMAIL=' . $credentials['CUSTOMER_EMAIL'],
    'CUSTOMER_PASSWORD=' . $rotatedCredentials['CUSTOMER_PASSWORD'],
    'MASTER_EMAIL=' . $credentials['MASTER_EMAIL'],
    'MASTER_PASSWORD=' . $rotatedCredentials['MASTER_PASSWORD'],
]) . "\n";
$newAppKeyContents = 'base64:' . base64_encode(random_bytes(32));

$stagedFiles = [];
$stageFile = static function (string $destination, string $contents) use (&$stagedFiles): void {
    $temporary = dirname($destination) . '/.' . basename($destination) . '.rotation-' . bin2hex(random_bytes(8));
    $handle = @fopen($temporary, 'x+b');
    if ($handle === false) {
        throw new RuntimeException('Unable to stage rotated private files.');
    }
    $stagedFiles[] = $temporary;
    try {
        if (!chmod($temporary, 0600) || fwrite($handle, $contents) !== strlen($contents) ||
            !fflush($handle)) {
            throw new RuntimeException('Unable to stage rotated private files.');
        }
    } finally {
        fclose($handle);
    }
};

try {
    $stageFile($credentialFile, $newCredentialContents);
    $stageFile($appKeyFile, $newAppKeyContents);

    $pdo = new PDO('sqlite:' . $database, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $tokenTable = $pdo->query(
        "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'personal_access_tokens'"
    )->fetchColumn();
    if ($tokenTable === false) {
        throw new RuntimeException('The owned token table is missing.');
    }

    $pdo->beginTransaction();
    try {
        $checkUser = $pdo->prepare('SELECT id FROM users WHERE id = ? AND email = ?');
        $updateUser = $pdo->prepare('UPDATE users SET password = ? WHERE id = ? AND email = ?');
        foreach ($accounts as $account) {
            $checkUser->execute([$account['id'], $account['email']]);
            $matched = $checkUser->fetchAll();
            if (count($matched) !== 1 || (int)$matched[0]['id'] !== $account['id']) {
                throw new RuntimeException('An expected synthetic account was not found.');
            }
            $hash = password_hash($rotatedCredentials[$account['passwordKey']], PASSWORD_BCRYPT);
            if (!is_string($hash)) {
                throw new RuntimeException('Unable to hash rotated credentials.');
            }
            $updateUser->execute([$hash, $account['id'], $account['email']]);
            if ($updateUser->rowCount() !== 1) {
                throw new RuntimeException('An expected synthetic account was not updated.');
            }
        }
        $tokenCount = (int)$pdo->query('SELECT COUNT(*) FROM personal_access_tokens')->fetchColumn();
        $pdo->exec('DELETE FROM personal_access_tokens');
        if ((int)$pdo->query('SELECT COUNT(*) FROM personal_access_tokens')->fetchColumn() !== 0) {
            throw new RuntimeException('Personal access tokens remain after invalidation.');
        }
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }

    foreach ([[$stagedFiles[0], $credentialFile], [$stagedFiles[1], $appKeyFile]] as [$temporary, $destination]) {
        if (!rename($temporary, $destination) || !chmod($destination, 0600)) {
            throw new RuntimeException('Unable to install rotated private files.');
        }
    }
    if (!chmod($database, 0600)) {
        throw new RuntimeException('Unable to retain private database permissions.');
    }
    foreach ([$database . '-wal', $database . '-shm'] as $sidecar) {
        if (is_file($sidecar) && !is_link($sidecar) && !chmod($sidecar, 0600)) {
            throw new RuntimeException('Unable to retain private database permissions.');
        }
    }

    printf(
        "Rotated passwords for %d owned users, replaced the preview application key, and invalidated %d personal access tokens.\n",
        count($accounts),
        $tokenCount
    );
} catch (Throwable $error) {
    $fail();
} finally {
    foreach ($stagedFiles as $temporary) {
        if (is_file($temporary)) {
            @unlink($temporary);
        }
    }
}