<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Booking;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\DataProvider;

final class BookingStaffAuthorizationTest extends BookingStaffFixture
{
    public static function roles(): array
    {
        return ['moderator' => ['moderator'], 'shop manager' => ['shop_manager']];
    }

    public static function destinations(): array
    {
        $cases = [];
        foreach (['moderator', 'shop_manager'] as $role) {
            foreach (array_values(Booking::STATUSES) as $status) {
                $cases["{$role} {$status}"] = [$role, $status];
            }
        }
        return $cases;
    }

    #[DataProvider('destinations')]
    public function test_every_foreign_same_country_destination_is_denied_before_lifecycle(string $role, string $status): void
    {
        $this->staffActor(300, [$role], 10);
        foreach ([[], ['shop_id' => 1], ['seller_id' => 100, 'vendor_id' => 100],
            ['moderator_shop_id' => 1, 'branch_id' => 1, 'master_id' => 999]] as $forgery) {
            $before = $this->fingerprint();
            $response = $this->requestBooking(12, ['status' => $status] + $forgery);
            self::assertSame(404, $response->getStatusCode(), $response->getContent());
            self::assertSame('ERROR_404', json_decode($response->getContent(), true)['statusCode']);
            self::assertSame($before, $this->fingerprint(), 'Denied request entered lifecycle or changed state');
        }
    }

    #[DataProvider('destinations')]
    public function test_native_own_shop_destinations_remain_reachable(string $role, string $status): void
    {
        $this->staffActor(300, [$role]);
        $this->database->table('bookings')->where('id', 11)->update(['status' => $status === 'booked' ? 'new' : 'booked']);
        $response = $this->requestBooking(11, ['status' => $status]);
        self::assertSame(200, $response->getStatusCode(), $response->getContent());
        self::assertSame($status, $this->database->table('bookings')->where('id', 11)->value('status'));
        self::assertSame(1, $this->lifecycle->entries);
        self::assertSame('booked', $this->database->table('bookings')->where('id', 12)->value('status'));
    }

    #[DataProvider('roles')]
    public function test_authorized_cancellation_uses_real_wallet_points_activity_and_payable_adjustment(string $role): void
    {
        $this->staffActor(300, [$role]);
        $response = $this->requestBooking(11, ['status' => 'canceled']);
        self::assertSame(200, $response->getStatusCode(), $response->getContent());
        self::assertSame('canceled', $this->database->table('bookings')->where('id', 11)->value('status'));
        self::assertSame(58.0, (float) $this->database->table('wallets')->where('user_id', 500)->value('price'));
        self::assertSame(100.0, (float) $this->database->table('wallets')->where('user_id', 501)->value('price'));
        self::assertSame(1, $this->database->table('booking_activities')->where('booking_id', 11)->count());
        self::assertSame(0, $this->database->table('point_histories')->where('model_id', 11)->count());
        self::assertSame(1, $this->database->table('point_histories')->where('model_id', 12)->count());
        $ledger = $this->database->table('platform_fee_ledger_entries')->where('entry_type', 'payable_adjustment');
        self::assertSame(1, $ledger->count());
        self::assertSame(-33.0, (float) $ledger->where('payable_id', 11)->value('amount'));
        self::assertSame('paid', $this->database->table('transactions')->where('id', 3)->value('status'));
    }

