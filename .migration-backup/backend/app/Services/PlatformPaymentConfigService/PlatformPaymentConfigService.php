<?php

declare(strict_types=1);

namespace App\Services\PlatformPaymentConfigService;

use App\Helpers\ResponseError;
use App\Models\Country;
use App\Models\Payment;
use App\Models\PlatformPaymentConfig;
use App\Services\CoreService;
use Throwable;

/**
 * The platform's own Orange Money/MTN Mobile Money merchant config for
 * platform-fee purchases (ShopSubscription, ShopAdsPackage) — mirrors
 * ShopPaymentService's country-availability gate and currency default,
 * keyed by country directly instead of resolved from a shop.
 * Superadmin-only; see PlatformPaymentConfigController.
 */
class PlatformPaymentConfigService extends CoreService
{
    // Encrypted-at-rest, never returned in plaintext — a blank value on
    // update means "leave unchanged", not "clear it" (see UpdateRequest).
    private const SECRET_KEYS = ['merchant_key', 'subscription_key', 'api_user', 'api_key'];

    protected function getModelClass(): string
    {
        return PlatformPaymentConfig::class;
    }

    public function create(array $data): array
    {
        try {
            $prepared = $this->prepareConfig($data);

            if (!data_get($prepared, 'status')) {
                return $prepared;
            }

            $config = $this->model()->create(data_get($prepared, 'data'));

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $config];
        } catch (Throwable $e) {
            // Never log credential-bearing SQL bindings.
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        }
    }

    public function update(array $data, PlatformPaymentConfig $config): array
    {
        try {
            $data['country_id'] ??= $config->country_id;
            $data['payment_id'] ??= $config->payment_id;

            $prepared = $this->prepareConfig($data);

            if (!data_get($prepared, 'status')) {
                return $prepared;
            }

            $update = collect(data_get($prepared, 'data'))->except(['country_id', 'payment_id']);
            $update = $update->reject(
                fn ($value, $key) => in_array($key, self::SECRET_KEYS, true) && blank($value)
            );

            $config->update($update->all());

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $config];
        } catch (Throwable $e) {
            // Never log credential-bearing SQL bindings.
            return ['status' => false, 'code' => ResponseError::ERROR_502];
        }
    }

    /**
     * Rejects a country that doesn't offer the gateway (per
     * country_payments — same gate ShopPaymentService applies), and
     * fills in a default currency from that country when not overridden.
     */
    private function prepareConfig(array $data): array
    {
        $country = Country::with('currency')->find(data_get($data, 'country_id'));
        $payment = Payment::find(data_get($data, 'payment_id'));

        if (!$country || !$payment || !$country->activePaymentIds()->contains($payment->id)) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_400,
                'message' => __('errors.' . ResponseError::ERROR_400, locale: $this->language),
            ];
        }

        if (empty($data['currency'])) {
            $data['currency'] = $country->currency?->title;
        }

        if (empty($data['currency'])) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_400,
                'message' => __('errors.' . ResponseError::ERROR_400, locale: $this->language),
            ];
        }

        return ['status' => true, 'data' => $data];
    }

    public function delete(array $ids = []): array
    {
        foreach ($this->model()->whereIn('id', $ids)->get() as $config) {
            $config->delete();
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }
}
