<?php

declare(strict_types=1);

namespace App\Http\Requests\ShopPayment;

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

        return [
            'payment_id' => 'required|integer|exists:payments,id',
            'status'     => 'required|boolean',
            'location_type' => 'nullable|integer|in:1,2',
            'client_id'  => 'nullable|string',
            'secret_id'  => 'nullable|string',

            // Orange Money / MTN Mobile Money: merchant registration is
            // done by the receiving shop itself, so these are mandatory
            // for those two gateways specifically — not for any other.
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
            // ShopPaymentService (needs the shop's country); here it's
            // just shape validation.
            'currency' => 'nullable|string|size:3',
            'base_url' => 'nullable|url',
        ];
    }
}
