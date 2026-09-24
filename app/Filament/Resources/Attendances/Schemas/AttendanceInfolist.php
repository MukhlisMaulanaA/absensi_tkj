<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Filament\Infolists\Components\AttendanceMapEntry;
use App\Models\Attendance;
use App\Models\OvertimeRequest;
use Dom\Text;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class AttendanceInfolist
{
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        // Employee Information Section
        Section::make('Employee Information')
          ->icon('heroicon-o-user')
          ->columns(2)
          ->schema([
            TextEntry::make('user.name')
              ->label('Employee Name')
              // ->url(fn($record) => $record->user ? route('filament.admin.resources.users.view', $record->user) : null)
              ->columnSpanFull(),
            TextEntry::make('user.email')
              ->label('Email')
              ->icon('heroicon-o-envelope')
              ->columnSpanFull(),
            TextEntry::make('location.name')
              ->label('Assigned Location')
              ->placeholder('-')
              ->icon('heroicon-o-map-pin')
              ->columnSpanFull(),
          ]),

        // Attendance Status Section
        Section::make('Attendance Status')
          ->icon('heroicon-o-check-badge')
          ->columns(3)
          ->schema([
            TextEntry::make('status')
              ->label('Status')
              ->getStateUsing(function ($record) {
                if (!$record->check_in_time) {
                  return 'Absent';
                }
                return $record->late_minutes > 0 ? 'Late' : 'On Time';
              })
              ->badge()
              ->color(function ($state) {
                return match ($state) {
                  'On Time' => 'success',
                  'Late' => 'warning',
                  'Absent' => 'danger',
                  default => 'gray',
                };
              })
              ->columnSpan(1),
            TextEntry::make('late_minutes')
              ->label('Late Minutes')
              ->getStateUsing(function ($record) {
                return $record->late_minutes > 0 ? $record->late_minutes . ' min' : '-';
              })
              ->columnSpan(1),
            IconEntry::make('is_within_radius')
              ->label('Within Radius')
              ->boolean()
              ->columnSpan(1),
          ]),

        Section::make('Check-In Details')
          ->icon('heroicon-o-arrow-down-on-square')
          ->collapsible()
          ->columns(2)
          ->schema([
            TextEntry::make('check_in_time')
              ->label('Check-In Time')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-')
              ->columnSpanFull(),
            TextEntry::make('check_in_latitude')
              ->label('Latitude')
              ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-')
              ->placeholder('-'),
            TextEntry::make('check_in_longitude')
              ->label('Longitude')
              ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-')
              ->placeholder('-'),
            TextEntry::make('check_in_photo')
              ->label('Check-In Photo')
              ->placeholder('-')
              ->columnSpanFull()
              ->url(fn($state) => $state ? asset('storage/' . $state) : null, shouldOpenInNewTab: true)
              ->hidden(fn($state) => !$state),
          ]),

        // Check-Out Details Section
        Section::make('Check-Out Details')
          ->icon('heroicon-o-arrow-up-on-square')
          ->collapsible()
          ->columns(2)
          ->schema([
            TextEntry::make('check_out_time')
              ->label('Check-Out Time')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-')
              ->columnSpanFull(),
            TextEntry::make('check_out_latitude')
              ->label('Latitude')
              ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-')
              ->placeholder('-'),
            TextEntry::make('check_out_longitude')
              ->label('Longitude')
              ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-')
              ->placeholder('-'),
            TextEntry::make('check_out_photo')
              ->label('Check-Out Photo')
              ->placeholder('-')
              ->columnSpanFull()
              ->url(fn($state) => $state ? asset('storage/' . $state) : null, shouldOpenInNewTab: true)
              ->hidden(fn($state) => !$state),
            TextEntry::make('working_hours')
              ->label('Working Hours')
              ->getStateUsing(function ($record) {
                if (!$record->check_in_time || !$record->check_out_time) {
                  return '-';
                }
                return $record->working_hours . ' hours';
              })
              ->columnSpanFull(),
          ]),

        // Location Map Section
        Section::make('Location Map')
          ->icon('heroicon-o-map')
          ->collapsible()
          ->columnSpanFull()
          ->schema([
            AttendanceMapEntry::make('location_map')
              ->columnSpanFull(),
          ]),

        // Timestamps Section
        Section::make('Record Information')
          ->icon('heroicon-o-clock')
          ->collapsible()
          ->columns(2)
          ->schema([
            TextEntry::make('created_at')
              ->label('Created At')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-'),
            TextEntry::make('updated_at')
              ->label('Updated At')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-'),
            TextEntry::make('deleted_at')
              ->label('Deleted At')
              ->dateTime('d/m/Y H:i:s')
              ->placeholder('-')
              ->visible(fn(Attendance $record): bool => $record->trashed()),
          ]),

        Section::make('Overtime Request')
          ->icon('heroicon-o-check-badge')
          ->columns(3)
          ->schema([
            TextEntry::make('overtime_hours')
              ->label('Overtime Hours')
              ->getStateUsing(function ($record) {
                // Validasi jika relasi user atau check_in_time kosong
                if (!$record->user || !$record->check_in_time) {
                  return '-';
                }

                $attendanceDate = $record->check_in_time->toDateString();

                $overtimeRequests = OvertimeRequest::where('user_id', $record->user_id)
                  ->whereDate('start_time', '<=', $attendanceDate)
                  ->whereDate('end_time', '>=', $attendanceDate)
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
            TextEntry::make('status')
              ->label('Status')
              ->badge() // 1. Tambahkan ini untuk mengubah teks biasa menjadi bentuk badge
              ->getStateUsing(function ($record) {
                $overtimeRequest = OvertimeRequest::where('user_id', $record->user_id)
                  ->first(['status']); // Mengambil kolom status saja demi performa
          
                if (!$overtimeRequest) {
                  return 'No Request'; // Mengembalikan teks default jika tidak ada data
                }

                return $overtimeRequest->status;
              })
              ->color(function ($state) {
                // 2. Sesuaikan value di dalam match dengan isi data di database Anda
                return match ($state) {
                  'approved', 'On Time' => 'success',   // Warna Hijau
                  'pending', 'Late' => 'warning',   // Warna Kuning
                  'rejected', 'Absent' => 'danger',    // Warna Merah
                  default => 'gray',      // Warna Abu-abu (untuk '-' atau 'No Request')
                };
              }),

            TextEntry::make('approved_by')
              ->label('Approved By')
              ->getStateUsing(function ($record) {
                // Ambil data overtime request (disarankan pakai filter tanggal seperti kode Anda sebelumnya agar akurat)
                $overtimeRequest = OvertimeRequest::where('user_id', $record->user_id)
                  ->where('status', 'approved') // Pastikan statusnya memang approved
                  ->first();

                // Jika tidak ada data lembur atau belum di-approve
                if (!$overtimeRequest || !$overtimeRequest->approved_by) {
                  return '-';
                }

                // --- OPSI 1: Jika Anda SUDAH punya relasi di Model OvertimeRequest ---
                // Misal di model OvertimeRequest ada: public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
                return $overtimeRequest->approver->name;

                // --- OPSI 2: Jika BELUM punya relasi (Query manual) ---
                // $approver = User::find($overtimeRequest->approved_by);
          
                return $approver ? $approver->name : '-';
              })
              ->icon('heroicon-o-check-badge') // (Opsional) Tambahkan icon agar tampilan lebih bagus
              ->color('success')               // (Opsional) Beri warna teks
              ->weight('bold'),
          ]),
      ]);
  }
}
