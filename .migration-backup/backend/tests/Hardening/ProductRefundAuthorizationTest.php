<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\OrderRefund;
use App\Models\WalletHistory;

final class ProductRefundAuthorizationTest extends ProductRefundFixture
{
    public static function allowedActors(): array { return [[1, 'seller', false], [4, 'shop_manager', false], [3, 'admin', true]]; }

    /** @dataProvider allowedActors */
    public function test_native_owner_granted_staff_and_admin_use_the_same_terminal_settlement(int $user, string $role, bool $admin): void
    {
        $this->authenticate($user, $role);
        $response = $this->updateRefund(['status' => 'accepted'], $admin);
        self::assertSame(200, $response->getStatusCode(), $response->getContent());
        self::assertSame(1, WalletHistory::count());
        $before = $this->persistedState();
        foreach (['pending', 'accepted', 'canceled'] as $status) {
            self::assertNotSame(200, $this->updateRefund(['status' => $status, 'answer' => 'Fixture'], $admin)->getStatusCode());
            self::assertSame($before, $this->persistedState());
        }
    }

    public static function deniedActors(): array { return [[3, 'seller', false], [5, 'shop_manager', false], [2, 'user', false], [null, 'user', false], [1, 'seller', true]]; }

    /** @dataProvider deniedActors */
    public function test_foreign_shop_ungranted_staff_customer_anonymous_and_nonadmin_are_denied(int|null $user, string $role, bool $admin): void
    {
        $this->authenticate($user, $role);
        $before = $this->persistedState();
        self::assertNotSame(200, $this->updateRefund(['status' => 'accepted', 'shop_id' => 1,
            'user_id' => 1, 'order_id' => 1, 'wallet_uuid' => 'wallet-1'], $admin)->getStatusCode());
        self::assertSame($before, $this->persistedState());
        self::assertSame('pending', OrderRefund::find(1)->status);
    }

    public function test_forged_financial_and_ownership_fields_do_not_alter_http_or_service_settlement(): void
    {
        $forged = ['status' => 'accepted', 'price' => 999999, 'total_price' => -1,
            'order_id' => 2, 'shop_id' => 2, 'user_id' => 3, 'customer_id' => 3,
            'wallet_uuid' => 'wallet-3', 'wallet_id' => 3, 'created_by' => 3, 'id' => 2];
        self::assertSame(200, $this->updateRefund($forged)->getStatusCode());
        self::assertSame([100.0, 80.0, 80.0], $this->balances());
        self::assertSame(1, OrderRefund::find(1)->order_id);
        self::assertSame('wallet-2', WalletHistory::first()->wallet_uuid);
        self::assertSame(50.0, (float) WalletHistory::first()->price);
        $before = $this->persistedState();
        self::assertFalse($this->settle($forged)['status']);
        self::assertSame($before, $this->persistedState());
    }

    public function test_direct_service_payload_cannot_redirect_first_acceptance(): void
    {
        $stale = OrderRefund::find(1);
        $stale->order_id = 2;
        self::assertTrue($this->settle(['price' => 999999, 'order_id' => 2, 'user_id' => 3,
            'shop_id' => 2, 'wallet_uuid' => 'wallet-3'], $stale)['status']);
        self::assertSame([100.0, 80.0, 80.0], $this->balances());
        self::assertSame(1, OrderRefund::find(1)->order_id);
    }

    public static function invalidPayloads(): array
    {
        return [[['status' => 'rejected']], [['status' => ['accepted']]], [['status' => 'canceled']],
            [['status' => 'invalid']], [['price' => 50]], [['status' => null]]];
    }

    /** @dataProvider invalidPayloads */
    public function test_invalid_status_payload_has_no_financial_effect(array $data): void
    {
        $before = $this->persistedState();
        self::assertNotSame(200, $this->updateRefund($data)->getStatusCode());
        self::assertSame($before, $this->persistedState());
    }
}