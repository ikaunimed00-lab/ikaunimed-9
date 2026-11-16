<?php

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Resources\AuditLogs\Pages\ManageAuditLogs;
use App\Models\AuditLog;
use App\Models\Kegiatan;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use UnitEnum;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'ToC';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Audit Log';

    protected static ?string $modelLabel = 'Audit Log';

    protected static ?string $pluralModelLabel = 'Audit Log';

    protected static ?string $slug = 'audit-log';

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasTocPermission('toc.audit.view') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('created_at')->label('Waktu (WIB)')->dateTime('d M Y, H:i:s')->timezone(Kegiatan::TIMEZONE),
            TextEntry::make('user.name')->label('Pengguna')->placeholder('Sistem'),
            TextEntry::make('aksi')->label('Aksi')->formatStateUsing(fn (string $state): string => AuditLog::ACTIONS[$state] ?? $state),
            TextEntry::make('subject')->label('Objek')->state(fn (AuditLog $record): string => self::subjectLabel($record)),
            TextEntry::make('ip')->label('IP')->placeholder('-'),
            TextEntry::make('perubahan')
                ->label('Perubahan')
                ->state(fn (AuditLog $record): HtmlString => new HtmlString(
                    '<pre style="white-space: pre-wrap; font-size: 0.75rem;">'
                    .e(json_encode($record->perubahan ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                    .'</pre>'
                ))
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu (WIB)')
                    ->dateTime('d M Y, H:i')
                    ->timezone(Kegiatan::TIMEZONE)
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->placeholder('Sistem')
                    ->searchable(),
                TextColumn::make('aksi')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => AuditLog::ACTIONS[$state] ?? $state),
                TextColumn::make('subject_id')
                    ->label('Objek')
                    ->state(fn (AuditLog $record): string => self::subjectLabel($record)),
                TextColumn::make('ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('aksi')
                    ->label('Aksi')
                    ->options(AuditLog::ACTIONS),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAuditLogs::route('/'),
        ];
    }

    private static function subjectLabel(AuditLog $record): string
    {
        if ($record->subject_type === null) {
            return '-';
        }

        return class_basename($record->subject_type).' #'.$record->subject_id;
    }
}
