<?php
declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Models\Shop;
use App\Models\Order;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;

class StoreRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'product_fulfillment_methods' => 'sometimes|array|min:1|max:2',
            'product_fulfillment_methods.*' => ['required', 'distinct', Rule::in([Order::DELIVERY, Order::PICKUP])],
            'delivery_time_from' => 'numeric',
            'delivery_time_to'   => 'numeric',
            'delivery_time_type' => ['string', Rule::in(Shop::DELIVERY_TIME_TYPE)],
            'status'             => ['string',  Rule::in(Shop::STATUS)],
            'delivery_type'      => ['int', Rule::in(Shop::DELIVERY_TYPES_BY)],
            'active'             => ['numeric', Rule::in(1,0)],
            'title'              => 'required|array',
            'title.*'            => 'required|string|min:2|max:191',
            'description'        => 'array',
            'description.*'      => 'string|min:3',
            'address'            => 'required|array',
            'address.*'          => 'string|min:2',
            'lat_long'           => 'array',
            'lat_long.latitude'  => 'numeric',
            'lat_long.longitude' => 'numeric',
            'images'             => 'array',
            'images.*'           => 'string',
            'documents'          => 'array',
            'documents.*'        => 'string',
            'tags'               => 'array',
            'tags.*'             => 'exists:shop_tags,id',
            'user_id'            => 'integer|exists:users,id',
            'tax'                => 'numeric',
            'percentage'         => 'numeric',
            'min_amount'         => 'string',
            'phone'              => 'string',
            'open'               => 'in:0,1',
            'verify'             => 'in:0,1',
            'status_note'        => 'string',
            'email_statuses'     => 'array',
            'email_statuses.*'   => ['string', Rule::in(Order::STATUSES)],
            'ai_token_limit'     => 'int|max:2147483647',
            'ai_access'          => 'bool',
            'collect_via_platform' => 'boolean',
        ];
    }
}
