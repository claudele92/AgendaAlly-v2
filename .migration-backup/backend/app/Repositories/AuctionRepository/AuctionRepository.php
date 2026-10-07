<?php
declare(strict_types=1);

namespace App\Repositories\AuctionRepository;

use App\Models\Auction;
use App\Models\AuctionUser;
use App\Repositories\CoreRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuctionRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Auction::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator
    {
        return Auction::filter($filter)
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->language),
                'brand',
                'user:firstname,lastname,img',
            ])
            ->withCount('users')
            ->whereHas('translation', fn($q) => $q->where('locale', $this->language))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param Auction $auction
     * @return Auction
     */
    public function show(Auction $auction): Auction
    {
        return $auction->loadMissing([
            'translation' => fn($q) => $q->where('locale', $this->language),
            'brand',
            'user:firstname,lastname,img',
        ]);
    }

    /**
     * @param int $id
     * @param int|null $userId
     * @return Auction|null|Model
     */
    public function showById(int $id, ?int $userId = null): Auction|null|Model
    {
        return Auction::with([
            'translation' => fn($q) => $q->where('locale', $this->language),
            'brand',
            'user:firstname,lastname,img',
        ])
            ->where('id', $id)
            ->when($userId, fn($q) => $q->whereHas('users', fn($q) => $q->where('user_id', $userId)))
            ->first();
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function users(array $filter = []): LengthAwarePaginator
    {
        return AuctionUser::filter($filter)->paginate(data_get($filter, 'perPage', 10));
    }
}
