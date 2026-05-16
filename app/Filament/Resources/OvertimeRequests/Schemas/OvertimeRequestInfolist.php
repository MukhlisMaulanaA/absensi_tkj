<?php

namespace App\Filament\Resources\OvertimeRequests\Schemas;

use App\Models\OvertimeRequest;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OvertimeRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('start_time')
                    ->dateTime(),
                TextEntry::make('end_time')
                    ->dateTime(),
                TextEntry::make('description')
                    ->columnSpanFull(),
                ImageEntry::make('image')
                    ->label('Lampiran Gambar')
                    ->disk('public')
                    ->url(fn ($record) => !empty($record->image) ? asset('storage/' . $record->image) : null)
                    ->openUrlInNewTab()
                    ->columnSpanFull()
                    ->visible(fn (OvertimeRequest $record): bool => !empty($record->image)),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('approved_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (OvertimeRequest $record): bool => $record->trashed()),
            ]);
    }
}
