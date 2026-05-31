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
    $totalOvertimeHours = 0;

    foreach ($overtimeRequests as $req) {
      $reqStart = Carbon::parse($req->start_time);

      if ($req->overtime_days && $req->overtime_days > 0) {
        // Skema Multi-Hari Lembuur (Asumsi 8 Jam Kerja per Hari)
        for ($i = 0; $i < $req->overtime_days; $i++) {
          $dateKey = $reqStart->copy()->addDays($i)->toDateString();
          $overtimeByDate[$dateKey] = ($overtimeByDate[$dateKey] ?? 0) + 8;
          $totalOvertimeHours += 8;
        }
      } else {
        // Skema Jam-jaman (Hari Tunggal)
        $dateKey = $reqStart->toDateString();
        $startOvt = Carbon::parse($req->start_time);
        $endOvt = Carbon::parse($req->end_time);
        $hours = round($startOvt->diffInMinutes($endOvt) / 60, 2);

        $overtimeByDate[$dateKey] = ($overtimeByDate[$dateKey] ?? 0) + $hours;
        $totalOvertimeHours += $hours;
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
      $dailyOvertimeHours = $overtimeByDate[$dateString] ?? 0;

      $rows[] = [
        'date' => $dateString,
        'day' => $date->locale('id')->translatedFormat('l'),
        'date_display' => $date->locale('id')->translatedFormat('d F Y'),
        'in' => $in,
        'out' => $out,
        'overtime_hours_daily' => $dailyOvertimeHours, // Jam Lembur Harian (Hasil Pengajuan)
        'u_overtime' => $uOvertimeDaily,       // Indeks U. Overtime Harian (0 atau 1 Hari)
        'keterangan' => $keterangan,
        'is_sunday' => $isSunday,
      ];
    }

    $periodString = $startDate->locale('id')->translatedFormat('d F Y') . ' - ' . $endDate->locale('id')->translatedFormat('d F Y');

    return [
      'user' => $user,
      'project' => optional($user->location)->name, // Diisi dari Assign Location proyek
      'jabatan' => $user->jabatan ?? '',
      'start_date' => $startDate,
      'end_date' => $endDate,
      'period_string' => $periodString,
      'rows' => $rows,
      'summary' => [
        'hari_kerja' => $hariKerja,
        'libur_masuk' => $liburMasuk,
        'overtime_hours' => $totalOvertimeHours, // Total keseluruhan overtime
        'u_overtime_days' => $uOvertimeDays, // Hasil Akumulasi U. Overtime untuk Footer PDF
      ],
    ];
  }
}