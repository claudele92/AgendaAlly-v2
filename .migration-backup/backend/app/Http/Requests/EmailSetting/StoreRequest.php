<?php
declare(strict_types=1);

namespace App\Http\Requests\EmailSetting;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $password = $this->input('password');
            if ($password === null || (is_string($password) && trim($password) === '')) {
                $this->replace(\Illuminate\Support\Arr::except($this->all(), ['password']));
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'smtp_auth'     => 'boolean',
            'smtp_debug'    => 'boolean',
            'host'          => 'required|string',
            'port'          => 'required|integer',
            'password'      => [
                ($this->isMethod('PUT') || $this->isMethod('PATCH')) ? 'sometimes' : 'required',
                'string', 'min:6', 'max:512',
            ],
            'from_to'       => 'required|string',
            'active'        => Rule::in(0,1),
            'from_site'     => 'string',
            'ssl'           => 'array',
        ];
    }
}
