<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class LoginRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
	{
		return [
            'phone'     => ['required_without:email', 'numeric'],
            'password'  => ['required', 'string'],
            'email'     => ['required_without:phone', 'email'],
		];
	}
}
