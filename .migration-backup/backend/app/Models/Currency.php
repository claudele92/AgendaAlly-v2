<?php
declare(strict_types=1);

namespace App\Models;

use Database\Factories\CurrencyFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string|null $symbol
 * @property string $title
 * @property float $rate
 * @property string $position
 * @property int $default
 * @property boolean $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static CurrencyFactory factory(...$parameters)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereActive($value)
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereDefault($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self wherePosition($value)
 * @method static Builder|self whereRate($value)
 * @method static Builder|self whereSymbol($value)
 * @method static Builder|self whereTitle($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Currency extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'default' => 'bool',
        'active'  => 'bool',
    ];

    const TTL = 86400; // 1 day

    public static function currenciesList()
    {
        return Cache::remember('currencies-list', self::TTL, function () {
            return self::orderByDesc('default')->get();
        });
    }

    /**
     * Converts $amount from one currency to another, given both as ids
     * into currenciesList(). All rates are stored USD-relative (units of
     * that currency per 1 USD; the base/default currency has rate=1), so
     * converting A -> B is amount * (rateB / rateA) — a no-op (returns
     * $amount unchanged) if either id doesn't resolve to a currency, so
     * this is safe to call with a possibly-unresolved target id.
     *
     * $fromCurrencyId omitted means "$amount is already in the platform
     * default currency" — the convention every raw price column in this
     * app is stored under (see Stock/Service/Subscription/AdsPackage).
     */
    public static function convert(float $amount, ?int $toCurrencyId, ?int $fromCurrencyId = null): float
    {
        if (!$toCurrencyId) {
            return $amount;
        }

        $list = self::currenciesList();

        $from = $fromCurrencyId
            ? $list->where('id', $fromCurrencyId)->first()
            : $list->where('default', 1)->first();

        $to = $list->where('id', $toCurrencyId)->first();

        if (!$to) {
            return $amount;
        }

        $fromRate = $from?->rate > 0 ? $from->rate : 1;
        $toRate   = $to->rate > 0 ? $to->rate : 1;

        return $amount * ($toRate / $fromRate);
    }
}
