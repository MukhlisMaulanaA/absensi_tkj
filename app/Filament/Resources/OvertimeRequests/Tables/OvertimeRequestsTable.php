<?php

namespace App\Filament\Resources\OvertimeRequests\Tables;

use App\Models\OvertimeRequest;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter; // <-- TAMBAHKAN IMPORT INI
use Filament\Tables\Table;

class OvertimeRequestsTable
{
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('user.name')
          ->label('Nama Karyawan')
          ->searchable()
          ->sortable(),
        TextColumn::make('start_time')
          ->dateTime()
          ->sortable(),
        TextColumn::make('end_time')
          ->dateTime()
          ->sortable(),
        BadgeColumn::make('overtime_days')
          ->label('Hari Lembur')
          ->color('info')
          ->formatStateUsing(fn(OvertimeRequest $record) => $record->getOvertimeDaysLabel())
          ->sortable(),
        TextColumn::make('status')
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
        ImageColumn::make('image')
          ->label('Lampiran Gambar')
          ->disk('public')
          // ->multiple() // <--- WAJIB DITAMBAHKAN agar Filament tahu ini adalah array gambar
          ->circular() // Opsional: membuat preview gambar jadi bulat rapi
          ->stacked()  // Opsional: membuat tumpukan gambar yang estetik menyamping
          ->limit(3),   // Opsional: batasi hanya 3 gambar yang muncul di tabel jika terlalu banyak
        // ->limitedRemainingImagesPopover(), // Opsional: sisa gambar bisa dilihat saat di-hover/klik
        TextColumn::make('approved_by')
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
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('deleted_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        TrashedFilter::make(),

        // --- TAMBAHKAN KODE FILTER STATUS DI SINI ---
        SelectFilter::make('status')
          ->label('Filter Status')
          ->options([
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
          ]),
        // --------------------------------------------
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