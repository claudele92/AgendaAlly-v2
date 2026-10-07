<?php
declare(strict_types=1);

namespace App\Models;

use App\Services\PaymentService\Contracts\GatewayConfig;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Schema;

/**
 * App\Models\ShopPayment
 *
 * @property int $id
 * @property int $payment_id
 * @property int $shop_id
 * @property int $status
 * @property string|null $client_id
 * @property string|null $secret_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $merchant_email
 * @property string|null $payment_key
 * @property string|null $merchant_key encrypted at rest — Orange Money
 * @property string|null $subscription_key encrypted at rest — MTN MoMo
 * @property string|null $api_user encrypted at rest — MTN MoMo
 * @property string|null $api_key encrypted at rest — MTN MoMo
 * @property string|null $target_environment MTN MoMo routing value, e.g. 'mtnivorycoast'
 * @property string|null $currency ISO code the gateway call uses; defaults from the shop's country, overridable
 * @property string|null $base_url sandbox vs. production endpoint override
 * @property-read Payment|null $payment
 * @property-read Shop|null $shop
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self filter(array $filter)
 * @method static Builder|self whereClientId($value)
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereMerchantEmail($value)
 * @method static Builder|self wherePaymentId($value)
 * @method static Builder|self wherePaymentKey($value)
 * @method static Builder|self whereSecretId($value)
 * @method static Builder|self whereShopId($value)
 * @method static Builder|self whereStatus($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ShopPayment extends Model implements GatewayConfig
{
    use HasFactory;

    protected $guarded = ['id'];

    // Defense in depth for any Eloquent/relationship JSON outside the resource.
    protected $hidden = [
        'client_id', 'secret_id', 'merchant_email', 'payment_key', 'merchant_key',
        'subscription_key', 'api_user', 'api_key', 'private_key', 'access_token',
    ];

    protected $casts = [
        'status'           => 'bool',
        'merchant_key'     => 'encrypted',
        'subscription_key' => 'encrypted',
        'api_user'         => 'encrypted',
        'api_key'          => 'encrypted',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Resolves the config a shop needs for one of the gateways whose
     * merchant registration is done by the receiving business itself
     * (Orange Money, MTN Mobile Money) — there is no platform-level
     * fallback for these, so a missing row means the shop hasn't
     * configured that gateway yet.
     */
    public static function forShopAndPayment(int $shopId, int $paymentId): ?self
    {
        return static::query()
            ->where('shop_id', $shopId)
            ->where('payment_id', $paymentId)
            ->first();
    }

    public function getClientId(): ?string
    {
        return $this->client_id;
    }

    public function getMerchantKey(): ?string
    {
        return $this->merchant_key;
    }

    public function getSubscriptionKey(): ?string
    {
        return $this->subscription_key;
    }

    public function getApiUser(): ?string
    {
        return $this->api_user;
    }

    public function getApiKey(): ?string
    {
        return $this->api_key;
    }

    public function getTargetEnvironment(): ?string
    {
        return $this->target_environment;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getBaseUrl(): ?string
    {
        return $this->base_url;
    }

    public function hasOrangeCredentials(): bool
    {
        return (bool) $this->merchant_key;
    }

    public function hasMtnCredentials(): bool
    {
        return (bool) ($this->subscription_key && $this->api_user && $this->api_key && $this->target_environment);
    }

    public function scopeFilter($query, array $filter) {

        $column = $filter['column'] ?? 'id';

        if ($column !== 'id') {
            $column = Schema::hasColumn($column, 'shop_payments') ? $column : 'id';
        }

        $query
            ->when(data_get($filter, 'shop_id'),    fn($q, $shopId)     => $q->where('shop_id', $shopId))
            ->when(data_get($filter, 'payment_id'), fn($q, $paymentId)  => $q->where('payment_id', $paymentId))
            ->when(data_get($filter, 'status'),     fn($q, $status)     => $q->where('status', $status))
            ->when(data_get($filter, 'client_id'),  fn($q, $clientId)   => $q->where('client_id', $clientId))
            ->when(data_get($filter, 'secret_id'),  fn($q, $secretId)   => $q->where('secret_id', $secretId))
            ->orderBy($column, $filter['sort'] ?? 'desc');
    }
}
