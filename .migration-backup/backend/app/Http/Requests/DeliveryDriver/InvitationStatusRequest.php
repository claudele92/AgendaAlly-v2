<?php
declare(strict_types=1);

namespace App\Http\Requests\DeliveryDriver;

use App\Http\Requests\BaseRequest;

final class InvitationStatusRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'active' => ['required', 'boolean'],
            'shop_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'invitation_id' => ['prohibited'],
            'status' => ['prohibited'],
            'role' => ['prohibited'],
        ];
    }
}