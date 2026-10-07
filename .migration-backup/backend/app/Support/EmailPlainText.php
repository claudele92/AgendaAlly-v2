<?php
declare(strict_types=1);

namespace App\Support;

/** Local HTML-to-text projection only; never fetches links or changes state. */
final class EmailPlainText
{
    public static function fromHtml(string $html, string $purpose = ''): string
    {
        $html = preg_replace('~<(head|script|style)\b[^>]*>.*?</\1>~is', '', $html) ?? '';
        $html = preg_replace_callback('~<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>~is', static function ($match): string {
            $label = html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $url = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $parts = parse_url($url);
            if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['https'], true)
                || isset($parts['user']) || isset($parts['pass']) || preg_match('/[\r\n]/', $url)) {
                return $label;
            }
            return $label . ' (' . $url . ')';
        }, $html) ?? '';
        $html = preg_replace('~<br\b[^>]*>|</(?:p|div|tr|h[1-6]|table)>~i', "\n", $html) ?? '';
        $html = preg_replace('~</(?:td|th)>~i', ' | ', $html) ?? '';
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lines = array_filter(array_map(static fn ($line) => trim(preg_replace('/[ \t]+/u', ' ', $line) ?? ''), explode("\n", $text)));
        return trim(($purpose !== '' ? $purpose . "\n\n" : '') . implode("\n", $lines));
    }
}
