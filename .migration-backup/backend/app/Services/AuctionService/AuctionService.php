<?php
declare(strict_types=1);

namespace App\Services\AuctionService;

use Throwable;
use Exception;
use App\Models\Auction;
use App\Helpers\Utility;
use App\Models\AuctionUser;
use App\Models\Transaction;
use App\Traits\PaymentRefund;
use App\Services\CoreService;
use App\Helpers\ResponseError;

class AuctionService extends CoreService
{
    use PaymentRefund;

    protected function getModelClass(): string
    {
        return Auction::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            $expiredAt = Utility::auctionExpiredAt(startAt: $data['start_at']);
            $data['expired_at'] = $expiredAt;

            $this->model()->create($data);

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
            ];

        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'message' => ResponseError::ERROR_501, 'code' => ResponseError::ERROR_501];
        }
    }

    public function update(Auction $auction, array $data): array
    {
        try {

            $expiredAt = Utility::auctionExpiredAt(startAt: $data['start_at'] ?? $auction->start_at);
            $data['expired_at'] = $expiredAt;

            if (isset($data['status']) && $data['status'] === Auction::CANCELED) {

                $users = AuctionUser::where('auction_id', $auction->id)
                    ->whereHas('transaction', fn($q) => $q->where('status', Transaction::STATUS_PAID))
                    ->get();

                foreach ($users as $user) {
                    $this->paymentRefund($user);
                }

            }

            $auction->update($data);

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
                'data'    => $auction,
            ];

        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => ResponseError::ERROR_501];
        }
    }

    public function delete(?array $ids = [], array $filter = []): void
    {
        $auctions = Auction::filter($filter)->find((array)$ids);

        foreach ($auctions as $auction) {
            $auction->delete();
        }

    }

    /**
     * @param array $data
     * @return array
     */
    public function userAssign(array $data): array
    {
        try {

            $auction = Auction::find($data['auction_id']);

            if ($auction->status !== Auction::ACCEPTED) {
                throw new Exception(__('errors.' . ResponseError::AUCTION_NOT_ACCEPTED, locale: $this->language));
            }

            $model = AuctionUser::with('auction')
                ->find([
                    'auction_id' => $data['auction_id'],
                    'user_id'    => $data['user_id']
                ]);

            if (empty($model)) {

                $data['status'] = AuctionUser::WAITING;
                $data['price']  = $auction->min_price;

                return [
                    'status'  => true,
                    'message' => ResponseError::NO_ERROR,
                    'data'    => AuctionUser::create($data),
                ];
            }

            /** @var AuctionUser $model */
            if ($model->status === AuctionUser::WAITING) {
                throw new Exception(__('errors.' . ResponseError::MAKE_DEPOSIT, locale: $this->language));
            }

            if ($model->auction->min_price > $data['price']) {
                throw new Exception(__('errors.' . ResponseError::MIN_AUCTION_PRICE, locale: $this->language));
            }

            $minPrice  = Utility::auctionMinBid($model->auction);

            if ($minPrice > $data['price']) {
                throw new Exception(__('errors.' . ResponseError::LAST_AUCTION_BID, locale: $this->language));
            }

            $expiredAt = Utility::auctionExpiredAt($model->auction);

            if ($expiredAt <= date('Y-m-d H:i:s')) {
                throw new Exception(__('errors.' . ResponseError::AUCTION_END, locale: $this->language));
            }

            $model->auction->update(['expired_at' => $expiredAt]);

            $model->update(['price' => $data['price'], 'status' => AuctionUser::MADE_A_BET]);

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
                'data'    => $model->fresh(['auction']),
            ];

        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'message' => $e->getMessage(), 'code' => ResponseError::ERROR_501];
        }
    }

}
