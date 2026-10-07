<?php

namespace App\Http\Requests\Report\Booking;

use App\Http\Requests\BaseRequest;

class PerformanceDashboardRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'date_from' => 'required|date_format:Y-m-d',
            'date_to'   => 'required|date_format:Y-m-d',
            'type'      => 'required|string|in:year,month,week,day',
            // Optional: the Admin controller passes this through as-is, so
            // omitting it means "platform-wide" (ReportRepository::
            // performanceDashboard() already treats a falsy shop_id as
            // "don't filter by shop" everywhere it's used). The Seller
            // controller always force-merges its own shop's id regardless
            // of what the request contains, so this relaxation doesn't
            // change seller behavior at all.
            'shop_id'   => 'sometimes|int|exists:shops,id',
            'master_id' => 'int|exists:users,id',
        ];
    }
}
