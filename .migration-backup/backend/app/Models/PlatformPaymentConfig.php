<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\PaymentService\Contracts\GatewayConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PlatformPaymentConfig
 *
 * The platform's own Orange Money/MTN Mobile Money merchant config for
 * platform-fee purchases, one row per country+gateway — see the
 * migration. Superadmin-managed only.
 *
 * @property int $id
 * @property int $country_id
 * @property int $payment_id
 * @property bool $status
 * @property string|null $client_id
 * @property string|null $merchant_key encrypted at rest — Orange Money
 * @property string|null $subscription_key encrypted at rest — MTN MoMo
 * @property string|null $api_user encrypted at rest — MTN MoMo
 * @property string|null $api_key encrypted at rest — MTN MoMo
 * @property string|null $target_environment
 * @property string|null $currency
 * @property string|null $base_url
 * @property int|null $created_by
 */
class PlatformPaymentConfig extends Model implements GatewayConfig
{
    protected $guarded = ['id'];

    protected $casts = [
        'status'           => 'bool',
        'merchant_key'     => 'encrypted',
        'subscription_key' => 'encrypted',
        'api_user'         => 'encrypted',
        'api_key'          => 'encrypted',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function forCountryAndPayment(int $countryId, int $paymentId): ?self
    {
        return static::query()
            ->where('country_id', $countryId)
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
}
