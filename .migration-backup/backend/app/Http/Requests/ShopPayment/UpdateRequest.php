<?php

declare(strict_types=1);

namespace App\Http\Requests\ShopPayment;

use App\Http\Requests\BaseRequest;
use App\Models\Payment;
use App\Models\ShopPayment;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        /** @var ShopPayment|null $shopPayment */
        $shopPayment = $this->route('shopPayment');

        /** @var User|null $user */
        $user = $this->user('sanctum');
        $shop = $user?->shop ?? $user?->moderatorShop;

        return $shopPayment instanceof ShopPayment
            && $shop
            && (int) $shopPayment->shop_id === (int) $shop->id
            && $user->hasShopPermission((int) $shop->id, 'payments.gateways.manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $tag = Payment::query()->find($this->input('payment_id'))?->tag;

        /** @var ShopPayment|null $shopPayment */
        $shopPayment = $this->route('shopPayment');

        return [
            'payment_id' => 'required|integer|exists:payments,id',
            'status'     => 'required|boolean',
            'location_type' => 'nullable|integer|in:1,2',
            'client_id'  => 'nullable|string',
            'secret_id'  => 'nullable|string',

            // Encrypted-at-rest credentials are never returned in
            // plaintext (see ShopPaymentResource) — so once one is
            // already configured, it's only required again if the
            // gateway actually needs it; leaving it blank means keep
            // the existing value (see ShopPaymentService::update()).
            'merchant_key'       => [Rule::requiredIf($tag === Payment::TAG_ORANGE && !$this->hasStoredCredential($shopPayment, 'merchant_key')), 'nullable', 'string'],
            'subscription_key'   => [Rule::requiredIf($tag === Payment::TAG_MTN && !$this->hasStoredCredential($shopPayment, 'subscription_key')), 'nullable', 'string'],
            'api_user'           => [Rule::requiredIf($tag === Payment::TAG_MTN && !$this->hasStoredCredential($shopPayment, 'api_user')), 'nullable', 'string'],
            'api_key'            => [Rule::requiredIf($tag === Payment::TAG_MTN && !$this->hasStoredCredential($shopPayment, 'api_key')), 'nullable', 'string'],
            'target_environment' => [
                Rule::requiredIf($tag === Payment::TAG_MTN),
                'nullable',
                'string',
                'regex:/^[a-z]+$/',
            ],
            'currency' => 'nullable|string|size:3',
            'base_url' => 'nullable|url',
        ];
    }

    private function hasStoredCredential(?\App\Models\ShopPayment $shopPayment, string $key): bool
    {
        $value = $shopPayment?->getRawOriginal($key);

        return is_string($value) && $value !== '';
    }
}
