<?php
declare(strict_types=1);

namespace App\Http\Requests\AuctionQuestion;

use App\Models\AuctionQuestion;
use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;

class SellerStoreRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title'      => 'string',
            'user_id'    => 'int|exists:users,id',
            'auction_id' => 'int|exists:auctions,id',
            'parent_id'  => 'int|exists:auction_questions,id',
            'status'     => ['string', Rule::in(AuctionQuestion::STATUSES)],
        ];
    }
}
