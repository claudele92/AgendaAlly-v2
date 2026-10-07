<?php
declare(strict_types=1);

namespace App\Http\Requests\EmailSetting;

use App\Http\Requests\BaseRequest;

class SendTestRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}
