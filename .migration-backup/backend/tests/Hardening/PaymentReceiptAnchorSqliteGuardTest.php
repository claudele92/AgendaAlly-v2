<?php
declare(strict_types=1);
namespace Tests\Hardening;

use Illuminate\Support\Facades\DB;

final class PaymentReceiptAnchorSqliteGuardTest extends PaymentReceiptAnchorGuardFixture
{
    public function test_original_sqlite_check_and_schema_behavior_are_unchanged(): void
    {
        $ddl = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name='payment_collection_contexts'")->sql;
        self::assertStringContainsString('CHECK(receipt_anchor_context_id IS NULL OR receipt_anchor_context_id <> id)',$ddl);
        self::assertSame(0,DB::table('sqlite_master')->where('type','trigger')->where('name','like','pcc_receipt_anchor_%')->count());
    }
}