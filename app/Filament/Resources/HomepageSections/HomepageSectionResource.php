<?php

namespace App\Filament\Resources\HomepageSections;

use App\Filament\Resources\HomepageSections\Pages\ManageHomepageSections;
use App\Filament\Resources\HomepageSections\Schemas\HomepageSectionForm;
use App\Filament\Resources\HomepageSections\Tables\HomepageSectionsTable;
use App\Models\HomepageSection;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HomepageSectionResource extends Resource
{
    protected static ?string $model = HomepageSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    protected static ?string $recordTitleAttribute = 'title';

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user?->can('site.homepage.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('site.homepage.edit') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('site.homepage.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('site.homepage.edit') ?? false;
    }

    public static function form(Schema $form): Schema
    {
        return HomepageSectionForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return HomepageSectionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageHomepageSections::route('/'),
        ];
    }
}
