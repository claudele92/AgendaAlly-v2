<?php
declare(strict_types=1);

namespace App\Http\Requests\Auction;

use App\Http\Requests\BaseRequest;
use App\Models\Auction;
use Illuminate\Validation\Rule;

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
            'min_price' => 'required|numeric',
            'brand_id'  => 'required|int|exists:brands,id',
            'user_id'   => 'required|int|exists:users,id',
            'start_at'  => 'required|date_format:Y-m-d H:i',
            'status'    => ['required', 'string', Rule::in(Auction::STATUSES)],
            'img'       => 'required|string|max:255',
            'video'     => 'required|string|max:255',
        ];
    }
}
