<?php

namespace App\Filament\Resources\LeaveRequests\Tables;

use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action as TableAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class LeaveRequestsTable
{
  public static function configure(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('user.name')->label('Nama Karyawan')->searchable()->sortable(),
        TextColumn::make('start_date')->date()->sortable(),
        TextColumn::make('end_date')->date()->sortable(),
        TextColumn::make('type')->label('Type')->sortable(),
        BadgeColumn::make('status')
          ->label('Status')
          ->color(fn($state) => match (strtolower($state)) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'gray',
          }),
        TextColumn::make('reason')->limit(50)->wrap()->columnSpanFull(),
        TextColumn::make('approved_by')
          ->label('Approved By')
          ->getStateUsing(function ($record) {
            if (!$record->approved_by) {
              return '-';
            }

            $approver = User::find($record->approved_by);

            return $approver ? $approver->name : '-';
          })
          ->color('success')
          ->placeholder('-'),
        TextColumn::make('created_at')->dateTime()->sortable(),
      ])
      ->filters([
        TrashedFilter::make(),
        SelectFilter::make('status')
          ->label('Filter Status')
          ->options([
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
          ]),
      ])
      ->recordActions([
        ViewAction::make(),
        EditAction::make(),

        // 1. OPSI APPROVE (Diubah visibilitasnya agar lebih aman)
        TableAction::make('approve')
          ->label('Approve')
          ->icon('heroicon-m-check-circle')
          ->color('success')
          ->requiresConfirmation()
          // Mengubah !== 'approved' menjadi === 'pending' agar tombol hilang jika sudah di-reject
          ->visible(fn($record) => $record->status === 'pending')
          ->action(function (LeaveRequest $record) {
            try {
              $record->approveBy(Auth::id());

              Notification::make()->success()->title('Leave Approved')->send();
            } catch (\Exception $e) {
              Notification::make()->danger()->title('Approval Failed')->body($e->getMessage())->send();
              throw $e;
            }
          }),

        // 2. OPSI REJECT (Baru)
        TableAction::make('reject')
          ->label('Reject')
          ->icon('heroicon-m-x-circle')
          ->color('danger')
          ->requiresConfirmation()
          ->modalHeading('Tolak Pengajuan Izin')
          ->modalDescription('Apakah Anda yakin ingin menolak pengajuan ini?')
          ->visible(fn($record) => $record->status === 'pending') // Hanya muncul saat pending
          ->action(function (LeaveRequest $record) {
            try {
              // Melakukan update status langsung ke database
              $record->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
              ]);

              Notification::make()->success()->title('Leave Request Rejected')->send();
            } catch (\Exception $e) {
              Notification::make()->danger()->title('Rejection Failed')->body($e->getMessage())->send();
              throw $e;
            }
          }),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
          ForceDeleteBulkAction::make(),
          RestoreBulkAction::make(),
        ]),
      ]);
  }
}
