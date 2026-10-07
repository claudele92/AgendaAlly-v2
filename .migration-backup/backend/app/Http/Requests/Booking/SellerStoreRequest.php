<?php
declare(strict_types=1);

namespace App\Http\Requests\Booking;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SellerStoreRequest extends BaseRequest
{
    public function rules(): array
    {
        $shopId = auth('sanctum')->user()?->shop?->id
            ?? auth('sanctum')->user()?->moderatorShop?->id
            ?? 0;

        $rules = (new StoreRequest)->rules();
        $rules['payment_id'][] = Rule::prohibitedIf($this->filled('local_client_id'));

        return $rules + [
            'user_id' => [
                'nullable',
                'integer',
                'required_without:local_client_id',
                Rule::prohibitedIf($this->filled('local_client_id')),
                Rule::exists('users', 'id'),
            ],
            'local_client_id' => [
                'nullable',
                'integer',
                'required_without:user_id',
                Rule::prohibitedIf($this->filled('user_id')),
                Rule::exists('seller_booking_clients', 'id')->where('shop_id', $shopId),
            ],
        ];
    }
}