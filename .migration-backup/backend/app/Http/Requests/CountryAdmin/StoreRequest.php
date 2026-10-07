<?php

declare(strict_types=1);

namespace App\Http\Requests\CountryAdmin;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    public function rules(): array
    {
        $adminId = $this->route('countryAdmin')?->id;

        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                // One country_admins row per user — matches the table's
                // unique(user_id) constraint, which guarantees a user can
                // never be restricted to more than one country.
                Rule::unique('country_admins', 'user_id')->ignore($adminId),
            ],
            'country_id' => [
                'required',
                'integer',
                Rule::exists('countries', 'id'),
            ],
        ];
    }
}
