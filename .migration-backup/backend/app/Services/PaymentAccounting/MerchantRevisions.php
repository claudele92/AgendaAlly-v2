<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use App\Models\PaymentPayload;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class MerchantRevisions
{
    public static function installed(): bool
    {
        return Schema::hasTable('payment_merchant_revisions');
    }

    /** Called within the configuration save transaction; never backfill reads. */
    public function append(PaymentPayload $profile): void
    {
        if (!self::installed()) return;
        $tag = (string) $profile->payment()->value('tag');
        if (!in_array($tag,['flutter-wave','paystack','stripe','paypal'],true)) return;
        $id=(string)Str::uuid();
        DB::table('payment_merchant_revisions')->insert([
            'id'=>$id,'payment_id'=>$profile->payment_id,'provider'=>$tag,'owner_type'=>'platform',
            'encrypted_payload'=>Crypt::encryptString(AllocationWriter::json($profile->payload ?? [])),
            'created_at'=>now(),
        ]);
        $profile->forceFill(['revision_id'=>$id])->saveQuietly();
    }

    public function payload(string $id, string $provider): array
    {
        $r=DB::table('payment_merchant_revisions')->where('id',$id)->where('provider',$provider)
            ->where('owner_type','platform')->first();
        if (!$r) throw new \DomainException('Original merchant revision is unavailable.');
        return json_decode(Crypt::decryptString($r->encrypted_payload),true,512,JSON_THROW_ON_ERROR);
    }

    public function forProcess(\App\Models\PaymentProcess $process, string $provider): ?array
    {
        if (!self::installed()) return null;
        $a=DB::table('electronic_collection_attempts')->where('process_reference',$process->id)
            ->where('provider',$provider)->first();
        return $a ? $this->payload($a->revision_id,$provider) : null;
    }

    public function forReference(string $reference, string $provider): ?array
    {
        if (!self::installed() || $reference==='') return null;
        $process=DurableCollections::process($reference);
        if (!$process) return null;
        $payload=$this->forProcess($process,$provider);
        if (!$payload && !empty($process->data['generic_attempt_id'])) {
            throw new \DomainException('Original attempt configuration is unavailable.');
        }
        return $payload;
    }
}