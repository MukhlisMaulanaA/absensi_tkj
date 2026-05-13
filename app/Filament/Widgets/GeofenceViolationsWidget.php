<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class GeofenceViolationsWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return 'Geofence Compliance (Last 30 Days)';
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Get data for last 7 days (aggregated)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            $nextDate = $date->copy()->endOfDay();
            $labels[] = $date->format('D');

            $within = Attendance::whereBetween('check_in_time', [$date, $nextDate])
                ->where('is_within_radius', true)
                ->distinct('user_id')
                ->count('user_id');

            $violations = Attendance::whereBetween('check_in_time', [$date, $nextDate])
                ->where('is_within_radius', false)
                ->distinct('user_id')
                ->count('user_id');

            $data['within'][] = $within;
            $data['violations'][] = $violations;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Within Geofence',
                    'data' => $data['within'] ?? [],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Geofence Violations',
                    'data' => $data['violations'] ?? [],
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                    ],
                ],
            ],
        ];
    }
}
