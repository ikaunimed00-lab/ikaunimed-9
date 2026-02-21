<?php

namespace App\Filament\Resources\Enrollments;

use App\Filament\Resources\Enrollments\Pages\EditEnrollment;
use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Models\Enrollment;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static \UnitEnum|string|null $navigationGroup = 'LMS Governance';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('elearning.enrollment.view')
            || $user->can('elearning.enrollment.view_any')
            || $user->can('elearning.enrollment.view_own_course');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('manage', $record);
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informasi Peserta')
                    ->schema([
                        TextInput::make('user.name')
                            ->label('Nama Peserta')
                            ->disabled(),
                        TextInput::make('user.email')
                            ->label('Email')
                            ->disabled(),
                    ])
                    ->columns(2),
                Section::make('Informasi Kursus')
                    ->schema([
                        TextInput::make('course.title')
                            ->label('Judul Kursus')
                            ->disabled(),
                        TextInput::make('course.slug')
                            ->label('Slug Kursus')
                            ->disabled(),
                    ])
                    ->columns(2),
                Section::make('Status Enrollment')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        TextInput::make('progress_percentage')
                            ->label('Progress (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->disabled(fn (Get $get) => $get('status') === 'cancelled'),
                        DateTimePicker::make('started_at')
                            ->label('Mulai')
                            ->disabled(),
                        DateTimePicker::make('completed_at')
                            ->label('Selesai')
                            ->disabled(fn (Get $get) => $get('status') !== 'completed'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peserta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Kursus')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => is_null($state) ? '0%' : $state . '%'),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Selesai')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['user', 'course']);

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->can('elearning.enrollment.view_any')) {
            return $query;
        }

        if ($user->can('elearning.enrollment.view_own_course')) {
            return $query->whereHas('course', function (Builder $q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        if ($user->can('elearning.enrollment.view')) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
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
            'index' => ListEnrollments::route('/'),
            'edit' => EditEnrollment::route('/{record}/edit'),
        ];
    }
}
