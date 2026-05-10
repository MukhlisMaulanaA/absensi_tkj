<?php

namespace App\Filament\Resources\OvertimeRequests\Pages;

use App\Filament\Resources\OvertimeRequests\OvertimeRequestResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;


class ViewOvertimeRequest extends ViewRecord
{
  protected static string $resource = OvertimeRequestResource::class;

  protected function getHeaderActions(): array
  {
    return [
      $this->getApproveAction(),
      $this->getRejectedAction(),
      EditAction::make(),
    ];
  }

  protected function getApproveAction(): Action
  {
    return Action::make('approve')
      ->label('Approve Overtime')
      ->icon('heroicon-m-check-circle')
      ->color('success')
      ->requiresConfirmation()
      ->visible(fn() => $this->record->status !== 'approved')
      ->action(function () {
        $this->record->update([
          'status' => 'approved',
          'approved_by' => Auth::id(),
        ]);

        // Tambahkan notifikasi di bawah ini
        Notification::make()
          ->success() // Tipe alert (success, warning, danger, info)
          ->title('Overtime Approved')
          ->body('Pengajuan lembur telah berhasil disetujui.')
          ->icon('heroicon-o-check-circle')
          ->send(); // Mengirim notifikasi ke UI
      });
  }

  protected function getRejectedAction(): Action
  {
    return Action::make('reject')
      ->label('Reject Overtime')
      ->icon('heroicon-m-x-circle')
      ->color('danger')
      ->requiresConfirmation()
      ->visible(fn() => $this->record->status != 'rejected')
      ->action(function () {
        $this->record->update([
          'status' => 'rejected',
          'rejected_by' => Auth::id(),
        ]);

        // Tambahkan notifikasi di bawah ini
        Notification::make()
          ->danger() // Tipe alert (success, warning, danger, info)
          ->title('Overtime Rejected')
          ->body('Pengajuan lembur tidak disetujui.')
          ->icon('heroicon-o-x-circle')
          ->send(); // Mengirim notifikasi ke UI
  
      });
  }
}
