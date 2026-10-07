<?php
declare(strict_types=1);

namespace App\Http\Requests\Story;

use App\Http\Requests\BaseRequest;
use App\Models\Story;
use Illuminate\Validation\Rule;

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
            'model_id'    => 'required|int',
            'model_type'  => ['string', 'required', Rule::in(array_keys(Story::TYPES))],
            'active'      => 'boolean',
            'file_urls'   => 'required|array',
            'file_urls.*' => 'required|string',
        ];
    }
}
