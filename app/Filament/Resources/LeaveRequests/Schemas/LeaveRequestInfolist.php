<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveRequestInfolist
{
  public static function configure(Schema $schema): Schema
  {
    Carbon::setLocale('id');

    return $schema
      ->schema([

        // ── Row 1: Informasi Pengajuan (kiri) + Detail Pengajuan (kanan) ──
        Grid::make([
          'default' => 1,
        ])
          ->schema([

            Section::make('Informasi Pengajuan')
              ->icon('heroicon-o-user')
              ->compact()
              ->schema([
                Grid::make(2)
                  ->schema([

                    TextEntry::make('user.name')
                      ->label('Pengaju')
                      ->badge()
                      ->color('primary'),

                    TextEntry::make('status')
                      ->label('Status')
                      ->badge()
                      ->color(fn(string $state): string => match (strtolower($state)) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                      }),

                    TextEntry::make('approver.name')
                      ->label('Approved By')
                      ->placeholder('-')
                      ->visible(fn($record) => filled($record->approved_by)),

                    TextEntry::make('updated_at')
                      ->label('Approval Date')
                      ->visible(fn($record) => $record->status === 'approved')
                      ->formatStateUsing(
                        fn($state) =>
                        Carbon::parse($state)
                          ->translatedFormat('l, d F Y H:i')
                      ),
                  ]),
              ]),

            Section::make('Detail Pengajuan')
              ->icon('heroicon-o-calendar-days')
              ->compact()
              ->columns(2)
              ->schema([

                TextEntry::make('type')
                  ->label('Jenis')
                  ->badge()
                  ->formatStateUsing(fn($state) => ucfirst($state))
                  ->color(fn(string $state): string => match (strtolower($state)) {
                    'sick' => 'danger',
                    'permission' => 'warning',
                    'leave' => 'info',
                    default => 'gray',
                  }),

                TextEntry::make('created_at')
                  ->label('Tanggal Pengajuan')
                  ->formatStateUsing(
                    fn($state) =>
                    Carbon::parse($state)
                      ->translatedFormat('l, d F Y')
                  ),

                TextEntry::make('start_date')
                  ->label('Mulai')
                  ->formatStateUsing(
                    fn($state) =>
                    Carbon::parse($state)
                      ->translatedFormat('l, d F Y')
                  ),

                TextEntry::make('end_date')
                  ->label('Selesai')
                  ->formatStateUsing(
                    fn($state) =>
                    Carbon::parse($state)
                      ->translatedFormat('l, d F Y')
                  ),

                TextEntry::make('duration')
                  ->label('Durasi')
                  ->badge()
                  ->color('gray')
                  ->getStateUsing(function (LeaveRequest $record) {
                    $days = Carbon::parse($record->start_date)
                      ->diffInDays(Carbon::parse($record->end_date)) + 1;

                    return $days . ' Hari';
                  }),

                TextEntry::make('id')
                  ->label('Request ID')
                  ->copyable(),
              ]),

          ]),

        // ── Row 2: Alasan (kiri) + Lampiran (kanan) ──
        Grid::make([
          'default' => 1,
        ])
          ->schema([

            Section::make('Alasan')
              ->icon('heroicon-o-document-text')
              ->schema([
                TextEntry::make('reason')
                  ->label('')
                  ->prose()
                  ->html()
                  ->formatStateUsing(
                    fn($state) => nl2br(e($state))
                  ),
              ]),

            Section::make('Lampiran')
              ->icon('heroicon-o-paper-clip')
              ->visible(fn(LeaveRequest $record) => filled($record->attachment))
              ->schema([

                Group::make([

                  ImageEntry::make('attachment')
                    ->label('Preview')
                    ->disk('public')
                    ->height(220)
                    ->extraImgAttributes([
                      'class' => 'rounded-xl object-cover w-full',
                    ])
                    ->visible(function (LeaveRequest $record) {
                      $extension = strtolower(
                        pathinfo($record->attachment, PATHINFO_EXTENSION)
                      );

                      return in_array($extension, [
                        'jpg',
                        'jpeg',
                        'png',
                        'webp',
                        'gif',
                      ]);
                    }),

                  TextEntry::make('attachment')
                    ->label('File')
                    ->formatStateUsing(
                      fn($state) => basename($state)
                    )
                    ->url(
                      fn($record) => asset('storage/' . $record->attachment)
                    )
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('primary'),

                ]),
              ]),

          ]),

        // ── Row 3: Informasi Sistem (full width, collapsible) ──
        Section::make('Informasi Sistem')
          ->icon('heroicon-o-information-circle')
          ->collapsible()
          ->collapsed()
          ->compact()
          ->columns([
            'default' => 1,
            'md' => 2,
          ])
          ->schema([

            TextEntry::make('created_at')
              ->label('Dibuat')
              ->formatStateUsing(
                fn($state) =>
                Carbon::parse($state)
                  ->translatedFormat('l, d F Y H:i')
              ),

            TextEntry::make('updated_at')
              ->label('Terakhir Update')
              ->formatStateUsing(
                fn($state) =>
                Carbon::parse($state)
                  ->translatedFormat('l, d F Y H:i')
              ),
          ]),

      ]);
  }
}
