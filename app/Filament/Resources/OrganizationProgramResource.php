<?php

namespace App\Filament\Resources;

use App\Core\Models\OrganizationProgram;
use App\Filament\Resources\OrganizationProgramResource\Pages;
use App\Filament\Resources\OrganizationProgramResource\RelationManagers\MembersRelationManager;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Str;
use BackedEnum;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Auth;

class OrganizationProgramResource extends Resource
{
    protected static ?string $model = OrganizationProgram::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static \UnitEnum|string|null $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Programs';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user?->can('cms.programs.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('cms.programs.manage') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('cms.programs.manage') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('cms.programs.manage') ?? false;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'archived' => 'Archived',
                    ])
                    ->required()
                    ->default('active'),
                
                Forms\Components\Select::make('visibility')
                    ->options([
                        'public' => 'Public Only',
                        'dashboard' => 'Dashboard Only',
                        'both' => 'Both',
                    ])
                    ->required()
                    ->default('both'),
                
                Forms\Components\TextInput::make('icon')
                    ->maxLength(255)
                    ->placeholder('Heroicon name (e.g. heroicon-o-home)'),
                
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'archived' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('visibility'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizationPrograms::route('/'),
            'create' => Pages\CreateOrganizationProgram::route('/create'),
            'edit' => Pages\EditOrganizationProgram::route('/{record}/edit'),
        ];
    }
}
