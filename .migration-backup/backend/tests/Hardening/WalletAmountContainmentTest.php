<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\User\WalletController;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\Payment\PaymentRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletHistory;
use App\Rules\PositiveWalletAmount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\DataProvider;

final class WalletAmountContainmentTest extends WalletTransferFixture
{
    private static function invalidAmounts(): array
    {
        return [
            'negative integer' => -1, 'negative decimal' => -0.01,
            'integer zero' => 0, 'decimal zero' => 0.00,
            'negative integer string' => '-1', 'negative decimal string' => '-0.01',
            'zero string' => '0', 'decimal zero string' => '0.00',
            'signed zero string' => '-0.00', 'plus zero' => '+0',
            'negative exponent' => '-1e2', 'exponent zero' => '0e10',
            'underflow' => '1e-999', 'overflow' => '1e999',
            'negative overflow' => '-1e999', 'whitespace negative' => ' -0.01 ',
            'not numeric' => 'NaN', 'infinity string' => 'INF',
            'infinity float' => INF, 'nan float' => NAN,
            'boolean' => true, 'array' => [20], 'null' => null,
            'empty' => '',
        ];
    }

    public static function invalidRequests(): array
    {
        $cases = [];
        foreach (['withdraw', 'send'] as $route) {
            foreach (self::invalidAmounts() as $label => $amount) {
                $cases["$route: $label"] = [$route, $amount];
            }
            $cases["$route: missing"] = [$route, null, true];
        }
        return $cases;
    }

