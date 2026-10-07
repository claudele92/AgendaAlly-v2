<?php
declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class TransactionUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'token' =>  [
//                'required',
                Rule::exists('payment_process', 'id')
            ],
            'status' => 'required|in:paid,canceled',
            // Required only for a non-cash transaction - enforced in
            // TransactionController::updateStatus() itself, since whether
            // it's required depends on the transaction's payment tag,
            // which isn't known until that model is loaded.
            'reason' => 'nullable|string|max:1000',
        ];
    }

}
