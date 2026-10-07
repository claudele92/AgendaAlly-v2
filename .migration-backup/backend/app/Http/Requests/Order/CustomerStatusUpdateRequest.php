<?php
declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Validation\Rule;

/** The native Customer client uses this endpoint to cancel a new Order. */
class CustomerStatusUpdateRequest extends StatusUpdateRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['status'] = ['required', 'string', Rule::in([Order::STATUS_CANCELED])];

        return $rules;
    }
}