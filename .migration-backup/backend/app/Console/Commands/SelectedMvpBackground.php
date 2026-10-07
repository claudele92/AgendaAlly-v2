<?php
declare(strict_types=1);
namespace App\Console\Commands;
use Illuminate\Console\Command;
final class SelectedMvpBackground extends Command
{
    protected $signature='mvp:background-tick {--status : Redacted operational counts only}';
    protected $description='Selected account outbox recovery and booking reminders only; no financial/provider/cleanup jobs';
    public function handle(): int {
        if ($this->option('status')) {
            foreach(\Illuminate\Support\Facades\DB::table('selected_email_deliveries')->selectRaw('state, count(*) as total')->groupBy('state')->get() as $row)
                $this->line($row->state.': '.$row->total);
            return self::SUCCESS;
        }
        app(\App\Services\EmailSettingService\SelectedEmailDelivery::class)->recover();
        $this->call('booking:send:notification');
        return self::SUCCESS;
    }
}