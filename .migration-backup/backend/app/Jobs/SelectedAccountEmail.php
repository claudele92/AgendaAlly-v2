<?php
declare(strict_types=1);
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue,SerializesModels};

final class SelectedAccountEmail implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
    public int $tries=3;
    public int $timeout=30;
    public function __construct(public string $deliveryId) {}
    public function handle(\App\Services\EmailSettingService\SelectedEmailDelivery $delivery): void {
        if ($delivery->deliver($this->deliveryId)==='UNKNOWN') {
            $this->fail(new \RuntimeException('Selected email acknowledgement uncertain; retained for operator review, never automatic resend.'));
        }
    }
    public function failed(?\Throwable $error): void {
        \Illuminate\Support\Facades\DB::table('selected_email_deliveries')->where('id',$this->deliveryId)
            ->whereIn('state',['PENDING','BLOCKED'])->update(['state'=>'FAILED','error_code'=>'SELECTED_QUEUE_INTERVENTION','updated_at'=>now()]);
    }
}