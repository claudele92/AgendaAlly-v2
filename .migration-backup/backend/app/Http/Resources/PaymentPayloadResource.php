<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PaymentPayload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentPayloadResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var PaymentPayload|JsonResource $this */
        return [
            'payment_id'    => $this->when($this->payment_id, $this->payment_id),
            'payload'       => $this->safePayload(),
            'credential_presence' => $this->credentialPresence(),
            'provider_state' => $this->providerState(),

            //Relations
            'payment'       => PaymentResource::make($this->whenLoaded('payment')),
        ];
    }

    private function safePayload(): array
    {
        $tag = \App\Models\Payment::find($this->payment_id)?->tag;
        $payload = $this->payload ?? [];
        if (!in_array($tag, \App\Services\PaymentEligibility\ProviderState::TAGS, true)) {
            return array_diff_key($payload, array_flip(\App\Services\PaymentEligibility\ProviderConfigurationFields::SECRET_KEYS));
        }
        // A strict allowlist, not a list of guessed secret names.
        return array_intersect_key($payload, array_flip(\App\Services\PaymentEligibility\ProviderConfigurationFields::publicKeys()));
    }

    private function credentialPresence(): array
    {
        $tag = \App\Models\Payment::find($this->payment_id)?->tag;
        $keys = \App\Services\PaymentEligibility\ProviderConfigurationFields::secrets((string) $tag);
        return array_combine($keys, array_map(fn ($key) => !empty(($this->payload ?? [])[$key]), $keys)) ?: [];
    }

    private function providerState(): ?array
    {
        $payment = \App\Models\Payment::find($this->payment_id);
        if (!$payment || !in_array($payment->tag, ['flutter-wave', 'paystack', 'stripe', 'paypal'], true)) return null;
        return (new \App\Services\PaymentEligibility\ProviderState)->decision($payment, [
            'valid' => false, 'reason' => 'business_context_required',
            'country_ids' => [], 'shop_ids' => [],
            'transaction_currency' => (string) (($this->payload ?? [])[$payment->tag === 'paypal' ? 'paypal_currency' : 'currency'] ?? ''),
            'collection_mode' => 'platform', 'transaction_type' => 'product',
        ]);
    }
}
