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
      ->visible(fn() => $this->record->status !== 'approved')
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
}
