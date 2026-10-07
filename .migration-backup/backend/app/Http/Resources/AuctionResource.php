<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Auction;
use App\Helpers\Utility;
use App\Traits\SetCurrency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
{
    use SetCurrency;

    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Auction|JsonResource $this */
        $locales = $this->relationLoaded('translations') ?
            $this->translations->pluck('locale')->toArray() : null;

        $minBid = Utility::auctionMinBid($this);

        return [
            'id'          => $this->when($this->id, $this->id),
            'min_price'   => $this->when($this->rate_min_price, $this->rate_min_price),
            'min_bid'     => $this->when($minBid, $minBid),
            'brand_id'    => $this->when($this->brand_id, $this->brand_id),
            'user_id'     => $this->when($this->user_id, $this->user_id),
            'winner_id'   => $this->when($this->winner_id, $this->winner_id),
            'start_at'    => $this->when($this->start_at, $this->start_at),
            'expired_at'  => $this->when($this->expired_at, $this->expired_at),
            'status'      => $this->when($this->status, $this->status),
            'img'         => $this->when($this->img, $this->img),
            'video'       => $this->when($this->video, $this->video),
            'users_count' => $this->when($this->users_count, $this->users_count),

            // Relations
            'translation'  => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'locales'      => $this->when($locales, $locales),
            'brand'        => BrandResource::make($this->whenLoaded('brand')),
            'user'         => UserResource::make($this->whenLoaded('user')),
            'winner'       => UserResource::make($this->whenLoaded('winner')),
            'users'        => AuctionUserResource::collection($this->whenLoaded('users')),
        ];
    }
}
