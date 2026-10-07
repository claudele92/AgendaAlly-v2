<?php
declare(strict_types=1);

namespace App\Http\Requests\Auction;

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
            'price'      => 'required|numeric',
            'auction_id' => 'required|int|exists:auctions,id',
        ];
    }
}
