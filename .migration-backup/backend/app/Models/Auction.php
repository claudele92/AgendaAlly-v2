<?php
declare(strict_types=1);

namespace App\Models;

use Eloquent;
use App\Traits\SetCurrency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Auction
 *
 * @property int $id
 * @property int $min_price
 * @property int $brand_id
 * @property int $user_id
 * @property int $winner_id
 * @property Carbon|null $start_at
 * @property Carbon|null $expired_at
 * @property string|null $status
 * @property string $img
 * @property string $video
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read int|null $users_count
 * @property-read float|null $rate_min_price
 * @property-read Brand|null $brand
 * @property-read User|null $user
 * @property-read User|null $winner
 * @property-read Collection|User[] $users
 * @property Collection|AuctionTranslation[] $translations
 * @property AuctionTranslation|null $translation
 * @property int|null $translations_count
 *
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self filter(array $filter)
 * @mixin Eloquent
 */
class Auction extends Model
{
    use HasFactory, SetCurrency;

    protected $guarded = ['id'];

    const NEW      = 'new';
    const ACCEPTED = 'accepted';
    const ENDED    = 'ended';
    const CANCELED = 'canceled';

    const STATUSES = [
        self::NEW      => self::NEW,
        self::ACCEPTED => self::ACCEPTED,
        self::ENDED    => self::ENDED,
        self::CANCELED => self::CANCELED,
    ];

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(AreaTranslation::class);
    }

    /**
     * @return HasOne
     */
    public function translation(): HasOne
    {
        return $this->hasOne(AreaTranslation::class);
    }

    /**
     * @return BelongsTo
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo // owner
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function winner(): BelongsTo // winner
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function users(): HasMany // users
    {
        return $this->hasMany(AuctionUser::class);
    }

    /**
     * @return float|int|null
     */
    public function getRateMinPriceAttribute(): float|int|null
    {
        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->min_price * $this->currency();
        }

        return $this->min_price;
    }

    public function scopeFilter(Builder $query, array $filter = []): void
    {
        $isRest = request()->is('api/v1/rest/*');

        $query
            ->when($isRest, fn($q) => $q->where('status', Auction::ACCEPTED)->where('expired_at', '>', now()))
            ->when(data_get($filter, 'owner_id'),   fn($q, $userId)   => $q->where('user_id', $userId))
            ->when(data_get($filter, 'winner_id'),  fn($q, $userId)   => $q->where('winner_id', $userId))
            ->when(data_get($filter, 'user_id'),    fn($q, $userId)   => $q->whereHas('users', fn($q) => $q->where('user_id', $userId)))
            ->when(data_get($filter, 'bran_id'),    fn($q, $brandId)  => $q->where('brand_id', $brandId))
            ->when(data_get($filter, 'min_price'),  fn($q, $minPrice) => $q->where('min_price', '>=', $minPrice))
            ->when(data_get($filter, 'max_price'),  fn($q, $maxPrice) => $q->where('min_price', '<=', $maxPrice))
            ->when(data_get($filter, 'start_from'), fn($q, $from)     => $q->where('start_at',  '>=', date('Y-m-d H:i:s', strtotime($from))))
            ->when(data_get($filter, 'start_to'),   fn($q, $to)       => $q->where('start_at',  '<=', date('Y-m-d H:i:s', strtotime($to))));
    }
}
