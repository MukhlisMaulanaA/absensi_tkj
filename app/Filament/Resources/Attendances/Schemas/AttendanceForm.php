<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('location_id')
                    ->numeric(),
                DateTimePicker::make('check_in_time'),
                TextInput::make('check_in_latitude')
                    ->numeric(),
                TextInput::make('check_in_longitude')
                    ->numeric(),
                TextInput::make('check_in_photo'),
                DateTimePicker::make('check_out_time'),
                TextInput::make('check_out_latitude')
                    ->numeric(),
                TextInput::make('check_out_longitude')
                    ->numeric(),
                TextInput::make('check_out_photo'),
                TextInput::make('late_minutes')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_within_radius')
                    ->required(),
            ]);
    }
}
