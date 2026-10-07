<?php
declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Point
 *
 * @property int $id
 * @property int|null $shop_id
 * @property Shop|null $shop
 * @property string $type
 * @property string $for
 * @property float $price
 * @property int $value
 * @property boolean $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereActive($value)
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self wherePrice($value)
 * @method static Builder|self whereShopId($value)
 * @method static Builder|self whereType($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereValue($value)
 * @mixin Eloquent
 */
class Point extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const FOR_ORDER = 'order';
    public const FOR_SERVICE = 'service';

    public const FOR = [
        self::FOR_ORDER   => self::FOR_ORDER,
        self::FOR_SERVICE => self::FOR_SERVICE,
    ];

    protected $casts = [
        'type'          => 'string',
        'price'         => 'string',
        'value'         => 'string',
        'active'        => 'bool',
        'created_at'    => 'datetime:Y-m-d H:i:s',
        'updated_at'    => 'datetime:Y-m-d H:i:s',
    ];

    public static function getActualPoint(string|int|float $amount)
    {
        $point = self::where('active', 1)
            ->where('value', '<=', (int) $amount)
            ->where('for', self::FOR_ORDER)
            ->orderByDesc('value')
            ->first();

        return $point?->type == 'percent' ? ($amount / 100) * ($point?->price ?? 0) : ($point?->price ?? 0);
    }

    public static function getBookingActualPoint(string|int|float|null $amount)
    {
        $point = self::where('active', 1)
            ->where('value', '<=', (int) $amount)
            ->where('for', self::FOR_SERVICE)
            ->orderByDesc('value')
            ->first();

        return $point?->type == 'percent' ? ($amount / 100) * ($point?->price ?? 0) : ($point?->price ?? 0);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
