<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopRoleResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\ShopRole|JsonResource $this */
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'staff_count'       => $this->when(isset($this->invitations_count), $this->invitations_count),
            'permissions'       => $this->whenLoaded('permissions', fn () => $this->permissions->map(fn ($permission) => [
                'id'    => $permission->id,
                'key'   => $permission->key,
                'group' => $permission->group,
                'label' => $permission->label,
            ])),
            'created_at'        => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s')),
        ];
    }
}
