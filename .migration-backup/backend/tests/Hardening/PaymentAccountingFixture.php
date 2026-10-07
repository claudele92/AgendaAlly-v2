<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentAccounting\AllocationWriter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class PaymentAccountingFixture extends IsolatedTestCase
{
    protected AllocationWriter $writer;
    protected string $checkout;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() === 'sqlite') DB::statement('PRAGMA foreign_keys=ON');
        foreach (['users','countries','currencies','payments'] as $table) {
            Schema::create($table, fn (Blueprint $t) => $t->id());
            DB::table($table)->insert(['id' => 1]);
        }
        Schema::table('payments', function (Blueprint $t): void {
            $t->string('tag')->nullable(); $t->boolean('active')->default(true);
        });
        DB::table('users')->insert(['id' => 2]);
        Schema::create('shops', function (Blueprint $t): void {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('country_id');
        });
        DB::table('shops')->insert(['id' => 1, 'user_id' => 1, 'country_id' => 1]);
        Schema::create('orders', function (Blueprint $t): void {
            $t->id(); $t->unsignedBigInteger('cart_id'); $t->unsignedBigInteger('shop_id');
            $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('currency_id');
        });
        Schema::create('bookings', fn (Blueprint $t) => $t->id());
        Schema::table('bookings', function (Blueprint $t): void {
            $t->unsignedBigInteger('parent_id')->nullable(); $t->unsignedBigInteger('shop_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable(); $t->unsignedBigInteger('currency_id')->nullable();
            $t->decimal('total_price',22,2)->nullable(); $t->decimal('rate',22,8)->default(1);
        });
        Schema::create('transactions', function (Blueprint $t): void {
            $t->id(); $t->string('payable_type'); $t->unsignedBigInteger('payable_id'); $t->string('status');
            $t->unsignedBigInteger('payment_sys_id')->nullable(); $t->string('payment_trx_id')->nullable();
            $t->unsignedBigInteger('parent_id')->nullable(); $t->unsignedBigInteger('user_id')->nullable();
            $t->decimal('price',22,2)->nullable(); $t->text('note')->nullable();
            $t->timestamp('perform_time')->nullable(); $t->string('status_description')->nullable();
            $t->timestamp('refund_time')->nullable(); $t->timestamps();
        });
        (require __DIR__.'/../../database/migrations/2026_09_20_120000_create_platform_fee_ledger_entries_table.php')->up();
        (require __DIR__.'/../../database/migrations/2026_09_26_030000_add_entry_type_to_platform_fee_ledger_entries_table.php')->up();
        // This fixture owns Phase 2 accounting, not later provider protocols.
        foreach (['2026_10_03_100000_create_commerce_payment_allocations.php',
            '2026_10_03_100100_create_payment_collection_contexts.php',
            '2026_10_03_100200_link_payment_accounting_evidence.php'] as $name) {
            (require __DIR__.'/../../database/migrations/'.$name)->up();
        }
        $this->writer = new AllocationWriter;
        $this->checkout = (string) Str::uuid();
        $user = (new \App\Models\User)->forceFill(['id' => 2]);
        $user->setRelation('roles', collect([]));
        $user->setRelation('countryAdmin', (object) ['country_id' => null]);
        $guard = \Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(2);
        $guard->shouldReceive('check')->andReturn(true);
        $auth = \Mockery::mock(\Illuminate\Contracts\Auth\Factory::class);
        $auth->shouldReceive('guard')->andReturn($guard);
        $this->app->instance('auth',$auth);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('auth');
    }

    protected function quote(int $id = 1, int $gross = 10000, int $commission = 1000, int $shop = 1): array
    {
        return [
            'checkout_key' => $this->checkout, 'origin_type' => 'booking', 'origin_id' => $id,
            'shop_id' => $shop, 'vendor_user_id' => 1, 'payer_user_id' => 2, 'local_client_id' => null,
            'country_id' => 1, 'currency_id' => 1, 'currency_code' => 'USD', 'money_scale' => 2,
            'purpose' => 'base', 'obligation_key' => 'base', 'payable_type' => 'booking', 'payable_id' => $id,
            'gross_amount' => $gross, 'commission_amount' => $commission,
            'vendor_entitlement_amount' => $gross - $commission, 'adjustment_amount' => 0,
            'native_components' => ['authority' => 'isolated_native_fixture', 'service_fee_units' => (string) $commission],
            'policy_key' => 'platform_held_first_commission',
        ];
    }

    protected function evidence(string $mode, int $amount, string $slot = 'selected_method', int $shop = 1, ?string $event = null, ?int $cap = null): array
    {
        $electronic = in_array($mode, ['platform','vendor_direct'], true);
        $direct = in_array($mode, ['vendor_direct','offline'], true);
        $event ??= (string) Str::uuid();
        return [
            'funding_key' => $slot.':'.$event, 'funding_slot' => $slot, 'funding_event_key' => $event,
            'collection_mode' => $mode, 'custody_type' => $direct ? 'vendor' : 'platform',
            'expected_collector_type' => $direct ? 'shop' : 'platform', 'expected_collector_id' => $direct ? $shop : null,
            'credential_owner_type' => $electronic ? ($direct ? 'shop' : 'platform') : 'none',
            'credential_owner_id' => $electronic && $direct ? $shop : null,
            'payment_id' => 1, 'provider_tag' => $electronic ? 'synthetic_provider' : null,
            'configuration_source' => $electronic ? 'synthetic' : null,
            'configuration_reference' => $electronic ? 'config-fixture' : null,
            'configuration_revision' => $electronic ? 'original-revision' : null,
            'merchant_binding_reference' => $electronic ? 'safe-merchant-fixture' : null,
            'currency_id' => 1, 'currency_code' => 'USD', 'money_scale' => 2,
            'amount' => $amount, 'receipt_total_amount' => $cap ?? $amount,
        ];
    }

    protected function fund(int $id, array $legs): array
    {
        $ids = [];
        foreach ($legs as $index => [$mode, $amount]) {
            if ($amount === 0) continue;
            $evidence = $this->evidence($mode, $amount, $index === 0 && count($legs) > 1 ? 'wallet_contribution' : 'selected_method');
            $context = $this->writer->stage($id, $evidence);
            $this->writer->confirm([$context], 'verified:'.$evidence['funding_event_key'], $amount);
            $ids[] = $context;
        }
        return $ids;
    }
}