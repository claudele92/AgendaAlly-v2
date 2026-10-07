<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ShopPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ShopPayment|JsonResource $this */
        $isConfigured = fn (string $key): bool => $this->hasStoredValue($key);

        return [
            'id'            => $this->id,
            'shop_id'       => $this->shop_id,
            'payment_id'    => $this->payment_id,
            'status'        => $this->status,
            'currency'      => $this->currency,
            'target_environment' => $this->target_environment,
            'base_url' => $this->base_url,

            // Only return configuration presence, never the configured
            // values. This resource is also used by public shop-payment
            // endpoints, so it must remain safe for unauthenticated callers.
            'configured'                 => $isConfigured('client_id') || $isConfigured('secret_id') || $isConfigured('merchant_email') || $isConfigured('payment_key') || $isConfigured('merchant_key') || $isConfigured('subscription_key') || $isConfigured('api_user') || $isConfigured('api_key'),
            'merchant_key_configured'     => $isConfigured('merchant_key'),
            'client_id_configured'        => $isConfigured('client_id'),
            'subscription_key_configured' => $isConfigured('subscription_key'),
            'api_user_configured'         => $isConfigured('api_user'),
            'api_key_configured'          => $isConfigured('api_key'),
            'provider_state' => $this->providerState(),

            'payment'       => $this->whenLoaded('payment', fn ($payment) => PaymentResource::make($payment))
        ];
    }

    private function providerState(): ?array
    {
        // Preserve pure presence-only serialization for unloaded projections.
        if (!$this->resource->relationLoaded('payment')) return null;
        $payment = $this->resource->getRelation('payment');
        $shop = \App\Models\Shop::find($this->shop_id);
        if (!$payment || !$shop || !in_array($payment->tag, \App\Services\PaymentEligibility\ProviderState::TAGS, true)) return null;
        $context = (new \App\Services\PaymentEligibility\PaymentContextFactory)->shop($shop);
        $context['collection_mode'] = 'vendor_direct';
        return (new \App\Services\PaymentEligibility\ProviderState)->decision($payment, $context);
    }

    /**
     * Inspect only whether a value is stored. In particular, do not invoke
     * encrypted model casts just to produce public configuration metadata.
     */
    private function hasStoredValue(string $key): bool
    {
        $value = $this->resource->getRawOriginal($key);

        return is_string($value) && $value !== '';
    }
}
