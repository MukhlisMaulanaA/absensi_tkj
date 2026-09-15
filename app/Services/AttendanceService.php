<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
    public function getTodayAttendanceStatus()
    {
        $user = Auth::user();

        $attendance = $this->openAttendanceFor($user->id)
          ?? Attendance::where('user_id', $user->id)
              ->whereDate('check_in_time', today())
              ->first();

        if (! $attendance) {
            return [
                'status' => 'not_checked_in',
                'data' => null,
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'status' => 'checked_out',
                'data' => $attendance,
            ];
        }

        return [
            'status' => 'checked_in',
            'data' => $attendance,
        ];
    }

    public function checkIn(array $data)
    {
        $user = Auth::user();

        if (! $user->location) {
            throw new \Exception('User belum memiliki lokasi kerja');
        }

        // An unclosed attendance must be completed before another shift begins.
        // This also protects against a missed scheduler run creating duplicates.
        $existing = $this->openAttendanceFor($user->id)
          ?? Attendance::where('user_id', $user->id)
              ->whereDate('check_in_time', today())
              ->first();

        if ($existing) {
            throw new \Exception('Anda sudah check-in hari ini');
        }

        $location = $user->location;

        $isWithinRadius = app(GeoFenceService::class)
            ->isWithinRadius(
                $data['latitude'],
                $data['longitude'],
                $location->latitude,
                $location->longitude,
                $location->radius
            );

        $photoPath = $data['photo']->store('attendance/checkin', 'public');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'location_id' => $location->id,

            'check_in_time' => now(),
            'check_in_latitude' => $data['latitude'],
            'check_in_longitude' => $data['longitude'],
            'check_in_photo' => $photoPath,

            'is_within_radius' => $isWithinRadius,
        ]);

        $attendance->update([
            'late_minutes' => $attendance->calculateLateMinutes(),
        ]);

        return $attendance;
    }

    public function checkOut(array $data)
    {
        $user = Auth::user();

        $attendance = $this->openAttendanceFor($user->id);

        if (! $attendance) {
            throw new \Exception('Belum check-in');
        }

        if ($attendance->check_out_time) {
            throw new \Exception('Sudah check-out');
        }

        if ($this->systemCheckoutDeadline($attendance)->isPast()) {
            throw new \Exception('Batas check-out pukul 06:30 telah terlewati. Check-out akan diproses oleh sistem.');
        }

        $photoPath = $data['photo']->store('attendance/checkout', 'public');

        $attendance->update([
            'check_out_time' => now(),
            'check_out_source' => 'manual',
            'check_out_latitude' => $data['latitude'],
            'check_out_longitude' => $data['longitude'],
            'check_out_photo' => $photoPath,
        ]);

        return $attendance;
    }

    private function openAttendanceFor(int $userId): ?Attendance
    {
        return Attendance::where('user_id', $userId)
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->latest('check_in_time')
            ->first();
    }

    private function systemCheckoutDeadline(Attendance $attendance): Carbon
    {
        return Carbon::parse($attendance->check_in_time)
            ->addDay()
            ->setTime(6, 30, 0);
    }
}
