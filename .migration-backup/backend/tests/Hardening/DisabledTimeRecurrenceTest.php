<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\MasterDisabledTime as Rule;
use App\Services\BookingService\DisabledTimeRecurrence as Recurrence;
use PHPUnit\Framework\TestCase;

final class DisabledTimeRecurrenceTest extends TestCase
{
    private function rule(array $attributes): Rule
    {
        return (new Rule)->forceFill($attributes + [
            'date' => '2030-01-07', 'repeats' => Rule::DAY, 'end_type' => Rule::NEVER,
        ]);
    }

    /** @dataProvider cases */
    public function test_calendar_contract(array $attributes, string $from, string $to, array $expected): void
    {
        $rule = $this->rule($attributes);
        $this->assertSame($expected, Recurrence::dates($rule, $from, $to));
        // Slicing the window cannot change its occurrence membership or count.
        $sliced = [];
        for ($day = new \DateTimeImmutable($from); $day <= new \DateTimeImmutable($to); $day = $day->modify('+1 day')) {
            $key = $day->format('Y-m-d');
            array_push($sliced, ...Recurrence::dates($rule, $key, $key));
        }
        $this->assertSame($expected, $sliced);
    }

    public static function cases(): array
    {
        return [
            'daily old origin' => [[], '2030-01-14', '2030-01-16', ['2030-01-14','2030-01-15','2030-01-16']],
            'no occurrence before origin' => [[], '2030-01-05', '2030-01-07', ['2030-01-07']],
            'after includes origin, exactly N' => [['end_type'=>'after','end_value'=>'2'], '2030-01-07', '2030-01-10', ['2030-01-07','2030-01-08']],
            'after expired before range' => [['end_type'=>'after','end_value'=>'2'], '2030-01-14', '2030-01-16', []],
            'end date inclusive' => [['end_type'=>'date','end_value'=>'2030-01-15'], '2030-01-14', '2030-01-16', ['2030-01-14','2030-01-15']],
            'weekly old origin' => [['repeats'=>'week'], '2030-01-14', '2030-01-22', ['2030-01-14','2030-01-21']],
            'weekly count' => [['repeats'=>'week','end_type'=>'after','end_value'=>'2'], '2030-01-14', '2030-01-22', ['2030-01-14']],
            'monthly 31 never clamps' => [['date'=>'2030-01-31','repeats'=>'month'], '2030-02-01', '2030-12-31', ['2030-03-31','2030-05-31','2030-07-31','2030-08-31','2030-10-31','2030-12-31']],
            'monthly missing dates do not consume count' => [['date'=>'2030-01-31','repeats'=>'month','end_type'=>'after','end_value'=>'2'], '2030-02-01', '2030-05-31', ['2030-03-31']],
            'monthly date end inclusive' => [['date'=>'2030-01-31','repeats'=>'month','end_type'=>'date','end_value'=>'2030-03-31'], '2030-02-01', '2030-05-31', ['2030-03-31']],
            'monthly 30 does not become end of month' => [['date'=>'2030-04-30','repeats'=>'month'], '2030-05-01', '2030-07-31', ['2030-05-30','2030-06-30','2030-07-30']],
            'monthly leap anchor' => [['date'=>'2028-02-29','repeats'=>'custom','custom_repeat_type'=>'month','custom_repeat_value'=>[12],'end_type'=>'after','end_value'=>'2'], '2029-01-01', '2033-01-01', ['2032-02-29']],
            'custom days anchored, not query offset' => [['repeats'=>'custom','custom_repeat_type'=>'day','custom_repeat_value'=>[3]], '2030-01-08', '2030-01-14', ['2030-01-10','2030-01-13']],
            'custom days count' => [['repeats'=>'custom','custom_repeat_type'=>'day','custom_repeat_value'=>[3],'end_type'=>'after','end_value'=>'2'], '2030-01-08', '2030-01-14', ['2030-01-10']],
            'custom weeks interval and selected weekdays' => [['repeats'=>'custom','custom_repeat_type'=>'week','custom_repeat_value'=>[2,'monday','wednesday']], '2030-01-07', '2030-01-24', ['2030-01-07','2030-01-09','2030-01-21','2030-01-23']],
            'custom weekly count occurrences, not weeks' => [['repeats'=>'custom','custom_repeat_type'=>'week','custom_repeat_value'=>[2,'monday','wednesday'],'end_type'=>'after','end_value'=>'3'], '2030-01-09', '2030-01-24', ['2030-01-09','2030-01-21']],
            'custom first partial week' => [['date'=>'2030-01-09','repeats'=>'custom','custom_repeat_type'=>'week','custom_repeat_value'=>[2,'monday','friday'],'end_type'=>'after','end_value'=>'2'], '2030-01-09', '2030-01-26', ['2030-01-11','2030-01-21']],
            'custom months original step after skipped month' => [['date'=>'2030-01-31','repeats'=>'custom','custom_repeat_type'=>'month','custom_repeat_value'=>[3]], '2030-02-01', '2030-12-31', ['2030-07-31','2030-10-31']],
            'one time does not recur' => [['repeats'=>'dont_repeat'], '2030-01-08', '2030-01-14', []],
            'one time includes origin' => [['repeats'=>'dont_repeat'], '2030-01-07', '2030-01-07', ['2030-01-07']],
            'count is not capped by availability horizon' => [['end_type'=>'after','end_value'=>'1000'], '2031-01-01', '2031-01-01', ['2031-01-01']],
        ];
    }

    /** @dataProvider invalidRules */
    public function test_invalid_existing_rule_fails_closed(array $attributes): void
    {
        $this->expectException(\DomainException::class);
        Recurrence::dates($this->rule($attributes), '2030-01-07', '2030-01-08');
    }

    public static function invalidRules(): array
    {
        return [
            [['date'=>'2030-02-31']],
            [['repeats'=>'custom','custom_repeat_type'=>'day','custom_repeat_value'=>[0]]],
            [['repeats'=>'custom','custom_repeat_type'=>'week','custom_repeat_value'=>[1]]],
            [['repeats'=>'custom','custom_repeat_type'=>'week','custom_repeat_value'=>[1,'invalid']]],
            [['end_type'=>'after','end_value'=>'0']],
            [['end_type'=>'date','end_value'=>'invalid']],
        ];
    }
}