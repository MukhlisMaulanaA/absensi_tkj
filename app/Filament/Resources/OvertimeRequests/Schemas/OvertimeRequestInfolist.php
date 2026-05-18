<?php

namespace App\Filament\Resources\OvertimeRequests\Schemas;

use App\Models\OvertimeRequest;
use App\Models\User;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid; // <-- Menggunakan Grid terpadu milik Filament v5
use Filament\Schemas\Schema;
use Carbon\Carbon;

class OvertimeRequestInfolist
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Grid::make(2) // Grid dari Filament\Schemas\Components
          ->schema([
            TextEntry::make('user.name')
              ->label('Nama Karyawan'),
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

        // --- TAMBAHAN: TextEntry untuk Durasi Overtime ---
        TextEntry::make('duration')
          ->label('Durasi Lembur')
          ->getStateUsing(function (OvertimeRequest $record) {
            if (!$record->start_time || !$record->end_time) {
              return '-';
            }

            // Menggunakan logika aturan sebelumnya
            if ($record->overtime_days == 0) {
              $startTime = Carbon::parse($record->start_time);
              $endTime = Carbon::parse($record->end_time);

              // Hitung selisih jam jika hari lembur bernilai 0
              return $startTime->diffInHours($endTime) . ' jam';
            }

            // Jika hari lembur lebih dari 0, langsung ambil nilai harinya saja
            return $record->overtime_days . ' hari';
          })
          ->badge()
          ->color('success'),
        // -------------------------------------------------

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
          ->label('Status')
          ->badge()
          ->color(function ($state) {
            // Ubah state menjadi huruf kecil semua agar tidak case-sensitive saat dicocokkan
            return match (strtolower($state)) {
              'approved', 'disetujui' => 'success', // Hijau
              'pending', 'menunggu' => 'warning', // Kuning
              'rejected', 'ditolak' => 'danger',  // Merah
              default => 'gray',    // Abu-abu
            };
          }),
        TextEntry::make('approved_by')
          ->label('Approved By')
          ->getStateUsing(function ($record) {
            // Cek apakah kolom approved_by kosong
            if (!$record->approved_by) {
              return '-';
            }

            // Cari data User berdasarkan ID yang ada di kolom approved_by
            $approver = User::find($record->approved_by);

            // Jika user ditemukan tampilkan namanya, jika tidak kembalikan '-'
            return $approver ? $approver->name : '-';
          })
          ->color('success')
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