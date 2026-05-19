<?php

namespace App\Filament\Resources\OvertimeRequests;

use App\Filament\Resources\OvertimeRequests\Pages\CreateOvertimeRequest;
use App\Filament\Resources\OvertimeRequests\Pages\EditOvertimeRequest;
use App\Filament\Resources\OvertimeRequests\Pages\ListOvertimeRequests;
use App\Filament\Resources\OvertimeRequests\Pages\ViewOvertimeRequest;
use App\Filament\Resources\OvertimeRequests\Schemas\OvertimeRequestForm;
use App\Filament\Resources\OvertimeRequests\Schemas\OvertimeRequestInfolist;
use App\Filament\Resources\OvertimeRequests\Tables\OvertimeRequestsTable;
use App\Models\OvertimeRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OvertimeRequestResource extends Resource
{
    protected static ?string $model = OvertimeRequest::class;
  
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'overtimerequest';

    public static function form(Schema $schema): Schema
    {
        return OvertimeRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OvertimeRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OvertimeRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOvertimeRequests::route('/'),
            'create' => CreateOvertimeRequest::route('/create'),
            'view' => ViewOvertimeRequest::route('/{record}'),
            'edit' => EditOvertimeRequest::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
