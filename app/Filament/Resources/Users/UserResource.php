<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Services\OrganizationScopeService;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static \UnitEnum|string|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    // protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user?->can('master.users.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isCentralAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        if (auth()->user()?->isCentralAdmin()) {
            return true;
        }

        return auth()->user()?->hasRole('admin') &&
               $record->organization_id === auth()->user()->organization_id &&
               ! $record->isAdmin();
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isCentralAdmin() ?? false;
    }

    public static function form(Schema $form): Schema
    {
        return UserForm::configure($form);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return UserInfolist::configure($infolist);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()->with(['organization']);

        $user = Auth::user();

        if (! $user) {
            return $query;
        }

        return app(OrganizationScopeService::class)->applyScope($query, $user);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
