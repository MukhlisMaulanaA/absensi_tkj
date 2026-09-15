<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCheckout extends Command
{
    protected $signature = 'attendance:auto-checkout';

    protected $description = 'Auto check out unfinished attendances at 06:30 the following day';

    public function handle()
    {
        $processed = 0;

        // Select only records whose 06:30 deadline has passed. Processing each
        // record keeps the recorded deadline correct even if the scheduler runs
        // late after downtime.
        Attendance::query()
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->orderBy('id')
            ->chunkById(100, function ($attendances) use (&$processed) {
                foreach ($attendances as $attendance) {
                    $systemCheckoutAt = Carbon::parse($attendance->check_in_time)
                        ->addDay()
                        ->setTime(6, 30, 0);

                    if ($systemCheckoutAt->isFuture()) {
                        continue;
                    }

                    // Keep the null check in the update so a simultaneous genuine
                    // employee check-out is never overwritten by the scheduled task.
                    $updated = Attendance::whereKey($attendance->id)
                        ->whereNull('check_out_time')
                        ->update([
                            'check_out_time' => $systemCheckoutAt,
                            'check_out_source' => 'system',
                        ]);

                    $processed += $updated;
                }
            });

        $this->info("Auto check-out processed {$processed} attendance record(s).");

        return self::SUCCESS;
    }
}
