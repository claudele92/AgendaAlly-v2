<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPickupPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

/** Product fulfillment only: no booking, Specialist or collection-point capacity. */
final class ProductPickupService
{
    public const ASAP = 'as_soon_as_ready';
    public const SCHEDULED = 'scheduled';

    public function location(int $shopId, int $locationId, bool $lock = false): ShopLocation
    {
        return ShopLocation::query()->where('shop_id', $shopId)->where('type', ShopLocation::PRODUCT)
            ->when($lock, fn ($q) => $q->lockForUpdate())->findOrFail($locationId);
    }

    public function validatePolicy(array $data): array
    {
        $data = Validator::make($data, [
            'timing_mode' => 'required|in:as_soon_as_ready,scheduled',
            'timezone' => 'nullable|required_if:timing_mode,scheduled|timezone',
            'weekly_hours' => 'nullable|required_if:timing_mode,scheduled|array|max:7',
            'weekly_hours.*.day' => 'required|integer|between:0,6|distinct',
            'weekly_hours.*.enabled' => 'required|boolean',
            'weekly_hours.*.start' => 'required|date_format:H:i',
            'weekly_hours.*.end' => 'required|date_format:H:i',
            'preparation_minutes' => 'required|integer|between:0,10080',
            'window_minutes' => 'required|integer|in:30,60,90,120,180,240',
            'same_day' => 'required|boolean',
            'cutoff' => 'nullable|date_format:H:i',
            'blackout_dates' => 'nullable|array|max:366',
            'blackout_dates.*' => 'required|date_format:Y-m-d|distinct',
        ])->validate();
        foreach ($data['weekly_hours'] ?? [] as $day) {
            if ($day['enabled'] && $day['start'] >= $day['end']) {
                throw ValidationException::withMessages(['weekly_hours' => 'Pickup opening time must precede closing time. Overnight pickup windows are not supported.']);
            }
        }
        if ($data['timing_mode'] === self::SCHEDULED &&
            !collect($data['weekly_hours'])->contains(fn ($day) => (bool) $day['enabled'])) {
            throw ValidationException::withMessages(['weekly_hours' => 'Enable at least one pickup day.']);
        }
        $data['weekly_hours'] = array_map(fn ($day) => [
            'day' => (int) $day['day'], 'enabled' => (bool) $day['enabled'],
            'start' => $day['start'], 'end' => $day['end'],
        ], $data['weekly_hours'] ?? []);
        $data['preparation_minutes'] = (int) $data['preparation_minutes'];
        $data['window_minutes'] = (int) $data['window_minutes'];
        $data['same_day'] = (bool) $data['same_day'];
        $data['cutoff'] = $data['cutoff'] ?? null;
        $data['blackout_dates'] = $data['blackout_dates'] ?? [];
        return $data;
    }

    public function defaultPolicy(): array
    {
        return ['timing_mode' => self::ASAP, 'timezone' => null, 'weekly_hours' => [],
            'preparation_minutes' => 0, 'window_minutes' => 120, 'same_day' => true,
            'cutoff' => null, 'blackout_dates' => []];
    }

    public function pickupAddress(Shop $shop, ShopLocation $location, int $productLocations): ?string
    {
        if (is_string($location->address) && trim($location->address) !== '') return $location->address;
        // A single native Product location may reuse its Shop's public postal
        // address. Never copy one Shop address onto several different branches.
        return $productLocations === 1 ? $shop->translation?->address : null;
    }

    public function policy(int $shopId, int $locationId): array
    {
        $row = ShopPickupPolicy::where('shop_id', $shopId)->where('shop_location_id', $locationId)->first();
        return $row ? array_intersect_key($row->toArray(), $this->defaultPolicy()) : $this->defaultPolicy();
    }

