<?php
declare(strict_types=1);
namespace App\Services\ManualFinance;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB,Storage,URL};
use Illuminate\Support\Str;

final class PrivateEvidence
{
    private function authorize(User $actor,object $w,bool $view): void
    {
        $a=DB::table('commerce_payment_allocations')->where('id',$w->allocation_id)->first();
        if (!$a) abort(404);
        $scope=new FinanceScope;
        if ($view) $scope->require($actor,$a,$w->kind,'evidence.view');
        elseif (!$scope->has($actor,$a,$w->kind,'complete') && !$scope->has($actor,$a,$w->kind,'reconcile')) abort(403);
    }

    public function upload(User $actor,object $w,UploadedFile $file): array
    {
        $this->authorize($actor,$w,false);
        if (in_array($w->state,['COMPLETED','REJECTED','CANCELLED'],true)) abort(409,'Terminal evidence is immutable.');
        $mime=$file->getMimeType();
        $extension=['application/pdf'=>'pdf','image/png'=>'png','image/jpeg'=>'jpg'][$mime]??null;
        if (!$file->isValid() || !$extension || $file->getSize()>2*1024*1024) abort(422,'Only PDF/PNG/JPEG receipts up to 2 MiB.');
        $bytes=file_get_contents($file->getRealPath());
        if ($bytes===false || ($mime==='application/pdf' && !str_starts_with($bytes,'%PDF-'))
            || ($mime!=='application/pdf' && @getimagesizefromstring($bytes)===false)) abort(422,'Malformed receipt.');
        // No scanner exists in the native uploader. Reject common active PDF
        // features; downloads remain attachments with sandbox/nosniff headers.
        if ($mime==='application/pdf' && preg_match('/\/(JavaScript|JS|Launch|EmbeddedFile|OpenAction|AA)\b/i',$bytes)) abort(422,'Active PDF documents are not accepted.');
        $id=(string)Str::uuid(); $path='manual-finance/'.$id.'.'.$extension;
        if (!Storage::disk('local')->put($path,$bytes,['visibility'=>'private'])) throw new \RuntimeException('Private receipt could not be retained.');
        try {
            DB::transaction(function() use($actor,$w,$id,$path,$mime,$bytes): void {
                $current=DB::table('manual_financial_workflows')->where('id',$w->id)->lockForUpdate()->first();
                $this->authorize($actor,$current,false);
                if (in_array($current->state,['COMPLETED','REJECTED','CANCELLED'],true)) abort(409);
                if (!DB::table('manual_financial_attachments')->insert(['id'=>$id,'workflow_id'=>$w->id,'created_by'=>$actor->id,
                    'path'=>$path,'mime'=>$mime,'bytes'=>strlen($bytes),'sha256'=>hash('sha256',$bytes),'created_at'=>now()->utc()])
                    || !DB::table('manual_financial_attachments')->where('id',$id)->exists()) throw new \RuntimeException('Private attachment metadata not saved.');
            });
        } catch (\Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
        return ['id'=>$id,'sha256'=>hash('sha256',$bytes)];
    }

    public function verify(object $attachment): string
    {
        if (!preg_match('~^manual-finance/[a-f0-9-]{36}\.(pdf|png|jpg)$~D',$attachment->path)) throw new \DomainException('Private receipt binding invalid.');
        $bytes=Storage::disk('local')->get($attachment->path);
        if (!is_string($bytes) || strlen($bytes)!==(int)$attachment->bytes || !hash_equals($attachment->sha256,hash('sha256',$bytes))) {
            throw new \DomainException('Private receipt digest mismatch or receipt unavailable.');
        }
        return $bytes;
    }

    public function link(User $actor,object $w,object $attachment): string
    {
        $this->authorize($actor,$w,true);
        if ($attachment->workflow_id!==$w->id) abort(404);
        $this->verify($attachment);
        $this->audit($actor,$attachment,'link');
        return URL::temporarySignedRoute('manual-finance.evidence',now()->addMinutes(5),
            ['workflow'=>$w->id,'attachment'=>$attachment->id,'actor'=>$actor->id],false);
    }

    public function download(User $actor,object $w,object $attachment): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize($actor,$w,true); // Fresh grant again, even for a previously signed URL.
        if ($attachment->workflow_id!==$w->id) abort(404);
        $bytes=$this->verify($attachment); $this->audit($actor,$attachment,'download');
        $extension=['application/pdf'=>'pdf','image/png'=>'png','image/jpeg'=>'jpg'][$attachment->mime];
        return response($bytes,200,['Content-Type'=>$attachment->mime,
            'Content-Disposition'=>'attachment; filename="receipt.'.$extension.'"','X-Content-Type-Options'=>'nosniff',
            'Content-Security-Policy'=>"sandbox; default-src 'none'",'Cache-Control'=>'private, no-store','Referrer-Policy'=>'no-referrer']);
    }
    private function audit(User $actor,object $attachment,string $action): void
    {
        if (!DB::table('manual_financial_evidence_access')->insert(['id'=>(string)Str::uuid(),'attachment_id'=>$attachment->id,
            'actor_id'=>$actor->id,'action'=>$action,'created_at'=>now()->utc()])) throw new \RuntimeException('Required evidence access audit unavailable.');
    }
}
