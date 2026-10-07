<?php
declare(strict_types=1);

namespace App\Http\Requests\DeliveryDriver;

use App\Http\Requests\BaseRequest;

final class InvitationCreateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'firstname' => ['sometimes', 'nullable', 'string', 'min:2', 'max:100'],
            'lastname' => ['sometimes', 'nullable', 'string', 'max:100'],
            'password' => ['prohibited'],
            'password_confirmation' => ['prohibited'],
            'shop_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'verified' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'phone_verified_at' => ['prohibited'],
            'role' => ['prohibited'],
            'roles' => ['prohibited'],
            'active' => ['prohibited'],
            'status' => ['prohibited'],
            'invitation_id' => ['prohibited'],
        ];
    }
}