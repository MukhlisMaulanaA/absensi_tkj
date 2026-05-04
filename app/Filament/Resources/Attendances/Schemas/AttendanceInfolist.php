<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\Attendance;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('location_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('check_in_time')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('check_in_latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('check_in_longitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('check_in_photo')
                    ->placeholder('-'),
                TextEntry::make('check_out_time')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('check_out_latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('check_out_longitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('check_out_photo')
                    ->placeholder('-'),
                TextEntry::make('late_minutes')
                    ->numeric(),
                IconEntry::make('is_within_radius')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Attendance $record): bool => $record->trashed()),
            ]);
    }
}
