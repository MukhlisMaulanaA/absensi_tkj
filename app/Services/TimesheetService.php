<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\OvertimeRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TimesheetService
{
  /**
   * Prepare timesheet data for a user between two dates.
   *
   * @param User $user
   * @param Carbon $startDate
   * @param Carbon $endDate
   * @return array
   */
  public function prepareTimesheetData(User $user, Carbon $startDate, Carbon $endDate): array
  {
    // 1. PETAKAN DATA OVERTIME TERLEBIH DAHULU (DI LUAR LOOP UTAMA)
    $overtimeRequests = OvertimeRequest::where('user_id', $user->id)
      ->where('status', 'approved')
      ->whereDate('start_time', '>=', $startDate->toDateString())
      ->whereDate('start_time', '<=', $endDate->toDateString())
      ->get();

    $overtimeByDate = [];
    $totalOvertimeDaysAll = 0;
    $totalOvertimeHoursAll = 0;

    foreach ($overtimeRequests as $req) {
      $dateKey = Carbon::parse($req->start_time)->toDateString();

      if (!isset($overtimeByDate[$dateKey])) {
        $overtimeByDate[$dateKey] = [
          'days' => 0,
          'hours' => 0
        ];
      }

      // Adopsi Logika Tabel Filament Website
      if ($req->overtime_days == 0) {
        // Skenario 1: Jika hari 0, hitung selisih jam antara start_time dan end_time
        $startTime = Carbon::parse($req->start_time);
        $endTime = Carbon::parse($req->end_time);

        // Menghitung selisih jam (gunakan diffInHours)
        $hours = $startTime->diffInHours($endTime);
        
        $overtimeByDate[$dateKey]['hours'] += $hours;
        $totalOvertimeHoursAll += $hours;
      } else {
        // Skenario 2: Jika hari > 0, langsung ambil nilai harinya saja
        $overtimeByDate[$dateKey]['days'] += $req->overtime_days;
        $totalOvertimeDaysAll += $req->overtime_days;
      }
    }

    // 2. PROSES GENERATE BARIS KALENDER TIMESHEET
    $period = CarbonPeriod::create($startDate->startOfDay(), $endDate->startOfDay());
    $rows = [];

    $hariKerja = 0;
    $liburMasuk = 0;
    $uOvertimeDays = 0; // Untuk akumulasi U. Overtime nasional/perusahaan

    foreach ($period as $date) {
      /** @var \Carbon\Carbon $date */
      $dateString = $date->toDateString();

      $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('check_in_time', $dateString)
        ->first();

      $rawStatus = $attendance?->getRawOriginal('status');
      $isSunday = $date->isSunday();

      $in = '-';
      $out = '-';
      $keterangan = '';
      $uOvertimeDaily = 0; // Flag harian (1 jika >= 12 jam, 0 jika tidak)

      if ($attendance) {
        // Parsing jam masuk
        if ($attendance->check_in_time) {
          $in = Carbon::parse($attendance->check_in_time)
            ->setTimezone('Asia/Jakarta')
            ->format('H:i');
        }

        // Parsing jam pulang
        if ($attendance->check_out_time) {
          $out = Carbon::parse($attendance->check_out_time)
            ->setTimezone('Asia/Jakarta')
            ->format('H:i');
        }

        /// KOREKSI 1: U. Overtime HANYA dihitung jika statusnya BUKAN sakit, izin, atau cuti
        if (!in_array($rawStatus, ['sick', 'permission', 'leave']) && $attendance->check_in_time && $attendance->check_out_time) {
          $checkInCarbon = Carbon::parse($attendance->check_in_time);
          $checkOutCarbon = Carbon::parse($attendance->check_out_time);

          $durationHours = $checkInCarbon->diffInMinutes($checkOutCarbon) / 60;

          if ($durationHours >= 12) {
            $uOvertimeDaily = 1;
            $uOvertimeDays++;
          }
        }

        // Klasifikasi Status Khusus (Sakit, Izin, Cuti)
        if (in_array($rawStatus, ['sick', 'permission', 'leave'])) {
          $statusMap = [
            'sick' => 'SAKIT',
            'permission' => 'IZIN',
            'leave' => 'CUTI',
          ];

          $keterangan = $statusMap[$rawStatus] ?? strtoupper($rawStatus);
          
        } else {
          // Klasifikasi Kehadiran Normal (Present)
          if ($attendance->late_minutes > 0) {
            $keterangan = 'TERLAMBAT ' . $attendance->late_minutes . ' MENIT';
          }

          if ($isSunday) {
            $liburMasuk++;
          } else {
            $hariKerja++;
          }
        }

      } else {
        // Tidak ada data transaksi absensi sama sekali (Hari Minggu / Alpa)
        if ($isSunday) {
          $keterangan = 'HARI MINGGU';
        } else {
          $keterangan = 'ALPA';
        }
      }

      // Ambil jam lembur yang sudah dipetakan di awal untuk tanggal ini
      $dailyOvt = $overtimeByDate[$dateString] ?? ['days' => 0, 'hours' => 0];
      $dailyResult = [];

      if ($dailyOvt['days'] > 0) {
        $dailyResult[] = $dailyOvt['days'] . ' Hari';
      }
      if ($dailyOvt['hours'] > 0) {
        $dailyResult[] = $dailyOvt['hours'] . ' Jam';
      }

      // Format string output tampilan harian (default '0 Jam' jika kosong)
      $dailyOvertimeDisplay = !empty($dailyResult) ? implode(', ', $dailyResult) : '0 Jam';

      $rows[] = [
        'date' => $dateString,
        'day' => $date->locale('id')->translatedFormat('l'),
        'date_display' => $date->locale('id')->translatedFormat('d F Y'),
        'in' => $in,
        'out' => $out,
        'overtime_hours_daily' => $dailyOvertimeDisplay, // Output String: "3 Hari" atau "2 Jam"
        'u_overtime' => $uOvertimeDaily,       
        'keterangan' => $keterangan,
        'is_sunday' => $isSunday,
      ];
    }

    $periodString = $startDate->locale('id')->translatedFormat('d F Y') . ' - ' . $endDate->locale('id')->translatedFormat('d F Y');

    // Menyusun teks output untuk total akumulasi di footer summary
    $summaryResult = [];
    if ($totalOvertimeDaysAll > 0) {
      $summaryResult[] = $totalOvertimeDaysAll . ' Hari';
    }
    if ($totalOvertimeHoursAll > 0) {
      $summaryResult[] = $totalOvertimeHoursAll . ' Jam';
    }
    $totalOvertimeString = !empty($summaryResult) ? implode(', ', $summaryResult) : '0 Jam';

    return [
      'user' => $user,
      'project' => optional($user->location)->name, 
      'jabatan' => $user->jabatan ?? '',
      'start_date' => $startDate,
      'end_date' => $endDate,
      'period_string' => $periodString,
      'rows' => $rows,
      'summary' => [
        'hari_kerja' => $hariKerja,
        'libur_masuk' => $liburMasuk,
        'overtime_hours' => $totalOvertimeString, // Output string gabungan untuk footer
        'u_overtime_days' => $uOvertimeDays, 
      ],
    ];
  }
}