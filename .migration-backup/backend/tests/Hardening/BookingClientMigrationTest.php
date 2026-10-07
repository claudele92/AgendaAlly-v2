<?php
declare(strict_types=1);

namespace Tests\Hardening;

use Illuminate\Database\Schema\Blueprint;

final class BookingClientMigrationTest extends IsolatedTestCase
{
    public function test_migration_preserves_existing_booking_and_user_foreign_key_while_enabling_walk_ins(): void
    {
        $schema = $this->database->schema('hardening');
        $schema->create('users', fn (Blueprint $table) => $table->id());
        $schema->create('shops', fn (Blueprint $table) => $table->id());
        $schema->create('shop_locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
        });
        $schema->create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
        });
        $this->database->table('users')->insert(['id' => 11]);
        $this->database->table('shops')->insert(['id' => 21]);
        $this->database->table('shop_locations')->insert(['id' => 31, 'shop_id' => 21]);
        $this->database->table('bookings')->insert(['id' => 41, 'user_id' => 11]);

        $migration = require dirname(__DIR__, 2) . '/database/migrations/2026_09_28_010000_add_shop_scoped_booking_clients.php';
        $migration->up();

        self::assertSame(11, (int) $this->database->table('bookings')->where('id', 41)->value('user_id'));
        self::assertTrue($schema->hasColumn('bookings', 'local_client_id'));
        self::assertTrue($schema->getColumnType('bookings', 'user_id') === 'integer');

        $userColumn = collect($this->database->getConnection()->select('PRAGMA table_info(bookings)'))
            ->first(fn ($column) => $column->name === 'user_id');
        self::assertSame(0, (int) $userColumn->notnull, 'user_id must be nullable for local clients.');

        $foreignKeys = collect($this->database->getConnection()->select('PRAGMA foreign_key_list(bookings)'));
        self::assertTrue(
            $foreignKeys->contains(fn ($key) => $key->from === 'user_id' && $key->table === 'users'),
            'The existing bookings.user_id foreign key must be restored.'
        );
        self::assertTrue(
            $foreignKeys->contains(fn ($key) => $key->from === 'local_client_id'
                && $key->table === 'seller_booking_clients'),
            'The new local-client reference must be constrained.'
        );

        $this->database->table('seller_booking_clients')->insert([
            'shop_id' => 21,
            'shop_location_id' => 31,
            'name' => 'Walk-in',
            'dedupe_scope' => 'location:31',
            'normalized_phone' => '+13125551212',
        ]);
        try {
            $this->database->table('seller_booking_clients')->insert([
                'shop_id' => 21,
                'shop_location_id' => 31,
                'name' => 'Duplicate walk-in',
                'dedupe_scope' => 'location:31',
                'normalized_phone' => '+13125551212',
            ]);
            self::fail('Branch-scoped phone uniqueness must reject duplicate clients.');
        } catch (\Illuminate\Database\QueryException) {
            self::assertSame(1, $this->database->table('seller_booking_clients')->count());
        }
        $this->database->table('bookings')->insert([
            'id' => 42,
            'user_id' => null,
            'local_client_id' => 1,
        ]);
        self::assertNull($this->database->table('bookings')->where('id', 42)->value('user_id'));
        self::assertSame(1, (int) $this->database->table('bookings')->where('id', 42)->value('local_client_id'));
    }
}