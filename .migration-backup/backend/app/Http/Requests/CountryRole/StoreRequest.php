<?php

declare(strict_types=1);

namespace App\Http\Requests\CountryRole;

use App\Helpers\CountryContext;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    public function rules(): array
    {
        $countryId = CountryContext::activeCountryId();

        $roleId = $this->route('role')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('country_roles', 'name')
                    ->where('country_id', $countryId)
                    ->ignore($roleId),
            ],
            'permission_ids'   => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['integer', Rule::exists('country_permissions', 'id')],
        ];
    }
}
