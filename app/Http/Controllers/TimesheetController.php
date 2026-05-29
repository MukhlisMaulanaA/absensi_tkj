<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TimesheetService;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;
use Carbon\Carbon;

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

    $viewData = $this->service->prepareTimesheetData($user, $start, $end);

    $filename = sprintf('timesheet_%s_%s_to_%s.pdf', $user->id, $start->format('Ymd'), $end->format('Ymd'));

    // return Pdf::view('pdf.timesheet', $viewData)->download($filename);
    return Pdf::view('pdf.timesheet', $viewData)
      ->withBrowsershot(function ($browsershot) {
        // Tambahkan dua baris ini untuk menstabilkan Chrome di Windows
        $browsershot->noSandbox()
                    ->disableGpu();

        // OPSI TAMBAHAN (Jika langkah di atas masih memunculkan kode error yang sama):
        // Paksa sistem menggunakan Google Chrome utama komputer Anda yang sudah terpasang stabil:
        $browsershot->setChromePath('C:\Program Files\Google\Chrome\Application\chrome.exe');
      })
      ->name($filename);
  }
}
