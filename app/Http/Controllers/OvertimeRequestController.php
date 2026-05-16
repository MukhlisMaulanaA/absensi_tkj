<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRequest;
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
    ]);

    $data = [
      'user_id' => Auth::id(),
      'start_time' => Carbon::parse($validated['start_time']),
      'end_time' => Carbon::parse($validated['end_time']),
      'description' => $validated['description'] ?? null,
      'status' => 'pending',
    ];

    // Handle image upload
    if ($request->hasFile('image')) {
      $imagePath = $request->file('image')->store('overtime-requests', 'public');
      $data['image'] = $imagePath;
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
