<?php
declare(strict_types=1);

namespace App\Repositories\UserRepository;

use DateTime;
use Exception;
use DateInterval;
use App\Models\User;
use App\Models\Booking;
use App\Models\Settings;
use App\Models\Invitation;
use App\Models\ShopLocation;
use Illuminate\Support\Str;
use App\Models\ServiceMaster;
use App\Helpers\ResponseError;
use App\Models\UserWorkingDay;
use App\Models\MasterDisabledTime;
use App\Services\BookingService\DisabledTimeRecurrence;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MasterRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return User::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function index(array $filter = []): LengthAwarePaginator
    {
        $serviceFirst = !empty($filter['service_id']) || !empty($filter['service_ids']);
        // Public profiles use the native master role. Accepted shop-invited
        // specialists without that public role remain service-first bookable.
        unset($filter['role']);
        return User::filter($serviceFirst ? $filter : array_merge($filter, ['role' => 'master']))
            ->with(\App\Helpers\SpecialistDiscovery::relations($this->language, $filter))
            ->whereHas('serviceMasters', fn ($q) => \App\Helpers\SpecialistDiscovery::eligibleAssignments($q, $filter)
                ->when(data_get($filter, 'service_id'), fn ($q, $id) => $q->where('service_id', $id))
                ->when(data_get($filter, 'service_ids'), fn ($q, $ids) => $q->whereIn('service_id', $ids)))
            ->whereHas('serviceMaster', fn($q) => $q
                ->select('service_id', 'active', 'master_id')
                ->where('active', true)
                ->when(data_get($filter, 'service_id'), fn ($query, $id) => $query->where('service_id', $id))
                ->when(data_get($filter, 'service_ids'), fn ($query, $ids) => $query->whereIn('service_id', $ids))
            )
            ->whereHas('invite', fn($q) => $q
                ->select(['user_id', 'status'])
                ->where('status', Invitation::ACCEPTED)
                ->when(data_get($filter, 'shop_location_id'), function ($query, $locationId) {
                    $query->where(fn ($assignment) => $assignment
                        ->whereHas('shopLocations', fn ($locations) => $locations
                            ->where('shop_locations.id', $locationId)
                            ->whereColumn('shop_locations.shop_id', 'invitations.shop_id'))
                        ->orWhereDoesntHave('shopLocations')
                    );
                })
                // Services are shop-wide, so a master's invitation_shop_locations
                // pivot (same one User::bookingBranchScope() uses) is the only
                // place a customer's branch context can narrow this list.
                // Assigning a branch is opt-in (see admin's "assign to
                // branch (optional)") - a master with no pivot rows at all
                // was never restricted to a branch, so they're available at
                // every one of them, not excluded from all of them. Only a
                // master who WAS given specific branches, but not this one,
                // should be filtered out.
                ->when(
                    collect($filter)->only(['region_id', 'country_id', 'city_id', 'area_id'])->filter()->isNotEmpty(),
                    function ($query) use ($filter) {
                        $locationFilter = collect($filter)->only(['region_id', 'country_id', 'city_id', 'area_id'])->filter()->all();
                        // Default to SERVICE the same way ShopResource::matched_location
                        // does - a master list is never about a shop's PRODUCT locations.
                        $locationFilter['type'] = (int) (data_get($filter, 'location_type') ?: ShopLocation::SERVICE);

                        $query->where(fn ($q2) => $q2
                            ->whereHas('shopLocations', fn ($q3) => $q3->filter($locationFilter))
                            ->orWhereDoesntHave('shopLocations')
                        );
                    }
                )
            )
            ->when(
                Settings::where('key', 'by_subscription')->first()?->value,
                fn($q) => $q->whereHas('invite.shop', fn ($query) => $query->where('visibility', true))
            )
            ->with([
                'invite' => fn($q) => $q
                    ->select(['id', 'user_id', 'shop_id', 'status'])
                    ->where('status', Invitation::ACCEPTED),
                'invite.shop:id,uuid,slug,latitude,longitude',
                'invite.shop.translation' => fn($query) => $query
                    ->where('locale', $this->language),
                'translation' => fn($q) => $q
                    ->where('locale', $this->language),
                'translations',
                'serviceMaster' => fn($q) => $q
                    ->where('active', true)
                    ->tap(fn ($q) => \App\Helpers\SpecialistDiscovery::eligibleAssignments($q, $filter))
                    ->when(data_get($filter, 'service_id'), fn ($query, $id) => $query->where('service_id', $id))
                    ->when(data_get($filter, 'service_ids'), fn ($query, $ids) => $query->whereIn('service_id', $ids)),
                'serviceMaster.service:id',
                'serviceMaster.service.translation' => fn($q) => $q
                    ->where('locale', $this->language),
            ])
            ->withMin(['serviceMasters' => fn ($q) => \App\Helpers\SpecialistDiscovery::eligibleAssignments($q, $filter)], 'price')
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * @param User $user
     * @return User
     */
    public function show(User $user): User
    {


        return $user
            ->load(\App\Helpers\SpecialistDiscovery::relations($this->language))
            ->loadMin(['serviceMasters' => fn ($q) => \App\Helpers\SpecialistDiscovery::eligibleAssignments($q)], 'price')
            ->loadMissing([
                'invite' => fn($q) => $q
                    ->select(['user_id', 'shop_id', 'status'])
                    ->where('status', Invitation::ACCEPTED),
                'invite.shop:id,uuid,latitude,longitude',
                'invite.shop.translation' => fn($query) => $query
                    ->where('locale', $this->language),
                'translation' => fn($q) => $q
                    ->where('locale', $this->language),
                'serviceMasters' => fn($q) => $q->where('active', true),
                'serviceMasters.service:id,slug,category_id',
                'serviceMasters.service.translation'=> fn($q) => $q
                    ->where('locale', $this->language),
                'serviceMasters.extras.translation' => fn($q) => $q
                    ->where('locale', $this->language),
            ]);
    }

    /**
     * @param int $id
     * @param array $filter
     * @param bool $values
     * @return array
     * @throws Exception
     */
    public function times(int $id, array $filter, bool $values = true, bool $withIntervals = false, array $excludedBookingIds = []): array
    {
        $maxDay = (int)(Settings::where('key', 'max_day_booking')->first()?->value ?: 90);

        $now  = date('Y-m-d', strtotime($filter['start_date'] ?? now()->format('Y-m-d')));
        $date = date('Y-m-d', strtotime($filter['end_date']   ?? $now));

        $skipDays = (new DateTime($now))->diff(new DateTime($date))->days;

        if ($skipDays > $maxDay) {
            $skipDays = $maxDay;
        } else if (!isset($filter['end_date'])) {
            $skipDays = $maxDay;
            $date = (new DateTime($now))->add(new DateInterval("P{$skipDays}D"));
        }

        $serviceMaster = \App\Helpers\SpecialistDiscovery::eligibleAssignments(ServiceMaster::query())
            ->find($filter['service_master_id'] ?? null);

        if (empty($serviceMaster) || $serviceMaster->master_id !== $id) {
            throw new Exception(__('errors.' . ResponseError::ERROR_400, locale: $this->language));
        }

        $skipMinute = (int)($serviceMaster->interval + $serviceMaster->pause);
        if ($skipMinute <= 0) {
            throw new \DomainException('Appointment duration must be positive.');
        }
        $rangeEnd = (new DateTime($date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date))
            ->modify('+1 day')->format('Y-m-d');

        $master = User::with([

            'workingDays',

            'closedDates' => fn($q) => $q
                ->whereNotNull('date')
                ->whereDate('date', '>=', $now)
                ->whereDate('date', '<=', $date),

            'disabledTimes' => fn($q) => $q
                ->whereNotNull('from')
                ->whereNotNull('to')
                ->where('date', '<=', $date)
                ->where(fn($q) => $q->where('date', '>=', $now)
                    ->orWhere('repeats', '<>', MasterDisabledTime::DONT_REPEAT))
                ->where('can_booking', false),

            'masterBookings' => fn($q) => $q
//                ->where('service_master_id', $filter['service_master_id'])
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereIn('status', [Booking::STATUS_NEW, Booking::STATUS_BOOKED, Booking::STATUS_PROGRESS])
                ->where('start_date', '<', $rangeEnd)
                ->where('end_date', '>', $now)
                ->whereNotIn('id', $excludedBookingIds),
            'masterBookings.serviceMaster' => fn($q) => $q->select(['id', 'interval', 'pause']),
        ])
            ->has('workingDays')
            ->whereHas('invitations', fn($q) => $q->where('role', 'master')
                ->where('status', Invitation::ACCEPTED)->where('shop_id', $serviceMaster->shop_id))
            ->find($id);

        if (empty($master)) {
            throw new Exception(__('errors.' . ResponseError::ERROR_404, locale: $this->language));
        }

        $data = [];

        /** @var User $master */

        for ($i = 0; $skipDays >= $i; $i++) {

            $nextDay = date('Y-m-d', strtotime("$now +$i days"));
            $day = Str::lower(date('l', strtotime($nextDay)));

            $workingDay = $master->workingDays->where('day', $day)->first();

            /** @var UserWorkingDay|null $workingDay */
            $closedDates = $master->closedDates;

            if (!$workingDay || $workingDay->disabled || $closedDates?->where('date', $nextDay)?->isNotEmpty()) {

                $data[$nextDay] = [
                    'date'           => $nextDay,
                    'month'          => date('m', strtotime($nextDay)),
                    'day'            => date('d', strtotime($nextDay)),
                    'name'           => $day,
                    'closed'         => true,
                    'times'          => [],
                    'disabled_times' => [],
                    '_working'       => null,
                    '_duration'      => $skipMinute,
                    '_blocked'       => [],
                ];

                continue;
            }

            $startTime = new DateTime("$nextDay {$workingDay->from}");
            $endTime   = new DateTime("$nextDay {$workingDay->to}");

            $times = $this->collectTimes($startTime, $endTime, $skipMinute);

            $data[$nextDay] = [
                'date'           => $nextDay,
                'month'          => date('m', strtotime($nextDay)),
                'day'            => date('d', strtotime($nextDay)),
                'name'           => $day,
                'closed'         => false,
                'times'          => array_values($times),
                'disabled_times' => [],
                '_working'       => [$startTime->format('Y-m-d H:i:s'), $endTime->format('Y-m-d H:i:s')],
                '_duration'      => $skipMinute,
                '_blocked'       => [],
            ];

        }

        if ($master->disabledTimes?->count() > 0) {
            // Use the actual bounded page (also handles the default DateTime end).
            $occurrenceEnd = array_key_last($data) ?? $now;
            foreach ($master->disabledTimes as $disabledTime) {
                foreach (DisabledTimeRecurrence::dates($disabledTime, $now, $occurrenceEnd) as $occurrence) {
                    if (isset($data[$occurrence]) && !$data[$occurrence]['closed']) {
                        $data[$occurrence] = $this->mergeTimes(
                            $data[$occurrence], [$disabledTime->from, $disabledTime->to]
                        );
                    }
                }
            }
        }

        $data = $this->collectBookingDays($data, $master);
        if (!$withIntervals) {
            foreach ($data as &$dayData) {
                unset($dayData['_working'], $dayData['_duration'], $dayData['_blocked']);
            }
            unset($dayData);
        }

        return $values ? collect($data)->sort()->values()->toArray() : $data;
    }

    /**
     * @param array $filter
     * @return array
     * @throws Exception
     */
    public function timesAll(array $filter): array
    {
        $data = [];

        $serviceMasters = ServiceMaster::with(['master:id,img,firstname,lastname'])
            ->select(['master_id', 'id'])
            ->whereIn('id', $filter['service_master_ids'])
            ->get();

        foreach ($serviceMasters as $serviceMaster) {

            /** @var ServiceMaster $serviceMaster */
            $filter['service_master_id'] = $serviceMaster->id;

            $times = $this->times($serviceMaster->master_id, $filter, false);

            $data[] = [
                'service_master' => $serviceMaster,
                'times'          => array_values($times),
            ];

//            if (count($data) === 0) { // for first iteration
//                $data = $times;
//                continue;
//            }
//
//            foreach ($times as $time) {
//
//                if (!isset($data[$time['date']]) || $time['closed']) {
//                    $data[$time['date']] = $time;
//                    continue;
//                }
//
//                if ($data[$time['date']]['closed']) {
//                    continue;
//                }
//
//                $disabledTimes = collect($time['disabled_times'])
//                    ->merge($data[$time['date']]['disabled_times'])
//                    ->sort()
//                    ->unique()
//                    ->values();
//
//                $min = $disabledTimes->min();
//                $max = $disabledTimes->max();
//
//                $times = collect($time['times'])
//                    ->merge($data[$time['date']]['times'])
//                    ->sort()
//                    ->filter(fn($hour) => $hour < $min || $hour > $max)
//                    ->unique()
//                    ->values()
//                    ->toArray();
//
//                $data[$time['date']]['disabled_times'] = $disabledTimes->toArray();
//                $data[$time['date']]['times']          = $times;
//            }

        }

        return array_values($data);
    }

    /**
     * @param array $data
     * @param User $master
     * @return array
     * @throws Exception
     */
    private function collectBookingDays(array $data, User $master): array
    {
        if ($master->masterBookings?->count() === 0) {
            return $data;
        }

        foreach ($master->masterBookings as $masterBooking) {
            // Persisted endpoints, not today's service duration or sampled ticks,
            // own occupancy. Include intervals crossing the requested date range.
            $period = [$masterBooking->start_date, $masterBooking->end_date];
            foreach ($data as $date => $dayData) {
                $dayStart = "$date 00:00:00";
                $dayEnd = (new DateTime($dayStart))->modify('+1 day')->format('Y-m-d H:i:s');
                if ($period[0] < $dayEnd && $period[1] > $dayStart) {
                    $data[$date] = $this->mergeTimes($dayData, $period);
                }
            }

        }

        return $data;
    }
    /**
     * @param array $data
     * @param int $days
     * @param string $type
     * @param DateTime $formatDisabledTime
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @param int $skipMinute
     * @param MasterDisabledTime $disabledTime
     * @return array
     * @throws Exception
     */
    private function collectAfterDates(
        array $data,
        int $days,
        string $type,
        DateTime $formatDisabledTime,
        DateTime $startTime,
        DateTime $endTime,
        int $skipMinute,
        MasterDisabledTime $disabledTime,
    ): array
    {
        $subDay = 0;

        for ($i = 0; !empty($disabledTime->custom_repeat_value) ? $days > $i : $days >= $i; $i++) {

            $day = $disabledTime->custom_repeat_value[0] ?? 1;

            $weekDays = [];

            if ($i > 0) {

                $dayNumber = (int)$formatDisabledTime->format('d');
                $formatDisabledTime->add(new DateInterval("P$day$type"));

                if ($type !== 'W') {

                    if ($subDay > 0) {
                        $formatDisabledTime->add(new DateInterval("P{$subDay}D"));
                        $subDay = 0;
                    }

                    if ($dayNumber > (int)$formatDisabledTime->format('d')) {

                        $subDay = $dayNumber - $formatDisabledTime->format('d') - $dayNumber;

                        $subDay = str_replace('-', '', (string)$subDay);
                        $formatDisabledTime->sub(new DateInterval("P{$subDay}D"));
                    }

                } else {
                    $dayOfWeek = $formatDisabledTime->format('w');

                    $formatDisabledTime->modify("-$dayOfWeek days");

                    if (!empty($disabledTime->custom_repeat_value)) {
                        for ($i = 0; $i < 7; $i++) {
                            $weekDays[] = $formatDisabledTime->format('Y-m-d');
                            $formatDisabledTime->modify('+1 day');
                        }
                    } else {
                        $formatDisabledTime->modify('+7 days');
                    }

                }

            }

            if (count($weekDays) == 0) {

                $newDate = $formatDisabledTime->format('Y-m-d');

                $disabledTimes = [$startTime->format('H:i:s'), $endTime->format('H:i:s')];

                $data = $this->setAfterDates($data, $disabledTimes, $formatDisabledTime, $newDate);

                continue;

            }

            $data = $this->eachByWeekDays($data, $weekDays, $disabledTime, $startTime, $endTime, $skipMinute);

        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $weekDays
     * @param MasterDisabledTime $disabledTime
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @param int $skipMinute
     * @return array
     * @throws Exception
     */
    private function eachByWeekDays(
        array $data,
        array $weekDays,
        MasterDisabledTime $disabledTime,
        DateTime $startTime,
        DateTime $endTime,
        int $skipMinute
    ): array
    {

        foreach ($weekDays as $weekDay) {

            $weekDay = new DateTime($weekDay);

            if (
                !empty($disabledTime->custom_repeat_value)
                && !in_array(Str::lower($weekDay->format('l')), $disabledTime->custom_repeat_value)
            ) {
                continue;
            }

            if (
                empty($disabledTime->custom_repeat_value) &&
                $weekDay->format('l') !== (new DateTime($disabledTime->date))->format('l')
            ) {
                continue;
            }

            $newDate = $weekDay->format('Y-m-d');

            $disabledTimes = [$startTime->format('H:i:s'), $endTime->format('H:i:s')];

            $data = $this->setAfterDates($data, $disabledTimes, $weekDay, $newDate);

        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $disabledTimes
     * @param DateTime $formatDisabledTime
     * @param string $newDate
     * @return array
     * @throws Exception+
     */
    private function setAfterDates(array $data, array $disabledTimes, DateTime $formatDisabledTime, string $newDate): array
    {
        if (!isset($data[$newDate]) || $data[$newDate]['closed']) {
            return $data;
        }

        $data[$newDate] = $this->mergeTimes($data[$newDate], $disabledTimes);

        $times = $data[$newDate]['times'];
        $disabledTimes = $data[$newDate]['disabled_times'];

        $startTime = new DateTime(key($disabledTimes));
        $endTime   = new DateTime(end($disabledTimes));

        $data[$newDate]['times'] = $this->removeBookedTimes($times, $startTime, $endTime);

        return $data;
    }

    /**
     * @param array $times
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @return array
     * @throws Exception
     */
    private function removeBookedTimes(array $times, DateTime $startTime, DateTime $endTime): array
    {

        foreach ($times as $key => $time) {

            $time = new DateTime($time);

            if ($time >= $startTime && $time <= $endTime) {
                unset($times[$key]);
            }

        }

        return array_values($times);
    }

    /**
     * @param DateTime $lastTime
     * @param DateTime $closedTime
     * @param int $minute
     * @param bool $skip
     * @return array
     */
    private function collectTimes(DateTime $lastTime, DateTime $closedTime, int $minute, bool $skip = true): array
    {
        if ($minute <= 0) {
            throw new \DomainException('Appointment duration must be positive.');
        }
        $times = [];
        $cursor = clone $lastTime;
        while ((clone $cursor)->modify("+$minute minutes") <= $closedTime) {
            $times[$cursor->format('H:i')] = $cursor->format('H:i');
            $cursor->modify("+$minute minutes");
        }
        return $times;
    }

    /**
     * @param array $data
     * @param array|null $disabledTimes
     * @return array
     */
    public function mergeTimes(array $data, ?array $disabledTimes): array
    {
        if (empty($disabledTimes)) {
            return $data;
        }

        foreach (array_chunk($disabledTimes, 2) as $period) {
            if (count($period) !== 2) {
                throw new \DomainException('A blocked period requires both endpoints.');
            }
            $from = new DateTime(preg_match('/^\d{4}-/', $period[0]) ? $period[0] : "{$data['date']} {$period[0]}");
            $to = new DateTime(preg_match('/^\d{4}-/', $period[1]) ? $period[1] : "{$data['date']} {$period[1]}");
            if ($to <= $from) {
                throw new \DomainException('Blocked period endpoints are invalid.');
            }
            $data['_blocked'][] = [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')];
            $minutes = $data['_duration'] ?? 1;
            // Mixed service durations must not hide legitimate boundary slots.
            if (!empty($data['_working'])) {
                foreach ([$to, (clone $from)->modify("-$minutes minutes")] as $boundary) {
                    $end = (clone $boundary)->modify("+$minutes minutes");
                    if ($boundary >= new DateTime($data['_working'][0])
                        && $end <= new DateTime($data['_working'][1])) {
                        $data['times'][] = $boundary->format('H:i');
                    }
                }
            }
            $data['times'] = array_values(array_unique($data['times']));
            foreach ($data['times'] as $key => $time) {
                $candidate = new DateTime("{$data['date']} $time");
                $end = (clone $candidate)->modify("+$minutes minutes");
                foreach ($data['_blocked'] as [$busyStart, $busyEnd]) {
                    if ($candidate < new DateTime($busyEnd) && $end > new DateTime($busyStart)) {
                        $data['disabled_times'][] = $time;
                        unset($data['times'][$key]);
                        break;
                    }
                }
            }
        }
        $data['times'] = collect($data['times'])->unique()->sort()->values()->toArray();
        $data['disabled_times'] = collect($data['disabled_times'])->diff($data['times'])->unique()->sort()->values()->toArray();

        return $data;
    }

}
