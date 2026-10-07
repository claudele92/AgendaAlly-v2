<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\WalletHistory;
use App\Services\WalletHistoryService\WalletHistoryService;
use DB;
use App\Models\User;
use App\Models\Order;
use App\Models\Booking;
use App\Models\Invitation;
use App\Models\NotificationUser;
use App\Models\PushNotification;
use App\Models\ParcelOrderSetting;
use App\Models\Auction;
use App\Models\Settings;
use App\Traits\SetCurrency;
use App\Http\Resources\AuctionResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Log;
use Throwable;

class Utility
{
    use \App\Traits\Notification, SetCurrency;

    const MONDAY    = 'monday';
    const TUESDAY   = 'tuesday';
    const WEDNESDAY = 'wednesday';
    const THURSDAY  = 'thursday';
    const FRIDAY    = 'friday';
    const SATURDAY  = 'saturday';
    const SUNDAY    = 'sunday';

    const DAYS = [
        self::MONDAY    => self::MONDAY,
        self::TUESDAY   => self::TUESDAY,
        self::WEDNESDAY => self::WEDNESDAY,
        self::THURSDAY  => self::THURSDAY,
        self::FRIDAY    => self::FRIDAY,
        self::SATURDAY  => self::SATURDAY,
        self::SUNDAY    => self::SUNDAY,
    ];

    /* Pagination for array */
    public static function paginate($items, $perPage, $page = null, $options = []): LengthAwarePaginator
    {
        return new LengthAwarePaginator($items?->forPage($page, $perPage), $items?->count() ?? 0, $perPage, $page, $options);
    }

    const SERVICE_FEE_TYPE_FIXED      = 'fixed';
    const SERVICE_FEE_TYPE_PERCENTAGE = 'percentage';

    const SERVICE_FEE_TYPES = [
        self::SERVICE_FEE_TYPE_FIXED      => self::SERVICE_FEE_TYPE_FIXED,
        self::SERVICE_FEE_TYPE_PERCENTAGE => self::SERVICE_FEE_TYPE_PERCENTAGE,
    ];

    /**
     * Resolves the platform service fee for either domain (bookings:
     * $settingKey = 'booking_service_fee', product orders: 'service_fee')
     * against the shared 'service_fee_type' setting - a single
     * fixed/percentage mode that governs both, while each keeps its own
     * numeric rate. Defaults to 'fixed', preserving pre-existing behavior
     * byte-for-byte for anyone who never touches the new setting.
     *
     * $baseAmount is the pre-fee subtotal a percentage rate applies
     * against - the same "subtotal / 100 * rate" shape already used for
     * OrderService::calculateOrder()'s existing shop-commission/tax
     * percentages, applied here for consistency rather than inventing a
     * different convention.
     */
    public static function resolveServiceFee(string $settingKey, float $baseAmount, float $rate = 1): float
    {
        $type       = Settings::where('key', 'service_fee_type')->first()?->value ?: self::SERVICE_FEE_TYPE_FIXED;
        $settingFee = (double) Settings::where('key', $settingKey)->first()?->value ?: 0;

        if ($type === self::SERVICE_FEE_TYPE_PERCENTAGE) {
            return max($baseAmount / 100 * $settingFee, 0);
        }

        return max($settingFee, 0) * $rate;
    }

    /**
     * Whether service_fee_type is currently 'percentage' - used where a
     * caller needs to branch on the mode itself rather than just get a
     * resolved amount (e.g. OrderService::calculateOrder() skipping its
     * flat-fee-only "split one fee across N orders" step, since a
     * percentage fee is already correctly scoped per order).
     */
    public static function isPercentageServiceFee(): bool
    {
        return Settings::where('key', 'service_fee_type')->first()?->value === self::SERVICE_FEE_TYPE_PERCENTAGE;
    }

