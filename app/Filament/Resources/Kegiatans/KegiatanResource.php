<?php

namespace App\Filament\Resources\Kegiatans;

use App\Filament\Resources\Kegiatans\Pages\ManageKegiatans;
use App\Filament\Resources\Kegiatans\Schemas\KegiatanForm;
use App\Filament\Resources\Kegiatans\Tables\KegiatansTable;
use App\Models\Kegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class KegiatanResource extends Resource
{
    protected static ?string $model = Kegiatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Kegiatan';

    protected static ?string $modelLabel = 'Kegiatan';

    protected static ?string $pluralModelLabel = 'Kegiatan';

    protected static ?string $slug = 'kegiatan';

    protected static ?string $recordTitleAttribute = 'title';

    public static function canViewAny(): bool
    {
        return self::userCanManage();
    }

    public static function canCreate(): bool
    {
        return self::userCanManage();
    }

    public static function canEdit($record): bool
    {
        return self::userCanManage();
    }

    public static function canDelete($record): bool
    {
        return self::userCanManage();
    }

    public static function form(Schema $form): Schema
    {
        return KegiatanForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return KegiatansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKegiatans::route('/'),
        ];
    }

    private static function userCanManage(): bool
    {
        return Auth::user()?->hasSystemRole(['super_admin', 'admin']) ?? false;
    }
}
