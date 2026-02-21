<?php

namespace App\Filament\Resources\JobVacancies;

use App\Filament\Resources\JobVacancies\Pages\CreateJobVacancy;
use App\Filament\Resources\JobVacancies\Pages\EditJobVacancy;
use App\Filament\Resources\JobVacancies\Pages\ListJobVacancies;
use App\Filament\Resources\JobVacancies\Pages\ViewJobVacancy;
use App\Filament\Resources\JobVacancies\Schemas\JobVacancyForm;
use App\Filament\Resources\JobVacancies\Schemas\JobVacancyInfolist;
use App\Filament\Resources\JobVacancies\Tables\JobVacanciesTable;
use App\Models\JobVacancy;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JobVacancyResource extends Resource
{
    protected static ?string $model = JobVacancy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('cms.job.view') ?? false;
    }

    // protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $form): Schema
    {
        return JobVacancyForm::configure($form);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return JobVacancyInfolist::configure($infolist);
    }

    public static function table(Table $table): Table
    {
        return JobVacanciesTable::configure($table);
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
            'index' => ListJobVacancies::route('/'),
            'create' => CreateJobVacancy::route('/create'),
            'view' => ViewJobVacancy::route('/{record}'),
            'edit' => EditJobVacancy::route('/{record}/edit'),
        ];
    }
}
