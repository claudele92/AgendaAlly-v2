<?php
declare(strict_types=1);

namespace App\Repositories\AuctionQuestionRepository;

use App\Models\Auction;
use App\Models\AuctionQuestion;
use App\Repositories\CoreRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuctionQuestionRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return AuctionQuestion::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function restPaginate(array $filter = []): LengthAwarePaginator
    {
        return AuctionQuestion::filter($filter)
            ->with([
                'user:firstname,lastname,img',
                'answers.user:firstname,lastname,img',
            ])
            ->withCount('answers')
            ->whereHas('translation', fn($q) => $q->where('locale', $this->language))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator
    {
        return AuctionQuestion::filter($filter)
            ->with([
                'auction.translation' => fn($q) => $q->where('locale', $this->language),
                'user:firstname,lastname,img',
            ])
            ->withCount('answers')
            ->whereHas('translation', fn($q) => $q->where('locale', $this->language))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param AuctionQuestion $auctionQuestion
     * @return AuctionQuestion
     */
    public function show(AuctionQuestion $auctionQuestion): AuctionQuestion
    {
        return $auctionQuestion->loadMissing([
            'auction.translation' => fn($q) => $q->where('locale', $this->language),
            'user:firstname,lastname,img',
        ]);
    }

    /**
     * @param int $id
     * @return Auction|null|Model
     */
    public function showById(int $id): Auction|null|Model
    {
        return AuctionQuestion::with([
            'auction.translation' => fn($q) => $q->where('locale', $this->language),
            'user:firstname,lastname,img',
        ])
            ->where('id', $id)
            ->first();
    }

    /**
     * @param int $id
     * @return Auction|null|Model
     */
    public function showByIdWithAnswers(int $id): Auction|null|Model
    {
        return AuctionQuestion::with([
            'auction.translation' => fn($q) => $q->where('locale', $this->language),
            'user:firstname,lastname,img',
            'answers.user:firstname,lastname,img',
        ])
            ->where('id', $id)
            ->first();
    }

}
