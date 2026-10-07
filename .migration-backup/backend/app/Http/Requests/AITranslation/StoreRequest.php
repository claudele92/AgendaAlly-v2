<?php
declare(strict_types=1);

namespace App\Http\Requests\AITranslation;

use App\Http\Requests\BaseRequest;

class StoreRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'model_id'   => 'int',
            'model_type' => 'required|string',
            'content'    => 'required|string',
            'lang'       => 'string|max:5',
        ];
    }
}
