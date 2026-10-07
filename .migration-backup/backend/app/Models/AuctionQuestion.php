<?php
declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\AuctionQuestion
 *
 * @property int $id
 * @property string $title
 * @property int $user_id
 * @property int $auction_id
 * @property int $parent_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Collection|AuctionQuestion[] $answers
 * @property-read int|null $answers_count
 * @property-read self|null $parent
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self filter(array $filter)
 * @mixin Eloquent
 */
class AuctionQuestion extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const STATUS_NEW      = 'new';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    const STATUSES = [
       self::STATUS_NEW      => self::STATUS_NEW,
       self::STATUS_ACCEPTED => self::STATUS_ACCEPTED,
       self::STATUS_REJECTED => self::STATUS_REJECTED,
    ];

    /**
     * @return BelongsTo
     */
    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    /**
     * @return HasMany
     */
    public function answers(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

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
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function scopeFilter(Builder $query, array $filter = []): void
    {
        $query
            ->when(data_get($filter, 'title'),      fn($q, $title)   => $q->where('title', 'like', "%$title%"))
            ->when(data_get($filter, 'user_id'),    fn($q, $userId)  => $q->where('user_id', $userId))
            ->when(data_get($filter, 'owner_id'),   fn($q, $userId)  => $q->whereHas('auction', fn ($q) => $q->where('user_id', $userId)))
            ->when(data_get($filter, 'auction_id'), fn($q, $id)      => $q->where('auction_id', $id))
            ->when(data_get($filter, 'parent_id'),  fn($q, $id)      => $q->where('parent_id', $id))
            ->when(data_get($filter, 'status'),     fn($q, $status)  => $q->where('status', $status));
    }
}
