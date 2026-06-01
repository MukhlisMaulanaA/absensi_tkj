<?php

namespace App\Filament\Resources\LeaveRequests\Pages;

use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewLeaveRequest extends ViewRecord
{
  protected static string $resource = LeaveRequestResource::class;

  protected function getHeaderActions(): array
  {
    return [
      $this->getApproveAction(),
      $this->getRejectAction(), // 1. Daftarkan opsi baru di sini
      EditAction::make(),
    ];
  }

  protected function getApproveAction(): Action
  {
    return Action::make('approve')
      ->label('Approve Leave')
      ->icon('heroicon-m-check-circle')
      ->color('success')
      ->requiresConfirmation()
      // Diubah menjadi === 'pending' agar tombol hilang jika status sudah 'rejected'
      ->visible(fn() => $this->record->status === 'pending')
      ->action(function () {
        try {
          $this->record->approveBy(Auth::id());

          Notification::make()->success()->title('Leave Approved')->send();
        } catch (\Exception $e) {
          Notification::make()->danger()->title('Approval Failed')->body($e->getMessage())->send();
          throw $e;
        }
      });
  }

  // 2. Buat fungsi getRejectAction khusus halaman View
  protected function getRejectAction(): Action
  {
    return Action::make('reject')
      ->label('Reject Leave')
      ->icon('heroicon-m-x-circle')
      ->color('danger')
      ->requiresConfirmation()
      ->modalHeading('Tolak Pengajuan Izin')
      ->modalDescription('Apakah Anda yakin ingin menolak pengajuan izin/sakit ini?')
      ->visible(fn() => $this->record->status === 'pending') // Hanya muncul jika status pending
      ->action(function () {
        try {
          $this->record->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
          ]);

          Notification::make()->success()->title('Leave Request Rejected')->send();
        } catch (\Exception $e) {
          Notification::make()->danger()->title('Rejection Failed')->body($e->getMessage())->send();
          throw $e;
        }
      });
  }
}
