<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Gallery;
use Illuminate\Validation\Rule;

class GalleryUploadRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'image'  => \App\Helpers\SelectedImageUpload::rules((string) $this->input('type')),
            'type'   => ['required', 'string', Rule::in(Gallery::TYPES)],
        ];
    }
}

