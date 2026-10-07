<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlatformPaymentConfigResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\PlatformPaymentConfig|JsonResource $this */
        return [
            'id'         => $this->id,
            'country_id' => $this->country_id,
            'payment_id' => $this->payment_id,
            'status'     => $this->status,
            'client_id'  => $this->client_id,

            // Credentials are encrypted at rest and never round-tripped
            // back out in plaintext — only whether each has been set.
            'merchant_key_configured'     => !empty($this->resource->getRawOriginal('merchant_key')),
            'subscription_key_configured' => !empty($this->resource->getRawOriginal('subscription_key')),
            'api_user_configured'         => !empty($this->resource->getRawOriginal('api_user')),
            'api_key_configured'          => !empty($this->resource->getRawOriginal('api_key')),
            'target_environment'          => $this->target_environment,
            'currency'                    => $this->currency,
            'base_url'                    => $this->base_url,
            'provider_state' => $this->providerState(),

            'created_by' => $this->created_by,
            'country'    => $this->whenLoaded('country', fn () => [
                'id'   => $this->country->id,
                'name' => $this->country->translation?->title,
            ]),
            'payment'    => PaymentResource::make($this->whenLoaded('payment')),
            'created_at' => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s')),
        ];
    }

    private function providerState(): ?array
    {
        if (!$this->resource->relationLoaded('payment')) return null;
        $payment = $this->resource->getRelation('payment');
        if (!$payment || !in_array($payment->tag, \App\Services\PaymentEligibility\ProviderState::TAGS, true)) return null;
        return (new \App\Services\PaymentEligibility\ProviderState)->decision($payment, [
            'valid' => true, 'country_ids' => [(int) $this->country_id], 'shop_ids' => [],
            'transaction_currency' => (string) $this->currency,
            'collection_mode' => 'platform', 'transaction_type' => 'product',
        ]);
    }
}
