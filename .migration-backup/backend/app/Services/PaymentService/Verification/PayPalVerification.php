<?php
declare(strict_types=1);

namespace App\Services\PaymentService\Verification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * PayPal uses its verify-webhook-signature API, not a shared-secret HMAC.
 * Retrieval/capture uses the same configured merchant credentials as checkout.
 */
final class PayPalVerification
{
    public static function credentials(array $payload): array
    {
        $mode = $payload['paypal_mode'] ?? 'sandbox';
        if (!in_array($mode, ['sandbox', 'live'], true)) {
            throw new RuntimeException('PayPal configuration unavailable');
        }
        $result = [
            'url' => $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com',
            'client' => $payload["paypal_{$mode}_client_id"] ?? '',
            'secret' => $payload["paypal_{$mode}_client_secret"] ?? '',
            'merchant' => $payload["paypal_{$mode}_merchant_id"] ?? $payload['paypal_merchant_id'] ?? '',
            'webhook' => $payload["paypal_{$mode}_webhook_id"] ?? $payload['paypal_webhook_id'] ?? '',
        ];
        foreach (['client', 'secret', 'merchant', 'webhook'] as $key) {
            if (!is_string($result[$key]) || trim($result[$key]) === '') {
                throw new RuntimeException('PayPal configuration unavailable');
            }
        }
        return $result;
    }

    public static function amount(int $minor, string $currency): string
    {
        $currency = strtoupper($currency);
        $supported = ['AUD','BRL','CAD','CHF','CZK','DKK','EUR','GBP','HKD','HUF',
            'ILS','JPY','MXN','MYR','NOK','NZD','PHP','PLN','SEK','SGD','THB','TWD','USD'];
        if ($minor <= 0 || !in_array($currency, $supported, true)) {
            throw new RuntimeException('PayPal currency or amount unsupported');
        }
        // The original v1 checkout intent stores hundredths even for these
        // currencies. Reject fractions instead of rounding/overcharging.
        if (in_array($currency, ['HUF', 'JPY', 'TWD'], true)) {
            if ($minor % 100 !== 0) {
                throw new RuntimeException('PayPal amount precision unsupported');
            }
            return (string) intdiv($minor, 100);
        }
        return intdiv($minor, 100) . '.' . str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function minor(string $amount): int
    {
        if (!preg_match('/^(0|[1-9][0-9]{0,12})(?:\.([0-9]{1,2}))?$/D', $amount, $matches)) {
            throw new RuntimeException('PayPal amount invalid');
        }
        return ((int) $matches[1]) * 100 + (int) str_pad($matches[2] ?? '', 2, '0');
    }

    public function token(array $credentials): string
    {
        $response = Http::withOptions(['verify' => true])->timeout(20)
            ->withBasicAuth($credentials['client'], $credentials['secret'])->asForm()
            ->post($credentials['url'] . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);
        $token = $response->json('access_token');
        if (!$response->successful() || !is_string($token) || $token === '') {
            throw new RuntimeException('PayPal authentication unavailable');
        }
        return $token;
    }

    public function authenticated(Request $request, array $credentials, string $token): bool
    {
        if (!self::hasSignatureHeaders($request)) {
            return false;
        }
        $values = [];
        foreach (['auth_algo', 'cert_url', 'transmission_id', 'transmission_sig', 'transmission_time'] as $name) {
            $value = $request->header('paypal-' . str_replace('_', '-', $name));
            if (!is_string($value) || $value === '') {
                return false;
            }
            $values[$name] = $value;
        }
        // Never follow a caller-supplied certificate URL locally.
        $host = parse_url($values['cert_url'], PHP_URL_HOST);
        if (parse_url($values['cert_url'], PHP_URL_SCHEME) !== 'https'
            || !is_string($host) || !preg_match('/(^|\.)paypal\.com$/D', $host)) {
            return false;
        }
        $response = Http::withOptions(['verify' => true])->timeout(20)->withToken($token)
            ->post($credentials['url'] . '/v1/notifications/verify-webhook-signature', $values + [
                'webhook_id' => $credentials['webhook'], 'webhook_event' => $request->json()->all(),
            ]);
        return $response->successful() && $response->json('verification_status') === 'SUCCESS';
    }

    public static function hasSignatureHeaders(Request $request): bool
    {
        foreach (['auth-algo', 'cert-url', 'transmission-id', 'transmission-sig', 'transmission-time'] as $header) {
            if (!$request->headers->has('paypal-' . $header)
                || trim((string) $request->header('paypal-' . $header)) === '') {
                return false;
            }
        }
        return true;
    }

    public function order(string $id, array $credentials, string $token): array
    {
        if (!preg_match('/^[A-Za-z0-9-]{1,128}$/D', $id)) {
            throw new RuntimeException('PayPal order invalid');
        }
        $response = Http::withOptions(['verify' => true])->timeout(20)->withToken($token)
            ->get($credentials['url'] . '/v2/checkout/orders/' . $id);
        if (!$response->successful() || !is_array($response->json())) {
            throw new RuntimeException('PayPal verification unavailable');
        }
        return $response->json();
    }

    public static function proof(array $order, string $id, array $intent, string $merchant): ?array
    {
        $units = $order['purchase_units'] ?? [];
        if (($order['id'] ?? null) !== $id || ($order['status'] ?? null) !== 'COMPLETED'
            || count($units) !== 1) {
            return null;
        }
        $unit = $units[0];
        $captures = $unit['payments']['captures'] ?? [];
        if (count($captures) !== 1 || ($captures[0]['status'] ?? null) !== 'COMPLETED'
            || ($unit['payee']['merchant_id'] ?? null) !== $merchant
            || ($intent['merchant_id'] ?? null) !== $merchant
            || ($unit['reference_id'] ?? null) !== ($intent['payment_reference'] ?? null)
            || empty($intent['payment_reference'])) {
            return null;
        }
        $amount = $captures[0]['amount'] ?? [];
        $currency = strtoupper((string) ($amount['currency_code'] ?? ''));
        try {
            $minor = self::minor((string) ($amount['value'] ?? ''));
        } catch (RuntimeException) {
            return null;
        }
        if ($minor !== ($intent['total_price'] ?? null)
            || $currency !== strtoupper((string) ($intent['currency'] ?? ''))) {
            return null;
        }
        return [
            'authenticated' => true, 'merchant_verified' => true,
            'reference' => $id, 'amount_minor' => $minor, 'currency' => $currency,
            'provider_collection_reference' => $id,
            'provider_payment_id' => $captures[0]['id'] ?? null,
            'payment_id' => $intent['payment_id'] ?? null,
            'model_type' => $intent['model_type'] ?? null, 'model_id' => $intent['model_id'] ?? null,
        ];
    }
}