<?php
declare(strict_types=1);
namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AccountEmailEvidence as Evidence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountEmailEvidence
{
    protected function context(): ?array { return Evidence::context(); }
    protected function record(string $event, array $facts): void { Evidence::append($event,$facts); }

    public function handle($request, \Closure $next)
    {
        $context = $this->context();
        $operation = ['api/v1/auth/register'=>'verification_request',
            'api/v1/auth/verify/email'=>'verification_submission'][$request->path()] ?? null;
        if (!$context || !$operation || !$request->isMethod('POST')) return $next($request);
        $user = User::find($context['user_id']);
        if (!$user || !hash_equals($context['recipient_sha256'], hash('sha256',strtolower(trim((string)$user->email))))
            || $request->input('email') !== $user->email) return $next($request);
        $id = (string)Str::uuid();
        $before = DB::table('selected_email_deliveries')->where('user_id',$user->id)->pluck('id')->all();
        $this->record($operation, [
            'request_id'=>$id,'user_id'=>(int)$user->id,
            'verified_before'=>$user->email_verified_at !== null,
        ]);
        try {
            $response = $next($request);
        } catch (\Throwable $error) {
            $status = $error instanceof \Illuminate\Validation\ValidationException ? 422
                : ($error instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $error->getStatusCode() : 500);
            $this->record('backend_exception_response', ['request_id'=>$id,'http_status'=>$status]);
            throw $error; // No exception text, arguments or submitted values retained.
        }
        $body = $response instanceof \Illuminate\Http\JsonResponse ? $response->getData(true) : [];
        $machineCode = $body['statusCode'] ?? null;
        $new = DB::table('selected_email_deliveries')->where('user_id',$user->id)
            ->whereNotIn('id',$before)->get(['id','kind','state','created_at','expires_at'])->all();
        $after = $user->fresh();
        $facts = [
            'request_id'=>$id,'operation'=>$operation,'http_status'=>$response->getStatusCode(),
            'machine_code'=>is_string($machineCode) && preg_match('/^[A-Z0-9_]{1,40}$/',$machineCode) ? $machineCode : null,
            'new_outbox'=>$new,'verified_after'=>$after->email_verified_at !== null,
            'verification_timestamp'=>$after->email_verified_at?->toISOString(),
            'access_token_count'=>DB::table('personal_access_tokens')->where('tokenable_type',User::class)
                ->where('tokenable_id',$user->id)->count(),
        ];
        try {
            $this->record('backend_response',$facts);
        } catch (\Throwable) {
            // Never convert committed verification success into an apparent
            // account failure merely because its evidence filesystem failed.
            $response->headers->set('X-AgendaAlly-Acceptance-Evidence','unavailable');
            error_log('Selected account acceptance evidence unavailable after backend response.');
        }
        $response->headers->set('X-AgendaAlly-Acceptance-Request',$id);
        return $response;
    }
}
