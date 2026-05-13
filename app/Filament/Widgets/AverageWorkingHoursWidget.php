<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class AverageWorkingHoursWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    public function getHeading(): string
    {
        return 'Average Working Hours (Last 7 Days)';
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

            $attendances = Attendance::whereBetween('check_in_time', [$date, $nextDate])
                ->where('check_out_time', '!=', null)
                ->get();

            if ($attendances->count() > 0) {
                $avgHours = $attendances
                    ->average(function ($attendance) {
                        if (!$attendance->check_in_time || !$attendance->check_out_time) {
                            return 0;
                        }
                        return Carbon::parse($attendance->check_in_time)
                            ->diffInMinutes(Carbon::parse($attendance->check_out_time)) / 60;
                    });
                $data[] = round($avgHours, 2);
            } else {
                $data[] = 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Average Hours',
                    'data' => $data,
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => '#8b5cf6',
                    'pointBorderColor' => '#8b5cf6',
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
                        'text' => 'Hours',
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
