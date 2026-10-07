<?php

declare(strict_types=1);

namespace App\Http\Requests\CountryInvitation;

use App\Helpers\CountryContext;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    public function rules(): array
    {
        $countryId = CountryContext::activeCountryId();

        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            // The inviting admin's own previously-created country role.
            // Must already exist — there is no default/auto-created role.
            'country_role_id' => [
                'required',
                'integer',
                Rule::exists('country_roles', 'id')->where('country_id', $countryId),
            ],
        ];
    }
}
