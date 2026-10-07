<?php

declare(strict_types=1);

namespace App\Http\Requests\PlatformPaymentConfig;

use App\Http\Requests\BaseRequest;
use App\Models\Payment;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $tag = Payment::query()->find($this->input('payment_id'))?->tag;

        $configId = $this->route('platformPaymentConfig')?->id;

        return [
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
                Rule::unique('platform_payment_configs', 'country_id')
                    ->where('payment_id', $this->input('payment_id'))
                    ->ignore($configId),
            ],
            'payment_id' => 'required|integer|exists:payments,id',
            'status'     => 'required|boolean',
            'client_id'  => 'nullable|string',

            'merchant_key'       => [Rule::requiredIf($tag === Payment::TAG_ORANGE), 'nullable', 'string'],
            'subscription_key'   => [Rule::requiredIf($tag === Payment::TAG_MTN), 'nullable', 'string'],
            'api_user'           => [Rule::requiredIf($tag === Payment::TAG_MTN), 'nullable', 'string'],
            'api_key'            => [Rule::requiredIf($tag === Payment::TAG_MTN), 'nullable', 'string'],
            'target_environment' => [
                Rule::requiredIf($tag === Payment::TAG_MTN),
                'nullable',
                'string',
                'regex:/^[a-z]+$/',
            ],
            // Required-if-no-default-resolvable is checked in
            // PlatformPaymentConfigService (needs the country); here
            // it's just shape validation.
            'currency' => 'nullable|string|size:3',
            'base_url' => 'nullable|url',
        ];
    }
}