    #[DataProvider('roles')]
    public function test_relationship_and_permission_failures_do_not_enter_lifecycle(string $role): void
    {
        // 999 is now the valid assigned specialist, not a missing user.
        foreach ([[301, 403], [302, 403], [303, 404], [999999, 401]] as [$actor, $http]) {
            $this->staffActor($actor, [$role]);
            $before = $this->fingerprint();
            self::assertSame($http, $this->requestBooking(11, ['status' => 'canceled'])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
        $this->database->table('invitations')->where('user_id', 300)->delete();
        $this->staffActor(300, [$role]);
        $before = $this->fingerprint();
        self::assertSame(401, $this->requestBooking(11, ['status' => 'canceled'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
    }

    #[DataProvider('roles')]
    public function test_revoked_grant_and_relationship_are_denied(string $role): void
    {
        $this->database->table('shop_role_permissions')->where('shop_role_id', 1)->where('shop_permission_id', 4)->delete();
        $this->staffActor(300, [$role]);
        $before = $this->fingerprint();
        self::assertSame(403, $this->requestBooking(11, ['status' => 'canceled'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        $this->database->table('shop_role_permissions')->insert(['shop_role_id' => 1, 'shop_permission_id' => 4]);
        foreach ([\App\Models\Invitation::REJECTED, \App\Models\Invitation::CANCELED] as $status) {
            $this->database->table('invitations')->where('user_id', 300)->update(['status' => $status]);
            $this->staffActor(300, [$role]);
            $before = $this->fingerprint();
            self::assertSame(403, $this->requestBooking(11, ['status' => 'progress'])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
    }

    #[DataProvider('roles')]
    public function test_missing_and_inconsistent_ownership_is_non_disclosing(string $role): void
    {
        $this->staffActor(300, [$role]);
        $foreign = $this->requestBooking(12, ['status' => 'canceled']);
        $missing = $this->requestBooking(999999, ['status' => 'canceled']);
        self::assertSame(404, $missing->getStatusCode());
        $a = json_decode($foreign->getContent(), true); $b = json_decode($missing->getContent(), true);
        unset($a['timestamp'], $b['timestamp']);
        self::assertSame($a, $b);
        // Staff ownership still validates Booking.service alongside the
        // current assignment used by capacity; preserve both real contracts.
        foreach ([['shop_id' => null], ['shop_id' => 999999], ['shop_id' => 1, 'service_id' => 102],
            ['shop_id' => 1, 'service_id' => 999999]] as $broken) {
            $this->database->table('bookings')->where('id', 11)->update($broken);
            $before = $this->fingerprint();
            self::assertSame(404, $this->requestBooking(11, ['status' => 'canceled'])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
        $this->database->table('bookings')->where('id', 11)->update(['shop_id' => 1, 'service_id' => 101]);
        $this->database->table('shops')->where('id', 1)->delete();
        // Retain constructor context to exercise the target policy after a
        // persisted Shop deletion, without an unrelated middleware denial.
        $actor = auth('sanctum')->user();
        $actor->setRelation('moderatorShop', (new \App\Models\Shop())->forceFill(['id' => 1]));
        $before = $this->fingerprint();
        self::assertSame(404, $this->requestBooking(11, ['status' => 'canceled'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
    }

    public function test_anonymous_and_invalid_status_are_denied_without_effects(): void
    {
        $this->staffActor(null, []);
        $before = $this->fingerprint();
        self::assertSame(401, $this->requestBooking(11, ['status' => 'canceled'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        $this->staffActor(300, ['moderator']);
        foreach (['paid', 'refund', 'unknown'] as $status) {
            $before = $this->fingerprint();
            self::assertSame(422, $this->requestBooking(11, ['status' => $status])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
    }

    public function test_seller_and_staff_seller_owner_combinations_retain_native_assignment(): void
    {
        foreach ([['seller'], ['seller', 'moderator'], ['moderator', 'seller']] as $roles) {
            $this->staffActor(100, $roles);
            $before = $this->fingerprint();
            self::assertSame(count($roles) === 1 ? 400 : 404, $this->requestBooking(12, ['status' => 'progress'])->getStatusCode());
            $after = $this->fingerprint();
            // Pure Seller's existing assignment denial remains in the shared
            // service; Staff combinations are rejected before service entry.
            unset($before['_lifecycle_entries'], $after['_lifecycle_entries']);
            self::assertSame($before, $after);
            self::assertSame(200, $this->requestBooking(11, ['status' => 'progress'])->getStatusCode());
            $this->database->table('bookings')->where('id', 11)->update(['status' => 'booked']);
        }
    }

    public function test_admin_and_staff_admin_combinations_keep_global_authority_and_country_scope(): void
    {
        foreach ([['admin'], ['admin', 'moderator'], ['moderator', 'admin'], ['shop_manager', 'admin']] as $roles) {
            $this->staffActor(100, $roles);
            self::assertSame(200, $this->requestBooking(12, ['status' => 'progress'])->getStatusCode());
            $this->database->table('bookings')->where('id', 12)->update(['status' => 'booked']);
        }
        $this->database->table('shop_locations')->where('shop_id', 2)->update(['country_id' => 20]);
        $this->staffActor(100, ['admin'], 10);
        $before = $this->fingerprint();
        self::assertSame(400, $this->requestBooking(12, ['status' => 'progress'])->getStatusCode());
        $after = $this->fingerprint();
        unset($before['_lifecycle_entries'], $after['_lifecycle_entries']);
        self::assertSame($before, $after);
    }

    public function test_shared_customer_and_master_paths_are_not_given_seller_shop_requirements(): void
    {
        foreach ([[500, ['user']], [999, ['master']], [500, ['user', 'moderator']],
            [999, ['master', 'shop_manager']], [999, ['admin']]] as [$actor, $roles]) {
            $this->staffActor($actor, $roles);
            $this->app->instance('request', \Illuminate\Http\Request::create('/api/v1/dashboard/user/bookings/11/status/update', 'POST'));
            Facade::clearResolvedInstance('request');
            self::assertSame('progress', $this->lifecycle->statusUpdate(11, ['status' => 'progress'])->status);
            $this->database->table('bookings')->where('id', 11)->update(['status' => 'booked']);
        }
    }

    public function test_native_seller_customer_fallback_and_shared_assignment_denials_are_preserved(): void
    {
        $this->database->table('bookings')->where('id', 12)->update(['user_id' => 100]);
        $this->staffActor(100, ['seller']);
        $response = $this->requestBooking(12, ['status' => 'progress']);
        self::assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->database->table('bookings')->where('id', 12)->update([
            'user_id' => 501, 'master_id' => 100, 'status' => 'booked',
        ]);
        foreach ([[500, ['user']], [999, ['master']]] as [$actor, $roles]) {
            $this->staffActor($actor, $roles);
            $before = $this->fingerprint();
            try {
                $this->lifecycle->statusUpdate(12, ['status' => 'canceled']);
                self::fail('Native Customer/Master assignment denial was bypassed');
            } catch (\Exception $e) {
                self::assertSame(
                    __('errors.'.\App\Helpers\ResponseError::ERROR_404, locale: 'en'),
                    $e->getMessage()
                );
            }
            $after = $this->fingerprint();
            unset($before['_lifecycle_entries'], $after['_lifecycle_entries']);
            self::assertSame($before, $after);
        }
    }

    #[DataProvider('roles')]
    public function test_staff_multi_role_operational_requests_remain_target_bound(string $role): void
    {
        foreach ([[$role, 'user'], ['user', $role], [$role, 'master'], ['master', $role]] as $roles) {
            // Make the Staff actor the foreign Booking's Customer/Master:
            // that other authority must not silently switch operational Shop.
            $this->database->table('bookings')->where('id', 12)->update(['user_id' => 300, 'master_id' => 300]);
            $this->staffActor(300, $roles);
            $before = $this->fingerprint();
            // CheckSellerShop uses the native last-role accessor; combinations
            // ending in user/master are already rejected there (401).
            $expected = in_array(end($roles), ['moderator', 'shop_manager']) ? 404 : 401;
            self::assertSame($expected, $this->requestBooking(12, ['status' => 'canceled'])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
            $this->database->table('bookings')->where('id', 11)->update(['user_id' => 300, 'master_id' => 300]);
            $response = $this->requestBooking(11, ['status' => 'progress']);
            self::assertSame($expected === 401 ? 401 : 200, $response->getStatusCode(), $response->getContent());
            $this->database->table('bookings')->where('id', 11)->update(['status' => 'booked']);
        }
    }

    #[DataProvider('roles')]
    public function test_adjacent_extra_time_financial_records_are_target_bound(string $role): void
    {
        $this->staffActor(300, [$role]);
        $data = ['price' => 7.5, 'duration' => 10, 'duration_type' => 'minute', 'shop_id' => 1];
        $before = $this->fingerprint();
        self::assertSame(404, $this->requestBooking(12, $data, 'extra-time')->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        $response = $this->requestBooking(11, $data, 'extra-time');
        self::assertSame(200, $response->getStatusCode(), $response->getContent());
        self::assertSame(7.5, (float) $this->database->table('booking_extra_times')->where('booking_id', 11)->value('price'));
    }

    #[DataProvider('roles')]
    public function test_bulk_deletion_rejects_the_entire_foreign_or_mixed_batch_before_mutation(string $role): void
    {
        foreach ([[300, [$role]], [100, ['seller']]] as [$actor, $roles]) {
            $this->staffActor($actor, $roles);
            foreach ([[12], [11, 12]] as $ids) {
                $before = $this->fingerprint();
                self::assertSame(404, $this->requestBooking(0, ['ids' => $ids, 'shop_id' => 2], 'delete')->getStatusCode());
                self::assertSame($before, $this->fingerprint());
            }
        }
        $this->staffActor(300, [$role]);
        self::assertSame(200, $this->requestBooking(0, ['ids' => [11]], 'delete')->getStatusCode());
        self::assertSame(0, $this->database->table('bookings')->where('id', 11)->count());
        self::assertSame(1, $this->database->table('bookings')->where('id', 12)->count());
    }
}