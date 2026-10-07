<?php
declare(strict_types=1);

namespace App\Http\Requests\WalletHistory;

use App\Http\Requests\BaseRequest;
use App\Rules\PositiveWalletAmount;

class SendRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'price'         => ['required', 'numeric', new PositiveWalletAmount()],
            'currency_id'   => 'required|exists:currencies,id',
            'uuid'          => 'required|exists:users,uuid',
        ];
    }
}
