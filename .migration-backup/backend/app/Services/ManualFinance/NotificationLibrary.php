<?php
declare(strict_types=1);
namespace App\Services\ManualFinance;

use App\Models\EmailTemplate;
use App\Support\{FinancialEmailTemplates,ManagedEmailPresentation};
use Illuminate\Support\Facades\DB;

/** A local rendering boundary only. Delivery failure cannot reexecute money. */
final class NotificationLibrary
{
    public function render(string $intent): array
    {
        $n=DB::table('manual_financial_notifications')->where('id',$intent)->first();
        $event=$n?DB::table('manual_financial_events')->where('id',$n->event_id)->first():null;
        $w=$event?DB::table('manual_financial_workflows')->where('id',$event->workflow_id)->first():null;
        if (!$n || !$event || !$w || (int)$n->recipient_id!==(int)$w->beneficiary_id
            || $n->template_type!==$w->kind.'_'.strtolower($event->new_state)
            || !in_array($event->new_state,['REQUESTED','APPROVED','COMPLETED','REJECTED'],true)) throw new \DomainException('Committed financial notification linkage required.');
        if ($event->new_state==='COMPLETED' && ($w->state!=='COMPLETED'
            || !DB::table('manual_financial_evidence')->where('workflow_id',$w->id)->where('state','SUCCESS')->exists())) throw new \DomainException('Committed completion evidence required.');
        $template=DB::table('email_templates')->where('type',$n->template_type)->orderBy('id')->first();
        $content=$template?(array)$template:FinancialEmailTemplates::definitions()[$n->template_type];
        $content+=['type'=>$n->template_type,'alt_body'=>$content['body']];
        FinancialEmailTemplates::validate($content);
        $rendered=ManagedEmailPresentation::preview($content);
        $rendered['mode']='committed_financial_event_library_only_smtp_disabled';
        return $rendered+['event_id'=>$event->id,'recipient_id'=>$n->recipient_id,'template_type'=>$n->template_type];
    }
}
