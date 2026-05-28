<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

use function PHPUnit\Framework\isEmpty;

class AttendancesTable
{
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('user.name')
          ->label('Employee')
          ->searchable()
          ->sortable()
          ->wrap(),
        TextColumn::make('location.name')
          ->label('Location')
          ->searchable()
          ->sortable(),
        BadgeColumn::make('status')
          ->label('Status')
          ->formatStateUsing(function ($state) {
            $statuses = [
              'on_time' => 'On Time',
              'late' => 'Late',
              'absent' => 'Absent',
              'sick' => 'Sakit',
              'permission' => 'Izin',
              'leave' => 'Cuti',
            ];
            return $statuses[$state] ?? ucfirst($state);
          })
          ->colors([
            'success' => 'on_time',
            'warning' => 'late',
            'danger' => 'absent',
            'danger' => 'sick',       // Warna badge merah/merah muda untuk Sakit
            'info' => 'permission', // Warna badge biru untuk Izin
            'secondary' => 'leave',    // Warna badge abu-abu untuk Cuti
          ])
          ->getStateUsing(function ($record) {
            // Ambil status asli langsung dari database untuk menghindari proteksi Eloquent
            $statusAsli = $record->getRawOriginal('status') ?? $record->status;

            // Jika status asli adalah perizinan, langsung kembalikan ke Filament
            if (in_array($statusAsli, ['sick', 'permission', 'leave', 'absent'])) {
              return $statusAsli;
            }

            // Jika statusnya kosong atau present, baru jalankan kalkulasi jam Anda
            if (!$record->check_in_time) {
              return 'absent';
            }

            return $record->late_minutes > 0 ? 'late' : 'on_time';
          }),
        TextColumn::make('check_in_time')
          ->label('Check-In')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->wrap(),
        TextColumn::make('check_out_time')
          ->label('Check-Out')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->wrap()
          ->placeholder('-'),
        TextColumn::make('working_hours')
          ->label('Working Hours')
          ->getStateUsing(function ($record) {
            if (!$record->check_in_time || !$record->check_out_time) {
              return '-';
            }
            return $record->working_hours . 'h';
          })
          ->sortable(),
        TextColumn::make('overtime_hours')
          ->label('Overtime Hours')
          ->getStateUsing(function ($record) {
            // Validasi jika relasi user atau check_in_time kosong
            if (!$record->user || !$record->check_in_time) {
              return '-';
            }

            // Ambil tanggal absensi
            $attendanceDate = Carbon::parse($record->check_in_time)->toDateString();

            // Mengambil data lembur yang 'start_time'-nya SAMA PERSIS dengan tanggal absensi
            $overtimeRequests = \App\Models\OvertimeRequest::where('user_id', $record->user_id)
              ->where('status', 'approved')
              ->whereDate('start_time', $attendanceDate) // <-- HANYA AMBIL DI HARI YANG SAMA
              ->get();

            if ($overtimeRequests->isEmpty()) {
              return '-';
            }

            // Inisialisasi variabel penampung total
            $totalDays = 0;
            $totalHours = 0;

            foreach ($overtimeRequests as $request) {
              if ($request->overtime_days == 0) {
                // Skenario 1: Jika hari 0, hitung selisih jam antara start_time dan end_time
                $startTime = Carbon::parse($request->start_time);
                $endTime = Carbon::parse($request->end_time);

                // Menghitung selisih jam (gunakan diffInHours)
                $totalHours += $startTime->diffInHours($endTime);
              } else {
                // Skenario 2: Jika hari > 0, langsung ambil nilai harinya saja
                $totalDays += $request->overtime_days;
              }
            }

            // Menyusun teks output berdasarkan hasil akumulasi
            $result = [];

            if ($totalDays > 0) {
              $result[] = $totalDays . ' hari';
            }

            if ($totalHours > 0) {
              $result[] = $totalHours . ' jam';
            }

            // Jika ada data tapi hasil akhirnya 0 (misal input salah), kembalikan '-'
            return !empty($result) ? implode(' ', $result) : '-';
          }),
        TextColumn::make('late_minutes')
          ->label('Late (mins)')
          ->numeric()
          ->sortable()
          ->getStateUsing(function ($record) {
            return $record->late_minutes > 0 ? $record->late_minutes : '-';
          }),
        IconColumn::make('is_within_radius')
          ->label('Within Radius')
          ->boolean()
          ->trueIcon('heroicon-o-check-circle')
          ->falseIcon('heroicon-o-x-circle'),
        TextColumn::make('created_at')
          ->label('Date')
          ->date('d/m/Y')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: false),
        TextColumn::make('deleted_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->defaultSort('check_in_time', 'desc')
      ->filters([
        Filter::make('today')
          ->label('Today Only')
          ->toggle()
          ->query(fn(Builder $query) => $query->whereDate('check_in_time', today())),
        TrashedFilter::make(),
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
