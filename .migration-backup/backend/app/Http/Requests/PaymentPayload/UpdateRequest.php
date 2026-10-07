<?php
declare(strict_types=1);

namespace App\Http\Requests\PaymentPayload;

use App\Http\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'payload' => 'required|array',
            // Provider service merges blank secrets with the stored values
            // before performing provider-specific completeness validation.
            'payload.*' => ['nullable']
        ];
    }

}
