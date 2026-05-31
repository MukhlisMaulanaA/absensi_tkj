<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TimesheetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PDFShift\PDFShift;

class TimesheetController extends Controller
{
  protected TimesheetService $service;

  public function __construct(TimesheetService $service)
  {
    $this->service = $service;
  }

  public function download(Request $request)
  {
    $data = $request->validate([
      'user_id' => 'required|integer|exists:users,id',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    $user = User::findOrFail($data['user_id']);
    $start = Carbon::parse($data['start_date']);
    $end = Carbon::parse($data['end_date']);

    // Sekarang properti $this->service sudah aman diakses karena sudah di-init di constructor
    $viewData = $this->service->prepareTimesheetData($user, $start, $end);

    $filename = sprintf('timesheet_%s_%s_to_%s.pdf', $user->name, $start->format('dmy'), $end->format('dmy'));

    try {
      // 1. Render file view menjadi string HTML murni
      $html = view('pdf.timesheet', $viewData)->render();

      // 2. Ambil API Key dari .env
      $apiKey = env('PDFSHIFT_API_KEY');

      // 3. Tembak API dengan struktur JSON V3 yang sudah diperbaiki
      $response = Http::withoutVerifying()
        ->withHeaders([
          'X-API-Key' => $apiKey,
          'Content-Type' => 'application/json',
        ])
        ->post('https://api.pdfshift.io/v3/convert/pdf', [
          'source' => $html,
          'sandbox' => true, // Set true jika kuota 50 ingin hemat saat testing
          // Di V3, margin bisa diatur langsung sebagai objek top, right, bottom, left
          'margin' => [
            'top' => '15mm',
            'right' => '15mm',
            'bottom' => '15mm',
            'left' => '15mm'
          ],
          'landscape' => false,
          'use_print' => false
        ]);

      // 4. Jika masih gagal, kita tangkap pesan error detail dari PDFShift
      if ($response->failed()) {
        throw new \Exception('PDFShift API Error (Status ' . $response->status() . '): ' . $response->body());
      }

      // 5. Ambil data biner PDF jika sukses
      $pdfContent = $response->body();

      // 6. PERBAIKAN: Konversi ke Base64 dan lempar ke view Blade
      $base64Pdf = base64_encode($pdfContent);

      return view('pdf.preview', [
        'pdfData' => $base64Pdf,
        'filename' => $filename,
        'user' => $user
      ]);

    } catch (\Exception $e) {
      Log::error('PDFShift Timesheet Error: ' . $e->getMessage());

      // Di sini kita return $e->getMessage() ke json sementara agar Anda bisa melihat 
      // alasan spesifik kenapa Bad Request langsung dari layar browser tanpa buka log.
      return response()->json([
        'status' => 'error',
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
