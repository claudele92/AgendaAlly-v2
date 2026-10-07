<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Str;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Story|JsonResource $this */
        $modelType = Str::lower(str_replace('App\\Models\\', '', (string)$this->model_type));

        return [
            'id'         => $this->when($this->id, $this->id),
            'model_id'   => $this->when($this->model_id, $this->model_id),
            'model_type' => $this->when($modelType, $modelType),
            'file_urls'  => $this->when($this->file_urls, $this->file_urls),
            'active'     => (bool) $this->active,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'expired'    => $this->expires_at ? $this->expires_at->lessThanOrEqualTo(now()) : true,
            'created_at' => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at' => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),

            //Relations
            'model' => $this->whenLoaded('model'),
            'shop'  => ShopResource::make($this->whenLoaded('shop')),
        ];
    }
}
