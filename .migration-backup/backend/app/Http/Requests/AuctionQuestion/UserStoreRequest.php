<?php
declare(strict_types=1);

namespace App\Http\Requests\AuctionQuestion;

use App\Http\Requests\BaseRequest;

class UserStoreRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title'      => 'required|string',
            'parent_id'  => 'int|exists:auction_questions,id',
        ];
    }
}
