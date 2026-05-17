<?php

namespace App\Filament\Resources\OvertimeRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;

class OvertimeRequestForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        TextInput::make('user_id')
          ->required()
          ->numeric(),
        DateTimePicker::make('start_time')
          ->required(),
        DateTimePicker::make('end_time')
          ->required(),
        Textarea::make('description')
          ->required()
          ->columnSpanFull(),
        FileUpload::make('images') // Catatan: Jika perlu, Anda bisa mengubah namanya jadi 'images'
          ->label('Lampiran Gambar')
          ->image()
          ->multiple()   // <--- Mengizinkan lebih dari 1 file
          ->maxFiles(5)  // <--- Membatasi maksimal 5 file
          ->imagePreviewHeight('250')
          ->maxSize(5120) // 5MB per file
          ->directory('overtime-requests')
          ->visibility('public')
          ->columnSpanFull(),
        Select::make('status')
          ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
          ->default('pending')
          ->required(),
        TextInput::make('approved_by')
          ->numeric(),
        TextInput::make('overtime_days')
          ->label('Hari Lembur')
          ->numeric()
          ->disabled()
          ->helperText('Hari lembur dihitung otomatis berdasarkan waktu mulai dan berakhir'),
      ]);
  }
}
