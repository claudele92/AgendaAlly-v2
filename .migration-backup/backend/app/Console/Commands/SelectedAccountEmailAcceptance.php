<?php
declare(strict_types=1);
namespace App\Console\Commands;

use App\Helpers\SelectedAccountEmailPolicy;
use App\Jobs\SelectedAccountEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** One approved native job only. Never runs recovery, a shared worker or scheduler. */
final class SelectedAccountEmailAcceptance extends Command
{
    protected $signature = 'selected:account-email {delivery : Approved outbox UUID}';
    protected $description = 'Process one explicitly approved disposable account-email record through an isolated native queue.';

    public function handle(): int
    {
        $id = (string) $this->argument('delivery');
        if (!Str::isUuid($id) || !SelectedAccountEmailPolicy::runtimeAllowed()) {
            $this->error('Selected account-email runtime authority is absent; nothing processed.');
            return self::FAILURE;
        }
        $row = DB::table('selected_email_deliveries')->find($id);
        if (!$row || !SelectedAccountEmailPolicy::permitsRow($row)
            || !in_array($row->state, ['PENDING', 'BLOCKED'], true)
            || now()->greaterThanOrEqualTo($row->expires_at)) {
            $this->error('Selected account-email approval/current state is invalid; nothing processed.');
            return self::FAILURE;
        }
        if (DB::connection()->transactionLevel() !== 0) {
            $this->error('Selected account-email processing requires committed state.');
            return self::FAILURE;
        }
        $queue = 'account-email-acceptance-' . $id;
        $pending = DB::table('jobs')->whereIn('queue', ['mvp-notifications', $queue])
            ->where('payload', 'like', '%' . $id . '%')->get(['id', 'queue', 'payload', 'reserved_at', 'attempts']);
        if ($pending->count() !== 1 || $pending[0]->reserved_at !== null || (int) $pending[0]->attempts !== 0
            || DB::table('jobs')->where('queue', $queue)->where('id', '<>', $pending[0]->id)->exists()) {
            $this->error('Selected queue must contain exactly one fresh, correlated native job; nothing processed.');
            return self::FAILURE;
        }
        $payload = json_decode($pending[0]->payload, true);
        $expected = (new SelectedAccountEmail($id))->onConnection('database')->onQueue('mvp-notifications')->afterCommit();
        if (($payload['job'] ?? null) !== 'Illuminate\Queue\CallQueuedHandler@call'
            || ($payload['data']['commandName'] ?? null) !== SelectedAccountEmail::class
            || !hash_equals(serialize($expected), (string) ($payload['data']['command'] ?? ''))) {
            $this->error('Selected job identity is invalid; nothing processed.');
            return self::FAILURE;
        }
        // Move only the existing native after-commit job's operational queue metadata.
        if (DB::table('jobs')->where('id', $pending[0]->id)->whereNull('reserved_at')->where('attempts', 0)
            ->where('queue', $pending[0]->queue)->update(['queue' => $queue]) !== 1) {
            $this->error('Selected job changed concurrently; nothing processed.');
            return self::FAILURE;
        }
        if (\App\Support\AccountEmailEvidence::selected($row)) {
            \App\Support\AccountEmailEvidence::append('isolated_job_selected', [
                'delivery_id'=>$id,'job_id'=>(int)$pending[0]->id,
                'previous_queue'=>$pending[0]->queue,'isolated_queue'=>$queue,
                'attempts_before'=>0,'reserved_before'=>false,
            ]);
        }
        $this->call('queue:work', ['connection' => 'database', '--queue' => $queue,
            '--once' => true, '--tries' => 1, '--sleep' => 0, '--timeout' => 30, '--quiet' => true]);
        $state = DB::table('selected_email_deliveries')->where('id', $id)->value('state');
        $this->line('Selected account-email result: ' . $state . '. No shared queue was consumed.');
        return $state === 'SENT' ? self::SUCCESS : self::FAILURE;
    }
}
