<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SellerBookingClientResource extends JsonResource
{
    public function toArray($request): array
    {
        $isLocal = $this->resource instanceof \App\Models\SellerBookingClient;

        return [
            'id' => $this->id,
            'kind' => $isLocal ? 'local' : 'registered',
            'name' => $isLocal
                ? $this->name
                : trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? '')),
            'phone' => $this->phone,
            'email' => $this->email,
            'shop_location_id' => $isLocal ? $this->shop_location_id : null,
        ];
    }
}