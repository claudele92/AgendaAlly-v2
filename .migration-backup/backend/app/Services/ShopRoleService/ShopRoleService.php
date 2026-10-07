<?php

declare(strict_types=1);

namespace App\Services\ShopRoleService;

use App\Helpers\ResponseError;
use App\Models\ShopRole;
use App\Services\CoreService;
use Exception;
use Throwable;

final class ShopRoleService extends CoreService
{
    protected function getModelClass(): string
    {
        return ShopRole::class;
    }

    public function create(int $shopId, array $data): array
    {
        try {
            /** @var ShopRole $role */
            $role = ShopRole::query()->create([
                'shop_id' => $shopId,
                'name'    => $data['name'],
            ]);

            $role->permissions()->sync($data['permission_ids'] ?? []);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $role->load('permissions'),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    public function update(ShopRole $role, array $data): array
    {
        try {
            $role->update(['name' => $data['name']]);
            $role->permissions()->sync($data['permission_ids'] ?? []);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $role->load('permissions'),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }

    public function deleteRole(ShopRole $role): array
    {
        // Blocked, not reassigned/nulled, if any invitation still references
        // this role — matches the invitations.shop_role_id restrictOnDelete
        // FK exactly, so this always returns a clean error instead of the
        // delete failing on the DB constraint. Deliberately checks any
        // invitation regardless of status (accepted, pending, even
        // rejected/canceled), since that FK does the same.
        if ($role->invitations()->exists()) {
            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_504,
                'message' => __('errors.' . ResponseError::ERROR_504, locale: $this->language),
            ];
        }

        try {
            $role->permissions()->detach();
            $role->delete();

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }
}
