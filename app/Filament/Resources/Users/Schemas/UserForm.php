<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Location;
use App\Models\Schedule;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('username')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('jabatan'),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->default('tkj@123456')
                    ->hiddenOn('edit')
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                TextInput::make('role')
                    ->required()
                    ->default('employee'),
                Select::make('location_id')
                    ->label('Assign Location')
                    ->options(Location::pluck('name', 'id'))
                    ->searchable(),
                Select::make('schedule_id')
                    ->label('Schedule')
                    ->options(Schedule::pluck('name', 'id'))
                    ->searchable(),
                TextInput::make('device_id'),
            ]);
    }
}
