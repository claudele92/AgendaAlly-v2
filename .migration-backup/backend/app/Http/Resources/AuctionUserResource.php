<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Auction;
use App\Models\AuctionUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var AuctionUser|JsonResource $this */
        return [
            'id'         => $this->when($this->id, $this->id),
            'user_id'    => $this->when($this->user_id, $this->user_id),
            'auction_id' => $this->when($this->auction_id, $this->auction_id),
            'price'      => $this->when($this->rate_price, $this->rate_price),

            // Relations
            'user'    => UserResource::make($this->whenLoaded('user')),
            'auction' => AuctionResource::collection($this->whenLoaded('auction')),
        ];
    }
}
