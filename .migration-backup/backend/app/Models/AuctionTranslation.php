<?php
declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\AuctionTranslation
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $auction_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Auction|null $auction
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereId($value)
 * @mixin Eloquent
 */
class AuctionTranslation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeFilter(Builder $query, array $filter = []): void
    {
        $query
            ->when(data_get($filter, 'title'),       fn($q, $title) => $q->where('title', 'like', "%$title%"))
            ->when(data_get($filter, 'description'), fn($q, $desc)  => $q->where('description', 'like', "%$desc%"))
            ->when(data_get($filter, 'auction_id'),  fn($q, $id)    => $q->where('auction_id', $id));
    }
}
