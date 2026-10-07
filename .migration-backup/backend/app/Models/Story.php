<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToShopCountry;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Schema;

/**
 * App\Models\Story
 *
 * @property int $id
 * @property array $file_urls
 * @property int $model_id
 * @property string $model_type
 * @property int $shop_id
 * @property boolean $active
 * @property Product|Shop|Service|null $model
 * @property Shop|null $shop
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self filter(array $filter = [])
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereProductId($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Story extends Model
{
    use HasFactory, BelongsToShopCountry;

    protected $guarded = ['id'];

    protected $casts = [
        'active'     => 'boolean',
        'file_urls'  => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public const TYPES = [
        'shop'    => Shop::class,
        'product' => Product::class,
        'service' => Service::class
    ];

    public const LIFETIME_HOURS = 24;

    public function scopeUnexpired(Builder $query): Builder
    {
        return $query->where('created_at', '>', now()->subHours(self::LIFETIME_HOURS));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('created_at', '<=', now()->subHours(self::LIFETIME_HOURS));
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->unexpired()->where('active', true)
            ->whereHas('shop', fn (Builder $shop) => $shop->where('status', Shop::APPROVED));
    }

    public function getExpiresAtAttribute(): ?Carbon
    {
        return $this->created_at?->copy()->addHours(self::LIFETIME_HOURS);
    }

    public function model(): BelongsTo
    {
        return $this->morphTo('model');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function scopeFilter($query, array $filter = []): void
    {
        $column = $filter['column'] ?? 'id';

        if ($column !== 'id') {
            $column = Schema::hasColumn('stories', $column) ? $column : 'id';
        }

        $query
            ->when(data_get($filter, 'shop_id'),    fn($q, $shopId) => $q->where('shop_id', $shopId))
            ->when(data_get($filter, 'model_id'),   fn($q, $id)     => $q->where('model_id', $id))
            ->when(data_get($filter, 'model_type'), fn($q, $type)   => $q->where('model_type', self::TYPES[$type]))
            ->when(isset($filter['active']), fn($q) => $q->where('active', $filter['active']))
            ->orderBy($column, $filter['sort'] ?? 'desc');
    }
}
