<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Services\PaymentAccounting\FinancialOperations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PaymentPhase3cSnapshotMySqlTest extends PaymentPhase3cMySqlFixture
{
    public function test_existing_repeatable_read_snapshot_cannot_overreserve_refund(): void
    {
        [$allocation, $context] = $this->funded();
        $manager = $this->database->getDatabaseManager();
        $owner = DB::connection('hardening');
        $reader = $this->reader();
        self::assertSame('REPEATABLE-READ', $reader->selectOne('SELECT @@transaction_isolation AS level')->level);
        $evidence = ['scope'=>'isolated synthetic service transactions, not authenticated API',
            'funding_units'=>10000, 'request_a_units'=>7000, 'request_b_units'=>7000,
            'owner_connection_id'=>(int)$owner->selectOne('SELECT CONNECTION_ID() AS id')->id,
            'reader_connection_id'=>(int)$reader->selectOne('SELECT CONNECTION_ID() AS id')->id];
        $reader->beginTransaction();
        try {
            $manager->setDefaultConnection('snapshot_reader');
            $evidence['reader_initial_reserved_units'] = (new FinancialOperations)->reserved($allocation, 'refund', $context);
            self::assertSame(0, $evidence['reader_initial_reserved_units']);
            // The reader transaction remains open: its ordinary reads now have an
            // older InnoDB consistent-read view. Owner commits during that overlap.
            $manager->setDefaultConnection('hardening');
            (new FinancialOperations)->reserve($allocation, 'refund', (string)Str::uuid(), 7000, 2, $context);
            $evidence['owner_committed_reserved_units'] = (new FinancialOperations)->reserved($allocation, 'refund', $context);
            self::assertSame(7000, $evidence['owner_committed_reserved_units']);
            $manager->setDefaultConnection('snapshot_reader');
            $evidence['reader_snapshot_reserved_after_owner_commit'] = (new FinancialOperations)->reserved($allocation, 'refund', $context);
            self::assertSame(0, $evidence['reader_snapshot_reserved_after_owner_commit']);
            $evidence['reader_current_locked_version'] = (int)DB::table('commerce_payment_allocations')
                ->where('id', $allocation)->lockForUpdate()->value('version');
            $evidence['request_b_accepted'] = false;
            try {
                (new FinancialOperations)->reserve($allocation, 'refund', (string)Str::uuid(), 7000, 2, $context);
                $evidence['request_b_accepted'] = true;
            } catch (\DomainException $e) {
                $evidence['rejection'] = $e->getMessage();
            }
            $reader->commit();
            $manager->setDefaultConnection('hardening');
            $evidence['final_reserved_units'] = (new FinancialOperations)->reserved($allocation, 'refund', $context);
            $evidence['final_operation_count'] = DB::table('payment_financial_operations')->count();
            $evidence['final_allocation_version'] = (int)DB::table('commerce_payment_allocations')->where('id', $allocation)->value('version');
            $evidence['refund_principal_effect_count'] = DB::table('platform_fee_ledger_entries')->where('effect_kind', 'refund_principal')->count();
            $evidence['provider_requests_made'] = false;
            file_put_contents(__DIR__.'/../../../../.local/payment-phase3c-reservation-current-read-evidence.json',
                json_encode($evidence, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n");
            self::assertLessThanOrEqual(10000, $evidence['final_reserved_units'],
                'Two overlapping native reservations must never exceed original refundable funding.');
            self::assertFalse($evidence['request_b_accepted']);
            self::assertSame(7000, $evidence['final_reserved_units']);
            self::assertSame(1, $evidence['final_operation_count']);
        } finally {
            while ($reader->transactionLevel() > 0) $reader->rollBack();
            $manager->setDefaultConnection('hardening');
        }
    }
}