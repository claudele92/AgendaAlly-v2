<?php

declare(strict_types=1);

namespace App\Services\CountryInviteService;

use App\Helpers\ResponseError;
use App\Models\CountryInvitation;
use App\Models\User;
use App\Services\AuthService\UserVerifyService;
use App\Services\CoreService;
use App\Services\UserServices\UserService;
use DB;
use Exception;
use Throwable;

final class CountryInviteService extends CoreService
{
    protected function getModelClass(): string
    {
        return CountryInvitation::class;
    }

    public function create(int $countryId, array $data): array
    {
        try {
            /** @var User $user */
            $user = User::firstWhere('id', data_get($data, 'user_id'));

            if ($user->hasAnyRole(['admin', 'seller'])) {
                throw new Exception(__('errors.' . ResponseError::ERROR_257, locale: $this->language));
            }

            $invite = $this->model()
                ->updateOrCreate(
                    ['user_id' => $user->id, 'country_id' => $countryId],
                    [
                        'created_by'      => auth('sanctum')->id(),
                        'country_role_id' => $data['country_role_id'],
                        'status'          => CountryInvitation::NEW,
                    ]
                );

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $invite->load(['user', 'countryRole']),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    /**
     * Creates a brand-new user account and grants it country-staff access
     * immediately (status ACCEPTED, no separate acceptance step) — the
     * create-account counterpart to create() above, mirroring how
     * Seller\UserController::store() assigns shop_role_id directly rather
     * than going through a pending Invitation for a newly-created account.
     */
    public function createAccount(int $countryId, array $data): array
    {
        try {
            $countryRoleId = data_get($data, 'country_role_id');

            $result = (new UserService)->create([
                ...$data,
                'role' => 'manager',
            ]);

            if (!data_get($result, 'status')) {
                return $result;
            }

            /** @var User $user */
            $user = data_get($result, 'data');

            (new UserVerifyService)->verifyEmail($user);

            $invite = $this->model()->create([
                'user_id'         => $user->id,
                'country_id'      => $countryId,
                'created_by'      => auth('sanctum')->id(),
                'country_role_id' => $countryRoleId,
                'status'          => CountryInvitation::ACCEPTED,
            ]);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $invite->load(['user', 'countryRole']),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    public function changeStatus(int $countryId, int $id, array $data): array
    {
        try {
            /** @var CountryInvitation|null $invite */
            $invite = $this->model()
                ->whereHas('user')
                ->firstWhere(['id' => $id, 'country_id' => $countryId]);

            if (!$invite) {
                return ['status' => false, 'code' => ResponseError::ERROR_404];
            }

            $invite->update(['status' => CountryInvitation::STATUS[$data['status']]]);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $invite->load(['user', 'countryRole']),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }

    public function deleteInvitations(array $ids, int $countryId): void
    {
        DB::table('country_invitations')
            ->whereIn('id', $ids)
            ->where('country_id', $countryId)
            ->delete();
    }
}
