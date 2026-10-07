<?php
declare(strict_types=1);
namespace AgendaAlly\Staging;
// Operational lab-only queue job. No financial or provider capability.
final class RecoveryProbe implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Bus\Queueable,\Illuminate\Foundation\Bus\Dispatchable,
        \Illuminate\Queue\InteractsWithQueue,\Illuminate\Queue\SerializesModels;
    public int $tries=1;
    public function __construct(public string $id) {}
    public function handle():void {
        $row=\Illuminate\Support\Facades\DB::table('staging_operational_probes')->find($this->id);
        if($row?->state==='FAIL_ONCE') {
            \Illuminate\Support\Facades\DB::table('staging_operational_probes')->where('id',$this->id)->update(['state'=>'RETRY_ALLOWED']);
            throw new \RuntimeException('STAGING_SAFE_QUEUE_FAILURE');
        }
        \Illuminate\Support\Facades\DB::table('staging_operational_probes')->where('id',$this->id)
            ->where('state','RETRY_ALLOWED')->update(['state'=>'RECOVERED']);
    }
}