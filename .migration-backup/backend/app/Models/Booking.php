<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToShopCountry;
use App\Traits\Payable;
use App\Traits\Reviewable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * App\Models\Booking
 *
 * @property int $id
 * @property int $service_master_id
 * @property int $gift_cart_id
 * @property int $user_member_ship_id
 * @property int $shop_id
 * @property int $master_id
 * @property int $user_id
 * @property int $service_id
 * @property int $category_id
 * @property int $currency_id
 * @property string|null $start_date
 * @property string|null $end_date
 * @property double $rate
 * @property double $gift_cart_price
 * @property double $price
 * @property double $total_price
 * @property double $coupon_price
 * @property double $extra_time_price
 * @property double $discount
 * @property double $parent_id
 * @property double $commission_fee
 * @property double $service_fee
 * @property double $extra_price
 * @property double $tips
 * @property string $status
 * @property string $canceled_note
 * @property string $note
 * @property array  $notes
 * @property string $type
 * @property array  $data
 * @property int    $gender
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read double|null $rate_price
 * @property-read double|null $rate_discount
 * @property-read double|null $rate_commission_fee
 * @property-read double|null $rate_service_fee
 * @property-read double|null $rate_extra_price
 * @property-read double|null $rate_total_price
 * @property-read double|null $rate_coupon_price
 * @property-read double|null $rate_extra_time_price
 * @property-read double|null $rate_gift_cart_price
 * @property-read double|null $seller_fee
 * @property-read double|null $rate_seller_fee
 * @property-read double|null $rate_tips
 * @property-read ServiceMaster $serviceMaster
 * @property-read PointHistory|null $pointHistory
 * @property-read PointHistory|null $pointHistories
 * @property-read Shop $shop
 * @property-read User $master
 * @property-read User $user
 * @property-read PaymentToPartner|null $paymentToPartner
 * @property-read UserMemberShip|null $userMemberShip
 * @property-read Currency $currency
 * @property-read self $parent
 * @property-read Collection|self[] $children
 * @property-read int|null $children_count
 * @property-read Collection|BookingActivity[] $activities
 * @property-read int|null $activities_count
 * @property-read Collection|BookingExtraTime $extra_time
 * @property-read Collection|BookingExtraTime[] $extra_times
 * @property-read int|null $extra_times_count
 * @property-read Collection|BookingExtra[] $extras
 * @property-read int|null $extras_count
 * @method static Builder|self active()
 * @method static Builder|self filter(array $filter)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereId($value)
 * @mixin Eloquent
 */
class Booking extends Model
{
    use \App\Models\Concerns\ProtectsCanonicalPayment;
    use Reviewable, Payable, BelongsToShopCountry;

    public $guarded = ['id'];

    protected $casts = [
//        'start_date' => 'datetime:Y-m-d H:i:s',
//        'end_date'   => 'datetime:Y-m-d H:i:s',
        'data'                  => 'array',
        'notes'                 => 'array',
        'collect_via_platform'  => 'bool',
    ];

    const STATUS_NEW       = 'new';
    const STATUS_CANCELED  = 'canceled';
    const STATUS_BOOKED    = 'booked';
    const STATUS_PROGRESS  = 'progress';
    const STATUS_ENDED     = 'ended';

    const STATUSES = [
        self::STATUS_NEW       => self::STATUS_NEW,
        self::STATUS_CANCELED  => self::STATUS_CANCELED,
        self::STATUS_BOOKED    => self::STATUS_BOOKED,
        self::STATUS_PROGRESS  => self::STATUS_PROGRESS,
        self::STATUS_ENDED     => self::STATUS_ENDED,
    ];

    const MALE        = 1;
    const FEMALE      = 2;
    const ALL_GENDERS = 3;

    const GENDERS = [
        self::MALE        => self::MALE,
        self::FEMALE      => self::FEMALE,
        self::ALL_GENDERS => self::ALL_GENDERS
    ];

    public function getRateCouponPriceAttribute(): ?float
    {
        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->coupon_price * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $this->coupon_price;
    }

