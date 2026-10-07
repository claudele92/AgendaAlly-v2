<?php
declare(strict_types=1);

namespace App\Services\ShopSubscriptionService;

use App\Helpers\ResponseError;
use App\Models\ShopSubscription;
use App\Models\Subscription;
use App\Services\CoreService;
use Throwable;

class ShopSubscriptionService extends CoreService
{

    protected function getModelClass(): string
    {
        return ShopSubscription::class;
    }

    /**
     * @param ShopSubscription $shopSubscription
     * @param array $data
     * @return array
     */
    public function update(ShopSubscription $shopSubscription, array $data): array
    {
        try {
            $subscription = Subscription::find(data_get($data, 'subscription_id'));

            if (empty($subscription)) {
                return ['status' => false, 'code' => ResponseError::ERROR_404];
            }

            $shopSubscription->update([
                'shop_id'         => data_get($data, 'shop_id'),
                'subscription_id' => $subscription->id,
                'expired_at'      => now()->addMonths($subscription->month),
                'price'           => $subscription->price,
                'type'            => data_get($subscription, 'type', 'order'),
                'active'          => data_get($data, 'active')
            ]);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $shopSubscription
            ];

        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_502,
                'message' => __('errors.' . ResponseError::ERROR_502, locale: $this->language)
            ];
        }
    }

    /**
     * Attaches a shop to a plan as a new, unpaid ShopSubscription — the
     * seller-side "pick a plan" step, before payment. Mirrors update()'s
     * field set except active stays false and expired_at is left unset;
     * both get set once the actual payment goes through (see
     * TransactionService::subscriptionTransaction()).
     *
     * @param int $shopId
     * @param int $subscriptionId
     * @return array
     */
    public function attach(int $shopId, int $subscriptionId): array
    {
        try {
            $subscription = Subscription::find($subscriptionId);

            if (empty($subscription)) {
                return ['status' => false, 'code' => ResponseError::ERROR_404];
            }

            $shopSubscription = ShopSubscription::updateOrCreate(['shop_id' => $shopId], [
                'subscription_id' => $subscription->id,
                'price'           => $subscription->price,
                'type'            => data_get($subscription, 'type', 'order'),
                'active'          => false,
            ]);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $shopSubscription
            ];

        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_502,
                'message' => __('errors.' . ResponseError::ERROR_502, locale: $this->language)
            ];
        }
    }

    /**
     * @param array|null $ids
     * @return void
     */
    public function delete(?array $ids = []): void
    {
        $models = $this->model()->find(is_array($ids) ? $ids : []);

        foreach ($models as $model) {
            $model->delete();
        }
    }
}
