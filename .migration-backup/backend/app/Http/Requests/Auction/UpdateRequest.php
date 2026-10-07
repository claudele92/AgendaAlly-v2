<?php
declare(strict_types=1);

namespace App\Http\Requests\Auction;

use App\Http\Requests\BaseRequest;
use App\Models\Auction;
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
            'min_price' => 'numeric',
            'brand_id'  => 'int|exists:brands,id',
            'user_id'   => 'int|exists:users,id',
            'start_at'  => 'date_format:Y-m-d H:i',
            'status'    => ['string', Rule::in(Auction::STATUSES)],
            'img'       => 'string|max:255',
            'video'     => 'string|max:255',
        ];
    }
}
