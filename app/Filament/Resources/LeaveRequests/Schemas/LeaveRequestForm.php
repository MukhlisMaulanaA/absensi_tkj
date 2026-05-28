<?php

namespace App\Filament\Resources\LeaveRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LeaveRequestForm
{
  public static function configure(Schema $schema): Schema
  {
    return $schema->components([
      TextInput::make('user_id')->required()->numeric(),
      Select::make('type')
        ->options([
          'sick' => 'Sick',
          'permission' => 'Permission',
          'leave' => 'Leave',
        ])
        ->required(),
      DatePicker::make('start_date')->required(),
      DatePicker::make('end_date')->required(),
      Textarea::make('reason')->required()->columnSpanFull(),
      FileUpload::make('attachment')
        ->label('Attachment')
        ->directory('leave-attachments')
        ->visibility('public')
        ->columnSpanFull(),
      Select::make('status')
        ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
        ->default('pending')
        ->required(),
      TextInput::make('approved_by')->numeric(),
    ]);
  }
}
