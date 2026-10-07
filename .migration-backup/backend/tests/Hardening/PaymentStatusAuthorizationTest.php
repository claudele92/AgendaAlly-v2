<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Transaction;
use PHPUnit\Framework\Attributes\DataProvider;

final class PaymentStatusAuthorizationTest extends PaymentStatusFixture
{
    public static function cashOperations(): array
    {
        return [
            'order paid' => ['order', 1, 2, 1, 'paid'],
            'order canceled' => ['order', 1, 2, 1, 'canceled'],
            'booking paid' => ['booking', 11, 12, 3, 'paid'],
            'booking canceled' => ['booking', 11, 12, 3, 'canceled'],
        ];
    }

    #[DataProvider('cashOperations')]
    public function test_owned_cash_operation_and_real_observer_are_preserved(
        string $type, int $own, int $foreign, int $transaction, string $status
    ): void {
        $response = $this->dispatchStatus($type, $own, ['status' => $status]);
        self::assertSame(200, $response->getStatusCode(), $response->getContent());
        self::assertTrue(json_decode($response->getContent(), true)['status']);
        self::assertSame($status, $this->database->table('transactions')->where('id', $transaction)->value('status'));
        $ledger = $this->database->table('platform_fee_ledger_entries');
        self::assertSame($status === 'paid' ? ($type === 'booking' ? 2 : 1) : 0, $ledger->count());
        if ($status === 'paid') {
            self::assertSame(1, $ledger->where('transaction_id', $transaction)->where('entry_type', 'fee')->count());
        }
        self::assertSame('progress', $this->database->table('transactions')->where('id', $type === 'order' ? 2 : 4)->value('status'));
    }

    #[DataProvider('cashOperations')]
    public function test_cross_shop_and_every_forged_identifier_are_denied_without_any_database_effect(
        string $type, int $own, int $foreign, int $transaction, string $status
    ): void {
        foreach ([
            [],
            ['shop_id' => 1],
            ['vendor_id' => 100, 'seller_id' => 100],
            ['owner_id' => 100, 'order_id' => 1, 'booking_id' => 11],
            ['shop_id' => 1, 'token' => 'isolated-intent'],
        ] as $forgery) {
            $before = $this->fingerprint();
            $response = $this->dispatchStatus($type, $foreign, ['status' => $status] + $forgery);
            self::assertSame(404, $response->getStatusCode(), $response->getContent());
            self::assertFalse(json_decode($response->getContent(), true)['status']);
            self::assertSame($before, $this->fingerprint(), 'Denied request changed isolated financial/accounting state');
        }
    }

