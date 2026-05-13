<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class DailyAttendanceTrendWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Attendance Trend (Last 7 Days)';
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Get data for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            $nextDate = $date->copy()->endOfDay();
            $labels[] = $date->format('D, M d');

            $attended = Attendance::whereBetween('check_in_time', [$date, $nextDate])
                ->distinct('user_id')
                ->count('user_id');

            $data[] = $attended;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Employees Present',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#10b981',
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7,
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
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Employees',
                    ],
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
