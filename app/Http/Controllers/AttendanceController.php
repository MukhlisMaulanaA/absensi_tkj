<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceService;

class AttendanceController 
{
  public function __construct(protected AttendanceService $service)
  {
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
}