<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MasterDisabledTime;
use Illuminate\Console\Command;
use Log;
use Throwable;

class RemoveExpiredMasterDisabledTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:expired:master:disabled:times';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'remove expired disabled times';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $now   = date('Y-m-d H:i');
        $class = MasterDisabledTime::class;

        $dates = $class::get();

        foreach ($dates as $date) {

            try {
                $dateFrom = date('Y-m-d H:i', strtotime("$date->date $date->from"));
                $dateTo   = date('Y-m-d H:i', strtotime("$date->date $date->to"));

                if ($date->repeats === $class::DONT_REPEAT && $dateFrom <= $now && $dateTo <= $now) {
                    $date->delete();
                }

                if ($date->end_type === $class::DATE && in_array($date->repeats, [$class::DAY, $class::CUSTOM], true)) {
                    $end = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $date->end_value);
                    // The end date is inclusive: never erase today's remaining blocks.
                    if ($end && $end->format('Y-m-d') === $date->end_value && $date->end_value < date('Y-m-d')) {
                        $date->delete();
                    }
                }
                // Keep counted recurrence metadata. date + N units is not its last
                // occurrence (custom intervals/weekdays and missing months differ).
                // Discovery/admission suppress expired counts with the shared engine.
                // Exact counted-rule garbage collection needs separate certification.

            } catch (Throwable $e) {
                Log::error($e->getMessage());
            }

        }

        return 0;
    }
}
