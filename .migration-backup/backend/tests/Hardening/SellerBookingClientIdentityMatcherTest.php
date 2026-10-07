<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Support\SellerBookingClientIdentityMatcher;
use App\Models\SellerBookingClient;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class SellerBookingClientIdentityMatcherTest extends IsolatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance('files', new \Illuminate\Filesystem\Filesystem());
    }

    public function test_same_identity_matched_by_phone_and_email_is_reused_once(): void
    {
        $match = ['resource' => ['kind' => 'local', 'id' => 17]];

        self::assertSame(
            $match,
            SellerBookingClientIdentityMatcher::singleOrConflict(new Collection([$match, $match]))
        );
    }

    public function test_conflicting_contact_matches_are_rejected_instead_of_selecting_arbitrarily(): void
    {
        $this->expectException(ValidationException::class);

        SellerBookingClientIdentityMatcher::singleOrConflict(new Collection([
            ['resource' => ['kind' => 'registered', 'id' => 17]],
            ['resource' => ['kind' => 'local', 'id' => 24]],
        ]));
    }

    public function test_client_name_is_trimmed_and_whitespace_only_names_are_rejected(): void
    {
        self::assertSame('A client', SellerBookingClient::normalizeName('  A client  '));
        try {
            SellerBookingClient::normalizeName(" \t ");
            self::fail('A whitespace-only client name must be rejected.');
        } catch (ValidationException $error) {
            self::assertArrayHasKey('name', $error->errors());
        }
    }
}