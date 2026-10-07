<?php
declare(strict_types=1);

namespace App\Models;

use App\Helpers\GetShop;
use App\Traits\Payable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Subscription
 *
 * @property int $id
 * @property string $type
 * @property float $price
 * @property int $month
 * @property int $active
 * @property string $title
 * @property int $product_limit
 * @property int $order_limit
 * @property int $booking_limit
 * @property boolean $with_report
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read float $rate_price
 * @property-read Currency $rate_currency
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereActive($value)
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereMonth($value)
 * @method static Builder|self wherePrice($value)
 * @method static Builder|self whereType($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Subscription extends Model
{
    use HasFactory, Payable;

    protected $guarded = ['id'];

    protected $casts = [
        'active'        => 'bool',
        'with_report'   => 'bool',
    ];

    /**
     * $price is stored in the platform default currency (see every other
     * price-bearing model in this app). Sellers/moderators browsing plans
     * see it converted into their own shop's resolved currency (see
     * Shop::displayCurrency()); every other viewer (admin included) sees
     * the raw platform-default value, unconverted — same gating as Stock/
     * Service, just resolving the viewer's own shop rather than an item's
     * owning shop, since a Subscription plan isn't tied to one shop.
     */
    public function getRatePriceAttribute(): float
    {
        return Currency::convert((float) $this->price, $this->resolvedCurrency()?->id);
    }

    /**
     * The currency rate_price above is actually expressed in — the
     * frontend must read the symbol/position from here rather than
     * assuming the platform default, since this can be the viewer's own
     * shop currency. Kept as its own accessor (rather than folding into
     * rate_price) so the resource can expose both consistently.
     */
    public function getRateCurrencyAttribute(): ?Currency
    {
        return $this->resolvedCurrency();
    }

    private function resolvedCurrency(): ?Currency
    {
        if (request()->is('api/v1/dashboard/seller/*')) {
            $shopCurrency = GetShop::shop()?->displayCurrency();

            if ($shopCurrency) {
                return $shopCurrency;
            }
        }

        return Currency::currenciesList()->where('default', 1)->first();
    }
}
