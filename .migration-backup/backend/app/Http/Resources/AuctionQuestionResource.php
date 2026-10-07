<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\AuctionQuestion;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var AuctionQuestion|JsonResource $this */
        return [
            'id'            => $this->id,
            'title'         => $this->when($this->title, $this->title),
            'user_id'       => $this->when($this->user_id, $this->user_id),
            'auction_id'    => $this->when($this->auction_id, $this->auction_id),
            'parent_id'     => $this->when($this->parent_id, $this->parent_id),
            'status'        => $this->when($this->status, $this->status),
            'answers_count' => $this->when($this->answers_count, $this->answers_count),

            // Relations
            'auction'       => AuctionResource::make($this->whenLoaded('auction')),
            'answers'       => self::collection($this->whenLoaded('answers')),
            'user'          => UserResource::make($this->whenLoaded('user')),
            'parent'        => self::make($this->whenLoaded('parent')),
        ];
    }
}
