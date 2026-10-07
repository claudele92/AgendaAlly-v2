<?php
declare(strict_types=1);
namespace App\Services\EmailSettingService;

use App\Jobs\SelectedAccountEmail;
use App\Models\User;
use App\Services\AuthService\PasswordResetService;
use Illuminate\Support\Facades\{Crypt,DB};

/** Explicit operator cancellation; never dispatches, recovers or delivers jobs. */
final class SelectedEmailRecovery
{
    public function cancel(int $userId, array $expected): array
    {
        if (!$expected || count($expected) > 3) throw new \RuntimeException('Invalid recovery scope.');
        return DB::transaction(function () use ($userId, $expected): array {
            $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            if ($user->email_verified_at) throw new \RuntimeException('Account state changed.');
            $selected = [];
            foreach ($expected as $entry) {
                $row = DB::table('selected_email_deliveries')->where('id', $entry['id'])->lockForUpdate()->first();
                if (!$row || (int)$row->user_id !== $userId
                    || !in_array($row->state, ['PENDING','BLOCKED'], true)
                    || $row->claimed_at !== null || $row->sent_at !== null
                    || !in_array($row->kind, ['verify','reset'], true)
                    || !hash_equals($entry['row_sha256'], self::fingerprint($row))) {
                    throw new \RuntimeException('Selected delivery changed or is unsafe.');
                }
                $payload = json_decode(Crypt::decryptString($row->encrypted_payload), true, 512, JSON_THROW_ON_ERROR);
                if (($payload['email'] ?? null) !== $user->email || !is_string($payload['challenge'] ?? null)) {
                    throw new \RuntimeException('Recipient binding mismatch.');
                }
                // Parse only inert scalar data. Never unserialize a queued PHP object.
                $matches = [];
                foreach (DB::table('jobs')->lockForUpdate()->get() as $job) {
                    $identity = self::jobIdentity($job);
                    if ($identity === $row->id) $matches[] = $job;
                    elseif (str_contains($job->payload, $row->id)) {
                        throw new \RuntimeException('Ambiguous selected job reference.');
                    }
                }
                if (count($matches) !== 1) throw new \RuntimeException('Selected job cardinality changed.');
                $job = $matches[0];
                if ((int)$job->id !== (int)$entry['job_id'] || $job->queue !== 'mvp-notifications'
                    || $job->reserved_at !== null || (int)$job->attempts !== 0
                    || !hash_equals($entry['job_sha256'], self::fingerprint($job))) {
                    throw new \RuntimeException('Selected job changed or was attempted.');
                }
                if (isset($selected[$row->id])) throw new \RuntimeException('Duplicate recovery identity.');
                $selected[$row->id] = [$row, $job, $payload];
            }
            // Validate the complete set before making any change.
            $receipt = [];
            foreach ($selected as [$row, $job, $payload]) {
                $resetBefore = DB::table('password_resets')->count();
                if ($row->kind === 'reset') {
                    // Native exact-recipient + exact-token discard. A newer token is untouched.
                    app(PasswordResetService::class)->discardEmailToken($user, $payload['challenge']);
                }
                if (DB::table('selected_email_deliveries')->where('id',$row->id)->where('state',$row->state)
                    ->whereNull('claimed_at')->whereNull('sent_at')->update([
                        'state'=>'EXPIRED','error_code'=>'ACCEPTANCE_RECOVERY_CANCELLED','updated_at'=>now(),
                    ]) !== 1
                    || DB::table('jobs')->where('id',$job->id)->where('queue',$job->queue)
                        ->where('payload',$job->payload)->whereNull('reserved_at')->where('attempts',0)->delete() !== 1) {
                    throw new \RuntimeException('Concurrent recovery change; transaction rolled back.');
                }
                $receipt[] = [
                    'delivery_id'=>$row->id,'user_id'=>$userId,'kind'=>$row->kind,
                    'previous_state'=>$row->state,'state'=>'EXPIRED',
                    'reason'=>'ACCEPTANCE_RECOVERY_CANCELLED','removed_job_id'=>(int)$job->id,
                    'removed_job_sha256'=>self::fingerprint($job),
                    'reset_challenge_revoked'=>$row->kind === 'reset' && DB::table('password_resets')->count() < $resetBefore,
                ];
            }
            return $receipt;
        });
    }

    public static function fingerprint(object $row): string
    {
        return hash('sha256', json_encode((array)$row, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }

    public static function jobIdentity(object $job): ?string
    {
        $json = json_decode($job->payload, true);
        if (!is_array($json) || ($json['displayName'] ?? null) !== SelectedAccountEmail::class
            || ($json['data']['commandName'] ?? null) !== SelectedAccountEmail::class) return null;
        $serialized = $json['data']['command'] ?? '';
        if (!is_string($serialized) || !str_starts_with($serialized, 'O:'.strlen(SelectedAccountEmail::class).':"'.SelectedAccountEmail::class.'":')
            || preg_match_all('/s:10:"deliveryId";s:36:"([a-f0-9-]{36})";/', $serialized, $matches) !== 1) return null;
        return $matches[1][0];
    }
}
