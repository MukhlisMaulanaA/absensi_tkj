<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;

class AutoCheckout extends Command
{
  protected $signature = 'attendance:auto-checkout';
  protected $description = 'Auto checkout users at 23:59';

  public function handle()
  {
    Attendance::whereNull('check_out_time')
      ->whereDate('check_in_time', today())
      ->update([
        'check_out_time' => now()->setTime(23, 59),
      ]);

    $this->info('Auto checkout executed');
  }
}