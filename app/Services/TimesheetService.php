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
        $period = CarbonPeriod::create($startDate->startOfDay(), $endDate->startOfDay());

        $rows = [];

        $hariKerja = 0;
        $liburMasuk = 0;

        foreach ($period as $date) {
            /** @var \Carbon\Carbon $date */
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('check_in_time', $date->toDateString())
                ->first();

            $rawStatus = $attendance ? $attendance->getRawOriginal('status') : null;

            $isSunday = $date->isSunday();

            $in = '';
            $out = '';
            $keterangan = '';

            if ($isSunday && !$attendance) {
                $keterangan = 'HARI MINGGU';
            } elseif ($attendance) {
                // If attendance has an explicit status like sick/permission/leave
                if (in_array($rawStatus, ['sick', 'permission', 'leave'])) {
                    $mapping = [
                        'sick' => 'SAKIT',
                        'permission' => 'IZIN',
                        'leave' => 'CUTI',
                    ];

                    $keterangan = $mapping[$rawStatus] ?? strtoupper($rawStatus);

                    if (! $isSunday) {
                        $hariKerja++;
                    } else {
                        // if on sunday but has attendance record with status, do not count as libur masuk
                    }
                } else {
                    // Regular present/checked-in day
                    $in = $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i') : '';
                    $out = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('H:i') : '';

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
                // No attendance record
                if ($isSunday) {
                    // handled above
                } else {
                    $keterangan = 'ALPA';
                }
            }

            $rows[] = [
                'date' => $date->toDateString(),
                'day' => $date->locale('id')->translatedFormat('l'),
                'date_display' => $date->locale('id')->translatedFormat('d F Y'),
                'in' => $in,
                'out' => $out,
                'keterangan' => $keterangan,
                'is_sunday' => $isSunday,
            ];
        }

        // Overtime: sum approved overtime requests whose start_time is within the date range
        $overtimeRequests = OvertimeRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_time', '>=', $startDate->toDateString())
            ->whereDate('start_time', '<=', $endDate->toDateString())
            ->get();

        $overtimeHours = 0;

        foreach ($overtimeRequests as $req) {
            if ($req->overtime_days && $req->overtime_days > 0) {
                // assume 8 hours per overtime day
                $overtimeHours += ($req->overtime_days * 8);
            } else {
                $start = Carbon::parse($req->start_time);
                $end = Carbon::parse($req->end_time);
                $overtimeHours += round($start->diffInMinutes($end) / 60, 2);
            }
        }

        $periodString = $startDate->locale('id')->translatedFormat('d F Y') . ' - ' . $endDate->locale('id')->translatedFormat('d F Y');

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
                'overtime_hours' => $overtimeHours,
            ],
        ];
    }
}
