<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
  protected static string $resource = AttendanceResource::class;

  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make(),
      // Timesheet export action
      \Filament\Actions\Action::make('exportTimesheet')
        ->label('Cetak Absen Harian')
        ->icon('heroicon-o-document-arrow-down')
        ->form([
          \Filament\Forms\Components\Select::make('user_id')
            ->label('Karyawan')
            ->options(\App\Models\User::query()->pluck('name', 'id')->toArray())
            ->searchable()
            ->required(),

          \Filament\Forms\Components\DatePicker::make('start_date')
            ->label('Periode (Mulai)')
            ->required(),

          \Filament\Forms\Components\DatePicker::make('end_date')
            ->label('Periode (Selesai)')
            ->required(),
        ])
        ->action(function (array $data, \Filament\Actions\Action $action) {
          $start = data_get($data, 'start_date');
          $end = data_get($data, 'end_date');

          // Buat string URL tujuan
          $url = route('attendances.timesheet.pdf', [
            'user_id' => $data['user_id'],
            'start_date' => \Carbon\Carbon::parse($start)->toDateString(),
            'end_date' => \Carbon\Carbon::parse($end)->toDateString(),
          ]);

          // TRICK: Picu eksekusi JavaScript langsung ke browser Client untuk force open tab baru
          $action->getLivewire()->js("window.open('{$url}', '_blank');");
        })
        // Hapus ->openUrlInNewTab() karena sudah di-handle secara native oleh script window.open di atas
        ->modalWidth('xl'),
    ];
  }
}
