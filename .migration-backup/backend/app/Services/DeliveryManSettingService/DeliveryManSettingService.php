<?php
declare(strict_types=1);

namespace App\Services\DeliveryManSettingService;

use App\Events\Order\SendDeliveryManLocationByOrder;
use App\Helpers\ResponseError;
use App\Models\DeliveryManSetting;
use App\Models\Invitation;
use App\Models\Order;
use App\Services\CoreService;
use App\Services\DeliveryDriver\DriverMembership;
use App\Traits\SetTranslations;
use Exception;
use Throwable;

class DeliveryManSettingService extends CoreService
{
    use SetTranslations;

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return DeliveryManSetting::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            /** @var DeliveryManSetting $deliveryManSetting */
            $deliveryManSetting = $this->model()->updateOrCreate([
                'user_id' => data_get($data, 'user_id')
            ], $data);

            $this->setTranslations($deliveryManSetting, $data);

            if (data_get($data, 'images.0')) {
                $deliveryManSetting->uploads(data_get($data, 'images'));
                $deliveryManSetting->update(['img' => data_get($data, 'images.0')]);
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryManSetting];
        } catch (Exception $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    public function update(DeliveryManSetting $deliveryManSetting, array $data): array
    {
        try {

            $data['city_id'] = data_get($data, 'city_id');
            $data['area_id'] = data_get($data, 'area_id');

            $deliveryManSetting->update($data);

            $this->setTranslations($deliveryManSetting, $data);

            if (data_get($data, 'images.0')) {
                $deliveryManSetting->galleries()->delete();
                $deliveryManSetting->uploads(data_get($data, 'images'));
                $deliveryManSetting->update(['img' => data_get($data, 'images.0')]);
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryManSetting];

        } catch (Exception $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => ResponseError::ERROR_400];
        }
    }

    public function createOrUpdate(array $data): array
    {
        try {
            $data['user_id'] = auth('sanctum')->id();

            /** @var DeliveryManSetting $deliveryManSetting */
            $deliveryManSetting = $this->model()->updateOrCreate([
                'user_id' => $data['user_id']
            ], $data);

            $this->setTranslations($deliveryManSetting, $data);

            if (data_get($data, 'images.0')) {
                $deliveryManSetting->galleries()->delete();
                $deliveryManSetting->uploads(data_get($data, 'images'));
                $deliveryManSetting->update(['img' => data_get($data, 'images.0')]);
            }

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $deliveryManSetting->loadMissing(['galleries', 'deliveryman'])
            ];

        } catch (Exception $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => ResponseError::ERROR_501];
        }
    }

    public function updateLocation(array $data): array
    {
        try {
            $driverId = auth('sanctum')->id();
            $deliveryManSetting = DeliveryManSetting::where('user_id', $driverId)->first();

            if (empty($deliveryManSetting)) {
                return [
                    'status'  => false,
                    'code'    => ResponseError::ERROR_404,
                    'message' => __('errors.' . ResponseError::DELIVERYMAN_SETTING_EMPTY, locale: $this->language)
                ];
            }

            $settingData = $data;
            unset($settingData['order_ids']);
            $deliveryManSetting->update($settingData + ['updated_at' => now()]);

            if (isset($data['order_ids']) && $driverId !== null) {
                $orderIds = array_values(array_unique(array_filter(
                    array_map(
                        static fn($id) => is_int($id) || (is_string($id) && ctype_digit($id))
                            ? (int)$id
                            : 0,
                        (array)$data['order_ids']
                    ),
                    static fn(int $id) => $id > 0
                )));

                $authorizedOrderIds = $orderIds === []
                    ? []
                    : DriverMembership::constrainAssignedOrders(Order::query(), (int)$driverId)
                        ->whereIn('orders.id', $orderIds)
                        ->pluck('orders.id')
                        ->map(static fn($id) => (int)$id)
                        ->all();

                if ($authorizedOrderIds !== []) {
                    event(new SendDeliveryManLocationByOrder(
                        $authorizedOrderIds,
                        $this->language,
                        (int)$driverId
                    ));
                }
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryManSetting];

        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_501,
                'message' => __('errors.' . ResponseError::ERROR_501, locale: $this->language)
            ];
        }
    }

    public function updateOnline(): array
    {
        try {
            $deliveryManSetting = DeliveryManSetting::where('user_id', auth('sanctum')->id())->first();

            $deliveryManSetting->update([
               'online' => !$deliveryManSetting->online
            ]);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryManSetting];

        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    public function destroy(?array $ids = [], ?int $shopId = null, ?array $authorizedUserIds = null): array
    {
        $query = DeliveryManSetting::whereIn('id', is_array($ids) ? $ids : []);

        if ($shopId !== null) {
            // Seller callers must supply the user IDs already authorized via
            // exact shop membership; a singular invite relation is unsafe.
            $query
                ->whereIn('user_id', is_array($authorizedUserIds) ? $authorizedUserIds : [])
                ->whereIn(
                    'user_id',
                    DriverMembership::eligibleUsers($shopId)->select('users.id')
                );
        }

        $deliveryManSettings = $query->get();

        if ($shopId !== null && $deliveryManSettings->count() !== count(array_unique($ids ?? []))) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        if ($shopId !== null) {
            foreach ($deliveryManSettings as $setting) {
                $sharedWithAnotherShop = Invitation::where('user_id', $setting->user_id)
                    ->where('role', 'deliveryman')
                    ->where('shop_id', '!=', $shopId)
                    ->exists();

                if ($sharedWithAnotherShop) {
                    return [
                        'status'  => false,
                        'code'    => ResponseError::ERROR_400,
                        'message' => 'Shared delivery driver settings cannot be deleted'
                    ];
                }
            }
        }

        foreach ($deliveryManSettings as $deliveryManSetting) {
            $deliveryManSetting->delete();
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR];

    }
}
