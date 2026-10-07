<?php
declare(strict_types=1);

namespace App\Http\Requests\EmailSetting;

use App\Http\Requests\BaseRequest;
use App\Models\EmailTemplate;
use Illuminate\Validation\Rule;

class EmailTemplatePreviewRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_diff(array_values(EmailTemplate::TYPES),[EmailTemplate::TYPE_ORDER]))],
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:50000',
            'alt_body' => 'required|string|max:50000',
            'email_setting_id' => 'prohibited', 'send_to' => 'prohibited',
            'recipient' => 'prohibited', 'user_id' => 'prohibited', 'email' => 'prohibited',
            'code' => 'prohibited', 'token' => 'prohibited', 'password' => 'prohibited',
        ];
    }
}
