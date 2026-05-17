<?php

namespace App\Filament\Resources\OvertimeRequests\Schemas;

use App\Models\OvertimeRequest;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid; // <-- Menggunakan Grid terpadu milik Filament v5

class OvertimeRequestInfolist
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Grid::make(2) // Grid dari Filament\Schemas\Components
          ->schema([
            TextEntry::make('user_id')
              ->numeric(),
            TextEntry::make('overtime_days')
              ->label('Hari Lembur')
              ->badge()
              ->color('info')
              ->formatStateUsing(fn(OvertimeRequest $record) => $record->getOvertimeDaysLabel()),
          ]),
        TextEntry::make('start_time')
          ->dateTime(),
        TextEntry::make('end_time')
          ->dateTime(),
        TextEntry::make('description')
          ->columnSpanFull(),
        ImageEntry::make('image')
          ->label('Lampiran Gambar')
          ->disk('public')
          // Hapus ->url(...) karena Filament otomatis tahu cara mengurai array gambar
          ->openUrlInNewTab()
          ->columnSpanFull()
          // Mengubah validasi visibilitas agar aman membaca array
          ->visible(fn(OvertimeRequest $record): bool => is_array($record->image) && count($record->image) > 0),
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
          ->visible(fn(OvertimeRequest $record): bool => $record->trashed()),
      ]);
  }
}