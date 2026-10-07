<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\RequestToModel;
use App\Traits\UserSearch;
use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $uuid
 * @property string $firstname
 * @property string|null $lastname
 * @property string|null $email
 * @property string|null $phone
 * @property Carbon|null $birthday
 * @property string $gender
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $phone_verified_at
 * @property string|null $ip_address
 * @property boolean $active
 * @property string|null $img
 * @property array|null $firebase_token
 * @property string|null $full_name
 * @property string|null $password
 * @property string|null $remember_token
 * @property string|null $name_or_email
 * @property string|null $verify_token
 * @property string|null $referral
 * @property string|null $my_referral
 * @property int|null $r_count
 * @property int|null $r_avg
 * @property int|null $r_sum
 * @property int|null $o_count
 * @property int|null $o_sum
 * @property int|null $b_count
 * @property int|null $b_sum
 * @property int|null $currency_id
 * @property string|null $lang
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property mixed $role
 * @property-read UserTranslation|null $translation
 * @property-read Collection|UserTranslation[] $translations
 * @property-read int|null $translations_count
 * @property-read Collection|UserTranslation[] $memberShips
 * @property-read int|null $member_ships_count
 * @property-read Collection|Booking[] $bookings
 * @property-read Collection|Booking[] $masterBookings
 * @property-read Collection|UserWorkingDay[] $workingDays
 * @property-read UserWorkingDay|null $workingDay
 * @property-read Invitation|null $invite
 * @property-read Collection|Invitation[] $invitations
 * @property-read int|null $invitations_count
 * @property-read Invitation|null $serviceMaster
 * @property-read Collection|ServiceMaster[] $serviceMasters
 * @property-read int|null $service_masters_count
 * @property-read int|null $service_masters_min_price
 * @property-read Collection|Banner[] $likes
 * @property-read int|null $likes_count
 * @property-read Shop|null $moderatorShop
 * @property-read Collection|Review[] $reviews
 * @property-read int|null $reviews_count
 * @property-read Collection|Review[] $assignReviews
 * @property-read int|null $assign_reviews_count
 * @property-read Collection|Notification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|OrderDetail[] $orderDetails
 * @property-read int|null $order_details_count
 * @property-read int|null $orders_sum_total_price
 * @property-read Collection|Order[] $orders
 * @property-read int|null $orders_count
 * @property-read Collection|Order[] $deliveryManOrders
 * @property-read int|null $delivery_man_orders_count
 * @property-read int|null $delivery_man_orders_sum_total_price
 * @property-read int|null $reviews_avg_rating
 * @property-read int|null $assign_reviews_avg_rating
 * @property-read Collection|Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection|Permission[] $transactions
 * @property-read int|null $transactions_count
 * @property-read UserAddress|null $address
 * @property-read Collection|UserAddress[] $addresses
 * @property-read int|null $addresses_count
 * @property-read UserPoint|null $point
 * @property-read Collection|PointHistory[] $pointHistory
 * @property-read int|null $point_history_count
 * @property-read Collection|Role[] $roles
 * @property-read int|null $roles_count
 * @property-read Shop|null $shop
 * @property-read DeliveryManSetting|null $deliveryManSetting
 * @property-read EmailSubscription|null $emailSubscription
 * @property-read Collection|SocialProvider[] $socialProviders
 * @property-read int|null $social_providers_count
 * @property-read Collection|PersonalAccessToken[] $tokens
 * @property-read Collection|PaymentProcess[] $paymentProcess
 * @property-read Collection|MasterClosedDate[] $closedDates
 * @property-read Collection|MasterDisabledTime[] $disabledTimes
 * @property-read int $payment_process_count
 * @property-read int|null $tokens_count
 * @property-read Wallet|HasOne|null $wallet
 * @property-read int|null $referral_from_topup_price
 * @property-read int|null $referral_from_withdraw_price
 * @property-read int|null $referral_to_withdraw_price
 * @property-read int|null $referral_to_topup_price
 * @property-read int|null $referral_from_topup_count
 * @property-read int|null $referral_from_withdraw_count
 * @property-read int|null $referral_to_withdraw_count
 * @property-read int|null $referral_to_topup_count
 * @method static UserFactory factory(...$parameters)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self permission($permissions)
 * @method static Builder|self query()
 * @method static Builder|self filter($filter)
 * @method static Builder|self role($roles, $guard = null)
 * @method static Builder|self whereActive($value)
 * @method static Builder|self whereBirthday($value)
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereEmail($value)
 * @method static Builder|self whereEmailVerifiedAt($value)
 * @method static Builder|self whereFirebaseToken($value)
 * @method static Builder|self whereFirstname($value)
 * @method static Builder|self whereGender($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereImg($value)
 * @method static Builder|self whereIpAddress($value)
 * @method static Builder|self whereLastname($value)
 * @method static Builder|self wherePassword($value)
 * @method static Builder|self wherePhone($value)
 * @method static Builder|self wherePhoneVerifiedAt($value)
 * @method static Builder|self whereRememberToken($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereUuid($value)
 * @mixin Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,
        HasFactory,
        HasRoles,
        Loadable,
        RequestToModel,
        UserSearch;

    const DATES = [
        'subMonth'  => 'subMonth',
        'subWeek'   => 'subWeek',
        'subYear'   => 'subYear',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verify_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
        'phone_verified_at' => 'datetime:Y-m-d H:i:s',
        'birthday'          => 'datetime:Y-m-d',
        'firebase_token'    => 'array',
        'active'            => 'bool',
    ];

    public function isOnline(): ?bool
    {
        return Cache::has("user-online-$this->id");
    }

    public function getRoleAttribute(): string
    {
        return $this->role = $this->roles?->last()?->name ?? 'no role';
    }

    public function getNameOrEmailAttribute(): ?string
    {
        return $this->firstname ?? $this->email;
    }

    public function getFullNameAttribute(): ?string
    {
        return "$this->firstname $this->lastname";
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function emailSubscription(): HasOne
    {
        return $this->hasOne(EmailSubscription::class);
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, NotificationUser::class)
            ->as('notification')
            ->withPivot('active');
    }

    public function moderatorShop(): HasOneThrough
    {
        return $this->hasOneThrough(Shop::class, Invitation::class,
            'user_id', 'id', 'id', 'shop_id');
    }

    /**
     * Whether this user can perform $permissionKey (e.g. 'bookings.view')
     * for the given shop. The shop's owner always has every permission,
     * unconditionally — they are never represented as a shop_role
     * assignment, so there is no row to misconfigure or delete that could
     * lock them out. Everyone else needs an accepted invitation to that
     * shop with a shop_role that grants the permission.
     */
    public function hasShopPermission(int $shopId, string $permissionKey): bool
    {
        if ($this->shop?->id === $shopId) {
            return true;
        }

        $invitation = $this->invitations()
            ->where('shop_id', $shopId)
            ->where('status', Invitation::ACCEPTED)
            ->whereNotNull('shop_role_id')
            ->with('shopRole.permissions')
            ->first();

        return (bool) $invitation?->shopRole?->hasPermission($permissionKey);
    }

    /**
     * Whether this user's view of $shopId's bookings is restricted to a
     * set of branches, and if so, which ones — see Seller\BookingController.
     *
     * Unrestricted (['unrestricted' => true]) for the shop owner (the same
     * structural exception hasShopPermission() uses) or anyone whose
     * accepted invitation's shopRole grants bookings.view_all_branches.
     *
     * Otherwise restricted to this user's own assigned branches (an
     * invitation can hold more than one — e.g. both the PRODUCT and
     * SERVICE ShopLocation for the same city, see
     * invitation_shop_locations), which can itself be empty (no branch
     * assigned yet). That's intentional, not a bug: fail-closed, not
     * fail-open. A viewer with no branch and no all-branches permission
     * sees no bookings at all, rather than silently seeing every branch's.
     *
     * @return array{unrestricted: bool, location_ids: int[]}
     */
    public function bookingBranchScope(int $shopId): array
    {
        if ($this->shop?->id === $shopId) {
            return ['unrestricted' => true, 'location_ids' => []];
        }

        $invitation = $this->invitations()
            ->where('shop_id', $shopId)
            ->where('status', Invitation::ACCEPTED)
            ->whereNotNull('shop_role_id')
            ->with(['shopRole.permissions', 'shopLocations'])
            ->first();

        if ($invitation?->shopRole?->hasPermission('bookings.view_all_branches')) {
            return ['unrestricted' => true, 'location_ids' => []];
        }

        return ['unrestricted' => false, 'location_ids' => $invitation?->shopLocations->pluck('id')->all() ?? []];
    }

    /**
     * Branch locations this user may read in the seller dashboard.
     *
     * Unlike bookingBranchScope(), this does not let the bookings.view_all_branches
     * permission widen location reads; branch assignment is the only staff
     * scope for location data. The shop owner remains unrestricted.
     *
     * @return array{unrestricted: bool, location_ids: int[]}
     */
    public function shopLocationBranchScope(int $shopId): array
    {
        if ($this->shop?->id === $shopId) {
            return ['unrestricted' => true, 'location_ids' => []];
        }

        $invitation = $this->invitations()
            ->where('shop_id', $shopId)
            ->where('status', Invitation::ACCEPTED)
            ->whereNotNull('shop_role_id')
            ->first();

        $locationIds = $invitation?->shopLocations()
            ->where('shop_id', $shopId)
            ->pluck('shop_locations.id')
            ->map(static fn ($id): int => (int) $id)
            ->all() ?? [];

        return ['unrestricted' => false, 'location_ids' => $locationIds];
    }

    public function scopeAvailableAtShopLocations(Builder $query, int $shopId, array $locationIds): Builder
    {
        if ($locationIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('invitations', fn (Builder $invitation) => $invitation
            ->where('shop_id', $shopId)
            ->where('status', Invitation::ACCEPTED)
            ->where(fn (Builder $assignment) => $assignment
                ->whereHas('shopLocations', fn (Builder $locations) => $locations
                    ->where('shop_locations.shop_id', $shopId)
                    ->whereIn('shop_locations.id', $locationIds))
                ->orWhereDoesntHave('shopLocations', fn (Builder $locations) => $locations->withoutGlobalScopes())));
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function assignReviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'assignable');
    }

    public function invite(): HasOne
    {
        return $this->hasOne(Invitation::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function countryAdmin(): HasOne
    {
        return $this->hasOne(CountryAdmin::class);
    }

    public function countryInvitations(): HasMany
    {
        return $this->hasMany(CountryInvitation::class);
    }

    /**
     * An 'admin'/'manager' role holder with no CountryAdmin row and no
     * accepted country_role invitation is an unrestricted global
     * superadmin — see the country_admins migration. Staff invited into a
     * country (an accepted CountryInvitation with a country_role_id, but
     * no CountryAdmin row of their own — see CountryContext) are also
     * restricted, even though they typically carry 'manager' too just to
     * pass the coarse role:admin|manager route gate; missing this check
     * let them bypass every country-permission check as an accidental
     * "superadmin".
     */
    public function isSuperAdmin(): bool
    {
        if (!$this->hasRole(['admin', 'manager']) || $this->countryAdmin) {
            return false;
        }

        return !$this->countryInvitations()
            ->where('status', CountryInvitation::ACCEPTED)
            ->whereNotNull('country_role_id')
            ->exists();
    }

    /**
     * Whether this user can perform $permissionKey for the given country.
     * A global superadmin always passes, unconditionally, before any
     * country_role lookup — mirrors User::hasShopPermission().
     */
    public function hasCountryPermission(int $countryId, string $permissionKey): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->countryAdmin?->country_id === $countryId) {
            return true;
        }

        $invitation = $this->countryInvitations()
            ->where('country_id', $countryId)
            ->where('status', CountryInvitation::ACCEPTED)
            ->whereNotNull('country_role_id')
            ->with('countryRole.permissions')
            ->first();

        return (bool) $invitation?->countryRole?->hasPermission($permissionKey);
    }

    public function socialProviders(): HasMany
    {
        return $this->hasMany(SocialProvider::class,'user_id','id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class,'user_id');
    }

    public function paymentProcess(): HasMany
    {
        return $this->hasMany(PaymentProcess::class);
    }

    public function deliveryManOrders(): HasMany
    {
        return $this->hasMany(Order::class,'deliveryman_id');
    }

    public function orderDetails(): HasManyThrough
    {
        return $this->hasManyThrough(OrderDetail::class,Order::class);
    }

    public function point(): HasOne
    {
        return $this->hasOne(UserPoint::class, 'user_id');
    }

    public function pointHistory(): HasMany
    {
        return $this->hasMany(PointHistory::class, 'user_id');
    }

    public function deliveryManSetting(): HasOne
    {
        return $this->hasOne(DeliveryManSetting::class, 'user_id');
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(Banner::class, Like::class);
    }

    public function activity(): HasOne
    {
        return $this->hasOne(UserActivity::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(UserAddress::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function closedDates(): HasMany
    {
        return $this->hasMany(MasterClosedDate::class, 'master_id');
    }

    public function disabledTimes(): HasMany
    {
        return $this->hasMany(MasterDisabledTime::class, 'master_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function serviceMaster(): HasOne
    {
        return $this->hasOne(ServiceMaster::class, 'master_id');
    }

    public function serviceMasters(): HasMany
    {
        return $this->hasMany(ServiceMaster::class, 'master_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    public function masterBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'master_id');
    }

    public function workingDays(): HasMany
    {
        return $this->hasMany(UserWorkingDay::class);
    }

    public function workingDay(): HasOne
    {
        return $this->hasOne(UserWorkingDay::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(UserTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(UserTranslation::class);
    }

    public function memberShips(): HasMany
    {
        return $this->hasMany(UserMemberShip::class);
    }

    public function scopeFilter($query, array $filter): void
    {
        $regionId  = data_get($filter, 'region_id');
        $countryId = data_get($filter, 'country_id');
        $cityId    = data_get($filter, 'city_id');

        $query
            // Restricts masters to ones with an accepted invitation to a
            // shop whose SERVICE-type location (the location that makes a
            // master actually bookable there - see Service::scopeFilter()'s
            // identical whereHas('serviceMaster.master.invitations', ...)
            // gate) matches the given region/country/city. Without this,
            // region_id/country_id/city_id were accepted as request params
            // (see FilterParamsRequest) and dutifully sent by every
            // masters-listing frontend call, but silently had no effect
            // here - masters from every country came back regardless,
            // unlike Shop::scopeFilter()'s equivalent, working
            // whereHas('locations', ...) clause this mirrors.
            ->when($regionId || $countryId || $cityId, function ($query) use ($regionId, $countryId, $cityId) {
                $query->whereHas('invitations', function ($q) use ($regionId, $countryId, $cityId) {
                    $q->where('status', Invitation::ACCEPTED)
                        ->whereHas('shop.locations', function ($q) use ($regionId, $countryId, $cityId) {
                            $q->where('type', ShopLocation::SERVICE)
                                ->when($regionId, fn($q) => $q->where('region_id', $regionId))
                                ->when($countryId, fn($q) => $q->where('country_id', $countryId))
                                ->when($cityId, fn($q) => $q->where('city_id', $cityId));
                        });
                });
            })
            ->when(data_get($filter, 'role'), function ($query, $role) {
                $query->when(
                    $role === 'deliveryman',
                    fn($q) => $q->role('deliveryman'),
                    fn($q) => $q->whereHas('roles', fn($q) => $q->where('name', $role)),
                );
            })
            ->when(data_get($filter, 'roles'), function ($q, $roles) {
                $q->whereHas('roles', fn($q) => $q->whereIn('name', (array)$roles));
            })
            ->when(isset($filter['my_clients']) && isset($filter['shop_id']), function ($q) use ($filter) {

                $q->where(function ($q) use ($filter) {
                    $q
                        ->whereHas('bookings', fn($q) => $q->where('shop_id', $filter['shop_id']))
                        ->orWhereHas('orders', fn($q) => $q->where('shop_id', $filter['shop_id']));
                });
            })
            ->when(!isset($filter['my_clients']) && isset($filter['shop_id']), function ($query) use ($filter) {

                if (data_get($filter, 'role') === 'deliveryman') {
                    return $query->where(function ($q) use ($filter) {
                        $q
                            ->whereHas('invitations', fn($q) => $q->where('shop_id', $filter['shop_id'])->when(
                                data_get($filter, 'invite_status'),
                                fn($q, $status) => $q->where('status', Invitation::STATUS[$status] ?? Invitation::ACCEPTED)
                            ))
                            ->orWhereDoesntHave('invitations');
                    });
                }

                return $query->whereHas('invitations', fn($q) => $q->where('shop_id', $filter['shop_id'])->when(
                    data_get($filter, 'invite_status'),
                    fn($q, $status) => $q->where('status', Invitation::STATUS[$status] ?? Invitation::ACCEPTED)
                ));
            })
            ->when(data_get($filter, 'service_id'), function ($query, $serviceId) {
                $query
                    ->has('workingDays')
                    ->whereHas('serviceMasters', fn($q) => $q->where('service_id', $serviceId));
            })
            ->when(data_get($filter, 'master_id'), function ($query, $id) {
                $query->whereHas('bookings', fn($q) => $q->select('master_id', 'user_id')->where('master_id', $id));
            })
            ->when(data_get($filter, 'service_ids'), function ($query, $serviceIds) {
                $query
                    ->has('workingDays')
                    ->whereHas('serviceMasters', fn($q) => $q->whereIn('service_id', $serviceIds));
            })
            ->when(data_get($filter, 'not_shop_id'), function ($query, $shopId) {
                $query->whereDoesntHave('invitations', fn($q) => $q->where('shop_id', '!=', $shopId));
            })
            ->when(data_get($filter, 'empty-shop'), function ($query) {
                $query->whereDoesntHave('shop');
            })
            ->when(data_get($filter, 'search'), function ($q, $search) {
                return $this->search($q, $search);
            })
            ->when(data_get($filter, 'statuses'), function ($query, $statuses) use ($filter) {

                if (!is_array($statuses)) {
                    return $query;
                }

                $statuses = array_intersect($statuses, Order::STATUSES);

                return $query->when(data_get($filter, 'role') === 'deliveryman',
                    fn($q) => $q->whereHas('deliveryManOrders', fn($q) => $q->whereIn('status', $statuses)),
                    fn($q) => $q->whereHas('orders', fn($q) => $q->whereIn('status', $statuses)),
                );
            })
            ->when(data_get($filter, 'date_from'), function ($query, $dateFrom) use ($filter) {

                $dateFrom = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
                $dateTo   = data_get($filter, 'date_to', date('Y-m-d'));
                $dateTo   = date('Y-m-d', strtotime($dateTo . ' +1 day'));

                return $query->when(data_get($filter, 'role') === 'deliveryman',
                    fn($q) => $q->whereHas('deliveryManOrders',
                        fn($q) => $q->where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)
                    ),
                    fn($q) => $q->whereHas('orders',
                        fn($q) => $q->where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)
                    ),
                );
            })
            ->when(isset($filter['online']) || data_get($filter, 'type_of_technique'), function ($query) use($filter) {

                $query->whereHas('deliveryManSetting', function (Builder $query) use($filter) {
                    $online = data_get($filter, 'online');

                    $typeOfTechnique = data_get($filter, 'type_of_technique');

                    $query
                        ->when($online === '1' || $online === '0', function ($q) use($online) {
                            $q->whereOnline(!!(int)$online)->where('location', '!=', null);
                        })
                        ->when(data_get(DeliveryManSetting::TYPE_OF_TECHNIQUES, $typeOfTechnique), function ($q, $type) {
                            $q->where('type_of_technique', $type);
                        });

                });

            })
            ->when(isset($filter['active']), fn($q) => $q->where('active', $filter['active']))
            ->when(isset($filter['ids']), fn($q) => $q->whereIn('id', $filter['ids']))
            ->when(data_get($filter, 'exist_token'), fn($query) => $query->whereNotNull('firebase_token'))
            ->when(data_get($filter, 'walletSort'), function ($q, $walletSort) use($filter) {
                $q->whereHas('wallet', function ($q) use($walletSort, $filter) {
                    $q->orderBy($walletSort, $filter['sort'] ?? 'desc');
                });
            })
            ->when(data_get($filter, 'empty-setting'), function (Builder $query) {
                $query->whereHas('deliveryManSetting', fn($q) => $q, '=', '0');
            })
            ->when(data_get($filter,'column', 'id'), function (Builder $query, $column) use($filter) {

                $column = match ($column) {
                    'rating'     => 'r_avg',
                    'count'      => 'o_count',
                    'sum'        => 'o_sum',
                    'booking'    => 'b_count',
                    'wallet_sum' => 'wallet_sum_price',
                    default      => Schema::hasColumn('users', $column) ? $column : 'id',
                };

                $query->orderBy($column, $filter['sort'] ?? 'desc');

                if (data_get($filter, 'by_rating')) {

                    $operator = data_get($filter, 'by_rating') === 'top' ? '>=' : '<';

                    $query->having('r_avg', $operator, 3.99);

                }

            });

    }
}
