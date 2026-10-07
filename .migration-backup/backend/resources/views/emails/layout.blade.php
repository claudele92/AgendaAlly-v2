<!DOCTYPE html>
<html lang="en">
<?php
/**
 * @var string $title
 * @var string $content
 */
use App\Models\Settings;
use App\Support\EmailPresentation;

$settings = Settings::whereIn('key', ['logo', 'title', 'instagram', 'facebook', 'twitter', 'linkedin', 'footer_text'])
    ->pluck('value', 'key');

$presentation = EmailPresentation::prepare($mailer ?? null, $settings->all());
$logo       = $presentation['logo'];
$appName    = $settings->get('title') ?: 'AgendaAlly';
$footerText = $settings->get('footer_text') ?: '&copy; ' . date('Y') . ' ' . $appName;

$socials = $presentation['socials'];
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? $appName }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family:Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="table-layout:fixed; background-color:#f4f4f5; padding:32px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="table-layout:fixed; background-color:#ffffff; border-radius:12px; overflow:hidden; max-width:600px; width:100%;">
                <tr>
                    <td align="center" style="padding:32px 24px 16px;">
                        @if($logo)
                            <img src="{{ $logo['src'] }}" alt="{{ $appName }}" width="{{ $logo['width'] }}" height="{{ $logo['height'] }}" style="display:block; border:0; max-width:220px;">
                        @else
                            <span style="font-size:22px; font-weight:700; color:#111827;">{{ $appName }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 32px 32px; color:#111827; font-size:15px; line-height:1.6; word-wrap:break-word; overflow-wrap:anywhere; word-break:break-word;">
                        {!! $content !!}
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 32px; background-color:#18181b;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            @if(count($socials))
                                <tr>
                                    <td align="center" style="padding-bottom:16px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" align="center">
                                            <tr>
                                                @foreach($socials as $social)
                                                    <td align="center" style="padding:0 8px;">
                                                        <a href="{{ $social['url'] }}" aria-label="{{ $social['label'] }}" style="color:#ffffff; text-decoration:underline; font-size:12px;">
                                                            @if($social['icon'])
                                                                <img src="{{ $social['icon']['src'] }}" alt="{{ $social['label'] }}" width="32" height="32" style="display:block; border:0;">
                                                            @else
                                                                {{ $social['label'] }}
                                                            @endif
                                                        </a>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td align="center" style="color:#a1a1aa; font-size:12px; word-wrap:break-word; overflow-wrap:anywhere; word-break:break-word;">
                                    {!! $footerText !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
