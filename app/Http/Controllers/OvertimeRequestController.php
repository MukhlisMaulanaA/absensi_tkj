<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
use App\Services\OvertimeCalculationService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OvertimeRequestController extends Controller
{
  public static function middleware(): array
  {
    // Kembalikan array middleware di sini
    return [
      'auth',
      // atau dengan opsi: new Middleware('auth', collect: ['index', 'show']),
    ];
  }
  // public function __construct()
  // {
  //   $this->middleware('auth');
  // }

  public function create()
  {
    return view('overtime.create');
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'start_time' => 'required|date',
      'end_time' => 'required|date|after:start_time',
      'description' => 'nullable|string|max:1000',
      'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
      'overtime_days' => 'nullable|integer|min:1',
    ]);

    $startTime = Carbon::parse($validated['start_time']);
    $endTime = Carbon::parse($validated['end_time']);

    $data = [
      'user_id' => Auth::id(),
      'start_time' => $startTime,
      'end_time' => $endTime,
      'description' => $validated['description'] ?? null,
      'status' => 'pending',
      'overtime_days' => $validated['overtime_days'] ?? OvertimeCalculationService::calculateOvertimeDays($startTime, $endTime),
    ];

    if ($request->hasFile('images')) {
      $imagePaths = []; // Siapkan array kosong untuk menampung lokasi file

      // Looping setiap file gambar yang diunggah
      foreach ($request->file('images') as $file) {
        // Simpan file ke storage dan masukkan path-nya ke dalam array
        $path = $file->store('overtime-requests', 'public');
        $imagePaths[] = $path;
      }

      // Masukkan array path ke dalam data yang akan disimpan ke database
      $data['image'] = $imagePaths;
    }

    $overtime = OvertimeRequest::create($data);

    if ($request->expectsJson()) {
      return response()->json([
        'success' => true,
        'message' => 'Permintaan lembur berhasil diajukan.',
        'redirect' => route('dashboard'),
        'data' => $overtime
      ]);
    }

    return redirect()->route('dashboard')->with('status', 'Overtime request submitted.');
  }
}
