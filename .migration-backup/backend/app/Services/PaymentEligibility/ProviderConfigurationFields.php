<?php
declare(strict_types=1);

namespace App\Services\PaymentEligibility;

/** Connection metadata only; never grants capability, funding or activation. */
final class ProviderConfigurationFields
{
    public const SECRET_KEYS = [
        'stripe_sk', 'stripe_webhook_secret',
        'flw_sk', 'flw_webhook_secret_hash', 'flw_account_id',
        'paystack_sk',
        'paypal_sandbox_client_id', 'paypal_sandbox_client_secret',
        'paypal_live_client_id', 'paypal_live_client_secret', 'paypal_webhook_id',
    ];

    public static function secrets(string $tag): array
    {
        return match ($tag) {
            'stripe' => ['stripe_sk', 'stripe_webhook_secret'],
            'flutter-wave' => ['flw_sk', 'flw_webhook_secret_hash', 'flw_account_id'],
            'paystack' => ['paystack_sk'],
            'paypal' => ['paypal_sandbox_client_id', 'paypal_sandbox_client_secret',
                'paypal_live_client_id', 'paypal_live_client_secret', 'paypal_webhook_id'],
            default => [],
        };
    }

    public static function publicKeys(): array
    {
        return ['currency', 'paypal_currency', 'title', 'description', 'logo',
            'paystack_pk', 'stripe_pk', 'paypal_mode', 'paypal_validate_ssl',
            'configured_environment', 'paypal_merchant_id'];
    }

    public static function allowed(string $tag): ?array
    {
        $public = match ($tag) {
            'stripe' => ['currency', 'stripe_pk', 'configured_environment'],
            'flutter-wave' => ['currency', 'title', 'description', 'logo', 'configured_environment'],
            'paystack' => ['currency', 'paystack_pk', 'configured_environment'],
            'paypal' => ['paypal_currency', 'paypal_mode', 'paypal_validate_ssl', 'configured_environment', 'paypal_merchant_id'],
            default => null,
        };
        return $public === null ? null : array_merge($public, self::secrets($tag));
    }
}