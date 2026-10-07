<?php
declare(strict_types=1);

$runtime = getenv('AGENDAALLY_PREVIEW_RUNTIME');
$database = getenv('AGENDAALLY_PREVIEW_DB');
$marker = is_string($runtime) ? $runtime . '/.original-http-preview-owned' : '';
$expectedDatabase = is_string($runtime) ? $runtime . '/storage/preview.sqlite' : '';

if (!$runtime || !is_file($marker) || realpath($runtime) !== $runtime ||
    !$database || realpath($database) !== $expectedDatabase ||
    !is_file($database)) {
    http_response_code(500);
    echo 'The owned original preview runtime is not configured.';
    return true;
}

$publicRoot = realpath($runtime . '/public');
$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$staticPath = realpath($publicRoot . $requestPath);
if ($publicRoot && $staticPath && str_starts_with($staticPath, $publicRoot . DIRECTORY_SEPARATOR) &&
    is_file($staticPath)) {
    return false;
}

require __DIR__ . '/original-http-preview-front-controller.php';
return true;