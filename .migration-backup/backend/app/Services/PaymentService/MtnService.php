<?php
declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Payout;
use App\Services\PaymentService\Contracts\GatewayConfig;
use Exception;
use Http;
use Illuminate\Database\Eloquent\Model;
use Str;
use Throwable;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Services\PaymentService\Verification\DecimalAmount;

/**
 * MTN Mobile Money via MTN's own Collections API (momodeveloper.mtn.com)
 * — not a third-party aggregator. Credentials/target_environment/currency
 * all come from a GatewayConfig (either a shop's own ShopPayment for
 * customer-facing checkout, or the platform's PlatformPaymentConfig for
 * a platform-fee purchase — see BaseService::resolveGatewayConfig());
 * nothing here branches on country — target_environment and currency
 * are just parameters the resolved config supplies.
 */
class MtnService extends BaseService
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param array $data
     * @return PaymentProcess|Model
     * @throws Throwable
     */
    public function processTransaction(array $data): Model|PaymentProcess
    {
        $attempts = new MtnAttempt;
        $attempts->committedBoundary();
        $payment = Payment::query()->where('tag', Payment::TAG_MTN)->first();
        if (!$payment) {
            throw new Exception('MTN Mobile Money payment method is unavailable');
        }
        // Protocol layer is bounded to the accepted native Product/Booking
        // accounting. No legacy/platform-fee request is silently reclassified.
        $keys=array_values(array_filter(['cart_id','booking_id'],fn($k)=>!empty($data[$k])));
        $other=['member_ship_id','parcel_id','subscription_id','ads_package_id','wallet_id','gift_cart_id','auction_id'];
        if (count($keys)!==1 || array_filter($other,fn($k)=>!empty($data[$k]))
            || !empty($data['tips']) || isset($data['extra_time'])) {
            throw new \DomainException('MTN durable checkout requires one supported original target.');
        }
        $key=$keys[0];
        $type=$key==='cart_id' ? \App\Models\Cart::class : \App\Models\Booking::class;
        if (!auth('sanctum')->id()) throw new \DomainException('Authenticated MTN owner required.');
        // Successful Product checkout may delete its live Cart. Frozen
        // server-owned process + canonical payer remain its recovery authority.
        $retained=PaymentProcess::where('model_type',$type)->where('model_id',$data[$key])
            ->where('user_id',auth('sanctum')->id())->whereNotNull('mtn_funding_event_key')
            ->orderByDesc('mtn_anchor_context_id')->first();
        if ($retained && !in_array($retained->mtn_dispatch_state,[MtnAttempt::RESERVED,MtnAttempt::FAILURE],true)) {
            $attempts->context($retained);
            return $retained;
        }
        $this->authorizePaymentTarget($key,(int)$data[$key]);
        $process=DB::transaction(function () use ($data,$key,$type,$payment,$attempts): PaymentProcess {
            // The target lock serializes event creation, including before a
            // funding event exists. SQLite needs a real write reservation.
            $table=$key==='cart_id'?'carts':'bookings';
            if (DB::connection()->getDriverName()==='sqlite') {
                DB::statement("UPDATE {$table} SET id=id WHERE id=?",[(int)$data[$key]]);
            }
            DB::table($table)->where('id',$data[$key])->lockForUpdate()->first();
            $old=PaymentProcess::where('model_type',$type)->where('model_id',$data[$key])
                ->whereNotNull('mtn_funding_event_key')->orderByDesc('mtn_anchor_context_id')->first();
            if ($old && $old->mtn_dispatch_state!==MtnAttempt::FAILURE) {
                $attempts->context($old);
                return $old; // Never call prepare/debit Wallet again on replay.
            }
            [, $before]=$this->getPayload($data,[],(int)$payment->id);
            $config=$this->resolveGatewayConfig($before,(int)$payment->id);
            if (!$config?->hasMtnCredentials() || strtoupper((string)$config->getCurrency())!==strtoupper((string)$before['currency'])) {
                throw new \DomainException('MTN credential/currency contract is unavailable.');
            }
            $this->checkpoint('before_persistence');
            $p=$attempts->reserve($before,(int)$payment->id,$this->configFingerprint($config));
            $this->checkpoint('after_persistence');
            return $p;
        },3);
        $this->checkpoint('identity_committed');
        if ($process->mtn_dispatch_state!==MtnAttempt::RESERVED) return $process;
        $this->assertInitiationEligible($key,(int)$data[$key],(int)$payment->id);
        $config=$this->configurationFor($process);
        if (!$attempts->claim($process->id)) return $process->refresh();
        $process->refresh();
        // From this committed claim onward ANY interruption is conservatively
        // unknown, including a crash before the socket actually transmits.
        try {
            $this->checkpoint('dispatch_claimed');
            $attempts->committedBoundary();
            $referenceId=$process->id;
            $token=$this->getToken($config);
            $host=request()->getSchemeAndHttpHost();
            $response=Http::withHeaders($this->requestHeaders($config,$token,$referenceId))
                ->post($this->baseUrl($config).'/collection/v1_0/requesttopay',[
                    'amount'=>\App\Services\PaymentAccounting\ExactMoney::decimal((int)$process->data['total_price'],2),
                    'currency'=>$config->getCurrency(),'externalId'=>$referenceId,
                    'payer'=>['partyIdType'=>'MSISDN','partyId'=>$process->data['phone'] ?? null],
                    'payerMessage'=>"Payment #$referenceId",'payeeNote'=>"Payment #$referenceId",
                    'callbackUrl'=>"$host/api/v1/webhook/mtn/payment",
                ]);
            $this->checkpoint('response_received');
            // A non-202 response is not blanket authoritative no-collection
            // proof. Retain UNKNOWN; reconciliation owns financial outcome.
            if ($response->status()===202) {
                $attempts->transition($process,MtnAttempt::ACCEPTED);
            }
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MTN dispatch outcome unresolved',['exception'=>get_class($e)]);
        }
        return $process->refresh();
    }

    protected function assertInitiationEligible(string $key,int $id,int $paymentId): void
    {
        $context=(new \App\Services\PaymentEligibility\PaymentContextFactory)->target($key,$id);
        (new \App\Services\PaymentEligibility\PaymentEligibilityService)->assertEligible($paymentId,$context);
    }

    /** Internal failure boundaries; no browser flags/configuration enable them. */
    protected function checkpoint(string $phase): void {}

    public function configurationFor(PaymentProcess $p): GatewayConfig
    {
        $c=(new MtnAttempt)->context($p);
        $config=$this->resolveGatewayConfig($p->data,(int)$c->payment_id);
        $source=$config instanceof \App\Models\ShopPayment ? 'shop' : 'platform_country';
        if (!$config || $source!==$c->configuration_source
            || (string)$config->getKey()!==$c->configuration_reference
            || $config->updated_at?->toIso8601String()!==$c->configuration_revision
            || !hash_equals((string)$p->mtn_config_fingerprint,$this->configFingerprint($config))) {
            throw new \DomainException('Original MTN merchant revision unavailable; no current-config fallback.');
        }
        return $config;
    }

    public function reconcileAttempt(string $id): array
    {
        (new MtnAttempt)->committedBoundary();
        $p=PaymentProcess::findOrFail($id);
        $config=$this->configurationFor($p);
        if (in_array($p->mtn_dispatch_state,[MtnAttempt::SUCCESS,MtnAttempt::FAILURE],true)) {
            return ['status'=>true,'message'=>'already settled'];
        }
        if ($p->mtn_dispatch_state===MtnAttempt::RESERVED) {
            return ['status'=>true,'message'=>'identity reserved; not dispatched'];
        }
        $r=$this->checkStatus($config,$p->id);
        $amount=DecimalAmount::minor($r['amount'] ?? null);
        if (($r['externalId'] ?? null)!==$p->id || $amount!==(int)$p->data['total_price']
            || strtoupper((string)($r['currency'] ?? ''))!==strtoupper((string)$p->data['currency'])
            || strtoupper((string)$config->getCurrency())!==strtoupper((string)$p->data['currency'])) {
            return ['status'=>false,'message'=>'MTN original reference/amount/currency mismatch'];
        }
        $status=match($r['status'] ?? null) {
            'SUCCESSFUL'=>Transaction::STATUS_PAID,'FAILED'=>Transaction::STATUS_CANCELED,default=>null,
        };
        if ($status===null) {
            if (($r['status'] ?? null)==='PENDING') {
                DB::transaction(function () use ($id): void {
                    $current=PaymentProcess::findOrFail($id);
                    (new MtnAttempt)->transition($current,MtnAttempt::PENDING);
                },3);
                return ['status'=>true,'message'=>'provider pending'];
            }
            return ['status'=>false,'message'=>'Unknown MTN status; retain original unresolved attempt'];
        }
        return $this->afterHook($p->id,$status,null,[
            'authenticated'=>true,'merchant_verified'=>true,'reference'=>$p->id,
            'amount_minor'=>$amount,'currency'=>$r['currency'],'payment_id'=>$p->data['payment_id'],
            'model_type'=>$p->model_type,'model_id'=>$p->model_id,
        ]);
    }

    public function afterHook($token,$status,?string $secondToken=null,?array $verification=null): array
    {
        try {
            $result=DB::transaction(function () use ($token,$status,$verification): array {
                // Reserve SQLite's writer before reading financial/lifecycle
                // state; FOR UPDATE alone is ignored by that engine.
                if (DB::connection()->getDriverName()==='sqlite') {
                    DB::table('payment_process')->where('id',$token)->whereNotNull('mtn_funding_event_key')
                        ->increment('mtn_attempt_version');
                }
                $p=PaymentProcess::whereKey($token)->lockForUpdate()->firstOrFail();
                (new MtnAttempt)->context($p);
                if ($p->mtn_dispatch_state===MtnAttempt::RESERVED) {
                    throw new \DomainException('Unsent MTN attempt has no financial authority.');
                }
                $this->checkpoint('before_accounting');
                $r=parent::afterHook($token,$status,null,$verification);
                if (!($r['status'] ?? false)) throw new \DomainException('Canonical MTN completion refused.');
                if (in_array($status,[Transaction::STATUS_PAID,Transaction::STATUS_CANCELED,Transaction::STATUS_REJECTED],true)) {
                    $p->refresh();
                    $terminal=$status===Transaction::STATUS_PAID ? MtnAttempt::SUCCESS : MtnAttempt::FAILURE;
                    if (in_array($p->mtn_dispatch_state,[MtnAttempt::SUCCESS,MtnAttempt::FAILURE],true)
                        && $p->mtn_dispatch_state!==$terminal) throw new \DomainException('Conflicting MTN terminal evidence.');
                    (new MtnAttempt)->transition($p,$terminal);
                    $p->refresh()->update(['data'=>array_merge($p->data,['mtn_resolved'=>true])]);
                }
                $this->checkpoint('during_accounting');
                return $r;
            },3);
            $this->checkpoint('accounting_committed');
            return $result;
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MTN canonical recovery failed',['exception'=>get_class($e)]);
            return ['status'=>false,'message'=>'MTN completion unresolved; retain original identity'];
        }
    }

    /**
     * Polls MTN's own status endpoint for a request-to-pay reference —
     * used both by an explicit status-check call and by the scheduled
     * reconciliation job, since MTN's webhook delivery isn't guaranteed.
     *
     * @throws Exception
     */
    public function checkStatus(GatewayConfig $config, string $referenceId): array
    {
        (new MtnAttempt)->committedBoundary();
        $token = $this->getToken($config);

        $response = Http::withHeaders($this->requestHeaders($config, $token, $referenceId))
            ->get("{$this->baseUrl($config)}/collection/v1_0/requesttopay/$referenceId");

        if (!$response->successful()) {
            throw new Exception('Unable to reach MTN status endpoint', $response->status());
        }

        return $response->json();
    }

    /**
     * @throws Exception
     */
    private function getToken(GatewayConfig $config): string
    {
        $credentials = base64_encode("{$config->getApiUser()}:{$config->getApiKey()}");

        $response = Http::withHeaders([
            'Authorization'             => "Basic $credentials",
            'Ocp-Apim-Subscription-Key' => $config->getSubscriptionKey(),
        ])->post($this->baseUrl($config) . '/collection/token/');

        $json = $response->json();

        if (!$response->successful() || !isset($json['access_token'])) {
            throw new Exception('Unable to obtain an MTN access token');
        }

        return $json['access_token'];
    }

    public function configFingerprint(GatewayConfig $config): string
    {
        return hash('sha256', implode("\0", [
            (string) $config->getClientId(),
            (string) $config->getApiUser(),
            (string) $config->getApiKey(),
            (string) $config->getSubscriptionKey(),
            (string) $config->getTargetEnvironment(),
            (string) $config->getCurrency(),
            (string) $config->getBaseUrl(),
        ]));
    }

    private function requestHeaders(GatewayConfig $config, string $token, string $referenceId): array
    {
        return [
            'Content-Type'              => 'application/json',
            'Authorization'             => "Bearer $token",
            'X-Reference-Id'            => $referenceId,
            'X-Target-Environment'      => $config->getTargetEnvironment(),
            'Ocp-Apim-Subscription-Key' => $config->getSubscriptionKey(),
        ];
    }

    private function baseUrl(GatewayConfig $config): string
    {
        $baseUrl = rtrim($config->getBaseUrl() ?: 'https://sandbox.momodeveloper.mtn.com', '/');
        if (strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)) !== 'https') {
            throw new Exception('MTN requires an HTTPS provider endpoint');
        }

        return $baseUrl;
    }
}
