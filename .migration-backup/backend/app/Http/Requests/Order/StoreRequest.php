<?php
declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;
use App\Models\Order;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('delivery_type') === Order::PICKUP) {
            // Do not persist a stale customer delivery address/tariff/driver on
            // a pickup order. Its own shop identity remains authoritative.
            $deliveryFields = [
                'delivery_price_id', 'delivery_point_id', 'address_id', 'address',
                'location', 'deliveryman_id',
            ];
            $this->replace(array_diff_key($this->all(), array_flip($deliveryFields)));
            foreach ($deliveryFields as $field) $this->query->remove($field);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('delivery_type') !== Order::DELIVERY) return;
            $text = $this->input('address.address') ?? $this->input('location.address');
            $lat = $this->input('location.latitude');
            $lng = $this->input('location.longitude');
            $pin = is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 &&
                abs((float) $lng) <= 180 && ((float) $lat !== 0.0 || (float) $lng !== 0.0);
            $hasDestination = $this->input('address_id') || (is_string($text) && trim($text) !== '') || $pin;
            if (!$hasDestination) {
                $validator->errors()->add('address', 'Choose a saved delivery address, enter a destination, or select a valid delivery pin.');
            }
        });
    }

    /**
     * Get the validation rules that apply to the request
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id'                       => 'integer|exists:users,id',
            'currency_id'                   => 'required|integer|exists:currencies,id',
            'payment_id'                    => [
                'integer',
                Rule::exists('payments', 'id')->whereIn('tag', ['wallet', 'cash'])
            ],
            'from_wallet_price'             => 'numeric',
            'rate'                          => 'numeric',
            'delivery_type'                 => ['required', Rule::in(Order::DELIVERY_TYPES)],
            'coupon'                        => 'array',
            'coupon.*'                      => 'string',
            'location'                      => 'array',
            'location.latitude'             => 'numeric',
            'location.longitude'            => 'numeric',
            'address'                       => 'array',
            'phone'                         => 'string',
            'username'                      => 'string',
            'delivery_date'                 => 'date|date_format:Y-m-d H:i',
            'cart_id'                       => 'integer|exists:carts,id',
            'pickup_selections'             => 'nullable|array|max:50',
            'pickup_selections.*.shop_location_id' => 'nullable|integer|min:1',
            'pickup_selections.*.date'       => 'nullable|date_format:Y-m-d',
            'pickup_selections.*.window_start' => 'nullable|string|max:40',
            'pickup_selections.*.window_end' => 'nullable|string|max:40',
            'tips'                          => 'numeric|min:0',

            'notes'                         => 'array',
            'notes.order'                   => 'array',
            'notes.product'                 => 'array',
            'notes.order.*'                 => 'required|string|max:255',
            'notes.product.*'               => 'required|string|max:255',

            'images'                        => 'array',
            'images.*'                      => 'array',
            'images.*.*'                    => 'required|string|max:255',

            'data'                          => 'nullable|array',
            'data.*.shop_id'                => 'required|exists:shops,id',
            'data.*.products'               => 'required|array',
            'data.*.products.*.stock_id'    =>  [
                'required',
                'integer',
                Rule::exists('stocks', 'id'),
            ],
            'data.*.products.*.quantity'    => 'required|integer',
            'data.*.products.*.note'        => 'nullable|string|max:255',
            'data.*.products.*.images'      => 'array',
            'data.*.products.*.images.*'    => 'string',
            'address_id'                    => [
                'integer',
                Rule::exists('user_addresses', 'id')
                    ->where('user_id', request('user_id', auth('sanctum')->id()))
            ],
            'delivery_price_id'     => [
                request('delivery_type') === Order::DELIVERY ? 'required' : 'nullable',
                'integer',
                Rule::exists('delivery_prices', 'id')
            ],
            'delivery_point_id'     => [
                request('delivery_type') === Order::POINT ? 'required' : 'nullable',
                'integer',
                Rule::exists('delivery_points', 'id')
            ],
        ];
    }
}
