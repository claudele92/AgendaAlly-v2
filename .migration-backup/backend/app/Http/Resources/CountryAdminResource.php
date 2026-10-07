<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryAdminResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\CountryAdmin|JsonResource $this */
        return [
            'id'                   => $this->id,
            'user_id'              => $this->user_id,
            'country_id'           => $this->country_id,
            'created_by'           => $this->created_by,
            // Whether removing this assignment will also revoke the
            // user's 'manager' role — see CountryAdminService.
            'manager_role_granted' => (bool) $this->manager_role_granted,
            'user'                 => UserResource::make($this->whenLoaded('user')),
            'country'              => $this->whenLoaded('country', fn () => [
                'id'   => $this->country->id,
                'name' => $this->country->translation?->title,
            ]),
            'created_at'           => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s')),
        ];
    }
}
