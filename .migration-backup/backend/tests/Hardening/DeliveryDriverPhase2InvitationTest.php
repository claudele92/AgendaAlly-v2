<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Auth\VerifyAuthController;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Services\DeliveryDriver\DriverInvitationService;
use App\Services\DeliveryDriver\DriverMembership;
use App\Services\InviteService\InviteService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class DeliveryDriverPhase2InvitationTest extends IsolatedTestCase
{
    private const SHOP_A = 11;
    private const SHOP_B = 22;
    private const VENDOR = 31;

    private DriverInvitationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // The slim isolated harness does not boot Laravel's default aliases.
        // Native services use these aliases; this suite must pass standalone.
        foreach (['DB' => \Illuminate\Support\Facades\DB::class, 'Log' => \Illuminate\Support\Facades\Log::class] as $alias => $facade) {
            if (!class_exists($alias, false)) {
                class_alias($facade, $alias);
            }
        }
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        $this->configureRoles();
        $this->authenticateAs(null);
        $this->createLegacySchema();
        $this->runOnboardingMigration();
        $this->seedRoles();
        $this->seedBaseRows();
        $this->service = new DriverInvitationService();
        config([
            'delivery_driver.invitation_email_enabled' => false,
            'delivery_driver.native_public_origin' => null,
            'development.email.mode' => 'disabled',
        ]);
    }

    public function test_a_vendor_invites_for_its_shop_with_hashed_256_bit_token_and_honest_delivery_status(): void
    {
        $created = $this->invite('target@example.test');
        $invitation = $created['invitation'];
        $token = $created['token'];

        self::assertSame(self::SHOP_A, (int) $invitation->shop_id);
        self::assertSame(64, strlen($token));
        self::assertSame(hash('sha256', $token), $invitation->driver_invite_token_hash);
        self::assertNotSame($token, $invitation->driver_invite_token_hash);
        self::assertSame('not_delivered', $created['notification_status']);
        self::assertSame('pending', $this->service->serialize($invitation)['state']);
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $invitation->user_id));
        self::assertArrayNotHasKey('driver_invite_token_hash', $invitation->toArray());
    }

    public function test_b_forged_shop_id_is_forbidden_by_vendor_invitation_form_request(): void
    {
        $rules = (new \App\Http\Requests\DeliveryDriver\InvitationCreateRequest())->rules();
        self::assertSame(['prohibited'], $rules['shop_id']);
        self::assertSame(['prohibited'], $rules['user_id']);
        self::assertSame(['prohibited'], $rules['password']);
    }

    public function test_c_shop_roster_and_actions_are_exactly_shop_scoped(): void
    {
        $created = $this->invite('target@example.test');
        self::assertSame(1, $this->service->listForShop(self::SHOP_A)->total());
        self::assertSame(0, $this->service->listForShop(self::SHOP_B)->total());

        try {
            $this->service->revoke(self::SHOP_B, (int) $created['invitation']->id);
            self::fail('A different shop cannot revoke this invitation.');
        } catch (NotFoundHttpException) {
            self::assertSame(Invitation::NEW, (int) $created['invitation']->fresh()->status);
        }
    }

    public function test_d_invitation_input_and_response_never_accept_or_return_vendor_credentials(): void
    {
        $requestRules = (new \App\Http\Requests\DeliveryDriver\InvitationCreateRequest())->rules();
        self::assertSame(['prohibited'], $requestRules['password']);
        $created = $this->invite('target@example.test');
        $serialized = $this->service->serialize($created['invitation']);

        self::assertArrayNotHasKey('password', $serialized);
        self::assertArrayNotHasKey('driver_invite_token_hash', $serialized);
        self::assertArrayNotHasKey('driver_invite_email_normalized', $serialized);
        self::assertNull($created['invitation']->user_id);
    }

    public function test_e_existing_verified_user_accepts_without_duplicate_account_and_keeps_all_roles(): void
    {
        $user = $this->createUser('existing@example.test', ['user', 'master'], true);
        $created = $this->invite('existing@example.test');

        self::assertSame((int) $user->id, (int) $created['invitation']->user_id);
        self::assertNotNull($created['invitation']->driver_contact_verified_at);
        $before = User::query()->count();
        $accepted = $this->service->accept($user, $created['token']);

        self::assertSame($before, User::query()->count());
        self::assertSame(Invitation::ACCEPTED, (int) $accepted->status);
        self::assertSame(['deliveryman', 'master', 'user'], $user->fresh()->roles->pluck('name')->sort()->values()->all());
        self::assertTrue(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
    }

    public function test_f_new_driver_registers_own_hashed_password_and_waits_for_native_email_proof_and_acceptance(): void
    {
        $created = $this->invite('new-driver@example.test');
        $password = 'Driver-owned-secret-42';
        $result = $this->service->register($created['token'], [
            'firstname' => 'New Driver',
            'lastname' => 'One',
            'password' => $password,
        ]);
        $user = User::query()->where('email', 'new-driver@example.test')->firstOrFail();

        self::assertTrue($result['verification_required']);
        self::assertSame('not_delivered', $result['notification_status']);
        self::assertTrue(\Illuminate\Support\Facades\Hash::check($password, $user->password));
        self::assertNotSame($password, $user->password);
        self::assertNull($user->email_verified_at);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $user->verify_token);
        self::assertTrue($user->hasRole('user'));
        self::assertFalse($user->hasRole('deliveryman'));
        self::assertNull($created['invitation']->fresh()->driver_contact_verified_at);
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));

        $this->service->issueNativeVerificationToken($user);
        $user->forceFill(['email_verified_at' => now()])->save();
        $this->markNativeContactVerified($user->fresh());
        $this->service->accept($user->fresh(), $created['token']);
        self::assertTrue(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
    }

    public function test_g_wrong_authenticated_identity_cannot_accept_a_bound_registration(): void
    {
        $created = $this->invite('bound@example.test');
        $this->service->register($created['token'], [
            'firstname' => 'Bound',
            'password' => 'Bound-own-password',
        ]);
        $wrong = $this->createUser('wrong@example.test', ['user'], true);
        $created['invitation']->fresh()->forceFill(['driver_contact_verified_at' => now()])->save();

        $this->expectException(AccessDeniedHttpException::class);
        $this->service->accept($wrong, $created['token']);
    }

    public function test_h_expired_or_revoked_invitation_cannot_accept_or_register(): void
    {
        $expired = $this->invite('expired@example.test');
        $expired['invitation']->forceFill(['driver_invite_expires_at' => now()->subMinute()])->save();
        self::assertSame('expired', $this->service->serialize($expired['invitation']->fresh())['state']);
        try {
            $this->service->register($expired['token'], [
                'firstname' => 'Expired',
                'password' => 'Expired-password-42',
            ]);
            self::fail('Expired token must not register.');
        } catch (NotFoundHttpException) {
            self::assertFalse(User::query()->where('email', 'expired@example.test')->exists());
        }

        $revoked = $this->invite('revoked@example.test');
        $this->service->revoke(self::SHOP_A, (int) $revoked['invitation']->id);
        self::assertSame('revoked', $this->service->serialize($revoked['invitation']->fresh())['state']);
        $this->expectException(NotFoundHttpException::class);
        $this->service->register($revoked['token'], [
            'firstname' => 'Revoked',
            'password' => 'Revoked-password-42',
        ]);
    }

    public function test_i_acceptance_replay_cannot_reuse_token_or_create_a_second_membership(): void
    {
        $user = $this->createUser('replay@example.test', ['user'], true);
        $created = $this->invite('replay@example.test');
        $this->service->accept($user, $created['token']);
        self::assertSame(1, Invitation::query()->where('shop_id', self::SHOP_A)->where('user_id', $user->id)->count());

        $this->expectException(NotFoundHttpException::class);
        $this->service->accept($user->fresh(), $created['token']);
    }

    public function test_j_pending_and_declined_invites_are_never_assignable(): void
    {
        $user = $this->createUser('decline@example.test', ['user'], true);
        $created = $this->invite('decline@example.test');
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));

        $declined = $this->service->decline($user, $created['token']);
        self::assertSame(Invitation::REJECTED, (int) $declined->status);
        self::assertFalse($user->fresh()->hasRole('deliveryman'));
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
    }

    public function test_k_accepted_active_membership_is_eligible_through_canonical_resolver(): void
    {
        $user = $this->createUser('eligible@example.test', ['user'], true);
        $created = $this->invite('eligible@example.test');
        $this->service->accept($user, $created['token']);

        self::assertTrue(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
        self::assertSame('active', $this->service->serialize($created['invitation']->fresh())['state']);
    }

    public function test_l_suspension_disables_only_this_shop_membership(): void
    {
        [$user, $membershipA] = $this->acceptUserForShop('multi@example.test', self::SHOP_A);
        $createdB = $this->invite('multi@example.test', self::SHOP_B);
        $user->forceFill(['email_verified_at' => now()])->save();
        $this->markNativeContactVerified($user->fresh());
        $membershipB = $this->service->accept($user->fresh(), $createdB['token']);

        $this->service->setActive(self::SHOP_A, (int) $membershipA->id, false);
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
        self::assertTrue(DriverMembership::isEligible(self::SHOP_B, (int) $user->id));
        self::assertTrue((bool) $user->fresh()->active);

        $this->service->setActive(self::SHOP_A, (int) $membershipA->id, true);
        self::assertTrue(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
        self::assertSame('active', $this->service->serialize($membershipB)['state']);
    }

    public function test_m_suspending_one_vendor_does_not_toggle_another_vendors_membership(): void
    {
        [$user, $membershipA] = $this->acceptUserForShop('two-shops@example.test', self::SHOP_A);
        $createdB = $this->invite('two-shops@example.test', self::SHOP_B);
        $user->forceFill(['email_verified_at' => now()])->save();
        $this->markNativeContactVerified($user->fresh());
        $membershipB = $this->service->accept($user->fresh(), $createdB['token']);

        $this->service->setActive(self::SHOP_A, (int) $membershipA->id, false);
        self::assertFalse((bool) $membershipA->fresh()->driver_active);
        self::assertTrue((bool) $membershipB->fresh()->driver_active);
    }

    public function test_n_global_driver_role_without_shop_consent_is_not_eligible(): void
    {
        $user = $this->createUser('role-only@example.test', ['deliveryman'], true);
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
    }

    public function test_o_forged_driver_id_remains_rejected_by_assignment_authority(): void
    {
        $eligible = $this->createUser('foreign-driver@example.test', ['deliveryman'], true);
        $order = Order::query()->create([
            'shop_id' => self::SHOP_A,
            'delivery_type' => Order::DELIVERY,
            'deliveryman_id' => null,
            'status' => Order::STATUS_NEW,
        ]);
        $result = $this->orderService()->updateDeliveryMan((int) $order->id, (int) $eligible->id, self::SHOP_A);

        self::assertFalse($result['status']);
        self::assertNull($order->fresh()->deliveryman_id);
    }

    public function test_o2_vendor_assignment_rechecks_membership_and_then_blocks_offboarding(): void
    {
        [$user, $membership] = $this->acceptUserForShop('assigned-driver@example.test', self::SHOP_A);
        $order = Order::query()->create([
            'shop_id' => self::SHOP_A,
            'delivery_type' => Order::DELIVERY,
            'deliveryman_id' => null,
            'status' => Order::STATUS_NEW,
        ]);

        $result = $this->orderService()->updateDeliveryMan((int) $order->id, (int) $user->id, self::SHOP_A);
        self::assertTrue($result['status']);
        self::assertSame((int) $user->id, (int) $order->fresh()->deliveryman_id);

        $this->expectException(ConflictHttpException::class);
        $this->service->setActive(self::SHOP_A, (int) $membership->id, false);
    }

    public function test_o3_global_admin_assignment_remains_global_authorized_without_vendor_membership_gate(): void
    {
        $globalDriver = $this->createUser('global-driver@example.test', ['deliveryman'], true);
        $order = Order::query()->create([
            'shop_id' => self::SHOP_A,
            'delivery_type' => Order::DELIVERY,
            'deliveryman_id' => null,
            'status' => Order::STATUS_NEW,
        ]);

        $result = $this->orderService()->updateDeliveryMan((int) $order->id, (int) $globalDriver->id);

        self::assertTrue($result['status']);
        self::assertSame((int) $globalDriver->id, (int) $order->fresh()->deliveryman_id);
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $globalDriver->id));
    }

    public function test_p_staff_view_and_invite_route_permissions_remain_separate(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2) . '/routes/api.php');
        self::assertIsString($routes);
        self::assertStringContainsString("shop.permission:staff.view", $routes);
        self::assertStringContainsString("shop.permission:staff.invite", $routes);
        self::assertStringContainsString("Route::get('delivery-driver-invitations'", $routes);
        self::assertStringContainsString("Route::post('delivery-driver-invitations'", $routes);
    }

    public function test_q_foreign_shop_actions_cannot_read_or_revoke_another_vendors_invite(): void
    {
        $created = $this->invite('foreign-shop@example.test');
        self::assertSame(0, $this->service->listForShop(self::SHOP_B)->total());
        $this->expectException(NotFoundHttpException::class);
        $this->service->revoke(self::SHOP_B, (int) $created['invitation']->id);
    }

    public function test_r_completed_driver_attribution_survives_suspension(): void
    {
        [$user, $membership] = $this->acceptUserForShop('history@example.test', self::SHOP_A);
        $order = Order::query()->create([
            'shop_id' => self::SHOP_A,
            'delivery_type' => Order::DELIVERY,
            'deliveryman_id' => $user->id,
            'status' => Order::STATUS_DELIVERED,
        ]);

        $this->service->setActive(self::SHOP_A, (int) $membership->id, false);
        self::assertSame((int) $user->id, (int) $order->fresh()->deliveryman_id);
        self::assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);
    }

    public function test_s_offboarding_fails_closed_for_every_nonterminal_or_unknown_delivery_status(): void
    {
        [$user, $membership] = $this->acceptUserForShop('active-delivery@example.test', self::SHOP_A);
        foreach ([Order::STATUS_NEW, Order::STATUS_ACCEPTED, Order::STATUS_READY, Order::STATUS_ON_A_WAY, Order::STATUS_PAUSE, 'future_status', null] as $status) {
            $order = Order::query()->create([
                'shop_id' => self::SHOP_A,
                'delivery_type' => Order::DELIVERY,
                'deliveryman_id' => $user->id,
                'status' => $status,
            ]);
            try {
                $this->service->setActive(self::SHOP_A, (int) $membership->id, false);
                self::fail('Active or unknown-status delivery must block offboarding.');
            } catch (ConflictHttpException) {
                self::assertTrue((bool) $membership->fresh()->driver_active);
                self::assertSame((int) $user->id, (int) $order->fresh()->deliveryman_id);
            }
            $order->delete();
        }
    }

    public function test_t_legacy_admin_or_seller_invitation_controls_cannot_fabricate_driver_consent(): void
    {
        $user = $this->createUser('legacy@example.test', ['user'], true);
        $membership = Invitation::query()->create([
            'shop_id' => self::SHOP_A,
            'user_id' => $user->id,
            'created_by' => self::VENDOR,
            'role' => 'deliveryman',
            'status' => Invitation::NEW,
            'driver_active' => false,
            'driver_invite_email' => $user->email,
            'driver_invite_email_normalized' => $user->email,
            'driver_invite_token_hash' => hash('sha256', 'not-a-real-token'),
            'driver_invite_expires_at' => now()->addDay(),
        ]);
        $legacy = new InviteService();

        self::assertFalse($legacy->create('shop-a', ['role' => 'deliveryman'])['status']);
        self::assertFalse($legacy->sellerCreate([
            'role' => 'deliveryman',
            'user_id' => $user->id,
            'shop_id' => self::SHOP_A,
        ])['status']);
        self::assertFalse($legacy->changeStatus((int) $membership->id, [
            'shop_id' => self::SHOP_A,
            'status' => 'accepted',
        ])['status']);
        self::assertSame(Invitation::NEW, (int) $membership->fresh()->status);
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
    }

    public function test_t2_legacy_non_driver_upserts_cannot_transform_driver_rows_or_mutate_other_shop_staff(): void
    {
        $vendorA = User::query()->where('email', 'vendor-a@example.test')->firstOrFail();
        $vendorB = User::query()->where('email', 'vendor-b@example.test')->firstOrFail();
        $target = $this->createUser('legacy-upsert@example.test', ['user'], true);
        $driverRow = Invitation::query()->create([
            'shop_id' => self::SHOP_A,
            'user_id' => $target->id,
            'created_by' => $vendorA->id,
            'role' => 'deliveryman',
            'status' => Invitation::ACCEPTED,
            'driver_active' => true,
        ]);
        $otherShopStaff = Invitation::query()->create([
            'shop_id' => self::SHOP_B,
            'user_id' => $target->id,
            'created_by' => $vendorB->id,
            'role' => 'master',
            'status' => Invitation::ACCEPTED,
            'driver_active' => true,
        ]);

        $legacy = new InviteService();
        $this->authenticateAs($vendorA);
        $crossShopAttempt = $legacy->sellerCreate([
            'shop_id' => self::SHOP_B,
            'shop_name' => 'Shop B',
            'user_id' => $target->id,
            'role' => 'master',
        ]);
        self::assertFalse($crossShopAttempt['status']);
        self::assertSame('deliveryman', $driverRow->fresh()->role);
        self::assertSame(Invitation::ACCEPTED, (int) $driverRow->fresh()->status);
        self::assertSame(self::SHOP_B, (int) $otherShopStaff->fresh()->shop_id);
        self::assertSame('master', $otherShopStaff->fresh()->role);

        $actorDriver = $this->createUser('legacy-driver-actor@example.test', ['deliveryman'], true);
        $actorMembership = Invitation::query()->create([
            'shop_id' => self::SHOP_A,
            'user_id' => $actorDriver->id,
            'created_by' => $actorDriver->id,
            'role' => 'deliveryman',
            'status' => Invitation::ACCEPTED,
            'driver_active' => true,
        ]);
        $this->authenticateAs($actorDriver);
        $genericAttempt = $legacy->create('shop-a', ['role' => 'master']);

        self::assertFalse($genericAttempt['status']);
        self::assertSame('deliveryman', $actorMembership->fresh()->role);
        self::assertSame(Invitation::ACCEPTED, (int) $actorMembership->fresh()->status);
    }

    public function test_u_unbound_invitation_does_not_trust_an_old_verified_timestamp_after_email_change(): void
    {
        $created = $this->invite('new-address@example.test');
        $user = $this->createUser('new-address@example.test', ['user'], true);

        self::assertNull($created['invitation']->user_id);
        self::assertNull($created['invitation']->driver_contact_verified_at);
        try {
            $this->service->accept($user, $created['token']);
            self::fail('A global verification timestamp does not prove the newly invited contact.');
        } catch (UnprocessableEntityHttpException) {
            self::assertSame(Invitation::NEW, (int) $created['invitation']->fresh()->status);
            self::assertFalse($user->fresh()->hasRole('deliveryman'));
        }
    }

    public function test_v_weak_native_timestamp_code_cannot_mint_token_for_driver_contact(): void
    {
        $created = $this->invite('secure-verify@example.test');
        $user = $this->createUser('secure-verify@example.test', ['user'], false);
        $user->forceFill(['verify_token' => '123456'])->save();
        $controller = new VerifyAuthController();
        $response = $controller->verifyEmail('123456');

        self::assertSame(404, $response->getStatusCode());
        self::assertNull($user->fresh()->email_verified_at);
        self::assertSame(0, $this->database->table('personal_access_tokens')->count());
        self::assertNull($created['invitation']->fresh()->driver_contact_verified_at);
    }

    public function test_v2_native_resend_uses_csprng_token_for_driver_contacts_without_claiming_delivery(): void
    {
        $user = $this->createUser('resend-driver@example.test', ['user'], false);
        $created = $this->invite('resend-driver@example.test');
        $oldWeakToken = $user->verify_token;
        $event = new \App\Events\Mails\SendEmailVerification($user);

        (new \App\Listeners\Mails\SendEmailVerificationListener())->handle($event);

        $token = (string) $user->fresh()->verify_token;
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        self::assertNotSame($oldWeakToken, $token);
        self::assertSame('not_delivered', $event->deliveryStatus);
        self::assertNull($created['invitation']->fresh()->driver_contact_verified_at);
    }

    public function test_w_strong_native_verification_records_contact_proof_without_bypassing_acceptance(): void
    {
        $created = $this->invite('strong-verify@example.test');
        $user = $this->createUser('strong-verify@example.test', ['user'], false);
        $strongToken = $this->service->issueNativeVerificationToken($user);
        self::assertSame(64, strlen($strongToken));

        $controller = new VerifyAuthController();
        $response = $controller->verifyEmail($strongToken);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($user->fresh()->email_verified_at);
        self::assertNull($user->fresh()->verify_token);
        self::assertNotNull($created['invitation']->fresh()->driver_contact_verified_at);
        self::assertSame((int) $user->id, (int) $created['invitation']->fresh()->user_id);
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));

        $this->service->accept($user->fresh(), $created['token']);
        self::assertTrue(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
    }

    public function test_w2_duplicate_pending_contact_rows_fail_closed_during_native_identity_binding(): void
    {
        $created = $this->invite('duplicate-contact@example.test');
        $duplicate = Invitation::query()->create([
            'shop_id' => self::SHOP_A,
            'user_id' => null,
            'created_by' => self::VENDOR,
            'role' => 'deliveryman',
            'status' => Invitation::NEW,
            'driver_active' => false,
            'driver_invite_email' => 'duplicate-contact@example.test',
            'driver_invite_email_normalized' => 'duplicate-contact@example.test',
            'driver_invite_token_hash' => hash('sha256', 'another-secure-token'),
            'driver_invite_expires_at' => now()->addDay(),
        ]);
        $user = $this->createUser('duplicate-contact@example.test', ['user'], false);
        $token = $this->service->issueNativeVerificationToken($user);
        $controller = new VerifyAuthController();

        try {
            $controller->verifyEmail($token);
            self::fail('Duplicate contact invitations must fail closed.');
        } catch (ConflictHttpException) {
            self::assertNull($user->fresh()->email_verified_at);
            self::assertSame($token, $user->fresh()->verify_token);
            self::assertNull($created['invitation']->fresh()->user_id);
            self::assertNull($duplicate->fresh()->user_id);
            self::assertNull($created['invitation']->fresh()->driver_contact_verified_at);
            self::assertNull($duplicate->fresh()->driver_contact_verified_at);
        }
    }

    public function test_x_duplicate_membership_conflicts_fail_closed_and_serializer_redacts_credentials(): void
    {
        [$user, $membership] = $this->acceptUserForShop('conflict@example.test', self::SHOP_A);
        Invitation::query()->create([
            'shop_id' => self::SHOP_A,
            'user_id' => $user->id,
            'created_by' => self::VENDOR,
            'role' => 'moderator',
            'status' => Invitation::ACCEPTED,
            'driver_active' => true,
        ]);

        self::assertNull(DriverMembership::relationship(self::SHOP_A, (int) $user->id));
        self::assertFalse(DriverMembership::isEligible(self::SHOP_A, (int) $user->id));
        self::assertSame('conflict', $this->service->serialize($membership->fresh())['state']);
        self::assertArrayNotHasKey('verify_token', $user->toArray());
    }

    public function test_y_email_change_invalidates_global_and_driver_contact_verification(): void
    {
        $user = $this->createUser('before@example.test', ['user'], true);
        $created = $this->invite('before@example.test');
        $this->authenticateAs($user);

        $service = new \App\Services\UserServices\UserService();
        $result = $service->update($user->uuid, ['email' => 'after@example.test']);

        self::assertTrue($result['status'], json_encode($result));
        self::assertNull($user->fresh()->email_verified_at);
        self::assertNull($user->fresh()->verify_token);
        self::assertNull($created['invitation']->fresh()->driver_contact_verified_at);
    }

    public function test_z_migration_up_preserves_foreign_key_and_rollback_never_deletes_null_contact_rows(): void
    {
        $legacyUser = $this->createUser('legacy-schema@example.test', ['user'], true);
        $legacy = Invitation::query()->create([
            'shop_id' => self::SHOP_A,
            'user_id' => $legacyUser->id,
            'created_by' => self::VENDOR,
            'role' => 'master',
            'status' => Invitation::ACCEPTED,
            'driver_active' => true,
        ]);
        $newContact = $this->invite('pending-rollback@example.test');

        $migration = require dirname(__DIR__, 2) . '/database/migrations/2026_10_03_010000_extend_invitations_for_delivery_driver_onboarding.php';
        try {
            $migration->down();
            self::fail('Rollback must fail closed while null-user contact invitations remain.');
        } catch (\RuntimeException $exception) {
            self::assertStringContainsString('null user_id', $exception->getMessage());
        }
        self::assertTrue(Invitation::query()->whereKey($newContact['invitation']->id)->exists());
        self::assertTrue(Invitation::query()->whereKey($legacy->id)->exists());

        $newContact['invitation']->delete();
        $migration->down();
        self::assertTrue(Invitation::query()->whereKey($legacy->id)->exists());
        self::assertTrue(Schema::hasColumn('invitations', 'user_id'));
        self::assertFalse(Schema::hasColumn('invitations', 'driver_contact_verified_at'));
        self::assertNotEmpty(\Illuminate\Support\Facades\DB::select('PRAGMA foreign_key_list(invitations)'));
    }

    private function invite(string $email, int $shopId = self::SHOP_A): array
    {
        return $this->service->create($shopId, self::VENDOR, ['email' => $email, 'firstname' => 'Driver']);
    }

    private function markNativeContactVerified(User $user): void
    {
        $contacts = $this->service->lockNativeContactInvitations($user);
        $this->service->markNativeContactVerified($user, $contacts);
    }

    /** @return array{0: User, 1: Invitation} */
    private function acceptUserForShop(string $email, int $shopId): array
    {
        $user = $this->createUser($email, ['user'], true);
        $created = $this->invite($email, $shopId);
        $this->service->accept($user, $created['token']);

        return [$user, $created['invitation']->fresh()];
    }

    private function createUser(string $email, array $roles, bool $verified): User
    {
        $user = User::query()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'firstname' => 'Fixture',
            'lastname' => 'User',
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make('fixture-password'),
            'active' => true,
            'email_verified_at' => $verified ? now() : null,
            'verify_token' => $verified ? null : '123456',
        ]);
        foreach ($roles as $role) {
            if (!Role::query()->where('name', $role)->exists()) {
                Role::query()->create(['name' => $role, 'guard_name' => 'sanctum']);
            }
            $user->assignRole($role);
        }

        return $user->fresh();
    }

    private function orderService(): \App\Services\OrderService\OrderService
    {
        return new class extends \App\Services\OrderService\OrderService {
            public function sendNotification(
                mixed $model = null,
                array|null $receivers = [],
                string|int|null $message = '',
                string|int|null $title = null,
                mixed $data = [],
                array $userIds = [],
            ): void {
            }
        };
    }

    private function authenticateAs(?User $user): void
    {
        $guard = \Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($user?->id);
        $auth = \Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('auth');
    }

    private function configureRoles(): void
    {
        $this->app['config']->set('auth.defaults.guard', 'sanctum');
        $this->app['config']->set('auth.guards.sanctum', ['driver' => 'sanctum', 'provider' => 'users']);
        $this->app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);
        $this->app['config']->set('permission', [
            'models' => [
                'permission' => \Spatie\Permission\Models\Permission::class,
                'role' => Role::class,
            ],
            'table_names' => [
                'roles' => 'roles',
                'model_has_roles' => 'model_has_roles',
                'model_has_permissions' => 'model_has_permissions',
                'role_has_permissions' => 'role_has_permissions',
                'permissions' => 'permissions',
            ],
            'column_names' => [
                'role_pivot_key' => 'role_id',
                'permission_pivot_key' => 'permission_id',
                'model_morph_key' => 'model_id',
            ],
            'teams' => false,
            'cache' => ['expiration_time' => 3600, 'key' => 'hardening.driver-phase2', 'store' => 'array'],
            'events_enabled' => false,
        ]);
        $this->app->singleton(PermissionRegistrar::class);
    }

    private function createLegacySchema(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->string('locale');
            $table->boolean('default')->default(false);
        });
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->boolean('default')->default(false);
        });
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('verify_token')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->default(true);
            $table->string('lang')->nullable();
            $table->longText('firebase_token')->nullable();
            $table->timestamps();
        });
        Schema::create('shops', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('uuid')->nullable();
        });
        Schema::create('shop_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->nullable();
            $table->string('title')->nullable();
        });
        Schema::create('invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable();
            $table->string('role')->nullable();
            $table->unsignedTinyInteger('status')->default(Invitation::NEW);
            $table->boolean('driver_active')->default(true);
            $table->unsignedBigInteger('shop_role_id')->nullable();
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });
        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('deliveryman_id')->nullable();
            $table->string('delivery_type')->default(Order::DELIVERY);
            $table->string('status')->nullable();
            $table->timestamps();
        });
        Schema::create('deliveryman_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('email_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
        });
        Schema::create('notification_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('active')->default(true);
        });
        Schema::create('wallets', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    private function runOnboardingMigration(): void
    {
        $migration = require dirname(__DIR__, 2) . '/database/migrations/2026_10_03_010000_extend_invitations_for_delivery_driver_onboarding.php';
        $migration->up();
    }

    private function seedBaseRows(): void
    {
        $this->database->table('languages')->insert(['locale' => 'en', 'default' => true]);
        $this->database->table('currencies')->insert(['default' => true]);
        $ownerA = $this->createUser('vendor-a@example.test', ['seller'], true);
        $ownerB = $this->createUser('vendor-b@example.test', ['seller'], true);
        $this->database->table('shops')->insert([
            ['id' => self::SHOP_A, 'user_id' => $ownerA->id, 'title' => 'Shop A', 'uuid' => 'shop-a'],
            ['id' => self::SHOP_B, 'user_id' => $ownerB->id, 'title' => 'Shop B', 'uuid' => 'shop-b'],
        ]);
        $this->database->table('shop_translations')->insert([
            ['shop_id' => self::SHOP_A, 'locale' => 'en', 'title' => 'Shop A'],
            ['shop_id' => self::SHOP_B, 'locale' => 'en', 'title' => 'Shop B'],
        ]);
        $this->createUser('creator@example.test', ['seller'], true);
    }

    private function seedRoles(): void
    {
        foreach (['user', 'seller', 'admin', 'master', 'moderator', 'deliveryman'] as $name) {
            Role::query()->create(['name' => $name, 'guard_name' => 'sanctum']);
        }
    }
}