<?php

namespace App\Filament\Resources\PendaftarTeacherOfChampions\Schemas;

use App\Filament\Resources\PendaftarTeacherOfChampions\PendaftarTeacherOfChampionsResource;
use App\Models\AuditLog;
use App\Models\Kegiatan;
use App\Models\PendaftarTeacherOfChampions;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PendaftarTeacherOfChampionsInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        $canViewContact = PendaftarTeacherOfChampionsResource::userCan('viewContact');

        return $infolist->schema([
            Section::make('Pendaftaran')
                ->columns(2)
                ->schema([
                    TextEntry::make('nomor_pendaftaran')->label('No. pendaftaran')->copyable(),
                    TextEntry::make('status')
                        ->label('Status')
                        ->formatStateUsing(fn (?string $state): string => PendaftarTeacherOfChampions::STATUSES[$state] ?? (string) $state)
                        ->badge(),
                    TextEntry::make('created_at')
                        ->label('Tanggal daftar (WIB)')
                        ->dateTime('d M Y, H:i')
                        ->timezone(Kegiatan::TIMEZONE),
                    TextEntry::make('alumni_unimed')
                        ->label('Alumni UNIMED')
                        ->formatStateUsing(fn (?bool $state): string => $state ? 'Ya' : 'Tidak'),
                ]),
            Section::make('Identitas')
                ->columns(2)
                ->schema([
                    TextEntry::make('nama')->label('Nama')->columnSpanFull(),
                    TextEntry::make('whatsapp')
                        ->label('WhatsApp')
                        ->formatStateUsing(fn (?string $state): ?string => $canViewContact ? $state : PendaftarTeacherOfChampions::maskContact($state)),
                    TextEntry::make('email')
                        ->label('Email')
                        ->formatStateUsing(fn (?string $state): ?string => $canViewContact ? $state : PendaftarTeacherOfChampions::maskContact($state)),
                    TextEntry::make('instagram')->label('Instagram')->placeholder('-'),
                ]),
            Section::make('Instansi & wilayah')
                ->columns(2)
                ->schema([
                    TextEntry::make('asal_instansi')->label('Asal instansi')->columnSpanFull(),
                    TextEntry::make('jenjang')
                        ->label('Jenjang')
                        ->formatStateUsing(fn (?string $state): string => $state ? (PendaftarTeacherOfChampions::JENJANG[$state] ?? $state) : '-'),
                    TextEntry::make('status_kepegawaian')->label('Status kepegawaian')->placeholder('-'),
                    TextEntry::make('provinsi')->label('Provinsi'),
                    TextEntry::make('kabupaten_kota')->label('Kabupaten/kota'),
                    TextEntry::make('bidang_studi')->label('Bidang studi'),
                    TextEntry::make('lama_mengabdi_tahun')->label('Lama mengabdi (tahun)'),
                    TextEntry::make('asal_universitas')->label('Asal universitas')->columnSpanFull(),
                ]),
            Section::make('Catatan')
                ->columns(1)
                ->schema([
                    TextEntry::make('catatan')->label('Catatan panitia')->placeholder('-')->columnSpanFull(),
                    TextEntry::make('catatan_internal')->label('Catatan internal')->placeholder('-')->columnSpanFull(),
                ]),
            Section::make('Riwayat perubahan')
                ->schema([
                    TextEntry::make('audit_history')
                        ->label('')
                        ->state(function (PendaftarTeacherOfChampions $record): string {
                            $logs = AuditLog::query()
                                ->with('user:id,name')
                                ->where('subject_type', $record->getMorphClass())
                                ->where('subject_id', $record->getKey())
                                ->latest('created_at')
                                ->limit(40)
                                ->get();

                            if ($logs->isEmpty()) {
                                return 'Belum ada riwayat.';
                            }

                            return $logs->map(function (AuditLog $log): string {
                                $when = $log->created_at
                                    ? $log->created_at->timezone(Kegiatan::TIMEZONE)->format('d M Y H:i').' WIB'
                                    : '-';
                                $who = $log->user?->name ?? 'sistem';
                                $aksi = AuditLog::ACTIONS[$log->aksi] ?? $log->aksi;

                                return "- {$when} · {$aksi} · {$who}";
                            })->implode("\n");
                        })
                        ->markdown()
                        ->prose()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
