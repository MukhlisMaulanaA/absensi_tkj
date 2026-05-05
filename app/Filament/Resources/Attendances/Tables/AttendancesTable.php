<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        $statuses = [
                            'on_time' => 'On Time',
                            'late' => 'Late',
                            'absent' => 'Absent',
                        ];
                        return $statuses[$state] ?? $state;
                    })
                    ->colors([
                        'success' => 'on_time',
                        'warning' => 'late',
                        'danger' => 'absent',
                    ])
                    ->getStateUsing(function ($record) {
                        if (!$record->check_in_time) {
                            return 'absent';
                        }
                        return $record->late_minutes > 0 ? 'late' : 'on_time';
                    }),
                TextColumn::make('check_in_time')
                    ->label('Check-In')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->wrap(),
                TextColumn::make('check_out_time')
                    ->label('Check-Out')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('working_hours')
                    ->label('Working Hours')
                    ->getStateUsing(function ($record) {
                        if (!$record->check_in_time || !$record->check_out_time) {
                            return '-';
                        }
                        return $record->working_hours . 'h';
                    })
                    ->sortable(),
                TextColumn::make('late_minutes')
                    ->label('Late (mins)')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->late_minutes > 0 ? $record->late_minutes : '-';
                    }),
                IconColumn::make('is_within_radius')
                    ->label('Within Radius')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('check_in_time', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
