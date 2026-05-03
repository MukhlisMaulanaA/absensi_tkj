<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceService 
{
  public function getTodayAttendanceStatus()
  {
    $user = \Illuminate\Support\Facades\Auth::user();
    
    $attendance = Attendance::where('user_id', $user->id)
      ->whereDate('check_in_time', today())
      ->first();

    if (!$attendance) {
      return [
        'status' => 'not_checked_in',
        'data' => null
      ];
    }

    if ($attendance->check_out_time) {
      return [
        'status' => 'checked_out',
        'data' => $attendance
      ];
    }

    return [
      'status' => 'checked_in',
      'data' => $attendance
    ];
  }

  public function checkIn(array $data)
  {
    $user = Auth::user();

    if (!$user->location) {
      throw new \Exception('User belum memiliki lokasi kerja');
    }

    $existing = Attendance::where('user_id', $user->id)
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
      'late_minutes' => $attendance->calculateLateMinutes()
    ]);

    return $attendance;
  }

  public function checkOut(array $data)
  {
    $user = Auth::user();

    $attendance = Attendance::where('user_id', $user->id)
      ->whereDate('check_in_time', today())
      ->first();

    if (!$attendance) {
      throw new \Exception('Belum check-in');
    }

    if ($attendance->check_out_time) {
      throw new \Exception('Sudah check-out');
    }

    $photoPath = $data['photo']->store('attendance/checkout', 'public');

    $attendance->update([
      'check_out_time' => now(),
      'check_out_latitude' => $data['latitude'],
      'check_out_longitude' => $data['longitude'],
      'check_out_photo' => $photoPath,
    ]);

    return $attendance;
  }
}