<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\PortableSql;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PortableSqlTest extends TestCase
{
    public function test_date_bucket_expressions_are_selected_for_each_supported_driver(): void
    {
        $this->assertSame("DATE_FORMAT(created_at, '%Y-%m')", PortableSql::dateFormat('created_at', '%Y-%m', 'mysql'));
        $this->assertSame("strftime('%Y-%m', created_at)", PortableSql::dateFormat('created_at', '%Y-%m', 'sqlite'));
        $this->assertSame("TO_CHAR(created_at, 'YYYY-MM')", PortableSql::dateFormat('created_at', '%Y-%m', 'pgsql'));
        $this->assertSame("FORMAT(created_at, 'yyyy-MM', 'en-US')", PortableSql::dateFormat('created_at', '%Y-%m', 'sqlsrv'));
    }

    public function test_weekday_and_hour_buckets_are_supported_on_all_drivers(): void
    {
        foreach (['mysql', 'sqlite', 'pgsql', 'sqlsrv'] as $driver) {
            $this->assertNotEmpty(PortableSql::dateFormat('created_at', '%Y-%m-%d %w', $driver));
            $this->assertNotEmpty(PortableSql::dateFormat('created_at', '%Y-%m-%d %H:00', $driver));
            $this->assertNotEmpty(PortableSql::elapsedWholeHours('start_date', 'end_date', $driver));
            $this->assertNotEmpty(PortableSql::monthName('created_at', $driver));
        }
    }

    public function test_date_patterns_and_column_names_are_allowlisted(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PortableSql::dateFormat('created_at); DROP TABLE orders; --', '%Y-%m', 'sqlite');
    }

    public function test_report_status_literals_are_escaped_and_expressions_are_checked(): void
    {
        $this->assertSame(
            "SUM(CASE WHEN status = 'new'' OR 1=1' THEN total_price ELSE 0 END)",
            PortableSql::sumWhenEquals('status', "new' OR 1=1", 'total_price')
        );

        $this->expectException(InvalidArgumentException::class);
        PortableSql::averageWhenEquals('status', 'ended', 'total_price; DROP TABLE orders');
    }
}