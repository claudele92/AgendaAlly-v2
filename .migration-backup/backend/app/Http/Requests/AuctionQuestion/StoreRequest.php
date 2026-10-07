<?php
declare(strict_types=1);

namespace App\Http\Requests\AuctionQuestion;

use App\Models\AuctionQuestion;
use Illuminate\Validation\Rule;
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
            'title'      => 'required|string',
            'user_id'    => 'required|int|exists:users,id',
            'auction_id' => 'required|int|exists:auctions,id',
            'parent_id'  => 'int|exists:auction_questions,id',
            'status'     => ['required', 'string', Rule::in(AuctionQuestion::STATUSES)],
        ];
    }
}
