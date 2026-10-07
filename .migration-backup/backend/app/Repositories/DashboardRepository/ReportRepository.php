<?php
declare(strict_types=1);

namespace App\Repositories\DashboardRepository;

use App\Helpers\CountryContext;
use App\Helpers\PortableSql;
use App\Models\Payment;
use App\Models\Transaction;
use Cache;
use DateTime;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Schema;
use Throwable;
use Exception;
use App\Models\Shop;
use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use App\Models\Booking;
use App\Models\Language;
use Illuminate\Support\Str;
use App\Models\UserGiftCart;
use App\Models\UserMemberShip;
use App\Models\ShopTranslation;
use App\Models\ServiceTranslation;
use Illuminate\Support\Facades\DB;
use App\Models\CategoryTranslation;
use App\Repositories\CoreRepository;

class ReportRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Order::class;
    }

    public function calculateWorkingDifference(DateTime $startDate, DateTime $endDate, int $dayCount = 7): int
    {
        // Интервал между датами в днях
        $interval = $startDate->diff($endDate);
        $totalDays = $interval->days;

        // Количество полных недель
        $fullWeeks = intdiv($totalDays, 7);

        // Полные рабочие дни ($dayCount дней в неделю)
        $workingDays = $fullWeeks * $dayCount;

        // Оставшиеся дни
        $remainingDays = $totalDays % 7;

        // Если остаются дни, проверяем, попадают ли они в рабочие дни недели (1-$dayCount)
        $startDayOfWeek = (int) $startDate->format('N');

        for ($i = 0; $i < $remainingDays; $i++) {

            // Если день недели от 1 до $dayCount, считаем его рабочим
            $currentDayOfWeek = ($startDayOfWeek + $i) % 7;

            if ($currentDayOfWeek >= 1 && $currentDayOfWeek <= $dayCount) {
                $workingDays++;
            }

        }

        return $workingDays;
    }

    /**
     * @param array $filter
     * @return array
     * @throws Exception
     */
    public function performanceDashboard(array $filter = []): array
    {
        $type = match ($filter['type'] ?? 'day') {
            'year'  => '%Y',
            'month' => '%Y-%m',
            'week'  => '%Y-%m-%w',
            'day'   => '%Y-%m-%d',
            default => '%Y-%m-%d %H:00',
        };

        $dateFrom = data_get($filter, 'date_from');
        $dateTo   = data_get($filter, 'date_to');
        $shopId   = data_get($filter, 'shop_id');
        $restrictedShopIds = CountryContext::restrictedShopIds();
        $masterId = data_get($filter, 'master_id');

        $canceledStatus = Booking::STATUS_CANCELED;
        $endedStatus    = Booking::STATUS_ENDED;

        $orderCanceledStatus = Order::STATUS_CANCELED;
        $deliveredStatus     = Order::STATUS_DELIVERED;


        /** @var Shop $shop */
        $shop = Shop::with([
            'workingDays',
            'closedDates' => fn($q) => $q->where('date', '>=', $dateFrom)->where('date', '<=', $dateTo)
        ])->find($shopId);

        $bookings = DB::table('bookings')
            ->whereIn('status', [$canceledStatus, $endedStatus])
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->when($shopId,   fn($q)            => $q->where('shop_id',   $shopId))
            ->when($restrictedShopIds !== null, fn($q) => $q->whereIn('shop_id', $restrictedShopIds))
            ->when($masterId, fn($q)            => $q->where('master_id', $masterId))
            ->select([
                DB::raw('count(id) as ended_count'),
                DB::raw(PortableSql::sumWhenEquals('status', $endedStatus, 'total_price') . ' as ended_price'),
                DB::raw(PortableSql::averageWhenEquals('status', $endedStatus, 'total_price') . ' as ended_avg_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $canceledStatus, 'total_price') . ' as canceled_price'),
                DB::raw('SUM(' . PortableSql::elapsedWholeHours('start_date', 'end_date') . ') as total_booked_hours'),
                DB::raw(PortableSql::dateFormat('created_at', $type) . ' as time'),
                DB::raw(PortableSql::dateFormat('created_at', '%Y-%m-%d') . ' as time_format'),
            ])
            ->groupBy('time')
            ->get();

        $orders = DB::table('orders')
            ->whereIn('status', [$orderCanceledStatus, $deliveredStatus])
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->when($shopId,   fn($q)            => $q->where('shop_id', $shopId))
            ->when($restrictedShopIds !== null, fn($q) => $q->whereIn('shop_id', $restrictedShopIds))
            ->select([
                DB::raw('count(id) as total_count'),
                DB::raw('sum(total_price) as total_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $deliveredStatus, 'total_price') . ' as delivered_price'),
                DB::raw(PortableSql::averageWhenEquals('status', $deliveredStatus, 'total_price') . ' as delivered_avg_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $orderCanceledStatus, 'total_price') . ' as canceled_price'),
                DB::raw(PortableSql::dateFormat('created_at', $type) . ' as time'),
            ])
            ->groupBy('time')
            ->get();

        $memberShips = UserMemberShip::when($shopId, fn($q) => $q->whereHas('memberShip', fn($q) => $q->where('shop_id', $shopId)))
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->select([
                DB::raw('sum(price) as price'),
            ])
            ->first();

        $giftCarts = UserGiftCart::when($shopId, fn($q) => $q->whereHas('giftCart', fn($q) => $q->where('shop_id', $shopId)))
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->select([
                DB::raw('sum(price) as price'),
            ])
            ->first();

        $newCustomers = User::whereHas('roles', fn($q) => $q->where('name', 'user'))
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->when($shopId,   fn($q)            => $q->where(function ($query) use ($shopId) {
                $query
                    ->whereHas('orders',     fn($q) => $q->where('shop_id', $shopId))
                    ->orWhereHas('bookings', fn($q) => $q->where('shop_id', $shopId));
            }))
            ->select([
                'id',
                'firstname',
                'lastname',
                DB::raw(PortableSql::dateFormat('created_at', $type) . ' as time'),
            ])
            ->withCount([
                'bookings as bookings_count' => function ($q) use ($dateFrom, $dateTo, $shopId) {
                    $q
                        ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                        ->where('created_at', '>=', $dateFrom)
                        ->where('created_at', '<=', $dateTo);
                },
            ])
            ->get();

        $workingHours  = 0;
        $continueCount = 0;
        $skipDays      = (new DateTime($dateFrom))->diff(new DateTime($dateTo))->days;
        $dayTimes      = [];
        $times         = [];

        // Occupancy (booked vs. unbooked hours against a shop's own
        // working-day calendar) is inherently a single-shop concept - it
        // doesn't aggregate across shops - so it's simply skipped for a
        // platform-wide request (no shop_id given). $shop is only ever
        // null in that case; every query above already handles a null/
        // omitted $shopId as "don't filter by shop" via ->when().
        if ($shop) {

            foreach ($shop->workingDays as $workingDay) {

                if ($workingDay->disabled) {
                    $continueCount += 1;
                    continue;
                }

                $dayTimes[$workingDay->day] = [
                    'from' => $workingDay->from,
                    'to'   => $workingDay->to,
                ];

                try {
                    $workingHour = (new DateTime($workingDay->from))->diff(new DateTime($workingDay->to))->h;
                } catch (Throwable) {
                    $workingHour = 0;
                }

                $workingHours += $workingHour;
            }

            for ($i = 0; $skipDays >= $i; $i++) {

                // This loop always walks one calendar day at a time
                // (matching the fixed '%Y-%m-%d' time_format column below),
                // regardless of the overall $type granularity requested -
                // $type is an allowlisted PortableSql report date format
                // (e.g. '%Y-%m-%d'),
                // not a PHP date() format, and using it here directly used
                // to throw a TypeError from strtotime() failing to parse
                // the resulting garbled string (e.g. '%2026-%08-%01').
                $nextDay     = date('Y-m-d', strtotime("$dateFrom +$i days"));
                $day         = Str::lower(date('l', strtotime($nextDay)));
                $workingHour = $workingHours / $shop->workingDays->count();

                if (!isset($dayTimes[$day]['from'])) {

                    if (!isset($times[$nextDay])) {
                        $times[$nextDay] = [
                            'date'           => $nextDay,
                            'working_hours'  => $workingHour,
                            'unbooked_hours' => 0,
                            'booked_hours'   => 0,
                            'hours'          => 0,
                        ];
                    }

                    continue;
                }

                $startTime = new DateTime($dayTimes[$day]['from']);
                $endTime   = new DateTime($dayTimes[$day]['to']);

                $bookedHours = ($bookings->where('time_format', $nextDay)->first())?->total_booked_hours ?? 0;
                $closedDate  = $shop->workingDays->where('day', $nextDay)->first();
                $closedHour  = 0;

                if ($closedDate) {
                    $closedHour = $startTime->diff($endTime)->h;
                }

                $times[$nextDay] = [
                    'date'           => $nextDay,
                    'hours'          => ($times[$nextDay]['hours'] ?? 0) + $workingHour + $bookedHours + $closedHour,
                    'working_hours'  => ($times[$nextDay]['working_hours'] ?? 0) + $workingHour,
                    'unbooked_hours' => ($times[$nextDay]['unbooked_hours'] ?? 0) + $workingHour - $bookedHours + $closedHour,
                    'booked_hours'   => ($times[$nextDay]['booked_hours'] ?? 0) + $bookedHours,
                ];

            }
        }

        $days = $this->calculateWorkingDifference(new DateTime($dateFrom), new DateTime($dateTo), 7 - $continueCount);

        $times = collect($times)->values();
        $hours = $times->sum('hours');

        $occupancyRate = $shop ? $hours / $days : 0;

        $canceledPrice  = $orders->sum('canceled_price');
        $deliveredPrice = $orders->sum('delivered_price');
        $endedAvgPrice  = $orders->sum('delivered_avg_price');
        $endedCount     = $orders->sum('total_count');

        return [
            'total_sales' => [
                'services'        => $bookings->sum('ended_price') ?? 0,
                'products'        => $orders->sum('total_price') ?? 0,
                'cancellations'   => ($bookings->sum('canceled_price') + $canceledPrice) ?? 0,
                'memberships'     => $memberShips?->price ?? 0,
                'gift_carts'      => $giftCarts?->price ?? 0,
                'shipping'        => $deliveredPrice ?? 0,
            ],

            'working_hours'       => $times->sum('working_hours'),
            'unbooked_hours'      => $times->sum('unbooked_hours'),
            'booked_hours'        => $times->sum('booked_hours'),
            'hours'               => $hours,

            'occupancy_chart'     => $times->toArray(),
            'occupancy_rate'      => $occupancyRate,

            'average_price'       => $endedAvgPrice,
            'ended_count'         => $endedCount,

            'top_customers'       => $newCustomers->sortByDesc('bookings_count')->values()->take(5)->toArray(),
            'bottom_customers'    => $newCustomers->sortBy('bookings_count')->values()->take(5)->toArray(),
            'new_customers'       => $newCustomers->count(),
            'returning_customers' => $newCustomers->where('bookings_count', '>', 1)->count(),

            'bookings_chart'      => $bookings->sortBy('time')->values()->toArray(),
            'sales_chart'         => $orders->sortBy('time')->values()->toArray(),
        ];
    }

    /**
     * @param array $filter
     * @return array
     * @throws Exception
     */
    public function onlinePresenceDashboard(array $filter = []): array
    {
        $type = match ($filter['type'] ?? 'day') {
            'year'  => '%Y',
            'month' => '%Y-%m',
            'week'  => '%Y-%m-%w',
            'day'   => '%Y-%m-%d',
            default => '%Y-%m-%d %H:00',
        };

        $dateFrom = data_get($filter, 'date_from');
        $dateTo   = data_get($filter, 'date_to');
        $shopId   = data_get($filter, 'shop_id');
        $restrictedShopIds = CountryContext::restrictedShopIds();
        $masterId = data_get($filter, 'master_id');

        $canceledStatus = Booking::STATUS_CANCELED;
        $endedStatus    = Booking::STATUS_ENDED;

        $orderCanceledStatus = Order::STATUS_CANCELED;
        $deliveredStatus     = Order::STATUS_DELIVERED;


        /** @var Shop $shop */
        $shop = Shop::with([
            'workingDays',
            'closedDates' => fn($q) => $q->where('date', '>=', $dateFrom)->where('date', '<=', $dateTo)
        ])->find($shopId);

        $bookings = DB::table('bookings')
            ->whereIn('status', [$canceledStatus, $endedStatus])
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->when($shopId,   fn($q)            => $q->where('shop_id',   $shopId))
            ->when($restrictedShopIds !== null, fn($q) => $q->whereIn('shop_id', $restrictedShopIds))
            ->when($masterId, fn($q)            => $q->where('master_id', $masterId))
            ->select([
                DB::raw('count(id) as ended_count'),
                DB::raw(PortableSql::sumWhenEquals('status', $endedStatus, 'total_price') . ' as ended_price'),
                DB::raw(PortableSql::averageWhenEquals('status', $endedStatus, 'total_price') . ' as ended_avg_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $canceledStatus, 'total_price') . ' as canceled_price'),
                DB::raw(PortableSql::countWhenEquals('status', $canceledStatus) . ' as canceled_count'),
                DB::raw('SUM(' . PortableSql::elapsedWholeHours('start_date', 'end_date') . ') as total_booked_hours'),
                DB::raw(PortableSql::dateFormat('created_at', $type) . ' as time'),
                DB::raw(PortableSql::dateFormat('created_at', '%Y-%m-%d') . ' as time_format'),
            ])
            ->groupBy('time')
            ->get();

        $orders = DB::table('orders')
            ->whereIn('status', [$orderCanceledStatus, $deliveredStatus])
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->when($shopId,   fn($q)            => $q->where('shop_id', $shopId))
            ->when($restrictedShopIds !== null, fn($q) => $q->whereIn('shop_id', $restrictedShopIds))
            ->select([
                DB::raw('count(id) as total_count'),
                DB::raw('sum(total_price) as total_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $deliveredStatus, 'total_price') . ' as delivered_price'),
                DB::raw(PortableSql::averageWhenEquals('status', $deliveredStatus, 'total_price') . ' as delivered_avg_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $orderCanceledStatus, 'total_price') . ' as canceled_price'),
                DB::raw(PortableSql::dateFormat('created_at', $type) . ' as time'),
            ])
            ->groupBy('time')
            ->get();

        $newCustomers = User::whereHas('roles', fn($q) => $q->where('name', 'user'))
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
//            ->when($shopId,   fn($q)            => $q->where(function ($query) use ($shopId) {
//                $query
//                    ->whereHas('orders',     fn($q) => $q->where('shop_id', $shopId))
//                    ->orWhereHas('bookings', fn($q) => $q->where('shop_id', $shopId));
//            }))
            ->select([
//                'id',
//                'firstname',
//                'lastname',
                DB::raw('count(id) as count'),
                DB::raw(PortableSql::dateFormat('created_at', $type) . ' as time'),
            ])
            ->withCount([
                'bookings as bookings_count' => function ($q) use ($dateFrom, $dateTo, $shopId) {
                    $q
                        ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
                        ->where('created_at', '>=', $dateFrom)
                        ->where('created_at', '<=', $dateTo);
                },
            ])
            ->get();

        $reviewsAvg = Review::whereIn('assignable_type', [Shop::class, User::class])
            ->whereIn('reviewable_type', [Order::class, Booking::class])
            ->whereIn('assignable_id', [$shopId, $masterId])
            ->where('updated_at', '>=', $dateFrom)
            ->where('updated_at', '<=', $dateTo)
            ->select([
                DB::raw('count(id)   as count_rating'),
                DB::raw('sum(rating) as sum_rating'),
                DB::raw('avg(rating) as avg_rating'),
                DB::raw(PortableSql::dateFormat('created_at', $type) . ' as time'),
            ])
            ->groupBy('time')
            ->get();

        $workingHours  = 0;
        $continueCount = 0;
        $skipDays      = (new DateTime($dateFrom))->diff(new DateTime($dateTo))->days;
        $dayTimes      = [];
        $times         = [];

        foreach ($shop->workingDays as $workingDay) {

            if ($workingDay->disabled) {
                $continueCount += 1;
                continue;
            }

            $dayTimes[$workingDay->day] = [
                'from' => $workingDay->from,
                'to'   => $workingDay->to,
            ];

            try {
                $workingHour = (new DateTime($workingDay->from))->diff(new DateTime($workingDay->to))->h;
            } catch (Throwable) {
                $workingHour = 0;
            }

            $workingHours += $workingHour;
        }

        for ($i = 0; $skipDays >= $i; $i++) {

            $nextDay = date(str_replace('%', '', $type), strtotime("$dateFrom +$i days"));
            $day     = Str::lower(date('l', strtotime($nextDay)));

            if (!isset($dayTimes[$day]['from'])) {

                if (!isset($times[$nextDay])) {
                    $times[$nextDay] = [
                        'time'           => $nextDay,
                        'booking_price' => 0,
                        'order_price'   => 0,
                    ];
                }

                continue;
            }

            $times[$nextDay] = [
                'time'          => $nextDay,
                'booking_price' => 0,
                'order_price'   => 0,
            ];

        }

        $deliveredPrice         = $orders->sum('delivered_price');
        $bookingEndedCount      = $bookings->sum('ended_count');
        $bookingEndedAvgPrice   = $bookings->sum('ended_avg_price');
        $bookingEndedPrice      = $bookings->sum('ended_price');
        $bookingCanceledPrice   = $bookings->sum('canceled_price');
        $bookingCanceledCount   = $bookings->sum('canceled_count');

        foreach ($bookings->sortBy('time')->toArray() as $item) {

            $item = (array)$item;

            if (!isset($times[$item['time']]['booking_price'])) {
                $times[$item['time']]['booking_price'] = $item['ended_price'];
                continue;
            }

            $times[$item['time']]['booking_price'] += $item['ended_price'];
        }

        foreach ($orders->sortBy('time')->toArray() as $item) {

            $item = (array)$item;

            if (!isset($times[$item['time']]['order_price'])) {
                $times[$item['time']]['order_price'] = $item['delivered_price'];
                continue;
            }

            $times[$item['time']]['order_price'] += $item['delivered_price'];
        }

        return [
            'orders_price'        => $deliveredPrice,
            'bookings_price'      => $bookingEndedPrice,
            'lifetime_sales'      => $bookingEndedPrice + $deliveredPrice,
            'online_clients'      => User::where('updated_at', '>=', $dateFrom)->where('updated_at', '<=', $dateTo)->count(),
            'average_price'       => $bookingEndedAvgPrice,
            'ended_count'         => $bookingEndedCount,
            'canceled_price'      => $bookingCanceledPrice,
            'canceled_count'      => $bookingCanceledCount,
            'online_ratings'      => $reviewsAvg->sum('count_rating') / $reviewsAvg->sum('sum_rating'),
            'lifetime_chart'      => array_values($times),

            'bookings'            => $bookings,
            'new_customers_chart' => $newCustomers->groupBy('time')->values()->toArray(),
            'new_customers'       => $newCustomers->sum('count'),
            'returning_customers' => $newCustomers->where('bookings_count', '>', 1)->count(),

            'ratings_chart'       => $reviewsAvg,
        ];
    }

    /**
     * @param array $filter
     * @return array
     */
    public function salesSummary(array $filter = []): array
    {

//        foreach (Booking::with(['serviceMaster.service'])->get() as $value) {
//            try {
//                $value->update([
//                    'service_id'  => $value->serviceMaster->service_id,
//                    'category_id' => $value->serviceMaster->service->category_id,
//                ]);
//            } catch (Throwable $e) {
//
//            }
//        }

        $type = match ($filter['type'] ?? 'service') {
            'category' => 'category_id',
            'shop'     => 'shop_id',
            'master'   => 'master_id',
            'user'     => 'user_id',
            'gender'   => 'gender',
            'type'     => 'type',
            default    => 'service_id',
        };

        $column     = $filter['column'] ?? 'count';
        $dateFrom   = data_get($filter, 'date_from');
        $dateTo     = data_get($filter, 'date_to');

        $bookings = Booking::filter($filter)
            ->where('status', Booking::STATUS_ENDED)
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->select([
                DB::raw('count(id) as count'),
                DB::raw('sum(gift_cart_price) as gift_cart_price'),
                DB::raw('sum(total_price) as total_price'),
                DB::raw('sum(coupon_price) as coupon_price'),
                DB::raw('sum(discount) as discount'),
                DB::raw('sum(commission_fee) as commission_fee'),
                DB::raw('sum(service_fee) as service_fee'),
                DB::raw('sum(extra_price) as extra_price'),
                DB::raw('sum(tips) as tips'),
                DB::raw($type),
            ])
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->groupBy([$type])
            ->get();

        foreach ($bookings as $booking) {
            $this->getNameByType($type, $booking);
        }

        return $bookings->toArray();
    }

    /**
     * @param string $type
     * @param mixed $booking
     * @return mixed
     */
    private function getNameByType(string $type, mixed $booking): mixed
    {
        
        $key    = "{$type}_{$booking->$type}";

        if ($type === 'service_id') {

            $service = Cache::remember($key, 3600, function () use ($booking) {
                return ServiceTranslation::where('service_id', $booking->service_id)
                    ->where('locale', $this->language)
                    ->first();
            });

            $booking->service_title = $service?->title ?? 'Empty';

        } else if ($type === 'category_id') {

            $category = Cache::remember($key, 3600, function () use ($booking) {
                return CategoryTranslation::where('category_id', $booking->category_id)
                    ->where('locale', $this->language)
                    ->first();
            });

            $booking->category_title = $category?->title ?? 'Empty';

        } else if ($type === 'master_id') {

            $fullName = Cache::remember($key, 3600, function () use ($booking) {
                return User::find($booking->master_id)->full_name;
            });

            $booking->master_name = $fullName ?? 'Empty';

        } else if ($type === 'user_id') {

            $fullName = Cache::remember($key, 3600, function () use ($booking) {
                return User::find($booking->user_id)->full_name;
            });

            $booking->user_name = $fullName ?? 'Empty';

        } else if ($type === 'shop_id') {

            $shop = Cache::remember($key, 3600, function () use ($booking) {
                return ShopTranslation::where('shop_id', $booking->shop_id)
                    ->where('locale', $this->language)
                    ->first();
            });

            $booking->shop_title = $shop->title ?? 'Empty';

        }

        return $booking;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function salesList(array $filter = []): LengthAwarePaginator
    {
        $column = $filter['column'] ?? 'id';

        if (!Schema::hasColumn('bookings', $column)) {
            $column = 'id';
        }

        return Booking::filter($filter)
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function salesLogDetail(array $filter = []): LengthAwarePaginator
    {
        $column = $filter['column'] ?? 'id';

        if (!Schema::hasColumn('bookings', $column)) {
            $column = 'id';
        }

        return Booking::filter($filter)
            ->with(['transaction'])
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function giftCardList(array $filter = []): LengthAwarePaginator
    {
        $column = $filter['column'] ?? 'id';

        if (!Schema::hasColumn('user_gift_carts', $column)) {
            $column = 'id';
        }

        return UserGiftCart::filter($filter)
            ->with(['transaction'])
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function membershipList(array $filter = []): LengthAwarePaginator
    {
        $column = $filter['column'] ?? 'id';

        if (!Schema::hasColumn('user_member_ships', $column)) {
            $column = 'id';
        }

        return UserMemberShip::filter($filter)
            ->with(['transaction'])
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param array $filter
     * @return array
     */
    public function paymentsSummary(array $filter = []): array
    {
        $dateFrom   = data_get($filter, 'date_from');
        $dateTo     = data_get($filter, 'date_to');

        $transactions = Transaction::filter($filter)
            ->select([
                DB::raw('payment_sys_id'),
                DB::raw('sum(price) as price'),
                DB::raw('count(id) as count'),
            ])
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->when(isset($filter['statuses']),
                fn($q) => $q->whereIn('status', $filter['statuses']),
                fn($q) => $q->where('status', Transaction::STATUS_PAID)
            )
            ->orderBy('price', 'desc')
            ->groupBy('payment_sys_id')
            ->get();

        $payments = Payment::get();

        $result = [];

        foreach ($payments as $payment) {

            $transaction = $transactions->where('payment_sys_id', $payment->id)->first();

            $result[] = [
                'id'    => $payment->id,
                'tag'   => $payment->tag,
                'price' => round($transaction?->price ?? 0, 2),
                'count' => $transaction?->count ?? 0,
            ];

        }

        return collect($result)->sortByDesc('price')->values()->toArray();
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paymentTransactions(array $filter = []): LengthAwarePaginator
    {
        $dateFrom   = data_get($filter, 'date_from');
        $dateTo     = data_get($filter, 'date_to');
        $column     = $filter['column'] ?? 'count';

        if (!Schema::hasColumn('transaction', $column)) {
            $column = 'id';
        }

        return Transaction::filter($filter)
            ->when(isset($filter['statuses']),
                fn($q) => $q->whereIn('status', $filter['statuses']),
                fn($q) => $q->where('status', Transaction::STATUS_PAID)
            )
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    public function financeSummary(array $filter = []): array
    {
        $dateFrom   = data_get($filter, 'date_from');
        $dateTo     = data_get($filter, 'date_to');

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $transactions = Transaction::filter($filter)
            ->select([
                DB::raw('price'),
                DB::raw('payment_sys_id'),
                DB::raw(PortableSql::monthName('created_at') . ' as time'),
            ])
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->when(isset($filter['statuses']),
                fn($q) => $q->whereIn('status', $filter['statuses']),
                fn($q) => $q->where('status', Transaction::STATUS_PAID)
            )
            ->get();
        $transactions = $transactions
            ->sortBy(fn ($transaction) => array_search($transaction->time, $months, true))
            ->values();

        $payments = Payment::get();

        $result = [];

        foreach ($payments as $payment) {

            $groupTransaction = $transactions->where('payment_sys_id', $payment->id)->groupBy('time')->toArray();

            $result[$payment->id] = [
                'id'     => $payment->id,
                'tag'    => $payment->tag,
                'values' => []
            ];

            foreach ($months as $month) {

                $value = collect($groupTransaction[$month] ?? []);

                $result[$payment->id]['values'][] = [
                    'time'  => $month,
                    'price' => round($value->sum('price'), 2),
                    'count' => $value->count(),
                ];

            }

        }

        $ended    = Booking::STATUS_ENDED;
        $canceled = Booking::STATUS_CANCELED;

        $bookings = Booking::filter($filter)
            ->select([
                DB::raw(PortableSql::countWhenEquals('status', $ended) . ' as count'),
                DB::raw(PortableSql::sumWhenEquals('status', $ended, 'gift_cart_price') . ' as gift_cart_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $ended, 'total_price') . ' as total_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $ended, 'coupon_price') . ' as coupon_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $ended, 'discount') . ' as discount'),
                DB::raw(PortableSql::sumWhenEquals('status', $ended, 'commission_fee') . ' as commission_fee'),
                DB::raw(PortableSql::sumWhenEquals('status', $ended, 'service_fee') . ' as service_fee'),
                DB::raw(PortableSql::sumWhenEquals('status', $ended, 'extra_price') . ' as extra_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $ended, 'tips') . ' as tips'),
                DB::raw(PortableSql::sumWhenEquals('status', $canceled, 'total_price') . ' as total_new_user_by_referral_count'),
                DB::raw(PortableSql::monthName('created_at') . ' as time'),
            ])
            ->groupBy('time')
            ->get();

        $sales = [
            'gross_sales' => [
                'discounts' => [],
            ],
            'net_sales' => [
                'taxes' => [],
            ],
            'total_sales' => [
                'gift_card_sales' => [],
                'service_charges' => [],
                'tips' => [],
            ],
            'total_other_sales' => [],
            'total_sales_and_other_sales' => [
                'sales_paid_in_period' => [],
                'unpaid_sales_in_period' => [],
            ]
        ];

        foreach ($months as $month) {

            $booking = $bookings->where('time', $month)->first();

            $sales['gross_sales']['discounts'][] = [
                'month' => $month,
                'value' => $booking?->discount ?? 0
            ];

            $sales['net_sales']['taxes'][] = [
                'month' => $month,
                'value' => $booking?->commission_fee ?? 0
            ];

            $sales['total_sales']['gift_card_sales'][] = [
                'month' => $month,
                'value' => $booking?->gift_cart_price ?? 0
            ];

            $sales['total_sales']['service_charges'][] = [
                'month' => $month,
                'value' => $booking?->tips ?? 0
            ];

            $sales['total_sales']['tips'][] = [
                'month' => $month,
                'value' => $booking?->service_fee ?? 0
            ];

            $sales['total_other_sales'][] = [
                'month' => $month,
                'value' => $booking?->total_price ?? 0
            ];

            $sales['total_sales_and_other_sales']['sales_paid_in_period'][] = [
                'month' => $month,
                'sales_paid_in_period'     => $booking?->total_price ?? 0,
                'canceled_sales_in_period' => $booking?->canceled_total_price ?? 0
            ];

        }

        return [
            'sales' => $sales,
            'payments' => collect($result)->values()->toArray(),
        ];
    }

    public function appointmentsSummary(array $filter): array
    {
        $dateFrom = data_get($filter, 'date_from');
        $dateTo   = data_get($filter, 'date_to');
        $column   = data_get($filter, 'column', 'id');
        $columns  = ['count', 'gift_cart_price', 'total_price', 'coupon_price', 'discount', 'commission_fee', 'service_fee', 'extra_price', 'tips'];

        if (!in_array($column, $columns)) {
            $column = 'id';
        }

        $language = Language::where('default', 1)->first();

        return Booking::filter($filter)
            ->with([
                'shop:id',
                'shop.translation' => fn($q) => $q
                    ->select('locale', 'title', 'shop_id')
                    ->where(fn($q) => $q->where('locale', $this->language)->orWhere('locale', $language)),
            ])
            ->where('status', Booking::STATUS_ENDED)
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->select([
                DB::raw('count(id) as count'),
                DB::raw('sum(gift_cart_price) as gift_cart_price'),
                DB::raw('sum(total_price) as total_price'),
                DB::raw('sum(coupon_price) as coupon_price'),
                DB::raw('sum(discount) as discount'),
                DB::raw('sum(commission_fee) as commission_fee'),
                DB::raw('sum(service_fee) as service_fee'),
                DB::raw('sum(extra_price) as extra_price'),
                DB::raw('sum(tips) as tips'),
                DB::raw('shop_id'),
            ])
            ->orderBy($column, $filter['sort'] ?? 'desc')
            ->groupBy('shop_id')
            ->get()
            ->toArray();
    }

    /**
     * @param array $filter
     * @return array
     * @throws Exception
     */
    public function workingHours(array $filter): array
    {
        $type     = '%Y-%m-%d';
        $dateFrom = data_get($filter, 'date_from');
        $dateTo   = data_get($filter, 'date_to');
        $shopId   = data_get($filter, 'shop_id');
        $restrictedShopIds = CountryContext::restrictedShopIds();
        $masterId = data_get($filter, 'master_id');

        $newStatus      = Booking::STATUS_NEW;
        $canceledStatus = Booking::STATUS_CANCELED;
        $bookedStatus   = Booking::STATUS_BOOKED;
        $progressStatus = Booking::STATUS_PROGRESS;
        $endedStatus    = Booking::STATUS_ENDED;

        $bookings = Booking::whereIn('status', [$canceledStatus, $endedStatus])
            ->when($dateFrom, fn($q, $dateFrom) => $q->where('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q, $dateTo)   => $q->where('created_at', '<=', $dateTo))
            ->when($shopId,   fn($q)            => $q->where('shop_id',   $shopId))
            ->when($masterId, fn($q)            => $q->where('master_id', $masterId))
            ->select([
                DB::raw(PortableSql::dateFormat('created_at', '%Y-%m-%d') . ' as time'),
                DB::raw('master_id'),
                DB::raw('count(id) as ended_count'),
                DB::raw('sum(total_price) as total_price'),
                DB::raw('SUM(' . PortableSql::elapsedWholeHours('start_date', 'end_date') . ') as total_booked_hours'),

                DB::raw(PortableSql::countWhenEquals('status', $newStatus) . ' as new_count'),
                DB::raw(PortableSql::countWhenEquals('status', $canceledStatus) . ' as canceled_count'),
                DB::raw(PortableSql::countWhenEquals('status', $bookedStatus) . ' as booked_count'),
                DB::raw(PortableSql::countWhenEquals('status', $progressStatus) . ' as progress_count'),
                DB::raw(PortableSql::countWhenEquals('status', $endedStatus) . ' as ended_count'),

                DB::raw(PortableSql::sumWhenEquals('status', $newStatus, 'total_price') . ' as new_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $canceledStatus, 'total_price') . ' as canceled_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $bookedStatus, 'total_price') . ' as booked_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $progressStatus, 'total_price') . ' as progress_price'),
                DB::raw(PortableSql::sumWhenEquals('status', $endedStatus, 'total_price') . ' as ended_price'),
            ])
            ->groupBy('time', 'master_id')
            ->get();

        $times = [];

        foreach ($bookings as $booking) {

            if (!isset($times[$booking->master_id])) {
                $times[$booking->master_id] = [
                    'master' => User::select(['img', 'firstname', 'lastname'])->find($booking->master_id)
                ];
            }

            $times[$booking->master_id]['values'][] = $booking;

        }

        return collect($times)->values()->toArray();
    }

}
