<?php
declare(strict_types=1);

namespace App\Services\CountryService;

use App\Helpers\ResponseError;
use App\Models\Country;
use App\Services\CoreService;
use App\Traits\SetTranslations;
use Exception;
use Throwable;

final class CountryService extends CoreService
{
    use SetTranslations;

    protected function getModelClass(): string
    {
        return Country::class;
    }

    public function create(array $data): array
    {
        try {
            $model = $this->model()->create($data);

            $this->setTranslations($model, $data);

            if (data_get($data, 'images.0')) {

                $model->uploads(data_get($data, 'images'));
                $model->update([
                    'img' => data_get($data, 'images.0')
                ]);

            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        } catch (Exception $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => ResponseError::ERROR_501];
        }
    }

    public function update(Country $model, $data): array
    {
        try {
            $model->update($data);

            $this->setTranslations($model, $data);

            if (data_get($data, 'images.0')) {
                $model->galleries()->delete();
                $model->uploads(data_get($data, 'images'));
                $model->update([
                    'img' => data_get($data, 'images.0')
                ]);

            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        } catch (Exception $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => ResponseError::ERROR_502];
        }
    }

    public function delete(?array $ids = []): array
    {
        foreach (Country::whereIn('id', is_array($ids) ? $ids : [])->get() as $model) {

            /** @var Country $model */

            try {
                $model->cities();
                $model->galleries()->delete();
            } catch (Throwable) {}

            $model->delete();
        }

        return ['status' => true, 'code' => ResponseError::ERROR_503];
    }

    public function changeActive(int $id): array
    {
        try {
            $model = Country::find($id);
            $model->update(['active' => !$model->active]);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $model
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }

    /**
     * Sync which payment gateways are available for a country, and whether
     * each is active. $payments is an array of ['payment_id' => int, 'active' => bool].
     */
    public function updatePayments(Country $model, array $payments): array
    {
        try {
            $sync = [];

            foreach ($payments as $payment) {
                $sync[data_get($payment, 'payment_id')] = ['active' => (bool) data_get($payment, 'active')];
            }

            $model->payments()->sync($sync);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $model->load('payments')
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }

}
