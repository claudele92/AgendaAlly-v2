<?php
declare(strict_types=1);

namespace App\Services\InviteService;

use App\Helpers\ResponseError;
use App\Models\Invitation;
use App\Models\PushNotification;
use App\Models\Shop;
use App\Models\User;
use App\Services\CoreService;
use App\Traits\Notification;
use DB;
use Exception;
use Throwable;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class InviteService extends CoreService
{
    use Notification;

    protected function getModelClass(): string
    {
        return Invitation::class;
    }

    public function create(string $uuid, array $data): array
    {
        try {
            if (($data['role'] ?? null) === 'deliveryman') {
                throw new ConflictHttpException('Use the dedicated delivery-driver invitation flow.');
            }

            /** @var Shop $shop */
            $shop = Shop::with(['seller:id,firebase_token,lang'])
                ->select(['id', 'user_id'])
                ->firstWhere('uuid', $uuid);

            /** @var User $user */
            $user = auth('sanctum')->user();

            if ($user->hasAnyRole(['seller', 'admin'])) {
                throw new Exception(__('errors.' . ResponseError::ERROR_257, locale: $user->lang ?? $this->language));
            }

            // See sellerCreate() — a shop_role always implies 'shop_manager'
            // as the platform role that gates seller-dashboard entry.
            $role = !empty($data['shop_role_id']) ? 'shop_manager' : ($data['role'] ?? 'master');
            $actorId = (int) auth('sanctum')->id();
            $this->assertNoDriverAssociationMutation((int) $shop->id, $actorId, $actorId);

            $invite = $this->model()
                ->updateOrCreate([
                    'user_id'    => $actorId,
                    'created_by' => $actorId
                ], [
                    'shop_id'      => $shop->id,
                    'role'         => $role,
                    'shop_role_id' => $data['shop_role_id'] ?? null,
                ]);

            $sellerToken = $shop->seller?->firebase_token;

            $this->sendNotification(
                $invite,
                is_array($sellerToken) ? $sellerToken : [$sellerToken],
                __('errors.' . ResponseError::INVITE_FOR_SHOP, $shop->seller?->lang ?? $this->language),
                __('errors.' . ResponseError::INVITE_FOR_SHOP, $shop->seller?->lang ?? $this->language),
                [
                    'id'   => $invite->id,
                    'type' => PushNotification::INVITE_MASTER
                ],
                [$shop->user_id]
            );

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $invite
            ];
        } catch (Exception $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    public function sellerCreate(array $data): array
    {
        try {
            if (($data['role'] ?? null) === 'deliveryman') {
                throw new ConflictHttpException('Use the dedicated delivery-driver invitation flow.');
            }

            /** @var User $user */
            $user = User::with(['roles'])->firstWhere('id', data_get($data, 'user_id'));

            if ($user->hasAnyRole(['seller', 'admin', 'deliveryman'])) {
                throw new Exception(__('errors.' . ResponseError::ERROR_257, locale: $user->lang ?? $this->language));
            }

            $createdBy = (int) auth('sanctum')->id();
            $this->assertNoDriverAssociationMutation((int) data_get($data, 'shop_id'), (int) $user->id, $createdBy);

            // A seller-defined shop_role always implies the 'shop_manager'
            // platform role too — that's what still gates entry to the
            // seller dashboard at the route level; shop_role_id is the
            // fine-grained layer on top of it, not a replacement for it.
            if (!empty($data['shop_role_id'])) {
                $data['role'] = 'shop_manager';
            }

            // Not an invitations column — a pivot relation synced below,
            // once $invite exists. Left in $data, updateOrCreate() would
            // try to write it as a plain column and fail.
            $shopLocationIds = data_get($data, 'shop_location_ids', []);
            unset($data['shop_location_ids']);

            $invite = $this->model()
                ->updateOrCreate([
                    'user_id'    => $user->id,
                    'created_by' => $createdBy
                ], $data);

            $invite->shopLocations()->sync($shopLocationIds);

            $this->sendNotification(
                $invite,
                is_array($user->firebase_token) ? $user->firebase_token : [$user->firebase_token],
                __('errors.' . ResponseError::INVITE_MASTER, ['shop' => data_get($data, 'shop_name', 'null')], $user->lang ?? $this->language),
                __('errors.' . ResponseError::INVITE_MASTER, ['shop' => data_get($data, 'shop_name', 'null')], $user->lang ?? $this->language),
                [
                    'id'   => $invite->id,
                    'type' => PushNotification::INVITE_MASTER
                ],
                [$user->id]
            );

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $invite
            ];
        } catch (Exception $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    public function changeStatus(int $id, array $data): array
    {
        try {
            $isDriverRelated = Invitation::query()
                ->whereKey($id)
                ->where('shop_id', data_get($data, 'shop_id'))
                ->where(function ($query): void {
                    $query->where('role', 'deliveryman')
                        ->orWhereHas('user.roles', fn ($roles) => $roles->where('name', 'deliveryman'));
                })
                ->exists();
            if ($isDriverRelated) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_400,
                    'message' => 'Delivery-driver invitations can only change through the consent-based flow.',
                ];
            }

            $invite = $this->model()
                ->with([
                    'user:id,firebase_token,lang,firstname,lastname,img',
                    'user.serviceMasters',
                    'user.roles',
                    'createdBy:id,firebase_token,lang,firstname,lastname,img'
                ])
                ->whereHas('user')
                ->firstWhere(['id' => $id, 'shop_id' => data_get($data, 'shop_id')]);


            /** @var User $authUser */
            $authUser = auth('sanctum')->user();

            if (!$invite) {
                return [
                    'status'  => false,
                    'code'    => ResponseError::ERROR_404,
                    'message' => __('errors.' . ResponseError::ERROR_404, locale: $authUser->lang ?? $this->language)
                ];
            }

            /** @var Invitation $invite */
            if ($invite->role === 'deliveryman' || $invite->user?->hasRole('deliveryman')) {
                throw new ConflictHttpException(
                    'Delivery-driver invitations can only be accepted through the consent-based invitation flow.'
                );
            }

            if (
                !$authUser->hasRole('admin')
                && $invite->created_by === $authUser->id
                && !in_array($data['status'], ['canceled', 'rejected'], true)
            ) {
                return [
                    'status'  => false,
                    'code'    => ResponseError::ERROR_400,
                    'message' => __('errors.' . ResponseError::ERROR_256, locale: $authUser->lang ?? $this->language)
                ];
            }

            $data['status'] = Invitation::STATUS[$data['status']];

            if ($invite->status === Invitation::ACCEPTED && in_array($data['status'], [Invitation::REJECTED, Invitation::CANCELED])) {
                $invite->user?->serviceMasters()?->where('shop_id', $invite->shop_id)->update(['active' => false]);
            }

            /** @var Invitation $invite */
            $invite->update($data);

            if ($data['status'] === Invitation::ACCEPTED) {
                $roles   = $invite->user->roles?->pluck('name')?->toArray() ?? [];
                $roles[] = $invite->role;

                $invite->user->syncRoles($roles);
            }

            $owner = $invite->createdBy;

            $status = Invitation::STATUS_BY[$invite->status] ?? $invite->status;

            $this->sendNotification(
                $invite,
                is_array($owner?->firebase_token) ? $owner?->firebase_token : [$owner?->firebase_token],
                __('errors.' . ResponseError::INVITE_STATUS_CHANGED, ['status' => $status], $owner->lang ?? $this->language),
                __('errors.' . ResponseError::INVITE_STATUS_CHANGED, ['status' => $status], $owner->lang ?? $this->language),
                [
                    'id'   => $invite->id,
                    'type' => PushNotification::INVITE_MASTER
                ],
                [$invite->created_by]
            );

            $user = $invite->user;

            $this->sendNotification(
                $invite,
                is_array($user?->firebase_token) ? $user?->firebase_token : [$user?->firebase_token],
                __('errors.' . ResponseError::INVITE_STATUS_CHANGED, ['status' => $status], $user->lang ?? $this->language),
                __('errors.' . ResponseError::INVITE_STATUS_CHANGED, ['status' => $status], $user->lang ?? $this->language),
                [
                    'id'   => $invite->id,
                    'type' => PushNotification::INVITE_MASTER
                ],
                [$invite->created_by]
            );

            return [
                'status' => true,
                'data'   => $invite,
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }

    public function show(int $shopId, int $id): ?Invitation
    {
        return $this->model()
            ->with(['user.roles', 'shopRole', 'shopLocations.country.translation', 'shopLocations.city.translation'])
            ->firstWhere(['id' => $id, 'shop_id' => $shopId]);
    }

    /**
     * Changes which shop_role and/or branch (shop_location) an existing
     * invitation is tied to - user_id and the platform role aren't editable
     * here (see InviteUpdateRequest). Allowed for 'new' and 'accepted'
     * invitations alike: shop-role permissions are resolved with a live
     * query against the invitation's shop_role_id on every request (see
     * User::hasShopPermission()), never cached or Spatie-synced, so editing
     * it on an already-accepted invitation takes effect immediately with no
     * extra sync step. Blocked on rejected/canceled - editing a dead
     * invitation has no purpose; re-inviting is the correct action there.
     *
     * Mirrors sellerCreate()'s one-way coupling: assigning a shop_role_id
     * still implies the shop_manager platform role. Clearing shop_role_id
     * back to null never reverts role - that's a separate, larger action
     * (it gates seller-dashboard entry) outside the scope of this edit.
     */
    public function update(int $id, array $data): array
    {
        try {
            /** @var Invitation|null $invite */
            $invite = $this->model()->with('user.roles')
                ->firstWhere(['id' => $id, 'shop_id' => data_get($data, 'shop_id')]);

            if (!$invite) {
                return [
                    'status'  => false,
                    'code'    => ResponseError::ERROR_404,
                    'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
                ];
            }

            if ($invite->role === 'deliveryman' || $invite->user?->hasRole('deliveryman')) {
                throw new ConflictHttpException(
                    'Delivery-driver invitations cannot be modified through legacy staff invitation routes.'
                );
            }

            if (in_array($invite->status, [Invitation::REJECTED, Invitation::CANCELED])) {
                return [
                    'status'  => false,
                    'code'    => ResponseError::ERROR_260,
                    'message' => __('errors.' . ResponseError::ERROR_260, locale: $this->language)
                ];
            }

            $updateData = [
                'shop_role_id' => data_get($data, 'shop_role_id'),
            ];

            if (!empty($updateData['shop_role_id'])) {
                $updateData['role'] = 'shop_manager';
            }

            $invite->update($updateData);
            $invite->shopLocations()->sync(data_get($data, 'shop_location_ids', []));

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $invite,
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }

    public function delete(array $ids, ?int $shopId = null, ?int $userId = null)
    {
        $hasDriverInvitations = Invitation::query()
            ->whereIn('id', $ids)
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->when($userId, fn ($q) => $q->where('created_by', $userId))
            ->where(function ($query): void {
                $query->where('role', 'deliveryman')
                    ->orWhereHas('user.roles', fn ($roles) => $roles->where('name', 'deliveryman'));
            })
            ->exists();
        if ($hasDriverInvitations) {
            throw new ConflictHttpException(
                'Delivery-driver invitations must be revoked through the dedicated invitation flow.'
            );
        }

        DB::table('invitations')->whereIn('id', $ids)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->when($userId, fn($q) => $q->where('created_by', $userId))
            ->delete();
    }

    /**
     * Guard only Driver relationships: updateOrCreate's legacy key omits
     * shop_id, so a non-Driver payload must not transform an existing Driver
     * row, nor create a second same-shop relationship through another creator.
     * Ordinary non-Driver staff associations are intentionally untouched.
     */
    private function assertNoDriverAssociationMutation(int $shopId, int $userId, int $createdBy): void
    {
        $wouldOverwriteDriver = Invitation::query()
            ->where('user_id', $userId)
            ->where('created_by', $createdBy)
            ->where('role', 'deliveryman')
            ->exists();
        $sameShopDriver = Invitation::query()
            ->where('shop_id', $shopId)
            ->where('user_id', $userId)
            ->where('role', 'deliveryman')
            ->exists();

        if ($wouldOverwriteDriver || $sameShopDriver) {
            throw new ConflictHttpException(
                'A Driver relationship cannot be transformed through legacy staff invitation routes.'
            );
        }
    }
}
