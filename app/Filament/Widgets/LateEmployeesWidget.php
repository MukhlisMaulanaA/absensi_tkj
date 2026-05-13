<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class LateEmployeesWidget extends BaseWidget
{
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Late Employees (Today)';
    }

    public function table(Table $table): Table
    {
        $today = Carbon::now()->startOfDay();
        $tomorrow = Carbon::now()->endOfDay();

        return $table
            ->query(
                Attendance::query()
                    ->whereBetween('check_in_time', [$today, $tomorrow])
                    ->where('late_minutes', '>', 0)
                    ->orderBy('late_minutes', 'desc')
                    ->with('user', 'location')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.jabatan')
                    ->label('Position')
                    ->searchable(),

                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable(),

                Tables\Columns\TextColumn::make('late_minutes')
                    ->label('Minutes Late')
                    ->formatStateUsing(fn($state): string => $state . ' min')
                    ->color(fn($state): string => $state > 30 ? 'danger' : 'warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Check-in Time')
                    ->dateTime('H:i:s')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_within_radius')
                    ->label('In Geofence')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->defaultPaginationPageOption(10)
            ->striped();
    }
}
