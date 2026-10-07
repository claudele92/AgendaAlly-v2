<?php
declare(strict_types=1);

namespace App\Http\Requests\DeliveryDriver;

use App\Http\Requests\BaseRequest;

final class InvitationRegistrationRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/i'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'firstname' => ['required', 'string', 'min:2', 'max:100'],
            'lastname' => ['sometimes', 'nullable', 'string', 'max:100'],
            'email' => ['prohibited'],
            'phone' => ['prohibited'],
            'shop_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'verified' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'phone_verified_at' => ['prohibited'],
            'active' => ['prohibited'],
            'role' => ['prohibited'],
            'roles' => ['prohibited'],
            'invitation_id' => ['prohibited'],
            'verify_token' => ['prohibited'],
        ];
    }
}