    #[DataProvider('invalidRequests')]
    public function test_request_rejection_precedes_all_financial_effects(string $route, mixed $amount, bool $missing = false): void
    {
        $input = ['currency_id' => 1, 'uuid' => 'user-2'];
        if (!$missing) $input['price'] = $amount;
        $before = $this->fingerprint();
        self::assertSame(422, $this->request($route, $input)->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame([100.0, 30.0, 80.0], $this->balances());
        self::assertSame(0, WalletHistory::count());
        self::assertSame(0, Transaction::count());
        self::assertSame(0, $this->transactionEvents);
    }

    public static function invalidServiceAmounts(): array
    {
        $cases = [];
        foreach (['withdraw', 'topup'] as $type) {
            foreach (self::invalidAmounts() as $label => $amount) {
                $cases["$type: $label"] = [$type, $amount];
            }
        }
        return $cases;
    }

    #[DataProvider('invalidServiceAmounts')]
    public function test_shared_service_rejects_invalid_magnitudes_without_request_validation(string $type, mixed $amount): void
    {
        $before = $this->fingerprint();
        $result = $this->service->create([
            'price' => $amount, 'type' => $type, 'user' => User::findOrFail(2),
            'status' => WalletHistory::PAID,
        ]);
        self::assertFalse($result['status']);
        self::assertSame('ERROR_400', $result['code']);
        self::assertSame($before, $this->fingerprint());
        self::assertSame(0, WalletHistory::count());
        self::assertSame(0, Transaction::count());
        self::assertSame(0, $this->transactionEvents);
    }

    public static function positiveRequests(): array
    {
        $cases = [];
        foreach (['withdraw', 'send'] as $route) {
            foreach (['small' => 0.01, 'normal' => 20, 'decimal string' => '0.01',
                'normal string' => '20', 'positive exponent' => '2e1',
                'small exponent' => '1e-2', 'signed positive' => '+20',
                'whitespace positive' => ' 20 '] as $label => $amount) {
                $cases["$route: $label"] = [$route, $amount];
            }
        }
        return $cases;
    }

    #[DataProvider('positiveRequests')]
    public function test_native_positive_formats_preserve_authorized_direction(string $route, mixed $amount): void
    {
        $x = (float) $amount;
        self::assertSame(200, $this->request($route, [
            'price' => $amount, 'currency_id' => 1, 'uuid' => 'user-2',
        ])->getStatusCode());
        $balances = $this->balances();
        self::assertEqualsWithDelta(100 - $x, $balances[0], 1e-9);
        self::assertEqualsWithDelta($route === 'send' ? 30 + $x : 30, $balances[1], 1e-9);
        self::assertSame(80.0, $balances[2]);
        self::assertSame($route === 'send' ? 2 : 1, WalletHistory::count());
        self::assertSame($route === 'send' ? 2 : 1, Transaction::count());
        self::assertSame($route === 'send' ? ['paid', 'paid'] : ['processed'],
            WalletHistory::orderBy('id')->pluck('status')->all());
        self::assertSame($route === 'send' ? ['paid', 'paid'] : ['progress'],
            Transaction::orderBy('id')->pluck('status')->all());
        if ($route === 'send') {
            self::assertEqualsWithDelta(130, $balances[0] + $balances[1], 1e-9);
        }
    }

    public static function invalidNormalization(): array
    {
        return [
            'negative rate' => [-1, 20], 'zero rate' => [0, 20],
            'normalized underflow' => [1e308, '1e-308'],
            'normalized overflow' => [0.01, '1e308'],
        ];
    }

    #[DataProvider('invalidNormalization')]
    public function test_invalid_normalized_amount_is_rejected_before_either_leg(float $rate, mixed $amount): void
    {
        $this->database->table('currencies')->where('id', 2)->update(['rate' => $rate]);
        $before = $this->fingerprint();
        self::assertTrue(PositiveWalletAmount::accepts($amount));
        self::assertSame(400, $this->request('send', [
            'price' => $amount, 'currency_id' => 2, 'uuid' => 'user-2',
        ])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame(0, WalletHistory::count());
        self::assertSame(0, Transaction::count());
        self::assertSame(0, $this->transactionEvents);
    }

    public function test_direct_withdrawal_call_cannot_bypass_the_invariant(): void
    {
        $request = BaseRequest::create('/', 'POST', ['price' => -20]);
        $before = $this->fingerprint();
        $result = $this->app->make(WalletController::class)->withDraw($request);
        self::assertFalse($result['status']);
        self::assertSame($before, $this->fingerprint());
        self::assertSame(0, $this->transactionEvents);
    }

    public static function topupAmounts(): array
    {
        $cases = [];
        foreach (self::invalidAmounts() as $label => $amount) {
            $cases[$label] = [$amount, false];
        }
        foreach (['small positive' => 0.01, 'positive string' => '20', 'positive exponent' => '1e-2'] as $label => $amount) {
            $cases[$label] = [$amount, true];
        }
        return $cases;
    }

    #[DataProvider('topupAmounts')]
    public function test_wallet_topup_sibling_uses_positive_request_contract_without_provider_calls(mixed $amount, bool $valid): void
    {
        $input = ['wallet_id' => 1, 'total_price' => $amount];
        $this->app->instance('request', Request::create('/', 'POST', $input));
        Facade::clearResolvedInstance('request');
        $before = $this->fingerprint();
        $validator = $this->app['validator']->make($input, (new PaymentRequest())->rules());
        self::assertSame($valid, $validator->passes());
        if (!$valid) self::assertArrayHasKey('total_price', $validator->errors()->messages());
        self::assertSame($before, $this->fingerprint());
        self::assertSame(0, $this->transactionEvents);
    }

    public function test_wallet_topup_requires_amount_but_non_wallet_contract_is_unchanged(): void
    {
        $this->app->instance('request', Request::create('/', 'POST', ['wallet_id' => 1]));
        Facade::clearResolvedInstance('request');
        $validator = $this->app['validator']->make(['wallet_id' => 1], (new PaymentRequest())->rules());
        self::assertFalse($validator->passes());
        self::assertArrayHasKey('total_price', $validator->errors()->messages());
        $this->app->instance('request', Request::create('/', 'POST'));
        Facade::clearResolvedInstance('request');
        self::assertSame(['numeric'], (new PaymentRequest())->rules()['total_price']);
    }

    public function test_valid_withdrawal_still_rejects_insufficient_funds(): void
    {
        $before = $this->fingerprint();
        self::assertSame(400, $this->request('withdraw', ['price' => 101])->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame(0, $this->transactionEvents);
    }

    public static function roles(): array
    {
        return [['user'], ['seller'], ['moderator'], ['shop_manager']];
    }

    #[DataProvider('roles')]
    public function test_forged_actor_fields_do_not_change_which_wallet_is_debited(string $role): void
    {
        $this->authenticate(1, $role);
        self::assertSame(200, $this->request('send', [
            'price' => 20, 'currency_id' => 1, 'uuid' => 'user-2',
            'user' => ['id' => 3], 'user_id' => 3, 'wallet_uuid' => 'wallet-3',
            'owner_id' => 3, 'created_by' => 3, 'type' => 'topup', 'status' => 'paid',
        ])->getStatusCode());
        self::assertSame([80.0, 50.0, 80.0], $this->balances());
        self::assertSame('wallet-1', WalletHistory::where('type', 'withdraw')->firstOrFail()->wallet_uuid);
        self::assertSame('wallet-2', WalletHistory::where('type', 'topup')->firstOrFail()->wallet_uuid);
        self::assertSame([1, 2], Transaction::orderBy('id')->pluck('user_id')->all());
    }

    #[DataProvider('roles')]
    public function test_withdrawal_ignores_forged_financial_owner_fields(string $role): void
    {
        $this->authenticate(1, $role);
        self::assertSame(200, $this->request('withdraw', [
            'price' => 20, 'user' => ['id' => 2], 'user_id' => 2,
            'wallet_uuid' => 'wallet-2', 'owner_id' => 2,
            'type' => 'topup', 'status' => 'paid',
        ])->getStatusCode());
        self::assertSame([80.0, 30.0, 80.0], $this->balances());
        self::assertSame('wallet-1', WalletHistory::firstOrFail()->wallet_uuid);
        self::assertSame('withdraw', WalletHistory::firstOrFail()->type);
        self::assertSame('processed', WalletHistory::firstOrFail()->status);
        self::assertSame(1, Transaction::firstOrFail()->user_id);
    }

    public static function invalidLegacyStatuses(): array
    {
        return [
            ['withdraw', -20, 'canceled', false], ['withdraw', 0, 'rejected', false],
            ['topup', -20, 'paid', true], ['topup', 0, 'paid', true],
        ];
    }

    #[DataProvider('invalidLegacyStatuses')]
    public function test_invalid_legacy_history_cannot_reach_approval_or_restoration(string $type, int $amount, string $status, bool $admin): void
    {
        // Synthetic legacy corruption only, bypassing the new creation boundary.
        $this->database->table('wallet_histories')->insert([
            'uuid' => 'legacy-invalid', 'wallet_uuid' => 'wallet-1', 'price' => $amount,
            'type' => $type, 'created_by' => 1, 'status' => WalletHistory::PROCESSED,
        ]);
        if ($admin) $this->authenticate(3, 'admin');
        $before = $this->fingerprint();
        self::assertSame(400, $this->request('history/legacy-invalid/status/change',
            ['status' => $status], $admin)->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        self::assertSame([100.0, 30.0, 80.0], $this->balances());
        self::assertSame(0, $this->transactionEvents);
    }
}