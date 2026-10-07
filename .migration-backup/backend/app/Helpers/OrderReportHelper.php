<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Helpers\PortableSql;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderReportHelper
{
    public static function rawPricesByOrderStatuses(): array
    {
        $statuses = Order::STATUSES;

        $raw = [];

        foreach ($statuses as $status) {
            $raw[] = DB::raw(PortableSql::countWhenEquals('status', (string) $status) . " as total_{$status}_count");
        }

        return $raw;
    }
}
