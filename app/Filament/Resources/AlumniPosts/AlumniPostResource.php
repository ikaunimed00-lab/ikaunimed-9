<?php

namespace App\Filament\Resources\AlumniPosts;

use App\Filament\Resources\AlumniPosts\Pages\CreateAlumniPost;
use App\Filament\Resources\AlumniPosts\Pages\EditAlumniPost;
use App\Filament\Resources\AlumniPosts\Pages\ListAlumniPosts;
use App\Filament\Resources\AlumniPosts\Schemas\AlumniPostForm;
use App\Filament\Resources\AlumniPosts\Tables\AlumniPostsTable;
use App\Models\AlumniPost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlumniPostResource extends Resource
{
    protected static ?string $model = AlumniPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    // protected static ?string $recordTitleAttribute = 'title';

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->can('alumni.moderate') ?? false;
    }

    public static function form(Schema $form): Schema
    {
        return AlumniPostForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return AlumniPostsTable::configure($table);
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
            'index' => ListAlumniPosts::route('/'),
            'create' => CreateAlumniPost::route('/create'),
            'edit' => EditAlumniPost::route('/{record}/edit'),
        ];
    }
}
