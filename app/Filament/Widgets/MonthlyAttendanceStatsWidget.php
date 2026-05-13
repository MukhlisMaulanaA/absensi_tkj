<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class MonthlyAttendanceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    public function getHeading(): string
    {
        return 'Monthly Attendance Summary';
    }

    protected function getStats(): array
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        // Total unique employees who attended this month
        $monthlyAttended = Attendance::whereBetween('check_in_time', [$monthStart, $monthEnd])
            ->distinct('user_id')
            ->count('user_id');

        // Average daily attendance
        $workDays = $monthStart->diffInDays($monthEnd);
        $avgDailyAttendance = $workDays > 0 ? round($monthlyAttended / $workDays, 1) : 0;

        // Geofence violations this month
        $geofenceViolations = Attendance::whereBetween('check_in_time', [$monthStart, $monthEnd])
            ->where('is_within_radius', false)
            ->count();

        // Average working hours this month
        $monthlyAttendances = Attendance::whereBetween('check_in_time', [$monthStart, $monthEnd])
            ->where('check_out_time', '!=', null)
            ->get();

        $avgWorkingHours = 0;
        if ($monthlyAttendances->count() > 0) {
            $avgWorkingHours = round($monthlyAttendances->average(function ($attendance) {
                if (!$attendance->check_in_time || !$attendance->check_out_time) {
                    return 0;
                }
                return Carbon::parse($attendance->check_in_time)
                    ->diffInMinutes(Carbon::parse($attendance->check_out_time)) / 60;
            }), 2);
        }

        return [
            Stat::make('Total Unique Attendees', $monthlyAttended)
                ->description('Employees attended in ' . $monthStart->format('F'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Average Daily', $avgDailyAttendance)
                ->description('Employees per working day')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Geofence Violations', $geofenceViolations)
                ->description('Check-ins outside designated area')
                ->descriptionIcon('heroicon-m-map')
                ->color($geofenceViolations > 0 ? 'warning' : 'success'),

            Stat::make('Average Working Hrs', $avgWorkingHours)
                ->description('Per day this month')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),
        ];
    }
}
