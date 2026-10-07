<?php
declare(strict_types=1);
namespace App\Services\EmailSettingService;

use App\Jobs\SelectedAccountEmail;
use App\Models\User;
use Illuminate\Support\Facades\{Crypt,DB};
use Illuminate\Support\Str;

/** Operational outbox only; no booking, payment or capacity authority. */
final class SelectedEmailDelivery
{
    public function enqueue(User $user, string $kind, string $challenge): string
    {
        if (!in_array($kind,['verify','reset'],true)) throw new \InvalidArgumentException('Unsupported selected email.');
        $key=hash_hmac('sha256',$kind.'|'.$user->id.'|'.$user->email.'|'.$challenge,(string)config('app.key'));
        return DB::transaction(function()use($user,$kind,$challenge,$key):string {
            // Serializes same-recipient issue/duplicate dispatch without new identity.
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $id=DB::table('selected_email_deliveries')->where('event_key',$key)->value('id');
            if ($id) return $id;
            $id=(string)Str::uuid();
            DB::table('selected_email_deliveries')->insert([
                'id'=>$id,'event_key'=>$key,'user_id'=>$user->id,'kind'=>$kind,
                'encrypted_payload'=>Crypt::encryptString(json_encode(['email'=>$user->email,'challenge'=>$challenge],JSON_THROW_ON_ERROR)),
                'state'=>'PENDING','expires_at'=>now()->addMinutes($kind==='verify'?10:60),
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            SelectedAccountEmail::dispatch($id)->onConnection('database')->onQueue('mvp-notifications')->afterCommit();
            return $id;
        },3);
    }

    /** Selected tick recovers the commit-to-queue gap; duplicate jobs are harmless. */
    public function recover(): int
    {
        $ids=DB::table('selected_email_deliveries')->whereIn('state',['PENDING','BLOCKED'])
            ->where('expires_at','>',now())->pluck('id');
        foreach($ids as $id) SelectedAccountEmail::dispatch($id)->onConnection('database')->onQueue('mvp-notifications');
        DB::table('selected_email_deliveries')->whereIn('state',['PENDING','BLOCKED'])
            ->where('expires_at','<=',now())->update(['state'=>'EXPIRED','updated_at'=>now()]);
        return $ids->count();
    }

    public function deliver(string $id): string
    {
        $row=DB::table('selected_email_deliveries')->find($id);
        if (!$row || in_array($row->state,['SENT','EXPIRED','FAILED','UNKNOWN'],true)) return $row?->state??'MISSING';
        if ($row->state==='SENDING') {
            if ($row->claimed_at && \Illuminate\Support\Carbon::parse($row->claimed_at)->greaterThan(now()->subSeconds(90))) {
                return 'BUSY'; // A duplicate job must not disturb a live worker.
            }
            // A killed worker cannot prove whether SMTP accepted DATA.
            DB::table('selected_email_deliveries')->where('id',$id)->where('state','SENDING')
                ->update(['state'=>'UNKNOWN','error_code'=>'SMTP_ACK_UNCERTAIN','updated_at'=>now()]);
            return 'UNKNOWN';
        }
        if (\App\Helpers\EnvironmentPolicy::emailMode()!=='smtp'
            && !\App\Helpers\SelectedAccountEmailPolicy::permitsRow($row)) {
            DB::table('selected_email_deliveries')->where('id',$id)->whereIn('state',['PENDING','BLOCKED'])
                ->update(['state'=>'BLOCKED','error_code'=>'EXTERNAL_TRANSPORT_DISABLED','updated_at'=>now()]);
            return 'BLOCKED';
        }
        if (now()->greaterThanOrEqualTo($row->expires_at)) {
            DB::table('selected_email_deliveries')->where('id',$id)->update(['state'=>'EXPIRED','updated_at'=>now()]);
            return 'EXPIRED';
        }
        $payload=json_decode(Crypt::decryptString($row->encrypted_payload),true,512,JSON_THROW_ON_ERROR);
        $user=User::find($row->user_id);
        $current=$user && ($row->kind==='verify'
            ?app(\App\Services\AuthService\EmailVerificationService::class)->isCurrent($user,$payload['challenge'])
            :app(\App\Services\AuthService\PasswordResetService::class)->isCurrentEmailToken($user,$payload['challenge']));
        if (!$current || $user->email!==$payload['email']) {
            DB::table('selected_email_deliveries')->where('id',$id)->update(['state'=>'EXPIRED','updated_at'=>now()]);
            return 'EXPIRED';
        }
        // Recipient claim is committed before any SMTP operation, never inside
        // the caller's still-open account/booking transaction.
        if (DB::connection()->transactionLevel()!==0) throw new \RuntimeException('Selected email requires committed caller.');
        if (DB::table('selected_email_deliveries')->where('id',$id)->whereIn('state',['PENDING','BLOCKED'])
            ->update(['state'=>'SENDING','claimed_at'=>now(),'updated_at'=>now()])!==1) return 'BUSY';
        try {
            if (\App\Support\AccountEmailEvidence::selected($row)) {
                \App\Support\AccountEmailEvidence::append('isolated_processing_claimed',['delivery_id'=>$id,'user_id'=>(int)$row->user_id]);
            }
            $sender=app(EmailSendService::class);
            // Ephemeral authority cannot escape the selected delivery callback.
            // Non-approved operations remain suppressed even during this scope.
            app()->instance('agendaally.selected_account_delivery', $row);
            try {
                $result=$row->kind==='verify'
                    ?$sender->sendVerify($user,$payload['challenge'])
                    :$sender->sendEmailPasswordReset($user,$payload['challenge']);
            } finally {
                app()->forgetInstance('agendaally.selected_account_delivery');
            }
            if (empty($result['status'])) throw new \RuntimeException('SMTP acknowledgement unavailable.');
            DB::table('selected_email_deliveries')->where('id',$id)->where('state','SENDING')
                ->update(['state'=>'SENT','sent_at'=>now(),'error_code'=>null,'updated_at'=>now()]);
            if (\App\Support\AccountEmailEvidence::selected($row)) {
                \App\Support\AccountEmailEvidence::append('selected_delivery_sent',['delivery_id'=>$id,'smtp_send_returned_true'=>true]);
            }
            return 'SENT';
        } catch (\Throwable) {
            DB::table('selected_email_deliveries')->where('id',$id)->where('state','SENDING')
                ->update(['state'=>'UNKNOWN','error_code'=>'SMTP_ACK_UNCERTAIN','updated_at'=>now()]);
            return 'UNKNOWN';
        }
    }
}