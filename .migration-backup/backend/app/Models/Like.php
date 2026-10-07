<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\ByLocation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Like
 *
 * @property int $id
 * @property string $likable_type
 * @property int $likable_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Blog|Product|Shop|Banner|null $likable
 * @method static Builder|self filter(array $filter)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereLikableId($value)
 * @method static Builder|self whereLikableType($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereUserId($value)
 * @mixin Eloquent
 */
class Like extends Model
{
    use HasFactory, ByLocation;

    protected $guarded = ['id'];

    const TYPES = [
        'blog'    => Blog::class,
        'product' => Product::class,
        'shop'    => Shop::class,
        'banner'  => Banner::class,
        'master'  => User::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likable(): MorphTo
    {
        return $this->morphTo('likable');
    }

    public function scopeFilter($query, array $filter): void
    {
        $query
            ->when(data_get($filter, 'type'), function($q, $type) use ($filter) {

                $type = data_get(self::TYPES, $type, Product::class);

                $q->whereHasMorph('likable', $type, function ($query) use ($type, $filter) {

                    /** @var User $user */
                    $user = auth('sanctum')->user();
                    $lang = $user->lang ?? request('lang', 'en');

                    return $query
                        ->when($type === Product::class, fn ($q) => $q->actual($lang))
                        ->when($type === Shop::class,    fn ($q) => $q->whereIn('id', $this->getShopIds($filter)))
                        ->when($type === Banner::class,  fn ($q) => $q->whereHas('products', fn($q) => $q->actual($lang)));

                });

            })
            ->when(data_get($filter, 'type'),    fn($q, $type)   => $q->where('likable_type', data_get(self::TYPES, $type, Product::class)))
            ->when(data_get($filter, 'type_id'), fn($q, $typeId) => $q->where('likable_id', $typeId))
            ->when(data_get($filter, 'user_id'), fn($q, $userId) => $q->where('user_id', $userId));
    }
}
