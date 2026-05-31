<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    return redirect()
      ->route('dashboard')
      ->with('success', 'Leave request submitted successfully.');
  }

  public function destroy(LeaveRequest $leaveRequest)
  {
    abort_if($leaveRequest->user_id !== Auth::id(), 403);

    if ($leaveRequest->status !== 'pending') {
      return back()->with('error', 'Only pending requests can be deleted.');
    }

    if ($leaveRequest->attachment) {
      Storage::disk('public')->delete($leaveRequest->attachment);
    }

    $leaveRequest->delete();

    return back()->with('success', 'Leave request deleted successfully.');
  }
}