    public function test_anonymous_and_other_roles_are_rejected(): void
    {
        foreach ([[null, 'seller', 401], [999, 'user', 404], [300, 'moderator', 404], [999, 'manager', 404]] as [$actor, $role, $http]) {
            $this->authenticate($actor, $role);
            $before = $this->fingerprint();
            self::assertSame($http, $this->dispatchStatus('order', 1, ['status' => 'paid'])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
    }

    #[DataProvider('cashOperations')]
    public function test_accepted_staff_grant_is_required_and_valid_staff_are_preserved(
        string $type, int $own, int $foreign, int $transaction, string $status
    ): void {
        foreach ([301, 302, 303, 999] as $actor) {
            $this->authenticate($actor);
            $before = $this->fingerprint();
            self::assertSame(404, $this->dispatchStatus($type, $own, ['status' => $status])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
        $this->authenticate(300);
        $before = $this->fingerprint();
        self::assertSame(404, $this->dispatchStatus($type, $foreign, ['status' => $status])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame(200, $this->dispatchStatus($type, $own, ['status' => $status])->getStatusCode());
        self::assertSame($status, $this->database->table('transactions')->where('id', $transaction)->value('status'));
    }

    public function test_missing_payables_shops_services_and_conflicting_booking_ownership_fail_closed(): void
    {
        foreach (['paid', 'canceled'] as $status) {
            $before = $this->fingerprint();
            self::assertSame(404, $this->dispatchStatus('order', 99999, ['status' => $status])->getStatusCode());
            self::assertSame(404, $this->dispatchStatus('booking', 99999, ['status' => $status])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
        foreach ([null, 99999] as $shop) {
            $this->database->table('orders')->where('id', 1)->update(['shop_id' => $shop]);
            $before = $this->fingerprint();
            foreach (['paid', 'canceled'] as $status) {
                self::assertSame(404, $this->dispatchStatus('order', 1, ['status' => $status])->getStatusCode());
                self::assertSame($before, $this->fingerprint());
            }
        }
        foreach ([['shop_id' => null], ['shop_id' => 99999], ['shop_id' => 1, 'service_id' => 102],
            ['shop_id' => 1, 'service_id' => 99999]] as $broken) {
            $this->database->table('bookings')->where('id', 11)->update($broken);
            $before = $this->fingerprint();
            foreach (['paid', 'canceled'] as $status) {
                self::assertSame(404, $this->dispatchStatus('booking', 11, ['status' => $status])->getStatusCode());
                self::assertSame($before, $this->fingerprint());
            }
        }
    }

    public function test_unsupported_types_do_not_fall_back_to_order_authority(): void
    {
        foreach (['made-up', 'auction-user', 'ORDER'] as $type) {
            foreach (['paid', 'canceled'] as $status) {
                $before = $this->fingerprint();
                self::assertSame(404, $this->dispatchStatus($type, 1, ['status' => $status])->getStatusCode());
                self::assertSame($before, $this->fingerprint());
            }
        }
        foreach (['wallet' => \App\Models\Wallet::class, 'parcel-order' => \App\Models\ParcelOrder::class] as $type => $class) {
            $table = (new $class)->getTable();
            $this->database->table($table)->insert(['id' => 1, 'user_id' => 100]);
            $this->database->table('transactions')->insert([
                'payable_type' => $class, 'payable_id' => 1, 'payment_sys_id' => 1, 'status' => 'progress',
            ]);
            foreach (['paid', 'canceled'] as $status) {
                $before = $this->fingerprint();
                self::assertSame(404, $this->dispatchStatus($type, 1, ['status' => $status])->getStatusCode());
                self::assertSame($before, $this->fingerprint());
            }
        }
    }

    public function test_admin_without_shop_membership_and_existing_country_scope_are_preserved(): void
    {
        $this->authenticate(999, 'admin');
        self::assertSame(200, $this->dispatchStatus('order', 2, ['status' => 'paid'])->getStatusCode());
        self::assertSame(200, $this->dispatchStatus('booking', 12, ['status' => 'canceled'])->getStatusCode());
        $this->authenticate(999, 'admin', 10);
        $before = $this->fingerprint();
        self::assertSame(404, $this->dispatchStatus('order', 2, ['status' => 'canceled'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame(200, $this->dispatchStatus('order', 1, ['status' => 'paid'])->getStatusCode());
    }

    public function test_all_other_supported_shop_payables_use_their_canonical_relationships(): void
    {
        $types = [
            'subscription' => [\App\Models\ShopSubscription::class, null],
            'ads' => [\App\Models\ShopAdsPackage::class, null],
            'ads-package' => [\App\Models\ShopAdsPackage::class, null],
            'member-ship' => [\App\Models\UserMemberShip::class, 'member_ship_id'],
            'gift-cart' => [\App\Models\UserGiftCart::class, 'gift_cart_id'],
        ];
        foreach ($types as $type => [$class, $parentKey]) {
            $table = (new $class)->getTable();
            foreach ([20 => 1, 21 => 2] as $id => $shop) {
                if ($parentKey !== null) {
                    $parentTable = $parentKey === 'member_ship_id' ? 'member_ships' : 'gift_carts';
                    $this->database->table($parentTable)->updateOrInsert(['id' => $id], ['shop_id' => $shop]);
                }
                $this->database->table($table)->updateOrInsert(['id' => $id], [
                    $parentKey ?? 'shop_id' => $parentKey ? $id : $shop,
                ]);
                $this->database->table('transactions')->updateOrInsert(
                    ['payable_type' => $class, 'payable_id' => $id],
                    ['payment_sys_id' => 1, 'status' => 'progress']
                );
            }
            foreach (['paid', 'canceled'] as $status) {
                $before = $this->fingerprint();
                self::assertSame(404, $this->dispatchStatus($type, 21, ['status' => $status, 'shop_id' => 1])->getStatusCode());
                self::assertSame($before, $this->fingerprint());
                self::assertSame(200, $this->dispatchStatus($type, 20, ['status' => $status])->getStatusCode());
            }
            $this->database->table($table)->where('id', 20)->update([$parentKey ?? 'shop_id' => 99999]);
            $before = $this->fingerprint();
            self::assertSame(404, $this->dispatchStatus($type, 20, ['status' => 'paid'])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
    }

    public function test_booking_without_optional_service_still_uses_its_native_persisted_shop(): void
    {
        $this->database->table('bookings')->where('id', 11)->update(['service_id' => null]);
        self::assertSame(200, $this->dispatchStatus('booking', 11, ['status' => 'paid'])->getStatusCode());
        self::assertSame('paid', $this->database->table('transactions')->where('id', 3)->value('status'));
    }

    public function test_missing_transaction_and_ownerless_shop_cannot_reach_mutation(): void
    {
        $this->database->table('orders')->insert(['id' => 5, 'shop_id' => 1]);
        $before = $this->fingerprint();
        self::assertSame(400, $this->dispatchStatus('order', 5, ['status' => 'paid'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        $this->database->table('shops')->where('id', 1)->update(['user_id' => null]);
        foreach (['paid', 'canceled'] as $status) {
            $before = $this->fingerprint();
            self::assertSame(404, $this->dispatchStatus('order', 1, ['status' => $status])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
    }

    public function test_revoked_payment_grant_is_not_replaced_by_global_seller_role(): void
    {
        $this->authenticate(300);
        $this->database->table('shop_role_permissions')->where('shop_role_id', 1)
            ->where('shop_permission_id', 1)->delete();
        foreach ([['order', 1], ['booking', 11]] as [$type, $id]) {
            foreach (['paid', 'canceled'] as $status) {
                $before = $this->fingerprint();
                self::assertSame(404, $this->dispatchStatus($type, $id, ['status' => $status])->getStatusCode());
                self::assertSame($before, $this->fingerprint());
            }
        }
    }

    public function test_invalid_destinations_and_existing_non_cash_restrictions_remain_denied(): void
    {
        foreach (['progress', 'refund', 'rejected', 'unknown', ''] as $status) {
            $before = $this->fingerprint();
            self::assertSame(422, $this->dispatchStatus('order', 1, ['status' => $status])->getStatusCode());
            self::assertSame($before, $this->fingerprint());
        }
        $this->database->table('transactions')->where('id', 1)->update(['payment_sys_id' => 2]);
        $before = $this->fingerprint();
        self::assertSame(404, $this->dispatchStatus('order', 1, [
            'status' => 'paid', 'reason' => 'isolated test', 'token' => 'isolated-intent',
        ])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        $this->authenticate(999, 'admin');
        self::assertSame(400, $this->dispatchStatus('order', 1, ['status' => 'paid', 'token' => 'isolated-intent'])->getStatusCode());
        self::assertSame(400, $this->dispatchStatus('order', 1, ['status' => 'paid', 'reason' => 'isolated test'])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame(200, $this->dispatchStatus('order', 1, [
            'status' => 'paid', 'reason' => 'isolated test', 'token' => 'isolated-intent',
        ])->getStatusCode());
        self::assertStringContainsString('manual override', $this->database->table('transactions')->where('id', 1)->value('note'));
    }

    public function test_native_cash_paid_to_canceled_contract_is_not_replaced_with_new_transition_rules(): void
    {
        self::assertSame(200, $this->dispatchStatus('order', 1, ['status' => 'paid'])->getStatusCode());
        self::assertSame(200, $this->dispatchStatus('order', 1, ['status' => 'canceled'])->getStatusCode());
        self::assertSame('canceled', $this->database->table('transactions')->where('id', 1)->value('status'));
    }
}