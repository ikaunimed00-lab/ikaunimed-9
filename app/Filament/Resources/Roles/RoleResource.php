<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static \UnitEnum|string|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    protected static function coreRoleNames(): array
    {
        return [
            'super_admin',
            'admin',
            'editor',
            'writer',
            'subscriber',
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('master.roles.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informasi Role')
                    ->description(function ($record) {
                        if (! $record) {
                            return null;
                        }

                        return in_array($record->name, self::coreRoleNames(), true)
                            ? 'Core system role – perubahan permission utama sebaiknya dilakukan melalui seeder.'
                            : null;
                    })
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Role')
                            ->disabled()
                            ->required(),
                        TextInput::make('guard_name')
                            ->label('Guard')
                            ->disabled()
                            ->required(),
                        Select::make('permissions')
                            ->label('Permissions')
                            ->disabled(function ($record) {
                                if (! $record) {
                                    return false;
                                }

                                return in_array($record->name, self::coreRoleNames(), true);
                            })
                            ->multiple()
                            ->relationship('permissions', 'name')
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->label('Jumlah Permissions')
                    ->counts('permissions'),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}

