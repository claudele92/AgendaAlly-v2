<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CountryInvitation;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryInvitationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var CountryInvitation|JsonResource $this */
        return [
            'id'              => $this->id,
            'country_id'      => $this->country_id,
            'user_id'         => $this->user_id,
            'country_role_id' => $this->country_role_id,
            'created_by'      => $this->created_by,
            'status'          => CountryInvitation::getStatusKey($this->status),
            'created_at'      => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s')),
            'updated_at'      => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s')),

            'user'         => UserResource::make($this->whenLoaded('user')),
            'created'      => UserResource::make($this->whenLoaded('createdBy')),
            'country_role' => CountryRoleResource::make($this->whenLoaded('countryRole')),
        ];
    }
}
