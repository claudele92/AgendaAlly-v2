<?php
declare(strict_types=1);

namespace App\Services\AuctionQuestionService;

use Throwable;
use App\Services\CoreService;
use App\Helpers\ResponseError;
use App\Models\AuctionQuestion;

class AuctionQuestionService extends CoreService
{
    protected function getModelClass(): string
    {
        return AuctionQuestion::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {

            $model = $this->model()->create($data);

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
                'data'    => $model,
            ];

        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'message' => $e->getMessage(), 'code' => ResponseError::ERROR_501];
        }
    }

    public function update(AuctionQuestion $auctionQuestion, array $data): array
    {
        try {

            $auctionQuestion->update($data);

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
                'data'    => $auctionQuestion,
            ];

        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => ResponseError::ERROR_501];
        }
    }

    public function delete(?array $ids = [], array $filter = []): void
    {
        $models = AuctionQuestion::filter($filter)->find((array)$ids);

        foreach ($models as $model) {
            $model->delete();
        }

    }
}
