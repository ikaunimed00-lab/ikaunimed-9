<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Legalizations\Pages\CreateLegalization;
use App\Filament\Resources\Legalizations\Pages\EditLegalization;
use App\Filament\Resources\Legalizations\Pages\ListLegalizations;
use App\Filament\Resources\Legalizations\Pages\ViewLegalization;
use App\Filament\Resources\Legalizations\Schemas\LegalizationForm;
use App\Filament\Resources\Legalizations\Schemas\LegalizationInfolist;
use App\Filament\Resources\Legalizations\Tables\LegalizationsTable;
use App\Models\Legalization;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LegalizationResource extends Resource
{
    protected static ?string $model = Legalization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('cms.legalization.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('cms.legalization.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('cms.legalization.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('cms.legalization.manage') ?? false;
    }

    // protected static ?string $recordTitleAttribute = 'request_number';

    public static function form(Schema $form): Schema
    {
        return LegalizationForm::configure($form);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return LegalizationInfolist::configure($infolist);
    }

    public static function table(Table $table): Table
    {
        return LegalizationsTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['user']);
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
            'index' => ListLegalizations::route('/'),
            'create' => CreateLegalization::route('/create'),
            'view' => ViewLegalization::route('/{record}'),
            'edit' => EditLegalization::route('/{record}/edit'),
        ];
    }
}
