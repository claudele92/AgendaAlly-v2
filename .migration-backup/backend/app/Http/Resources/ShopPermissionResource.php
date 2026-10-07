<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopPermissionResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\ShopPermission|JsonResource $this */
        return [
            'id'    => $this->id,
            'key'   => $this->key,
            'group' => $this->group,
            'label' => $this->label,
        ];
    }
}
