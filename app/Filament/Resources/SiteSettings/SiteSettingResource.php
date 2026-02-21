<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\ManageSiteSettings;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Filament\Resources\SiteSettings\Tables\SiteSettingsTable;
use App\Models\SiteSetting;
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

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    protected static ?string $recordTitleAttribute = 'label';

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user?->can('site.settings.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('site.settings.edit') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('site.settings.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('site.settings.edit') ?? false;
    }

    public static function form(Schema $form): Schema
    {
        return SiteSettingForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return SiteSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSiteSettings::route('/'),
        ];
    }
}
