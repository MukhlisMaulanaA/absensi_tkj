<?php

namespace App\Filament\Widgets;

use App\Models\OvertimeRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOvertimeRequestsWidget extends BaseWidget
{
  protected static ?int $sort = 6;
  protected int|string|array $columnSpan = 'full';

  public function getHeading(): string
  {
    return 'Recent Overtime Requests';
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(
        OvertimeRequest::query()
          ->whereDate('created_at', today()) // 1. Filter hanya untuk hari ini
          ->orderBy('created_at', 'desc')
          ->limit(10)
      )
      ->columns([
        Tables\Columns\TextColumn::make('user.name')
          ->label('Employee')
          ->searchable()
          ->sortable(),

        Tables\Columns\TextColumn::make('description')
          ->label('Reason')
          ->limit(50)
          ->searchable(),

        Tables\Columns\TextColumn::make('status')
          ->label('Status')
          ->badge()
          ->color(fn(string $state): string => match ($state) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
          })
          ->formatStateUsing(fn(string $state): string => ucfirst($state)),

        Tables\Columns\TextColumn::make('created_at')
          ->label('Date')
          ->dateTime('M d, Y H:i')
          ->sortable(),

        Tables\Columns\TextColumn::make('duration_hours')
          ->label('Duration')
          ->formatStateUsing(fn($state): string => $state . ' hrs'),


      ])
      ->defaultPaginationPageOption(5)
      ->striped();
  }
}
