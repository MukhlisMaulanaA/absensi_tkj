<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Filament\Infolists\Components\AttendanceMapEntry;
use App\Models\Attendance;
// use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceInfolist
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        // Employee Information Section
        Section::make('Employee Information')
          ->icon('heroicon-o-user')
          ->columns(2)
          ->schema([
            TextEntry::make('user.name')
              ->label('Employee Name')
              // ->url(fn($record) => $record->user ? route('filament.admin.resources.users.view', $record->user) : null)
              ->columnSpanFull(),
            TextEntry::make('user.email')
              ->label('Email')
              ->icon('heroicon-o-envelope')
              ->columnSpanFull(),
            TextEntry::make('location.name')
              ->label('Assigned Location')
              ->placeholder('-')
              ->icon('heroicon-o-map-pin')
              ->columnSpanFull(),
          ]),

        // Attendance Status Section
        Section::make('Attendance Status')
          ->icon('heroicon-o-check-badge')
          ->columns(3)
          ->schema([
            TextEntry::make('status')
              ->label('Status')
              ->getStateUsing(function ($record) {
                if (!$record->check_in_time) {
                  return 'Absent';
                }
                return $record->late_minutes > 0 ? 'Late' : 'On Time';
              })
              ->badge()
              ->color(function ($state) {
                return match ($state) {
                  'On Time' => 'success',
                  'Late' => 'warning',
                  'Absent' => 'danger',
                  default => 'gray',
                };
              })
              ->columnSpan(1),
            TextEntry::make('late_minutes')
              ->label('Late Minutes')
              ->getStateUsing(function ($record) {
                return $record->late_minutes > 0 ? $record->late_minutes . ' min' : '-';
              })
              ->columnSpan(1),
            IconEntry::make('is_within_radius')
              ->label('Within Radius')
              ->boolean()
              ->columnSpan(1),
          ]),

        // Check-In Details Section
        Section::make('Check-In Details')
          ->icon('heroicon-o-arrow-down-on-square')
          ->collapsible()
          ->columns(2)
          ->schema([
            TextEntry::make('check_in_time')
              ->label('Check-In Time')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-')
              ->columnSpanFull(),
            TextEntry::make('check_in_latitude')
              ->label('Latitude')
              ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-')
              ->placeholder('-'),
            TextEntry::make('check_in_longitude')
              ->label('Longitude')
              ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-')
              ->placeholder('-'),
            TextEntry::make('check_in_photo')
              ->label('Check-In Photo')
              ->placeholder('-')
              ->columnSpanFull()
              ->url(fn($state) => $state ? asset('storage/' . $state) : null, shouldOpenInNewTab: true)
              ->hidden(fn($state) => !$state),
          ]),

        // Check-Out Details Section
        Section::make('Check-Out Details')
          ->icon('heroicon-o-arrow-up-on-square')
          ->collapsible()
          ->columns(2)
          ->schema([
            TextEntry::make('check_out_time')
              ->label('Check-Out Time')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-')
              ->columnSpanFull(),
            TextEntry::make('check_out_latitude')
              ->label('Latitude')
              ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-')
              ->placeholder('-'),
            TextEntry::make('check_out_longitude')
              ->label('Longitude')
              ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-')
              ->placeholder('-'),
            TextEntry::make('check_out_photo')
              ->label('Check-Out Photo')
              ->placeholder('-')
              ->columnSpanFull()
              ->url(fn($state) => $state ? asset('storage/' . $state) : null, shouldOpenInNewTab: true)
              ->hidden(fn($state) => !$state),
            TextEntry::make('working_hours')
              ->label('Working Hours')
              ->getStateUsing(function ($record) {
                if (!$record->check_in_time || !$record->check_out_time) {
                  return '-';
                }
                return $record->working_hours . ' hours';
              })
              ->columnSpanFull(),
          ]),

        // Location Map Section
        Section::make('Location Map')
          ->icon('heroicon-o-map')
          ->collapsible()
          ->columnSpanFull()
          ->schema([
            AttendanceMapEntry::make('location_map')
              ->columnSpanFull(),
          ]),

        // Timestamps Section
        Section::make('Record Information')
          ->icon('heroicon-o-clock')
          ->collapsible()
          ->columns(2)
          ->schema([
            TextEntry::make('created_at')
              ->label('Created At')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-'),
            TextEntry::make('updated_at')
              ->label('Updated At')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-'),
            TextEntry::make('deleted_at')
              ->label('Deleted At')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-')
              ->visible(fn(Attendance $record): bool => $record->trashed()),
          ]),
      ]);
  }
}
