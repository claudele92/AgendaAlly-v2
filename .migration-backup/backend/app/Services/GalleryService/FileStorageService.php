<?php
declare(strict_types=1);

namespace App\Services\GalleryService;

use App\Helpers\ResponseError;
use App\Models\Gallery;
use App\Models\User;
use App\Services\CoreService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Throwable;

class FileStorageService extends CoreService
{
    protected function getModelClass(): string
    {
        return Gallery::class;
    }

    public function getStorageFiles(array $filter): LengthAwarePaginator
    {
        return \App\Helpers\ServiceMedia::scopeGallery(Gallery::filter($filter))->paginate($filter['perPage'] ?? 10);
    }

    public function deleteFileFromStorage(array $filter = []): array
    {
        try {
            $ids = data_get($filter, 'ids', []);
            $allowed = \App\Helpers\ServiceMedia::scopeGallery(Gallery::query())->whereIn('id', (array) $ids)->pluck('id');
            if (Gallery::whereIn('id', (array) $ids)->whereNotIn('id', $allowed)->exists()) {
                return ['status' => false, 'code' => ResponseError::ERROR_404];
            }

            foreach (Gallery::find((array)$ids) as $gallery) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($gallery): void {
                    if ($gallery->loadable_type === (new \App\Models\Service)->getMorphClass()) {
                        $service = \App\Models\Service::find($gallery->loadable_id);
                        if ($service && \App\Helpers\ServiceMedia::publicUrl($service->img)
                            === \App\Helpers\ServiceMedia::publicUrl($gallery->path)) {
                            $service->update(['img' => $service->galleries()->whereKeyNot($gallery->id)
                                ->orderBy('id')->value('path')]);
                        }
                    }
                    // Association removal only. Shared native media must not
                    // be physically deleted without a reference-aware cleanup.
                    $gallery->delete();
                });
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => []];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }
    }

    public function create(array $data = []): array
    {
        try {
            /** @var User $user */
            $user = auth('sanctum')->user();

            $user->galleries()->delete();
            $user->uploads(data_get($data, 'images'));

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $user->load(['galleries' => fn($q) => $q->where('type', Gallery::MASTER_GALLERIES)])
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_404, 'message' => $e->getMessage()];
        }
    }
}
