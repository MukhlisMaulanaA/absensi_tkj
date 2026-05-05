<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
// use Filament\Forms\Components\Section;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\User;
use App\Models\Location;

class AttendanceForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        // Employee Information Section
        Section::make('Employee & Location')
          ->icon('heroicon-o-user')
          ->columns(2)
          ->schema([
            Select::make('user_id')
              ->label('Employee')
              ->relationship('user', 'name')
              ->options(User::query()->pluck('name', 'id'))
              ->required()
              ->searchable()
              ->columnSpanFull(),
            Select::make('location_id')
              ->label('Location')
              ->relationship('location', 'name')
              ->options(Location::query()->pluck('name', 'id'))
              ->searchable()
              ->columnSpanFull(),
          ]),

        // Check-In Section
        Section::make('Check-In Details')
          ->icon('heroicon-o-arrow-down-on-square')
          ->columns(2)
          ->schema([
            DateTimePicker::make('check_in_time')
              ->label('Check-In Time')
              ->columnSpanFull(),
            TextInput::make('check_in_latitude')
              ->label('Latitude')
              ->numeric()
              ->placeholder('e.g., -6.123456'),
            TextInput::make('check_in_longitude')
              ->label('Longitude')
              ->numeric()
              ->placeholder('e.g., 106.654321'),
            TextInput::make('check_in_photo')
              ->label('Check-In Photo URL')
              ->url()
              ->columnSpanFull(),
          ]),

        // Check-Out Section
        Section::make('Check-Out Details')
          ->icon('heroicon-o-arrow-up-on-square')
          ->columns(2)
          ->schema([
            DateTimePicker::make('check_out_time')
              ->label('Check-Out Time')
              ->columnSpanFull(),
            TextInput::make('check_out_latitude')
              ->label('Latitude')
              ->numeric()
              ->placeholder('e.g., -6.123456'),
            TextInput::make('check_out_longitude')
              ->label('Longitude')
              ->numeric()
              ->placeholder('e.g., 106.654321'),
            TextInput::make('check_out_photo')
              ->label('Check-Out Photo URL')
              ->url()
              ->columnSpanFull(),
          ]),

        // Attendance Status Section
        Section::make('Attendance Status')
          ->icon('heroicon-o-check-badge')
          ->columns(2)
          ->schema([
            TextInput::make('late_minutes')
              ->label('Late Minutes')
              ->required()
              ->numeric()
              ->default(0)
              ->helperText('Number of minutes the employee was late'),
            Toggle::make('is_within_radius')
              ->label('Within Radius')
              ->required()
              ->helperText('Whether the employee checked in/out within the allowed radius'),
          ]),
      ]);
  }
}
