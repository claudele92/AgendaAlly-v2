<?php
declare(strict_types=1);

namespace App\Support;

use App\Models\EmailTemplate;
use Illuminate\Validation\ValidationException;

/** Canonical literal placeholders; template text is never executable Blade/PHP. */
final class EmailTemplateContent
{
    public static function validate(array $data): void
    {
        $type = $data['type'] ?? null;
        if (!in_array($type, EmailTemplate::TYPES, true)) self::fail('type', 'Unsupported email template type.');
        FinancialEmailTemplates::validate($data);
        foreach (['subject' => 255, 'body' => 50000, 'alt_body' => 50000] as $field => $maximum) {
            $value = $data[$field] ?? null;
            if (!is_string($value) || trim($value) === '' || mb_strlen($value) > $maximum) {
                self::fail($field, 'Required presentation text is missing or too long.');
            }
            if (str_contains($value, '<?') || preg_match('/@(?:php|if|foreach|include|extends|yield|section|component|inject|eval|while|for|switch)\b/i', $value)) {
                self::fail($field, 'Executable template syntax is not allowed.');
            }
            preg_match_all('/\$[a-z_][a-z0-9_]*|\{\{.*?\}\}|\{!!.*?!!\}/is', $value, $matches);
            $allowed = $field === 'subject' ? [] : SystemEmailTemplates::placeholders($type);
            foreach ($matches[0] as $placeholder) {
                if (!in_array($placeholder, $allowed, true)) self::fail($field, 'Unknown or forbidden placeholder.');
            }
            if ($field === 'subject' && preg_match('/[\r\n]/', $value)) self::fail($field, 'Subject must be one line.');
            if ($field !== 'subject' && SystemEmailTemplates::isSystem($type) && !str_contains($value, '$verify_code')) {
                self::fail($field, 'The required $verify_code placeholder must remain in body and alternate body.');
            }
        }
        self::validateHtml($data['body']);
    }

    private static function validateHtml(string $body): void
    {
        if (stripos($body, '<!doctype') !== false) self::fail('body', 'Use a presentation fragment, not a document.');
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new \DOMDocument();
            $document->loadHTML('<div>'.$body.'</div>', LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $allowed = ['div', 'p', 'span', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
                'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'code', 'pre',
                'table', 'thead', 'tbody', 'tr', 'td', 'th', 'hr', 'a', 'figure'];
            foreach ($document->getElementsByTagName('*') as $element) {
                if (!in_array(strtolower($element->tagName), $allowed, true)) self::fail('body', 'Unsafe HTML element.');
                foreach ($element->attributes as $attribute) {
                    $name = strtolower($attribute->name);
                    if (str_contains($attribute->value, '$verify_code')) {
                        self::fail('body', 'Challenge placeholders belong in visible copy, never in HTML attributes or links.');
                    }
                    if ($name === 'class' && $element->tagName === 'figure' && $attribute->value === 'table') continue;
                    if ($name === 'href' && $element->tagName === 'a'
                        && preg_match('/^mailto:[a-z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-z0-9.-]+$/i', $attribute->value)) continue;
                    if ($name === 'style' && self::safeStyle($attribute->value)) continue;
                    if (in_array($name, ['colspan', 'rowspan'], true) && ctype_digit($attribute->value)) continue;
                    self::fail('body', 'Unsafe HTML attribute or link. Account/action URLs remain application controlled.');
                }
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private static function safeStyle(string $style): bool
    {
        foreach (explode(';', $style) as $declaration) {
            if (trim($declaration) === '') continue;
            if (!preg_match('/^\s*(color|background-color|font-size|font-family|font-weight|font-style|text-align|text-decoration|line-height|letter-spacing|margin(?:-(?:top|bottom|left|right))?|padding(?:-(?:top|bottom|left|right))?)\s*:\s*[a-z0-9#%,.\'"\s-]+\s*$/i', $declaration)) return false;
        }
        return true;
    }

    private static function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => [$message]]);
    }
}
