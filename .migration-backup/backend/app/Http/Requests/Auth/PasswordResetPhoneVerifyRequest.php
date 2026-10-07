<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class PasswordResetPhoneVerifyRequest extends BaseRequest
{
    public function rules(): array
    {
        $firebase = request('type') === 'firebase';

        return [
            'verifyId' => [$firebase ? 'nullable' : 'required', 'string', 'max:100'],
            'verifyCode' => [$firebase ? 'nullable' : 'required', 'digits:6'],
            'phone' => ['required', 'numeric'],
            'id' => [$firebase ? 'required' : 'nullable', 'string'],
            'email' => ['nullable', 'email'],
            'password' => [$firebase ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }
}