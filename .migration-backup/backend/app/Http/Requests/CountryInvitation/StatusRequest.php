<?php

declare(strict_types=1);

namespace App\Http\Requests\CountryInvitation;

use App\Http\Requests\BaseRequest;
use App\Models\CountryInvitation;
use Illuminate\Validation\Rule;

class StatusRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(array_keys(CountryInvitation::STATUS)),
            ],
        ];
    }
}
