<?php
declare(strict_types=1);

namespace App\Models;

use Eloquent;
use App\Traits\Payable;
use App\Traits\SetCurrency;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\AuctionUser
 *
 * @property int $id
 * @property float|null $price
 * @property string|null $status
 * @property string $user_id
 * @property int $auction_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read float|null $rate_price
 * @property-read Auction|null $auction
 * @property-read User|null $user
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self filter(array $filter)
 * @mixin Eloquent
 */
class AuctionUser extends Model
{
    use HasFactory, SetCurrency, Payable;

    protected $guarded = ['id'];

    const WAITING      = 'waiting';
    const DEPOSITED    = 'deposited';
    const MADE_A_BET   = 'made_a_bet';
    const DIDNT_BET    = 'didnt_bet';

    const STATUSES = [
        self::WAITING    => self::WAITING,
        self::DEPOSITED  => self::DEPOSITED,
        self::MADE_A_BET => self::MADE_A_BET,
        self::DIDNT_BET  => self::DIDNT_BET,
    ];

    const ACTIVE = [
        self::DEPOSITED  => self::DEPOSITED,
        self::MADE_A_BET => self::MADE_A_BET,
        self::DIDNT_BET  => self::DIDNT_BET,
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function getRatePriceAttribute(): float|int|null
    {
        if (request()->is('api/v1/dashboard/user/*') || request()->is('api/v1/rest/*')) {
            return $this->price * $this->currency();
        }

        return $this->price;
    }

    public function scopeFilter(Builder $query, array $filter = []): void
    {
        $query
            ->when(data_get($filter, 'status'),     fn($q, $status) => $q->where('status', $status))
            ->when(data_get($filter, 'price_from'), fn($q, $from)   => $q->where('price_from', '>=', $from))
            ->when(data_get($filter, 'price_to'),   fn($q, $to)     => $q->where('price_to', '<=', $to))
            ->when(data_get($filter, 'user_id'),    fn($q, $id)     => $q->where('user_id', $id))
            ->when(data_get($filter, 'auction_id'), fn($q, $id)     => $q->where('auction_id', $id));
    }
}
