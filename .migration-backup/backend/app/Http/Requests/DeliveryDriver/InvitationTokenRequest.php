<?php
declare(strict_types=1);

namespace App\Http\Requests\DeliveryDriver;

use App\Http\Requests\BaseRequest;

final class InvitationTokenRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/i'],
            'shop_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'invitation_id' => ['prohibited'],
            'role' => ['prohibited'],
            'roles' => ['prohibited'],
            'email' => ['prohibited'],
            'phone' => ['prohibited'],
            'firstname' => ['prohibited'],
            'lastname' => ['prohibited'],
            'password' => ['prohibited'],
            'password_confirmation' => ['prohibited'],
            'verified' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'phone_verified_at' => ['prohibited'],
            'active' => ['prohibited'],
        ];
    }
}