<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class EmployeeAttendanceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public function getHeading(): string
    {
        return 'Daily Attendance Overview';
    }

    protected function getStats(): array
    {
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->endOfDay();

        // Today's attendance data
        $todayAttendances = Attendance::whereBetween('check_in_time', [$today, $tomorrow])
            ->get();

        $totalEmployees = User::where('role', 'employee')->count();
        $presentToday = $todayAttendances->pluck('user_id')->unique()->count();
        $lateToday = $todayAttendances->where('late_minutes', '>', 0)->pluck('user_id')->unique()->count();
        $absentToday = $totalEmployees - $presentToday;

        // Calculate percentages
        $presentPercentage = $totalEmployees > 0 ? round(($presentToday / $totalEmployees) * 100) : 0;
        $latePercentage = $presentToday > 0 ? round(($lateToday / $presentToday) * 100) : 0;

        return [
            Stat::make('Total Employees', $totalEmployees)
                ->description('Active employees in system')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Present Today', $presentToday)
                ->description($presentPercentage . '% attendance rate')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Late Today', $lateToday)
                ->description($latePercentage . '% of present employees')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($lateToday > 0 ? 'warning' : 'success'),

            Stat::make('Absent Today', $absentToday)
                ->description(round(($absentToday / $totalEmployees) * 100) . '% absence rate')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($absentToday > 0 ? 'danger' : 'success'),
        ];
    }
}
