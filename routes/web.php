<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\TimesheetController;

Route::get('/', function () {
  return redirect()->route('login');
});

Route::get('/dashboard', [AttendanceController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
  Route::get('/attendance/status', [AttendanceController::class, 'getTodayStatus']);
  Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
  Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
});

Route::middleware(['auth'])->group(function () {
  Route::get('/attendance', function () {
    return view('attendance.index');
  })->name('attendance.page');
  Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
  Route::get('/overtime/request', [OvertimeRequestController::class, 'create'])->name('overtime.create');
  Route::post('/overtime/request', [OvertimeRequestController::class, 'store'])->name('overtime.store');
  Route::delete('/overtime/{overtime}', [OvertimeRequestController::class, 'destroy'])
    ->name('overtime.destroy')
    ->middleware('auth');
});

Route::middleware(['auth'])->group(function () {
  Route::get('/leave/request', [LeaveRequestController::class, 'create'])->name('leave.create');
  Route::post('/leave/request', [LeaveRequestController::class, 'store'])->name('leave.store');
});

Route::middleware(['auth'])->group(function () {
  Route::get('/attendances/timesheet/pdf', [TimesheetController::class, 'download'])->name('attendances.timesheet.pdf');
});
