<?php
declare(strict_types=1);

namespace App\Services\ShopServices;

use App\Helpers\ResponseError;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\ShopPayment;
use App\Services\CoreService;
use Throwable;

class ShopPaymentService extends CoreService
{
    protected function getModelClass(): string
    {
        return ShopPayment::class;
    }

    // Encrypted-at-rest, never returned in plaintext — a blank value on
    // update means "leave unchanged", not "clear it" (see UpdateRequest).
    private const SECRET_KEYS = ['client_id', 'merchant_key', 'subscription_key', 'api_user', 'api_key'];

    public function create(array $data): array
    {
        try {
            $prepared = $this->prepareGatewayConfig($data);

            if (!data_get($prepared, 'status')) {
                return $prepared;
            }

            $this->model()->create(data_get($prepared, 'data'));

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        } catch (Throwable $e) {
            // Database exceptions may contain encrypted-input bindings.
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        }
    }

    public function update(array $data, ShopPayment $shopPayment): array
    {
        try {
            $data['shop_id'] ??= $shopPayment->shop_id;
            $data['payment_id'] ??= $shopPayment->payment_id;

            $prepared = $this->prepareGatewayConfig($data);

            if (!data_get($prepared, 'status')) {
                return $prepared;
            }

            $update = collect(data_get($prepared, 'data'))->except('shop_id');
            $update = $update->reject(
                fn ($value, $key) => in_array($key, self::SECRET_KEYS, true) && blank($value)
            );

            $shopPayment->update($update->all());

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        } catch (Throwable $e) {
            // Never log merchant configuration input or SQL bindings.
            return ['status' => false, 'code' => ResponseError::ERROR_502];
        }
    }

    /**
     * Registration uses the shared country/readiness/configuration policy.
     * A stored Vendor configuration is not permission to charge: initiation
     * independently evaluates the actual collector and exact currency.
     */
    private function prepareGatewayConfig(array $data): array
    {
        $payment = Payment::query()->find(data_get($data, 'payment_id'));

        if (!$payment) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => 'Payment method unavailable'];
        }

        /** @var Shop|null $shop */
        $shop = Shop::find(data_get($data, 'shop_id'));

        $context = $shop ? (new \App\Services\PaymentEligibility\PaymentContextFactory)->shop(
            $shop, isset($data['location_type']) ? (int) $data['location_type'] : null
        ) : ['valid' => false];
        $decision = $shop ? (new \App\Services\PaymentEligibility\PaymentEligibilityService)
            ->decision($payment, $context, true) : null;
        if (!$decision || !$decision['available_for_configuration']) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_400,
                'message' => 'This payment method is not available for Vendor configuration',
            ];
        }
        unset($data['location_type']);
        if (empty($data['currency'])) {
            $data['currency'] = $context['transaction_currency'];
        }
        if (strtoupper((string) $data['currency']) !== $context['transaction_currency']) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_400,
                'message' => 'Gateway currency must match the authoritative transaction currency',
            ];
        }

        $data['currency'] = strtoupper((string) $data['currency']);
        return ['status' => true, 'data' => $data];
    }

    public function delete(?array $ids = [], ?int $shopId = null): array
    {
        if ($shopId === null || !is_array($ids) || $ids === []) {
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }

        ShopPayment::query()
            ->where('shop_id', $shopId)
            ->whereIn('id', $ids)
            ->delete();

        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }

    public function setActive(int $id, int $shopId, ?bool $desiredStatus = null): array
    {
        try {
            $shopPayment = ShopPayment::query()
                ->whereKey($id)
                ->where('shop_id', $shopId)
                ->first();

            if (!$shopPayment) {
                return ['status' => false, 'code' => ResponseError::ERROR_404];
            }
            $next = $desiredStatus ?? !$shopPayment->status;
            if ($next) {
                $prepared = $this->prepareGatewayConfig([
                    'shop_id' => $shopId, 'payment_id' => $shopPayment->payment_id,
                ]);
                if (!data_get($prepared, 'status')) return $prepared;
            }
            $shopPayment->update([
                'status' => $next,
            ]);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $shopPayment->refresh()];
        } catch (Throwable $e) {
            // Configuration exceptions must not disclose credentials.
            return ['status' => false, 'code' => ResponseError::ERROR_502];
        }
    }
}
