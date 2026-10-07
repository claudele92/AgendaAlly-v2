<?php
declare(strict_types=1);

namespace App\Support;

use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\View;

final class ManagedEmailPresentation
{
    public static function render(PHPMailer $mail, array $presentation, ?string $challenge = null): void
    {
        EmailTemplateContent::validate($presentation);
        $mail->Subject = $presentation['subject'];
        $content = $presentation['body'];
        $alternate = $presentation['alt_body'];
        if (SystemEmailTemplates::isSystem($presentation['type'])) {
            if ($challenge === null) throw new \LogicException('Application-owned challenge is required.');
            $content = str_replace('$verify_code', e($challenge), $content);
            $alternate = str_replace('$verify_code', $challenge, $alternate);
        }
        $mail->Body = View::make('emails.layout', [
            'mailer' => $mail, 'title' => $mail->Subject, 'content' => $content,
        ])->render();
        $mail->AltBody = $presentation['type'] === \App\Models\EmailTemplate::TYPE_SUBSCRIBE
            ? $alternate // Match the existing Subscription sender exactly.
            : EmailPlainText::fromHtml($alternate, $mail->Subject);
    }

    /** Brand assets are local/CID; preview never prepares SMTP or a live account. */
    public static function preview(array $presentation): array
    {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $sample = SystemEmailTemplates::isSystem($presentation['type']) ? '123456' : null;
        self::render($mail, $presentation, $sample);
        $html = $mail->Body;
        foreach ($mail->getAttachments() as $attachment) {
            if ($attachment[6] !== 'inline' || empty($attachment[7])) continue;
            $bytes = $attachment[5] ? $attachment[0] : file_get_contents($attachment[0]);
            $html = str_replace('cid:'.$attachment[7], 'data:'.$attachment[4].';base64,'.base64_encode($bytes), $html);
        }
        return [
            'type' => $presentation['type'], 'subject' => $mail->Subject,
            'html' => $html, 'text' => $mail->AltBody, 'mode' => 'synthetic_no_send',
            'placeholders' => SystemEmailTemplates::placeholders($presentation['type']),
            'sample_values' => $sample === null ? [] : ['$verify_code' => $sample],
        ];
    }
}