    /**
     * Geocodes a free-text address into coordinates via Google's Geocoding
     * API, so the server can be authoritative about a shop's lat/long
     * instead of trusting whatever (possibly stale) map-pin state the
     * admin/seller frontend happened to submit alongside an address edit.
     * Uses a separate IP/API-restricted server credential. A browser key must
     * never be reused by the backend; native/client SDK initialization differs.
     *
     * Returns null on any failure (missing key, no results, network
     * error) so callers fall back to their own prior behavior rather than
     * overwriting good coordinates with a failed lookup.
     */
    public static function geocodeAddress(string $address): ?array
    {
        $address = trim($address);

        if ($address === '' || !EnvironmentPolicy::mapsEnabled() || !MapsConfiguration::enabled()) {
            return null;
        }

        $key = Settings::where('key', MapsConfiguration::SERVER_KEY)->first()?->value;

        if (empty($key)) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key'     => $key,
            ]);

            $location = $response->json('results.0.geometry.location');

            if (!$response->successful() || $response->json('status') !== 'OK' || empty($location)) {
                return null;
            }

            return [
                'latitude'  => (float) data_get($location, 'lat'),
                'longitude' => (float) data_get($location, 'lng'),
            ];
        } catch (Throwable $e) {
            Log::warning('Server Maps lookup failed; existing coordinates were retained.');
            return null;
        }
    }

    /**
     * @param ParcelOrderSetting $type
     * @param float|null $km
     * @param float|null $rate
     * @return float|null
     */
    public function getParcelPriceByDistance(ParcelOrderSetting $type, ?float $km, ?float $rate): ?float
    {
        $price      = $type->special ? $type->special_price : $type->price;
        $pricePerKm = $type->special ? $type->special_price_per_km : $type->price_per_km;

        return round(($price + ($pricePerKm * $km)) * $rate, 2);
    }

    /**
     * @param array $origin, Адрес селлера (откуда)
     * @param array $destination, Адрес клиента (куда)
     * @return float|int|null
     */
    public function getDistance(array $origin, array $destination): float|int|null
    {

        if (
            !data_get($origin, 'latitude') && !data_get($origin, 'longitude') &&
            !data_get($destination, 'latitude') && !data_get($destination, 'longitude')
        ) {
            return 0;
        }

        $originLat          = $this->toRadian(data_get($origin, 'latitude'));
        $originLong         = $this->toRadian(data_get($origin, 'longitude'));
        $destinationLat     = $this->toRadian(data_get($destination, 'latitude'));
        $destinationLong    = $this->toRadian(data_get($destination, 'longitude'));

        $deltaLat           = $destinationLat - $originLat;
        $deltaLon           = $originLong - $destinationLong;

        $delta              = pow(sin($deltaLat / 2), 2);
        $cos                = cos($destinationLong) * cos($destinationLat);

        $sqrt               = ($delta + $cos * pow(sin($deltaLon / 2), 2));
        $asin               = 2 * asin(sqrt($sqrt));

        $earthRadius        = 6371;

        return (string)$asin != 'NAN' ? round($asin * $earthRadius, 2) : 1;
    }

    /**
     * @param mixed $degree
     * @return float|null
     */
    private function toRadian(mixed $degree = 0): ?float
    {
        return $degree * pi() / 180;
    }

    /**
     * @param $reviews
     * @return float[]
     */
    public static function groupRating($reviews): array
    {
        $result = [
            1 => 0.0,
            2 => 0.0,
            3 => 0.0,
            4 => 0.0,
            5 => 0.0,
        ];

        foreach ($reviews as $review) {

            $rating = (int)data_get($review, 'rating');

            if (data_get($result, $rating)) {
                $result[$rating] += data_get($review, 'count');
                continue;
            }

            $result[$rating] = data_get($review, 'count');
        }

        return $result;
    }

    /**
     * @param array $where
     * @return array
     */
    public static function reviewsGroupRating(array $where): array
    {
        $reviews = DB::table('reviews')
            ->where($where)
            ->select([
                DB::raw('count(id) as count, sum(rating) as rating, rating')
            ])
            ->groupBy(['rating'])
            ->get();

        return [
            'group' => Utility::groupRating($reviews),
            'count' => $reviews->sum('count'),
            'avg'   => round((double)$reviews->avg('rating'), 1),
        ];
    }

    /**
     * @param int $userId
     * @param mixed $roles
     * @param int $shopId
     * @return bool
     */
    public static function checkUserInvitationByRole(int $userId, mixed $roles, int $shopId): bool
    {
        return User::where('id', $userId)
            ->whereHas('roles',  fn($q) => $q->whereIn('name', (array)$roles))
            ->whereHas('invite', function ($q) use ($shopId) {
                $q->select(['user_id', 'status'])->where('shop_id', $shopId)->where('status', Invitation::ACCEPTED);
            })
            ->exists();
    }

    public static function topUpCashBack(
        string|int|float|null $point,
        Order|Booking $model,
        ?string $language = 'en',
        bool $requireSuccess = false
    ): void
    {
        if (empty($point)) {
            return;
        }

        $token = $model->user?->firebase_token;
        $token = is_array($token) ? $token : [$token];

        /** @var NotificationUser $notification */
        $notification = $model->user
            ?->notifications
            ?->where('type', \App\Models\Notification::PUSH)
            ?->first();

        $title = __(
            'errors.' . ResponseError::ADD_BOOKING_CASHBACK,
            locale: $model->user?->lang ?? $language
        );

        try {
            $data = [
                'type' => 'topup',
                'price' => (double)$point,
                'note' => $title,
                'status' => WalletHistory::PAID,
                'user' => $model->user,
            ];
            $service = new WalletHistoryService;
            $result = $requireSuccess ? $service->createForFulfillment($data) : $service->create($data);
            if ($requireSuccess && ($result['status'] ?? false) !== true) {
                throw new \RuntimeException('Required Product cashback credit failed.');
            }

            if ($notification?->notification?->active) {

                $notify = fn () => (new self)->sendNotification(
                    $model,
                    $token,
                    $title,
                    $title,
                    [
                        'id' => $model->id,
                        'status' => $model->status,
                        'type' => PushNotification::ADD_BOOKING_CASHBACK
                    ],
                    [$model->user_id]
                );
                if ($requireSuccess) {
                    \Illuminate\Support\Facades\DB::afterCommit($notify);
                } else {
                    $notify();
                }
            }

            $history = $model->pointHistories()->create([
                'user_id' => $model->user_id,
                'price'   => $point,
                'note'    => 'cashback',
            ]);
            if ($requireSuccess && !$history->exists) {
                throw new \RuntimeException('Required Product PointHistory was not saved.');
            }
        } catch (Throwable $e) {
            if ($requireSuccess) throw $e;
            Log::error($e->getMessage(), [$e->getCode(), $e->getLine(), $e->getFile()]);
        }

    }

    public static function auctionExpiredAt(Auction|AuctionResource|null $model = null, ?string $startAt = null): string
    {
        $auctionTime = Settings::where('key', 'auction_time')->first() ?? 90; // в минутах сколько продлиться аукцион
        $auctionAfterBidTime = Settings::where('key', 'auction_after_bid_time')->first() ?? 30; // доп время после ставки если expired_at меньше 30 секунд
        $minutes = $auctionTime + $auctionAfterBidTime;

        return date('Y-m-d H:i:s', strtotime(($model?->start_at ?? $startAt) . " +$minutes minutes"));
    }

    public static function auctionMinBid(Auction|AuctionResource $model): string
    {
        $minBid = DB::table('auction_users')->where('auction_id', $model->id)->max('price');

        if ($minBid === 0) {
            $minBid = $model->min_price;
        }

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            $minBid = $minBid * (new self)->currency();
        }

        return $minBid;
    }
}
