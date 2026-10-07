<?php
declare(strict_types=1);

namespace App\Http\Requests\ShopLocation;

use App\Http\Requests\BaseRequest;
use App\Models\ShopLocation;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    /**
     * The seller UI's "whole country" city option sends the literal string
     * 'all' (matching InfiniteSelect's option value in location-select.jsx)
     * rather than omitting city_id — omitting it on an update would leave a
     * previously-set city_id untouched instead of clearing it, since
     * ShopLocationService::update() only writes keys actually present in
     * the request. Normalizing to null here lets city_id's own 'nullable'
     * rule accept it and the update genuinely clear the column.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('city_id') === 'all') {
            $this->merge(['city_id' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'region_id' => [
                'required',
                'integer',
                Rule::exists('regions', 'id')
            ],
            'country_id' => [
                'required',
                'integer',
                Rule::exists('countries', 'id')
            ],
            'city_id' => [
                'nullable',
                'integer',
                Rule::exists('cities', 'id')
            ],
            'area_id' => [
                'nullable',
                'integer',
                Rule::exists('areas', 'id')
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'alias' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'type' => [
                'integer',
                Rule::in(ShopLocation::TYPES),
            ],
        ];
    }
}
