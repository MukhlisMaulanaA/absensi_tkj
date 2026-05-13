<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{
  protected static ?int $navigationSort = -2;

  public function getTitle(): string
  {
    return 'Admin Dashboard';
  }

  public function getWidgets(): array
  {
    return [
      \App\Filament\Widgets\EmployeeAttendanceStatsWidget::class,
      \App\Filament\Widgets\AttendanceStatusWidget::class,
      \App\Filament\Widgets\DailyAttendanceTrendWidget::class,
      \App\Filament\Widgets\AverageWorkingHoursWidget::class,
      \App\Filament\Widgets\GeofenceViolationsWidget::class,
      \App\Filament\Widgets\MonthlyAttendanceStatsWidget::class,
      \App\Filament\Widgets\RecentOvertimeRequestsWidget::class,
      \App\Filament\Widgets\LateEmployeesWidget::class,
    ];
  }

  public function getColumns(): int|array
  {
    return [
      'default' => 1,
      'md' => 2,
      'lg' => 3,
      'xl' => 3,
    ];
  }
}
