<?php
declare(strict_types=1);

namespace App\Http\Requests\Invitation;

use App\Http\Requests\BaseRequest;
use App\Models\Invitation;
use Illuminate\Validation\Rule;

class StatusRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(array_keys(Invitation::STATUS))
            ],
            'shop_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'invitation_id' => ['prohibited'],
            'role' => ['prohibited'],
            'password' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'phone_verified_at' => ['prohibited'],
        ];
    }
}
