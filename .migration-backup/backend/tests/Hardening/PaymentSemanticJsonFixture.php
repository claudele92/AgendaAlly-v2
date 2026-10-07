<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentAccounting\AccountingEffects;
use App\Services\PaymentAccounting\AllocationWriter;
use App\Services\PaymentAccounting\SemanticJson;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class PaymentSemanticJsonFixture extends PaymentAccountingFixture
{
    public static function comparisons(): array
    {
        return [
            'format/order/escape' => ['{"a":{"b":"café","n":1},"z":[true,null,"/"]}', '{ "z": [ true, null, "\\/" ], "a": {"n": 1, "b":"caf\\u00e9"} }', true],
            'numeric/string' => ['{"a":1}', '{"a":"1"}', false],
            'boolean/numeric' => ['{"a":true}', '{"a":1}', false],
            'missing/null' => ['{}', '{"a":null}', false],
            'array order' => ['{"a":[1,2]}', '{"a":[2,1]}', false],
            'object/array' => ['{"a":{}}', '{"a":[]}', false],
            'member changed' => ['{"a":1}', '{"b":1}', false],
            'nested type' => ['{"a":[{"v":false}]}', '{"a":[{"v":0}]}', false],
            'numeric property membership' => ['{"1":"a","01":"b"}', '{"01":"b","1":"a"}', true],
            'large integer precision' => ['{"a":9223372036854775808}', '{"a":9223372036854775809}', false],
            'large integer/string' => ['{"a":9223372036854775808}', '{"a":"9223372036854775808"}', false],
            'float precision' => ['{"a":0.100000000000000001}', '{"a":0.1}', false],
            'float notation' => ['{"a":1.5}', '{"a":15e-1}', true],
            'decoded integer/float' => ['{"a":1}', '{"a":1.0}', false],
        ];
    }

    public function test_strict_semantics_on_retained_engine_json(): void
    {
        foreach (self::comparisons() as $case => [$left, $right, $equal]) {
            self::assertSame($equal, SemanticJson::equal($left, $right), $case);
            // MySQL may itself round fractional numbers when storing its native JSON.
            // The direct comparison above must not inherit that loss of precision.
            if (str_contains($left, '0.100000000000000001')) continue;
            $retained = DB::connection()->getDriverName() === 'mysql'
                ? DB::selectOne('SELECT CAST(? AS JSON) AS evidence', [$left])->evidence
                : DB::selectOne('SELECT json(?) AS evidence', [$left])->evidence;
            self::assertSame($equal, SemanticJson::equal((string)$retained, $right), $case);
        }
    }

    public function test_invalid_json_fails_closed(): void
    {
        $this->expectException(\JsonException::class);
        SemanticJson::equal('{"a":', '{"a":null}');
    }

    public function test_quote_and_effect_replays_preserve_persisted_json_and_reject_mutation(): void
    {
        $quote = $this->quote();
        $quote['native_components'] = ['z'=>[true, null, '1'], 'a'=>(object)['b'=>'café', 'n'=>1]];
        $id = $this->writer->commit($quote);
        $stored = DB::table('commerce_payment_allocations')->find($id)->native_components;
        $quote['native_components'] = ['a'=>(object)['n'=>1, 'b'=>'café'], 'z'=>[true, null, '1']];
        self::assertSame($id, $this->writer->commit($quote));
        self::assertSame($stored, DB::table('commerce_payment_allocations')->find($id)->native_components);
        $this->rejects(fn()=> $this->writer->commit(array_replace($quote, ['native_components'=>['a'=>$quote['native_components']['a'], 'z'=>[true, null, 1]]])));
        self::assertSame($stored, DB::table('commerce_payment_allocations')->find($id)->native_components);
        $context = $this->writer->stage($id, $this->evidence('platform', 10000));
        $this->writer->confirm([$context], 'synthetic-json-receipt', 10000);
        $group = (string)Str::uuid();
        $effect = ['kind'=>'vendor_settlement', 'amount'=>1000, 'proof'=>['authority'=>'synthetic', 'operation'=>$group, 'nested'=>['value'=>'1', 'items'=>[true, null]]]];
        (new AccountingEffects)->append($id, $group, [$effect]);
        $row = (array)DB::table('platform_fee_ledger_entries')->where('event_group_key', $group)->first();
        $effect['proof'] = ['nested'=>['items'=>[true, null], 'value'=>'1'], 'operation'=>$group, 'authority'=>'synthetic'];
        (new AccountingEffects)->append($id, $group, [$effect]);
        self::assertSame($row, (array)DB::table('platform_fee_ledger_entries')->where('event_group_key', $group)->first());
        $effect['proof']['nested']['value'] = 1;
        $this->rejects(fn()=> (new AccountingEffects)->append($id, $group, [$effect]));
        self::assertSame($row, (array)DB::table('platform_fee_ledger_entries')->where('event_group_key', $group)->first());
    }

    private function rejects(callable $action): void
    {
        try { $action(); self::fail('Changed frozen evidence must be rejected.'); }
        catch (\DomainException $e) {
            self::assertTrue(str_contains($e->getMessage(), 'Committed quote is immutable')
                || $e->getMessage() === 'Economic effect evidence cannot change.');
        }
    }
}