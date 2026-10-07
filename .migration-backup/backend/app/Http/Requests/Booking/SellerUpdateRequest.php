<?php
declare(strict_types=1);

namespace App\Http\Requests\Booking;

use Illuminate\Validation\Rule;

class SellerUpdateRequest extends AdminUpdateRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $shopId = auth('sanctum')->user()?->shop?->id
            ?? auth('sanctum')->user()?->moderatorShop?->id
            ?? 0;

        $rules['user_id'] = [
            'nullable',
            'integer',
            Rule::prohibitedIf($this->filled('local_client_id')),
            Rule::exists('users', 'id'),
        ];
        $rules['local_client_id'] = [
            'nullable',
            'integer',
            Rule::prohibitedIf($this->filled('user_id')),
            Rule::exists('seller_booking_clients', 'id')->where('shop_id', $shopId),
        ];

        return $rules;
    }
}