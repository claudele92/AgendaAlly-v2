<?php
declare(strict_types=1);
namespace Tests\Hardening;

final class BookingRefundAuthorizationTest extends BookingRefundFixture
{
    public static function permitted(): array
    { return [[1, 'seller', 'seller'], [4, 'shop_manager', 'seller'], [3, 'admin', 'admin'], [2, 'user', 'user'], [3, 'master', 'master']]; }
    /** @dataProvider permitted */
    public function test_native_permitted_actors_settle_once(int $id, string $role, string $route): void
    {
        $this->authenticate($id, $role);
        $response = $this->bookingRequest(['status' => 'canceled', 'price' => 999, 'user_id' => 3,
            'wallet_uuid' => 'wallet-3', 'shop_id' => 2], $route);
        self::assertSame(200, $response->getStatusCode(), $response->getContent());
        self::assertSame(75.0, $this->balances()[1]);
        $before = $this->persistedState();
        self::assertNotSame(200, $this->bookingRequest(['status' => 'canceled'], $route)->getStatusCode());
        self::assertSame($before, $this->persistedState());
    }
    public static function forbidden(): array
    { return [[3, 'seller', 'seller'], [5, 'shop_manager', 'seller'], [4, 'shop_manager', 'seller', 2],
        [2, 'user', 'seller'], [2, 'user', 'admin'], [null, 'user', 'seller']]; }
    /** @dataProvider forbidden */
    public function test_unauthorized_actors_do_not_mutate_finance(?int $id, string $role, string $route, int $booking = 1): void
    {
        $this->authenticate($id, $role);
        $before = $this->persistedState();
        self::assertNotSame(200, $this->bookingRequest(['status' => 'canceled', 'shop_id' => 1,
            'user_id' => 2, 'booking_id' => 1], $route, $booking)->getStatusCode());
        self::assertSame($before, $this->persistedState());
    }
}