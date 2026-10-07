<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\AILog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AILogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var AILog|JsonResource $this */

        $content = $this->content;

        if (request()->is('api/v1/dashboard/seller/*')) {
            unset($content['response']);
        }

        return [
            'id'          => $this->id,
            'model_id'    => $this->when($this->model_id,    $this->model_id),
            'mode_type'   => $this->when($this->mode_type,   $this->mode_type),
            'user_id'     => $this->when($this->user_id,     $this->user_id),
            'locale'      => $this->when($this->locale,      $this->locale),
            'total_token' => $this->when($this->total_token, $this->total_token),
            'status'      => $this->when($this->status,      $this->status),
            'content'     => $content,
            'created_at'  => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'  => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),

            // Relations
            'model'     => $this->whenLoaded('model'),
            'user'      => UserResource::make($this->whenLoaded('user')),
            'shop'      => ShopResource::make($this->whenLoaded('shop')),
        ];
    }
}
