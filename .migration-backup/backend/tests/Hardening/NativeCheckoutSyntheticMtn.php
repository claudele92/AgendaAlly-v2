<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentService\Contracts\GatewayConfig;
use App\Services\PaymentService\MtnService;

/** Only merchant lookup/network response are synthetic; native verifier is real. */
final class NativeCheckoutSyntheticMtn extends MtnService
{
    public array $result = [];
    public int $checks = 0;
    public function __construct(private GatewayConfig $gateway)
    { $this->language='en'; $this->currency=1; }
    public function resolveGatewayConfig(array $before, int $paymentId): ?GatewayConfig
    { return $this->gateway; }
    public function checkStatus(GatewayConfig $config, string $referenceId): array
    { ++$this->checks; return $this->result; }
}