    public function getRateExtraTimePriceAttribute(): ?float
    {
        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->extra_time_price * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $this->extra_time_price;
    }

    public function getRatePriceAttribute(): float|int|null {

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->price * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $this->price;
    }

    public function getRateDiscountAttribute(): float|int|null {

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->discount * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $this->discount;
    }

    public function getRateCommissionFeeAttribute(): float|int|null {

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->commission_fee * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $this->commission_fee;
    }

    public function getRateExtraPriceAttribute(): float|int|null {

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->extra_price * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $this->extra_price;
    }

    public function getRateServiceFeeAttribute(): float|int|null {

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->service_fee * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $this->service_fee;
    }

    public function getRateGiftCartPriceAttribute(): float|int|null {

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->gift_cart_price * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $this->gift_cart_price;
    }

    public function getRateTotalPriceAttribute(): float|int|null {

        $totalPrice = $this->total_price;

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $totalPrice * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $totalPrice;
    }

    public function getSellerFeeAttribute(): float|int|null {
        return $this->total_price - $this->service_fee - $this->commission_fee - $this->coupon_price;
    }

    public function getRateSellerFeeAttribute(): float|int|null {

        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->seller_fee * ($this->rate <= 0 ? 1 : $this->rate);
        }

        return $this->seller_fee;
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceMaster(): BelongsTo
    {
        return $this->belongsTo(ServiceMaster::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function shopLocation(): BelongsTo
    {
        return $this->belongsTo(ShopLocation::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function localClient(): BelongsTo
    {
        return $this->belongsTo(SellerBookingClient::class, 'local_client_id');
    }

    public function userMemberShip(): BelongsTo
    {
        return $this->belongsTo(UserMemberShip::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(BookingActivity::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(BookingExtra::class);
    }

    public function extraTime(): HasOne
    {
        return $this->hasOne(BookingExtraTime::class);
    }

    public function extraTimes(): HasMany
    {
        return $this->hasMany(BookingExtraTime::class);
    }

    public function pointHistory(): MorphOne
    {
        return $this->morphOne(PointHistory::class, 'model')->latest();
    }

    public function pointHistories(): MorphMany
    {
        return $this->morphMany(PointHistory::class, 'model');
    }

    public function paymentToPartner(): MorphOne
    {
        return $this->morphOne(PaymentToPartner::class, 'model');
    }

    public function scopeFilter($query, array $filter): void
    {
        $query
            ->when(data_get($filter, 'shop_id'),  fn ($query, $shopId)  => $query->where('shop_id', $shopId))
            ->when(data_get($filter, 'shop_ids'), fn ($query, $shopIds) => $query->whereIn('shop_id', $shopIds))
            // Server-computed only (see User::bookingBranchScope() /
            // Seller\BookingController) — never accepted as raw client
            // input, so a branch-scoped viewer can't spoof another branch.
            ->when(data_get($filter, 'branch_scope_active'), function ($q) use ($filter) {
                $locationIds = data_get($filter, 'branch_scope_location_ids', []);

                if (empty($locationIds)) {
                    // Fail-closed: the viewer has no branch assigned and no
                    // all-branches permission, so they see nothing rather
                    // than everything.
                    $q->whereRaw('1 = 0');
                    return;
                }

                $q->whereHas('master.invitations', fn ($q2) => $q2
                    ->where('shop_id', data_get($filter, 'shop_id'))
                    ->where('status', Invitation::ACCEPTED)
                    ->whereHas('shopLocations', fn ($q3) => $q3->whereIn('shop_locations.id', $locationIds))
                );
            })
            ->when(data_get($filter, 'search'), fn($q, $search) => $q->where(function ($query) use ($search) {
                $query
                    ->whereHas('service.translation',  fn($q) => $q->where(fn($q) => $q->where('title', 'like', "%$search%")))
                    ->orWhereHas('master', fn($q) => $q->where(fn($q) => $q->where('firstname', 'like', "%$search%")->orWhere('lastname', 'like', "%$search%")))
                    ->orWhereHas('user',   fn($q) => $q->where(fn($q) => $q->where('firstname', 'like', "%$search%")->orWhere('lastname', 'like', "%$search%")))
                    ->orWhere('note', 'like', "%$search%")->orWhere('canceled_note', 'like', "%$search%");
            }))
            ->when(isset($filter['parent']), fn($q) => $filter['parent'] ? $q->whereNull('parent_id') : $q->whereNotNull('parent_id'))
            ->when(isset($filter['parent_id']), fn($q) => $q->where('parent_id', $filter['parent_id']))
            ->when(data_get($filter, 'service_extra_id'), function ($q, $id) {
                $q->whereHas('extras', fn($q) => $q->where('service_extra_id', $id));
            })
            ->when(data_get($filter, 'service_extra_ids'), function ($q, $ids) {
                $q->whereHas('extras', fn($q) => $q->whereIn('service_extra_id', (array)$ids));
            })
            ->when(data_get($filter, 'service_master_id'),   fn($q, $id)         => $q->where('service_master_id', $id))
            ->when(data_get($filter, 'user_member_ship_id'), fn($q, $id)         => $q->where('user_member_ship_id', $id))
            ->when(data_get($filter, 'master_id'),           fn($q, $id)         => $q->where('master_id', $id))
            ->when(data_get($filter, 'user_id'),             fn($q, $id)         => $q->where('user_id', $id))
            ->when(data_get($filter, 'service_id'),          fn($q, $id)         => $q->where('service_id', $id))
            ->when(data_get($filter, 'category_id'),         fn($q, $id)         => $q->where('category_id', $id))
            ->when(data_get($filter, 'start_date'),          fn($q, $time)       => $q->where('start_date', '>=', $time))
            ->when(data_get($filter, 'end_date'),            fn($q, $time)       => $q->where('end_date', '<=', $time))
            ->when(data_get($filter, 'status'),              fn($q, $status)     => $q->where('status',  $status))
            ->when(data_get($filter, 'statuses'),            fn($q, $statuses)   => $q->whereIn('status',  $statuses))
            // Used only by the customer's own "My Appointments" list
            // (Dashboard/User/BookingController::index()) as its default,
            // never sent as raw client input. A 'new' booking with a
            // Transaction row completed checkout and is simply waiting on
            // the seller to confirm it - that includes every cash booking,
            // since BookingService::create() writes a Transaction the
            // moment a payment_id is supplied, whatever its tag. Only a
            // 'new' booking with no Transaction at all - the create request
            // never carried a payment_id - is a genuinely abandoned
            // checkout, so that's the only case hidden here.
            ->when(data_get($filter, 'hide_abandoned_new'), function ($q) {
                $q->where(fn($q2) => $q2
                    ->where('status', '!=', Booking::STATUS_NEW)
                    ->orWhereHas('transaction'));
            })
            ->when(data_get($filter, 'price_from'),          fn($q, $price)      => $q->where('price', '>=', $price))
            ->when(data_get($filter, 'price_to'),            fn($q, $price)      => $q->where('price', '<=', $price))
            ->when(data_get($filter, 'discount_from'),       fn($q, $discount)   => $q->where('discount', '>=', $discount))
            ->when(data_get($filter, 'discount_to'),         fn($q, $discount)   => $q->where('discount', '<=', $discount))
            ->when(data_get($filter, 'commission_fee_from'), fn($q, $commission) => $q->where('commission_fee', '>=', $commission))
            ->when(data_get($filter, 'commission_fee_to'),   fn($q, $commission) => $q->where('commission_fee', '<=', $commission))
            ->when(data_get($filter, 'service_fee_from'),    fn($q, $service)    => $q->where('service_fee', '>=', $service))
            ->when(data_get($filter, 'service_fee_to'),      fn($q, $service)    => $q->where('service_fee', '<=', $service))
            ->when(data_get($filter, 'type'),                fn($q, $type)       => $q->where('type', $type))
            ->when(data_get($filter, 'gender'),              fn($q, $gender)     => $q->where('gender', $gender));
    }
}
