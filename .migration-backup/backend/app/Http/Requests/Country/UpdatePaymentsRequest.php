<?php

declare(strict_types=1);

namespace App\Http\Requests\Country;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'payments'              => ['required', 'array'],
            'payments.*.payment_id' => ['required', 'integer', Rule::exists('payments', 'id')],
            'payments.*.active'     => ['required', 'boolean'],
        ];
    }
}
