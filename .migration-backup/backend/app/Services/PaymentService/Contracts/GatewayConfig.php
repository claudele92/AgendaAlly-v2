<?php

declare(strict_types=1);

namespace App\Services\PaymentService\Contracts;

/**
 * The credential/config surface OrangeService and MtnService need,
 * satisfied by both ShopPayment (customer-facing checkout — bookings,
 * orders) and PlatformPaymentConfig (platform-fee purchases —
 * subscriptions, ads packages). Neither service needs to know which
 * concrete source it was given; see BaseService::resolveGatewayConfig().
 */
interface GatewayConfig
{
    public function getClientId(): ?string;

    public function getMerchantKey(): ?string;

    public function getSubscriptionKey(): ?string;

    public function getApiUser(): ?string;

    public function getApiKey(): ?string;

    public function getTargetEnvironment(): ?string;

    public function getCurrency(): ?string;

    public function getBaseUrl(): ?string;

    public function hasOrangeCredentials(): bool;

    public function hasMtnCredentials(): bool;
}
