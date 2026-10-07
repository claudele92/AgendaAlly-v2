<?php
declare(strict_types=1);

namespace App\Support;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Presentation defaults only; never issues, queues or sends a challenge. */
final class SystemEmailTemplates
{
    public static function definitions(): array
    {
        return [
            EmailTemplate::TYPE_VERIFY => [
                'subject' => 'Verify your email address',
                'body' => 'Enter this verification code in AgendaAlly to verify your email address: $verify_code. It is valid for up to 10 minutes after your request. If you did not request it, ignore this email.',
            ],
            EmailTemplate::TYPE_RESET => [
                'subject' => 'Reset password',
                'body' => 'Enter this password-reset code in AgendaAlly: $verify_code. It is valid for up to 60 minutes after your request. If you did not request it, ignore this email.',
            ],
            EmailTemplate::TYPE_SUBSCRIBE => [
                'subject' => 'AgendaAlly updates',
                'body' => '<p>News and updates from AgendaAlly.</p><p>Thank you for subscribing.</p>',
                'alt_body' => "News and updates from AgendaAlly.\n\nThank you for subscribing.",
            ],
        ];
    }

    public static function isSystem(?string $type): bool
    {
        // Account challenge semantics must never be applied to Subscription.
        return in_array($type, [EmailTemplate::TYPE_VERIFY, EmailTemplate::TYPE_RESET], true);
    }

    public static function isSystemRecord(EmailTemplate $template): bool
    {
        if (FinancialEmailTemplates::isFinancial($template->type)) return true;
        if (self::isSystem($template->type)) return true;
        if ($template->type !== EmailTemplate::TYPE_SUBSCRIBE) return false;
        // Preserve/adopt the oldest existing Subscription content as the
        // built-in default without rewriting it. Additional records are custom.
        return (int) $template->id === (int) DB::table('email_templates')
            ->where('type', EmailTemplate::TYPE_SUBSCRIBE)->orderBy('id')->value('id');
    }

    public static function placeholders(?string $type): array
    {
        return self::isSystem($type) ? ['$verify_code'] : [];
    }

    /** Serialize bootstrap on the existing provider; preserve all custom rows. */
    public static function ensure(): int
    {
        if (!Schema::hasTable('email_settings') || !Schema::hasTable('email_templates')) return 0;
        $financial=Schema::hasTable('manual_financial_notifications') ? FinancialEmailTemplates::ensure() : 0;
        return $financial+DB::transaction(function (): int {
            // Matches the native sender's fallback provider selection.
            $provider = DB::table('email_settings')->where('active', true)
                ->orderByDesc('updated_at')->lockForUpdate()->first(['id']);
            if (!$provider) return 0; // Provision on a later Admin visit once a provider exists.
            $created = 0;
            foreach (self::definitions() as $type => $definition) {
                if (DB::table('email_templates')->where('type', $type)->exists()) continue;
                DB::table('email_templates')->insert([
                    'email_setting_id' => $provider->id, 'type' => $type,
                    'subject' => $definition['subject'], 'body' => $definition['body'],
                    'alt_body' => $definition['alt_body'] ?? $definition['body'],
                    'status' => $type === EmailTemplate::TYPE_SUBSCRIBE ? EmailTemplate::STATUS_LIBRARY_ONLY : 0,
                    // Required legacy field only. Subscription's library-only
                    // status excludes it from scheduling at any date.
                    'send_to' => '2099-01-01 00:00:00',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $created++;
            }
            return $created;
        });
    }
}
