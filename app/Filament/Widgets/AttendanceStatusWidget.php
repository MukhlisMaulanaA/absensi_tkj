<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class AttendanceStatusWidget extends ChartWidget
{
  protected static ?int $sort = 3;

  public function getHeading(): string
  {
    return 'Today\'s Attendance Status';
  }

  protected function getData(): array
  {
    $today = Carbon::now()->startOfDay();
    $tomorrow = Carbon::now()->endOfDay();

    $todayAttendances = Attendance::whereBetween('check_in_time', [$today, $tomorrow])
      ->get();

    $onTime = $todayAttendances
      ->where('late_minutes', '<=', 0)
      ->pluck('user_id')
      ->unique()
      ->count();

    $late = $todayAttendances
      ->where('late_minutes', '>', 0)
      ->pluck('user_id')
      ->unique()
      ->count();

    return [
      'datasets' => [
        [
          'label' => 'Attendance Status',
          'data' => [$onTime, $late],
          'backgroundColor' => [
            '#10b981', // Green for on-time
            '#ef4444', // Red for late
          ],
          'borderColor' => [
            '#059669',
            '#dc2626',
          ],
          'borderWidth' => 2,
        ],
      ],
      'labels' => ['On Time', 'Late'],
    ];
  }

  protected function getType(): string
  {
    return 'doughnut';
  }

  protected function getOptions(): array
  {
    return [
      'plugins' => [
        'legend' => [
          'display' => true,
          'position' => 'bottom',
          'labels' => [
            'usePointStyle' => true,
            'padding' => 15,
            'font' => [
              'size' => 12,
            ],
          ],
        ],
      ],
    ];
  }
}
