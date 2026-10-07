<?php
declare(strict_types=1);

namespace App\Services\DeliveryDriver;

use App\Events\Mails\SendEmailVerification;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Services\EmailSettingService\EmailSendService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class DriverInvitationService
{
    public const EXPIRY_DAYS = 7;

    public function listForShop(int $shopId, int $perPage = 15): LengthAwarePaginator
    {
        return Invitation::query()
            ->where('shop_id', $shopId)
            ->where('role', 'deliveryman')
            ->with(['user:id,uuid,firstname,lastname,img,active'])
            ->orderByDesc('id')
            ->paginate(max(1, min($perPage, 100)))
            ->through(fn (Invitation $invitation): array => $this->serialize($invitation));
    }

    /**
     * Create a pending contact invitation. The plaintext secret is only ever
     * returned once to the authenticated shop manager for local sharing.
     *
     * @return array{invitation: Invitation, token: string}
     */
    public function create(int $shopId, int $createdBy, array $input): array
    {
        $email = trim((string) $input['email']);
        $normalized = $this->normalizeEmail($email);

        $result = DB::transaction(function () use ($shopId, $createdBy, $input, $email, $normalized): array {
            $shop = Shop::query()->whereKey($shopId)->lockForUpdate()->first();
            if (!$shop) {
                throw new NotFoundHttpException('Shop not found.');
            }

            $this->assertNoExistingShopInvitation($shopId, $normalized);
            $existingUser = User::query()
                ->whereRaw('LOWER(email) = ?', [$normalized])
                ->first();
            if ($existingUser?->hasAnyRole(['admin', 'seller'])) {
                throw new ConflictHttpException('This contact cannot be invited as a delivery driver.');
            }

            $token = $this->newToken();
            $invitation = Invitation::query()->create([
                'shop_id' => $shopId,
                'user_id' => $existingUser?->id,
                'created_by' => $createdBy,
                'role' => 'deliveryman',
                'status' => Invitation::NEW,
                'driver_active' => false,
                'driver_invite_email' => $email,
                'driver_invite_email_normalized' => $normalized,
                'driver_invite_token_hash' => hash('sha256', $token),
                'driver_invite_expires_at' => now()->addDays(self::EXPIRY_DAYS),
                'driver_contact_verified_at' => $existingUser?->email_verified_at ? now() : null,
                'driver_notification_status' => 'not_delivered',
                'driver_invite_firstname' => $input['firstname'] ?? null,
                'driver_invite_lastname' => $input['lastname'] ?? null,
            ]);

            return ['invitation' => $invitation, 'token' => $token];
        }, 3);

        $result['notification_status'] = $this->deliverInvitation($result['invitation'], $result['token']);
        $result['invitation']->refresh();

        return $result;
    }

    public function resend(int $shopId, int $invitationId): array
    {
        $result = DB::transaction(function () use ($shopId, $invitationId): array {
            $invitation = $this->shopInvitation($shopId, $invitationId, true);
            if ($invitation->role !== 'deliveryman' || (int) $invitation->status !== Invitation::NEW) {
                throw new ConflictHttpException('Only a pending delivery-driver invitation can be resent.');
            }

            $token = $this->newToken();
            $invitation->forceFill([
                'driver_invite_token_hash' => hash('sha256', $token),
                'driver_invite_expires_at' => now()->addDays(self::EXPIRY_DAYS),
                'driver_notification_status' => 'not_delivered',
            ])->save();

            return ['invitation' => $invitation->fresh(), 'token' => $token];
        }, 3);

        $result['notification_status'] = $this->deliverInvitation($result['invitation'], $result['token']);
        $result['invitation']->refresh();

        return $result;
    }

    public function revoke(int $shopId, int $invitationId): Invitation
    {
        return DB::transaction(function () use ($shopId, $invitationId): Invitation {
            $invitation = $this->shopInvitation($shopId, $invitationId, true);
            if ($invitation->role !== 'deliveryman' || (int) $invitation->status !== Invitation::NEW) {
                throw new ConflictHttpException('Only a pending delivery-driver invitation can be revoked.');
            }

            $invitation->forceFill([
                'status' => Invitation::CANCELED,
                'driver_invite_token_hash' => null,
                'driver_invite_expires_at' => null,
            ])->save();

            return $invitation;
        }, 3);
    }

    public function preview(string $token): array
    {
        $invitation = $this->findByToken($token);
        if (!$invitation || $invitation->role !== 'deliveryman') {
            return ['state' => 'unavailable'];
        }

        $state = $this->state($invitation);
        $shop = $invitation->shop;

        return [
            'state' => $state,
            'email' => $invitation->driver_invite_email,
            'shop_name' => $shop?->translation?->title ?? $shop?->title ?? $shop?->name,
            'expires_at' => $invitation->driver_invite_expires_at,
        ];
    }

    /**
     * Creates credentials only for a genuinely new identity; no account is
     * activated or assigned the Driver role until verified acceptance.
     */
    public function register(string $token, array $input): array
    {
        $result = DB::transaction(function () use ($token, $input): array {
            $invitation = $this->findByToken($token, true);
            $this->assertUsablePending($invitation);
            $email = (string) $invitation->driver_invite_email;
            $normalized = (string) $invitation->driver_invite_email_normalized;

            // Serialize registrations against all live invitations for the
            // same contact, including invitations issued by different shops.
            Invitation::query()
                ->where('driver_invite_email_normalized', $normalized)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            if (User::query()->whereRaw('LOWER(email) = ?', [$normalized])->exists()) {
                throw new ConflictHttpException(
                    'If you already have an AgendaAlly account, sign in with it and verify this email before accepting.'
                );
            }

            $user = User::query()->create([
                'uuid' => (string) Str::uuid(),
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'] ?? null,
                'email' => $email,
                'password' => Hash::make($input['password']),
                'verify_token' => bin2hex(random_bytes(32)),
                'email_verified_at' => null,
                'active' => true,
            ]);
            $user->assignRole('user');

            $invitation->forceFill([
                'user_id' => $user->id,
                'driver_contact_verified_at' => null,
            ])->save();

            return [
                'verification_required' => true,
                'email' => $email,
                'notification_status' => 'not_delivered',
                '_verification_user' => $user,
            ];
        }, 3);

        // Reuse native independent verification after committing the identity.
        // The environment policy suppresses external delivery in this workspace.
        $verificationEvent = new SendEmailVerification($result['_verification_user']);
        unset($result['_verification_user']);
        event($verificationEvent);
        $result['notification_status'] = $verificationEvent->deliveryStatus;

        return $result;
    }

    public function accept(User $user, string $token): Invitation
    {
        return DB::transaction(function () use ($user, $token): Invitation {
            $invitation = $this->findByToken($token, true);
            $this->assertUsablePending($invitation);

            if (!(int) $user->id || !$user->active) {
                throw new AccessDeniedHttpException('Only an active AgendaAlly account can accept this invitation.');
            }
            if (!$user->email_verified_at) {
                throw new UnprocessableEntityHttpException(
                    'Verify your email through the normal AgendaAlly verification flow before accepting.'
                );
            }
            if (!$invitation->driver_contact_verified_at) {
                throw new UnprocessableEntityHttpException(
                    'Complete independent verification of the invited email before accepting.'
                );
            }
            if ($user->hasAnyRole(['admin', 'seller'])) {
                throw new AccessDeniedHttpException('This account cannot accept a Vendor delivery-driver invitation.');
            }
            if ($invitation->user_id !== null && (int) $invitation->user_id !== (int) $user->id) {
                throw new AccessDeniedHttpException('This invitation belongs to a different account.');
            }
            if ($this->normalizeEmail((string) $user->email) !== $invitation->driver_invite_email_normalized) {
                throw new AccessDeniedHttpException('Sign in with the verified email address invited to this shop.');
            }

            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if (!$lockedUser->email_verified_at
                || $this->normalizeEmail((string) $lockedUser->email) !== $invitation->driver_invite_email_normalized
            ) {
                throw new AccessDeniedHttpException('The invited email must be verified on this account.');
            }

            $otherShopRelationship = Invitation::query()
                ->where('shop_id', $invitation->shop_id)
                ->where('user_id', $lockedUser->id)
                ->where('id', '!=', $invitation->id)
                ->lockForUpdate()
                ->exists();
            if ($otherShopRelationship) {
                throw new ConflictHttpException('A shop relationship already exists; manual reconciliation is required.');
            }

            // The accepted invitation remains the sole authority for the
            // relationship. Role assignment happens only after proven consent.
            $invitation->forceFill([
                'user_id' => $lockedUser->id,
                'status' => Invitation::ACCEPTED,
                'driver_active' => true,
                'driver_invite_token_hash' => null,
            ])->save();
            if (!$lockedUser->hasRole('deliveryman')) {
                $lockedUser->assignRole('deliveryman');
            }

            return $invitation->fresh();
        }, 3);
    }

    public function decline(User $user, string $token): Invitation
    {
        return DB::transaction(function () use ($user, $token): Invitation {
            $invitation = $this->findByToken($token, true);
            $this->assertUsablePending($invitation);
            if (!$user->email_verified_at) {
                throw new UnprocessableEntityHttpException(
                    'Verify your invited email through the normal AgendaAlly verification flow before responding.'
                );
            }
            if (!$invitation->driver_contact_verified_at) {
                throw new UnprocessableEntityHttpException(
                    'Complete independent verification of the invited email before responding.'
                );
            }
            if ($invitation->user_id !== null && (int) $invitation->user_id !== (int) $user->id) {
                throw new AccessDeniedHttpException('This invitation belongs to a different account.');
            }
            if ($this->normalizeEmail((string) $user->email) !== $invitation->driver_invite_email_normalized) {
                throw new AccessDeniedHttpException('Sign in with the verified email address invited to this shop.');
            }

            $invitation->forceFill([
                'user_id' => $user->id,
                'status' => Invitation::REJECTED,
                'driver_invite_token_hash' => null,
            ])->save();

            return $invitation->fresh();
        }, 3);
    }

    public function setActive(int $shopId, int $invitationId, bool $active): Invitation
    {
        return DB::transaction(function () use ($shopId, $invitationId, $active): Invitation {
            Shop::query()->whereKey($shopId)->lockForUpdate()->firstOrFail();
            $invitation = $this->shopInvitation($shopId, $invitationId, true);
            if ($invitation->role !== 'deliveryman' || (int) $invitation->status !== Invitation::ACCEPTED) {
                throw new ConflictHttpException('Only an accepted delivery-driver relationship can be changed.');
            }

            $user = User::query()->whereKey($invitation->user_id)->lockForUpdate()->first();
            if (!$user || !$user->hasRole('deliveryman')
                || (int) DriverMembership::relationship($shopId, (int) $user->id)?->id !== (int) $invitation->id
            ) {
                throw new ConflictHttpException('Conflicting membership requires manual reconciliation.');
            }

            if (!$active && $this->hasUnresolvedAssignedDelivery($shopId, (int) $user->id)) {
                throw new ConflictHttpException(
                    'This driver has an assigned delivery that is not completed or canceled. Complete or safely reassign it first.'
                );
            }

            $invitation->forceFill(['driver_active' => $active])->save();

            return $invitation->fresh();
        }, 3);
    }

    public function hasUnresolvedAssignedDelivery(int $shopId, int $userId): bool
    {
        return Order::query()
            ->where('shop_id', $shopId)
            ->where('delivery_type', Order::DELIVERY)
            ->where('deliveryman_id', $userId)
            ->where(function ($query): void {
                $query->whereNull('status')
                    ->orWhereNotIn('status', [Order::STATUS_DELIVERED, Order::STATUS_CANCELED]);
            })
            ->exists();
    }

    public function serialize(Invitation $invitation): array
    {
        $user = $invitation->user;
        $state = $this->state($invitation);
        $eligible = $state === 'active'
            && $user !== null
            && DriverMembership::isEligible((int) $invitation->shop_id, (int) $user->id);
        // An unaccepted contact must not reveal a pre-existing account's
        // identity merely because a Vendor entered its email address.
        $visibleUser = $invitation->driver_invite_email_normalized
            && (int) $invitation->status !== Invitation::ACCEPTED ? null : $user;

        return [
            'id' => (int) $invitation->id,
            'email' => $invitation->driver_invite_email ?? $user?->email,
            'firstname' => $invitation->driver_invite_firstname ?? $visibleUser?->firstname,
            'lastname' => $invitation->driver_invite_lastname ?? $visibleUser?->lastname,
            'state' => $state,
            'eligible' => $eligible,
            'expires_at' => $invitation->driver_invite_expires_at,
            'user' => $visibleUser ? [
                'id' => (int) $visibleUser->id,
                'uuid' => $visibleUser->uuid,
                'firstname' => $visibleUser->firstname,
                'lastname' => $visibleUser->lastname,
                'img' => $visibleUser->img,
                'active' => (bool) $visibleUser->active,
            ] : null,
            'notification_status' => $invitation->driver_notification_status ?? 'not_delivered',
        ];
    }

    private function assertNoExistingShopInvitation(int $shopId, string $normalizedEmail): void
    {
        $conflict = Invitation::query()
            ->where('shop_id', $shopId)
            ->where('role', 'deliveryman')
            ->where(function ($query) use ($normalizedEmail): void {
                $query->where('driver_invite_email_normalized', $normalizedEmail)
                    ->orWhereHas('user', fn ($users) => $users
                        ->whereRaw('LOWER(email) = ?', [$normalizedEmail]));
            })
            ->lockForUpdate()
            ->exists();
        if ($conflict) {
            throw new ConflictHttpException('A driver invitation or membership already exists for this shop and email.');
        }
    }

    private function shopInvitation(int $shopId, int $invitationId, bool $lock): Invitation
    {
        $query = Invitation::query()
            ->where('shop_id', $shopId)
            ->whereKey($invitationId);
        if ($lock) {
            $query->lockForUpdate();
        }

        $invitation = $query->first();
        if (!$invitation) {
            throw new NotFoundHttpException('Invitation not found for this shop.');
        }

        return $invitation;
    }

    private function findByToken(string $token, bool $lock = false): ?Invitation
    {
        if ($lock) {
            $candidate = Invitation::query()
                ->where('driver_invite_token_hash', hash('sha256', $token))
                ->first();
            if (!$candidate) {
                return null;
            }
            Shop::query()->whereKey($candidate->shop_id)->lockForUpdate()->first();

            return Invitation::query()
                ->whereKey($candidate->id)
                ->where('driver_invite_token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->with('shop')
                ->first();
        }

        return Invitation::query()
            ->where('driver_invite_token_hash', hash('sha256', $token))
            ->with('shop')
            ->first();
    }

    private function assertUsablePending(?Invitation $invitation): void
    {
        if (!$invitation || $invitation->role !== 'deliveryman'
            || (int) $invitation->status !== Invitation::NEW
            || !$invitation->driver_invite_expires_at
            || $invitation->driver_invite_expires_at->isPast()
            || !$invitation->driver_invite_email_normalized
        ) {
            throw new NotFoundHttpException('This delivery-driver invitation is unavailable or expired.');
        }
    }

    private function state(Invitation $invitation): string
    {
        $status = (int) $invitation->status;
        if ($status === Invitation::NEW) {
            return !$invitation->driver_invite_expires_at
                || $invitation->driver_invite_expires_at->isPast() ? 'expired' : 'pending';
        }
        if ($status === Invitation::REJECTED) {
            return 'declined';
        }
        if ($status === Invitation::CANCELED) {
            return 'revoked';
        }
        if ($status === Invitation::ACCEPTED) {
            if (!$invitation->user_id || !$invitation->driver_active && $invitation->driver_active !== false) {
                return 'conflict';
            }
            $matchingRows = Invitation::query()
                ->where('shop_id', $invitation->shop_id)
                ->where('user_id', $invitation->user_id)
                ->count();
            if ($matchingRows !== 1 || $invitation->role !== 'deliveryman') {
                return 'conflict';
            }

            return (bool) $invitation->driver_active ? 'active' : 'suspended';
        }

        return 'conflict';
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function newToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Driver contact invitations use a CSPRNG verification token instead of
     * the legacy six-digit timestamp-derived code. The native mail resend
     * endpoint and verification endpoint call these bounded helpers.
     */
    public function hasDedicatedContactInvitation(User $user): bool
    {
        $email = $this->normalizeEmail((string) $user->email);

        return Invitation::query()
            ->where('role', 'deliveryman')
            ->whereNotNull('driver_invite_email_normalized')
            ->where(function ($query) use ($user, $email): void {
                $query->where('user_id', $user->id)
                    ->orWhere('driver_invite_email_normalized', $email);
            })
            ->exists();
    }

    public function issueNativeVerificationToken(User $user): string
    {
        if (!$this->hasDedicatedContactInvitation($user)) {
            throw new ConflictHttpException('A Driver contact invitation is required for this verification flow.');
        }

        $token = bin2hex(random_bytes(32));
        $user->forceFill(['verify_token' => $token])->save();

        return $token;
    }

    /**
     * Lock pending contact rows with the same shop -> invitation order as
     * acceptance and offboarding; the caller then locks the user.
     *
     * @return Collection<int, Invitation>
     */
    public function lockNativeContactInvitations(User $user): Collection
    {
        $email = $this->normalizeEmail((string) $user->email);
        $candidates = Invitation::query()
            ->where('role', 'deliveryman')
            ->where('status', Invitation::NEW)
            ->whereNotNull('driver_invite_token_hash')
            ->whereNotNull('driver_invite_expires_at')
            ->where('driver_invite_expires_at', '>', now())
            ->where('driver_invite_email_normalized', $email)
            ->where(function ($query) use ($user): void {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->orderBy('shop_id')
            ->orderBy('id')
            ->get(['id', 'shop_id']);

        $shopIds = $candidates->pluck('shop_id')->unique()->sort()->values();
        if ($shopIds->isEmpty()) {
            return new Collection();
        }

        Shop::query()->whereIn('id', $shopIds)->orderBy('id')->lockForUpdate()->get(['id']);

        return Invitation::query()
            ->whereIn('id', $candidates->pluck('id'))
            ->where('role', 'deliveryman')
            ->where('status', Invitation::NEW)
            ->whereNotNull('driver_invite_token_hash')
            ->where('driver_invite_expires_at', '>', now())
            ->where('driver_invite_email_normalized', $email)
            ->where(function ($query) use ($user): void {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->orderBy('shop_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    /**
     * Record contact proof and bind an unbound contact to the exact user who
     * completed the strong native challenge. Duplicate same-shop links are
     * deliberately left unbound for manual reconciliation.
     *
     * @param Collection<int, Invitation> $invitations
     */
    public function markNativeContactVerified(User $user, Collection $invitations): void
    {
        if (!$user->email_verified_at) {
            return;
        }

        $email = $this->normalizeEmail((string) $user->email);
        foreach ($invitations as $invitation) {
            if ($invitation->driver_invite_email_normalized !== $email
                || $invitation->role !== 'deliveryman'
                || (int) $invitation->status !== Invitation::NEW
                || ($invitation->user_id !== null && (int) $invitation->user_id !== (int) $user->id)
            ) {
                continue;
            }

            $existingRelationships = Invitation::query()
                ->where('shop_id', $invitation->shop_id)
                ->where('user_id', $user->id)
                ->where('id', '!=', $invitation->id)
                ->lockForUpdate()
                ->get(['id']);
            if ($existingRelationships->isNotEmpty()) {
                throw new ConflictHttpException(
                    'A shop relationship already exists; manual reconciliation is required before accepting this contact.'
                );
            }

            $duplicateContacts = Invitation::query()
                ->where('shop_id', $invitation->shop_id)
                ->where('id', '!=', $invitation->id)
                ->where('role', 'deliveryman')
                ->where('driver_invite_email_normalized', $email)
                ->where('status', Invitation::NEW)
                ->lockForUpdate()
                ->get(['id']);
            if ($duplicateContacts->isNotEmpty()) {
                throw new ConflictHttpException(
                    'Duplicate shop contact invitations require manual reconciliation.'
                );
            }

            $invitation->forceFill([
                'user_id' => $user->id,
                'driver_contact_verified_at' => now(),
            ])->save();
        }
    }

    private function deliverInvitation(Invitation $invitation, string $token): string
    {
        if (!config('delivery_driver.invitation_email_enabled')
            || !is_string(config('delivery_driver.native_public_origin'))
            || !filter_var(config('delivery_driver.native_public_origin'), FILTER_VALIDATE_URL)
        ) {
            $status = 'not_delivered';
        } else {
            $origin = rtrim((string) config('delivery_driver.native_public_origin'), '/');
            $link = $origin . '/delivery-driver-invitation#token=' . $token;
            $shopName = $invitation->shop?->translation?->title
                ?? $invitation->shop?->title
                ?? $invitation->shop?->name
                ?? 'the shop';
            $result = (new EmailSendService())->sendDeliveryDriverInvitation(
                (string) $invitation->driver_invite_email,
                (string) $shopName,
                $link,
                $invitation->driver_invite_expires_at->toDayDateTimeString()
            );
            $status = $result['delivery_status'] ?? 'not_delivered';
        }

        $invitation->forceFill(['driver_notification_status' => $status])->save();

        return $status;
    }
}