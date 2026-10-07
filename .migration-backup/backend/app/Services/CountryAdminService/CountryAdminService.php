<?php

declare(strict_types=1);

namespace App\Services\CountryAdminService;

use App\Helpers\ResponseError;
use App\Models\CountryAdmin;
use App\Models\User;
use App\Services\CoreService;
use Throwable;

/**
 * Assigns/reassigns a user's single restricted country. Creating the
 * assignment also grants the 'manager' Spatie role in the same operation
 * if the user doesn't already hold 'admin'/'manager' — otherwise a newly
 * assigned country admin couldn't reach the admin panel at all, since
 * that's still gated by the coarse `role:admin|manager` route group.
 *
 * That grant is tracked via manager_role_granted so delete() can revoke
 * exactly it, and only it: a user who already held 'manager' (or 'admin')
 * before being restricted to a country keeps that role on removal — it
 * was never ours to take away, and its holder reverting to an
 * unrestricted superadmin is just the restriction being lifted. A user
 * who only had 'manager' because this assignment granted it must lose it
 * on removal, or they'd accidentally become an unrestricted superadmin
 * despite never having earned that on their own.
 */
final class CountryAdminService extends CoreService
{
    protected function getModelClass(): string
    {
        return CountryAdmin::class;
    }

    public function create(array $data): array
    {
        try {
            /** @var User $user */
            $user = User::findOrFail($data['user_id']);

            $grantRole = !$user->hasAnyRole(['admin', 'manager']);

            if ($grantRole) {
                $user->assignRole('manager');
            }

            $admin = CountryAdmin::query()->create([
                'user_id'              => $data['user_id'],
                'country_id'           => $data['country_id'],
                'created_by'           => auth('sanctum')->id(),
                'manager_role_granted' => $grantRole,
            ]);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $admin->load(['user', 'country.translation']),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    public function update(CountryAdmin $admin, array $data): array
    {
        try {
            $admin->update(['country_id' => $data['country_id']]);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $admin->load(['user', 'country.translation']),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }

    public function deleteAdmin(CountryAdmin $admin): array
    {
        try {
            if ($admin->manager_role_granted) {
                $admin->user?->removeRole('manager');
            }

            $admin->delete();

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }
}
