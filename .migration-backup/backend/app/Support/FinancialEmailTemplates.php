<?php
declare(strict_types=1);
namespace App\Support;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Admin-managed presentation; no sender, queue job, provider, account or recipient lookup. */
final class FinancialEmailTemplates
{
    public static function definitions(): array
    {
        $definitions=[];
        foreach (['refund','payout'] as $kind) {
            $label=ucfirst($kind);
            $definitions[$kind.'_requested']=['subject'=>$label.' request received',
                'body'=>'Your '.$kind.' request was received. This is a request, not confirmation of money movement.'];
            $definitions[$kind.'_approved']=['subject'=>$label.' approved — not yet '.($kind==='refund'?'refunded':'paid'),
                'body'=>'Your '.$kind.' request was approved — not yet '.($kind==='refund'?'refunded':'paid').'. Finance still needs to execute and record the transfer.'];
            $definitions[$kind.'_completed']=['subject'=>$label.' completed',
                'body'=>'Finance recorded your '.$kind.' as completed after checking retained manual execution evidence. View the amount, currency and reference in AgendaAlly.'];
            $definitions[$kind.'_rejected']=['subject'=>$label.' request rejected',
                'body'=>'Your '.$kind.' request was rejected. This is a request decision, not a report of a failed provider transfer.'];
        }
        return $definitions;
    }
    public static function isFinancial(?string $type): bool { return isset(self::definitions()[$type??'']); }
    public static function validate(array $data): void
    {
        $type=$data['type']??'';
        if (!self::isFinancial($type)) return;
        $state=substr($type,strrpos($type,'_')+1);
        $text=html_entity_decode(strip_tags(implode(' ',[$data['subject']??'',$data['body']??'',$data['alt_body']??''])),ENT_QUOTES|ENT_HTML5,'UTF-8');
        if ($state==='approved' && !preg_match('/approved\s*[—–-]\s*not yet (?:refunded|paid)/iu',$text)) {
            throw ValidationException::withMessages(['body'=>['Approved presentation must explicitly say approved — not yet refunded/paid.']]);
        }
        $neutral=preg_replace('/\bnot\s+(?:yet\s+)?(?:paid|refunded|completed)\b/iu','',$text);
        if ($state!=='completed' && preg_match('/\b(?:paid|refunded|completed)\b|\b(?:money|funds|refund|payout)\s+(?:was\s+|were\s+)?received\b/iu',$neutral)) {
            throw ValidationException::withMessages(['body'=>['Only committed completion may claim financial completion.']]);
        }
        if ($state==='rejected' && preg_match('/(?:provider|refund|payout|transfer|payment)\s+(?:has\s+|was\s+)?failed\b/iu',$text)) {
            // The built-in wording negates this claim, so remove its complete
            // literal sentence before applying this invariant.
            $safe=str_replace('not a report of a failed provider transfer','',$text);
            if (preg_match('/(?:provider|refund|payout|transfer|payment)\s+(?:has\s+|was\s+)?failed\b/iu',$safe)) {
                throw ValidationException::withMessages(['body'=>['Rejection is not a failed provider transfer.']]);
            }
        }
        if ($state==='completed' && !preg_match('/\bcompleted\b/iu',$text)) throw ValidationException::withMessages(['body'=>['Completion presentation must identify the committed completed state.']]);
    }
    public static function ensure(): int
    {
        return DB::transaction(function(): int {
            $provider=DB::table('email_settings')->where('active',true)->orderByDesc('updated_at')->lockForUpdate()->first(['id']);
            if (!$provider) return 0;
            $count=0;
            foreach (self::definitions() as $type=>$definition) {
                if (DB::table('email_templates')->where('type',$type)->exists()) continue;
                if (!DB::table('email_templates')->insert($definition+['type'=>$type,'alt_body'=>$definition['body'],
                    'email_setting_id'=>$provider->id,'status'=>EmailTemplate::STATUS_LIBRARY_ONLY,
                    'send_to'=>'2099-01-01 00:00:00','created_at'=>now(),'updated_at'=>now()])) throw new \RuntimeException('Financial presentation not retained.');
                $count++;
            }
            return $count;
        });
    }
}
