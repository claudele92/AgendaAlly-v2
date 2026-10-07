<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentService\MtnService;
use App\Services\PaymentService\Contracts\GatewayConfig;
use App\Models\Payment;

/** Native initiation/accounting; synthetic merchant lookup and eligibility only.
 * All token/POST/GET operations use the native HTTP client under fake transport.
 */
final class MtnDurableSyntheticService extends MtnService
{
    public ?string $fault=null;
    public bool $eligible=true;
    public bool $configurationMissing=false;
    public function __construct(public GatewayConfig $gateway)
    { $this->language='en'; $this->currency=1; }
    public function resolveGatewayConfig(array $before,int $paymentId): ?GatewayConfig
    { return $this->configurationMissing ? null : $this->gateway; }
    protected function checkpoint(string $phase): void
    {
        if ($this->fault===$phase) throw new \RuntimeException('Synthetic interruption '.$phase);
    }
    protected function assertInitiationEligible(string $key,int $id,int $paymentId): void
    {
        if (!$this->eligible) throw new \DomainException('Synthetic activation gate closed');
    }
    public function getPayload(array $data,array $payload,?int $paymentId=null): array
    {
        $key=isset($data['cart_id'])?'cart_id':'booking_id';
        $this->authorizePaymentTarget($key,(int)$data[$key]);
        $this->assertInitiationEligible($key,(int)$data[$key],(int)$paymentId);
        $context=(new \App\Services\PaymentAccounting\ProviderContributionAdapter)
            ->prepare($key,$data,Payment::findOrFail($paymentId),['collect_via_platform'=>true],$this);
        $before=$key==='cart_id'?$this->beforeCart($data,$payload):$this->beforeBooking($data,$payload);
        return [$key,array_merge($before,['collect_via_platform'=>true],$context)];
    }
}