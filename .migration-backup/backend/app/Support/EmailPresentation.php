<?php
declare(strict_types=1);

namespace App\Support;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Presentation only: attaches owned, local raster files; never fetches URLs,
 * opens a transport, or reads provider settings/credentials.
 */
final class EmailPresentation
{
    private const SOCIALS = [
        'instagram' => ['Instagram', ['instagram.com', 'www.instagram.com']],
        'facebook' => ['Facebook', ['facebook.com', 'www.facebook.com']],
        'twitter' => ['X', ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com']],
        'linkedin' => ['LinkedIn', ['linkedin.com', 'www.linkedin.com']],
    ];

    public static function prepare(?PHPMailer $mailer, array $settings): array
    {
        $presentation = ['logo' => null, 'logo_status' => 'not_configured', 'socials' => []];
        $source = trim((string) ($settings['logo'] ?? ''));
        if ($source !== '') {
            $presentation['logo_status'] = 'unavailable';
            // Resolve /storage/ against owned public storage, never a recipient's
            // localhost and never an HTTP fetch. realpath prevents traversal.
            $path = parse_url($source, PHP_URL_PATH);
            if (is_string($path) && str_starts_with($path, '/storage/')) {
                $root = realpath(storage_path('app/public'));
                $file = realpath(storage_path('app/public/' . rawurldecode(substr($path, 9))));
                if ($root && $file && str_starts_with($file, $root . DIRECTORY_SEPARATOR)) {
                    $presentation['logo'] = self::embed($mailer, $file, 'agendaally-logo');
                    if ($presentation['logo']) {
                        $presentation['logo_status'] = 'embedded';
                    }
                }
            }
        }

        foreach (self::SOCIALS as $key => [$label, $hosts]) {
            $url = self::socialUrl((string) ($settings[$key] ?? ''), $hosts);
            if ($url === null) {
                continue; // Unconfigured/invalid destinations must not become links.
            }
            $icon = self::embed($mailer, resource_path("email/icons/$key.png"), "agendaally-$key");
            $presentation['socials'][] = ['label' => $label, 'url' => $url, 'icon' => $icon];
        }

        return $presentation;
    }

    public static function socialUrl(string $value, array $hosts): ?string
    {
        $value = trim($value);
        if ($value === '' || strlen($value) > 2048 || preg_match('/[\x00-\x20\x7f]/', $value)) {
            return null;
        }
        if (str_starts_with($value, '//')) {
            $value = 'https:' . $value;
        } elseif (!preg_match('~^[a-z][a-z0-9+.-]*://~i', $value)) {
            $value = 'https://' . $value;
        }
        $parts = parse_url($value);
        if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || !in_array(strtolower($parts['host'] ?? ''), $hosts, true)
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])
            || !filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }
        $path = trim($parts['path'] ?? '', '/');
        if ($path === '' || preg_match('~(?:^|/)(?:example|placeholder|your-profile|username)(?:/|$)~i', $path)) {
            return null;
        }
        return 'https://' . strtolower($parts['host']) . ($parts['path'] ?? '')
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    public static function attachPublicFile(PHPMailer $mailer, string $path): void
    {
        $root = realpath(storage_path('app/public'));
        $file = realpath(storage_path('app/public/' . ltrim($path, '/')));
        if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR)
            || !is_file($file) || !is_readable($file)) {
            throw new \RuntimeException('EMAIL_TEMPLATE_ATTACHMENT_UNAVAILABLE');
        }
        $mailer->addAttachment($file);
    }

    private static function embed(?PHPMailer $mailer, string $file, string $cid): ?array
    {
        if (!$mailer || !is_file($file) || !is_readable($file) || filesize($file) > 2 * 1024 * 1024) {
            return null;
        }
        $image = @getimagesize($file);
        if (!$image || !in_array($image['mime'] ?? '', ['image/png', 'image/jpeg', 'image/gif'], true)
            || $image[0] < 1 || $image[1] < 1 || $image[0] > 8192 || $image[1] > 8192) {
            return null;
        }
        $exists = false;
        foreach ($mailer->getAttachments() as $attachment) {
            if ($attachment[6] === 'inline' && $attachment[7] === $cid) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $mailer->addEmbeddedImage($file, $cid, basename($file), 'base64', $image['mime']);
        }
        $scale = min(40 / $image[1], 220 / $image[0], 1);
        return ['src' => 'cid:' . $cid, 'width' => max(1, (int) round($image[0] * $scale)),
            'height' => max(1, (int) round($image[1] * $scale))];
    }
}
