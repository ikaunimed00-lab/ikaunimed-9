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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class KegiatanResource extends Resource
{
    protected static ?string $model = Kegiatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static UnitEnum|string|null $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Kegiatan';

    protected static ?string $modelLabel = 'Kegiatan';

    protected static ?string $pluralModelLabel = 'Kegiatan';

    protected static ?string $slug = 'kegiatan';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return self::isLimitedToTeacherOfChampions() ? 'ToC' : parent::getNavigationGroup();
    }

    public static function getNavigationLabel(): string
    {
        return self::isLimitedToTeacherOfChampions() ? 'Kegiatan ToC' : parent::getNavigationLabel();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (self::isLimitedToTeacherOfChampions()) {
            $query->where('slug', Kegiatan::TEACHER_OF_CHAMPIONS_SLUG);
        }

        return $query;
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

    public static function isLimitedToTeacherOfChampions(): bool
    {
        $user = Auth::user();

        return $user !== null && ! $user->hasSystemRole(['super_admin', 'admin']);
    }
}
