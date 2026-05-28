<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
  public function create()
  {
    return view('leave.create');
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'type' => 'required|in:sick,permission,leave',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'reason' => 'required|string|max:1000',
      'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ]);

    $data = [
      'user_id' => Auth::id(),
      'type' => $validated['type'],
      'start_date' => $validated['start_date'],
      'end_date' => $validated['end_date'],
      'reason' => $validated['reason'],
      'status' => 'pending',
    ];

    if ($request->hasFile('attachment')) {
      $data['attachment'] = $request->file('attachment')->store('leave-attachments', 'public');
    }

    $leaveRequest = LeaveRequest::create($data);

    if ($request->expectsJson()) {
      return response()->json([
        'success' => true,
        'message' => 'Leave request submitted successfully.',
        'redirect' => route('dashboard'),
        'data' => $leaveRequest
      ]);
    }

    return redirect()->route('dashboard')->with('status', 'Leave request submitted.');
  }
}
