<?php
declare(strict_types=1);

namespace App\Http\Requests\WalletHistory;

use App\Http\Requests\FilterParamsRequest;
use App\Rules\PositiveWalletAmount;

class WithdrawRequest extends FilterParamsRequest
{
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'price' => ['required', 'numeric', new PositiveWalletAmount()],
        ]);
    }
}