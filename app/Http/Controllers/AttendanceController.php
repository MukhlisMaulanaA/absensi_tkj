<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController
{
  public function __construct(protected AttendanceService $service)
  {
  }

  public function dashboard()
  {
    $user = Auth::user();
    $todayStatus = $this->service->getTodayAttendanceStatus();

    // Get last 3 attendance records
    $recentAttendances = Attendance::where('user_id', $user->id)
      ->orderBy('check_in_time', 'desc')
      ->limit(3)
      ->get();

    return view('dashboard', [
      'user' => $user,
      'todayStatus' => $todayStatus,
      'recentAttendances' => $recentAttendances,
    ]);
  }

  // Tadi di sini error karena tidak ada nama fungsi:
  public function getStatus()
  {
    try {
      $status = $this->service->getTodayAttendanceStatus();

      return response()->json([
        'status' => $status['status'],
        'data' => $status['data']
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'message' => $e->getMessage()
      ], 400);
    }
  }

  public function checkIn(Request $request)
  {
    $request->validate([
      'latitude' => 'required|numeric',
      'longitude' => 'required|numeric',
      'photo' => 'required|image|max:2048',
    ]);

    try {
      $attendance = $this->service->checkIn($request->all());

      return response()->json([
        'message' => 'Check-in berhasil',
        'data' => $attendance
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'message' => $e->getMessage()
      ], 400);
    }
  }

  public function checkOut(Request $request)
  {
    $request->validate([
      'latitude' => 'required|numeric',
      'longitude' => 'required|numeric',
      'photo' => 'required|image|max:2048',
    ]);

    try {
      $attendance = $this->service->checkOut($request->all());

      return response()->json([
        'message' => 'Check-out berhasil',
        'data' => $attendance
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'message' => $e->getMessage()
      ], 400);
    }
  }

  public function history()
  {
    $user = Auth::user();

    $attendances = Attendance::where('user_id', $user->id)
      ->orderBy('check_in_time', 'desc')
      ->paginate(10, ['*'], 'attendance_page');

    $overtimeRequests = \App\Models\OvertimeRequest::where('user_id', $user->id)
      ->with('approver')
      ->orderBy('created_at', 'desc')
      ->paginate(10, ['*'], 'overtime_page');

    $leaveRequests = LeaveRequest::where('user_id', $user->id)
      ->with('approver')
      ->latest()
      ->paginate(10, ['*'], 'leave_page');

    return view('attendance.history', [
      'user' => $user,
      'attendances' => $attendances,
      'overtimeRequests' => $overtimeRequests,
      'leaveRequests' => $leaveRequests,
    ]);
  }
}