<?php

declare(strict_types=1);

namespace App\Http\Requests\CountryInvitation;

use App\Helpers\CountryContext;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CreateAccountRequest extends BaseRequest
{
    public function rules(): array
    {
        $countryId = CountryContext::activeCountryId();

        return [
            'firstname' => ['required', 'string', 'min:2', 'max:100'],
            'lastname'  => ['string'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')],
            'phone'     => ['nullable', 'numeric', Rule::unique('users', 'phone')],
            'password'  => ['required', 'min:6', 'confirmed'],
            'birthday'  => ['nullable', 'date_format:Y-m-d'],
            'gender'    => ['nullable', 'string', Rule::in('male', 'female')],
            'images'    => ['array'],
            'images.*'  => ['string'],
            // Must already exist — there is no default/auto-created role,
            // same rule as StoreRequest::country_role_id.
            'country_role_id' => [
                'required',
                'integer',
                Rule::exists('country_roles', 'id')->where('country_id', $countryId),
            ],
        ];
    }
}