    public function windows(array $policy, string $date, ?CarbonImmutable $clock = null): array
    {
        if ($policy['timing_mode'] !== self::SCHEDULED) return [];
        $tz = $policy['timezone'];
        $now = ($clock ?? CarbonImmutable::now())->setTimezone($tz);
        try {
            $day = CarbonImmutable::createFromFormat('!Y-m-d', $date, $tz);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['date' => 'Choose a valid pickup date.']);
        }
        if ($day->format('Y-m-d') !== $date || $day->lt($now->startOfDay()) || $day->gt($now->startOfDay()->addDays(30))) return [];
        if (in_array($date, $policy['blackout_dates'] ?? [], true)) return [];
        $today = $day->isSameDay($now);
        if ($today && (!$policy['same_day'] || (!empty($policy['cutoff']) && $now->format('H:i') >= $policy['cutoff']))) return [];
        $hours = collect($policy['weekly_hours'] ?? [])->first(fn ($h) => $h['day'] === $day->dayOfWeek && $h['enabled']);
        if (!$hours) return [];
        [$startH, $startM] = array_map('intval', explode(':', $hours['start']));
        [$endH, $endM] = array_map('intval', explode(':', $hours['end']));
        $end = $day->setTime($endH, $endM);
        $lead = $now->addMinutes($policy['preparation_minutes']);
        $result = [];
        for ($start = $day->setTime($startH, $startM); $start->addMinutes($policy['window_minutes'])->lte($end);
             $start = $start->addMinutes($policy['window_minutes'])) {
            if ($start->lt($lead)) continue;
            $result[] = [
                'window_start' => $start->utc()->toIso8601String(),
                'window_end' => $start->addMinutes($policy['window_minutes'])->utc()->toIso8601String(),
                'local_start' => $start->format('H:i'),
                'local_end' => $start->addMinutes($policy['window_minutes'])->format('H:i'),
            ];
        }
        return $result;
    }

    public function availability(Shop $shop, ?int $locationId, ?string $date): array
    {
        $eligible = in_array(Order::PICKUP, $shop->productFulfillmentMethods(), true);
        $locations = ShopLocation::where('shop_id', $shop->id)->where('type', ShopLocation::PRODUCT)->get();
        if ($locationId) $this->location((int) $shop->id, $locationId);
        $selected = $locationId ?: ($locations->count() === 1 ? (int) $locations->first()->id : null);
        $policy = $selected ? $this->policy((int) $shop->id, $selected) : $this->defaultPolicy();
        $today = $policy['timezone'] ? CarbonImmutable::now($policy['timezone'])->format('Y-m-d') : null;
        return [
            'eligible' => $eligible, 'shop_id' => (int) $shop->id,
            'shop_location_id' => $selected, 'timing_mode' => $policy['timing_mode'],
            'timezone' => $policy['timezone'], 'today' => $today,
            'preparation_minutes' => $policy['preparation_minutes'],
            'locations' => $locations->map(fn ($l) => ['id' => $l->id,
                'address' => $this->pickupAddress($shop, $l, $locations->count()),
                'alias' => $l->alias ?: $l->city?->translation?->title,
                'timing_mode' => $this->policy((int) $shop->id, (int) $l->id)['timing_mode']])->values()->all(),
            'date' => $date,
            'windows' => $eligible && $selected && $date ? $this->windows($policy, $date) : [],
        ];
    }

    /** Called inside the native order transaction, independently for each Shop. */
    public function orderFields(Shop $shop, array $data): array
    {
        if (($data['delivery_type'] ?? null) !== Order::PICKUP) return ['pickup' => null];
        if (!in_array(Order::PICKUP, $shop->productFulfillmentMethods(), true)) {
            throw ValidationException::withMessages(['delivery_type' => 'Shop pickup is disabled.']);
        }
        $selection = data_get($data, 'pickup_selections.' . $shop->id, []);
        $locations = ShopLocation::where('shop_id', $shop->id)->where('type', ShopLocation::PRODUCT)->get();
        $locationId = $selection['shop_location_id'] ?? ($locations->count() === 1 ? $locations->first()->id : null);
        $location = $locationId ? $this->location((int) $shop->id, (int) $locationId, true) : null;
        if (!$location && ShopPickupPolicy::where('shop_id', $shop->id)->where('timing_mode', self::SCHEDULED)->exists()) {
            throw ValidationException::withMessages(['pickup_selections' => 'Choose the pickup branch.']);
        }
        $policy = $location ? $this->policy((int) $shop->id, (int) $locationId) : $this->defaultPolicy();
        $snapshot = ['timing_mode' => $policy['timing_mode'], 'shop_id' => (int) $shop->id,
            'shop_location_id' => $location?->id, 'address' => $location
                ? $this->pickupAddress($shop, $location, $locations->count()) : $shop->translation?->address,
            'alias' => $location?->alias, 'timezone' => $policy['timezone'],
            'window_start' => null, 'window_end' => null];
        if ($policy['timing_mode'] === self::SCHEDULED) {
            $windows = $this->windows($policy, (string) ($selection['date'] ?? ''));
            $window = collect($windows)->first(fn ($w) => $w['window_start'] === ($selection['window_start'] ?? null)
                && $w['window_end'] === ($selection['window_end'] ?? null));
            if (!$window) throw ValidationException::withMessages(['pickup_selections' => 'The pickup window is no longer available. Choose another window.']);
            $snapshot['window_start'] = $window['window_start'];
            $snapshot['window_end'] = $window['window_end'];
        } elseif (!empty($selection['window_start']) || !empty($selection['window_end'])) {
            throw ValidationException::withMessages(['pickup_selections' => 'Ready-based pickup does not reserve a time window.']);
        }
        return ['pickup' => $snapshot, 'delivery_date' => null, 'delivery_fee' => 0,
            'address_id' => null, 'address' => null, 'location' => null,
            'delivery_price_id' => null, 'delivery_point_id' => null, 'deliveryman_id' => null];
    }
}