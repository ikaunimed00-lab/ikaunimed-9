<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('email_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('two_factor_secret')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('two_factor_recovery_codes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('two_factor_confirmed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('wa')
                    ->placeholder('-'),
                TextEntry::make('nik')
                    ->placeholder('-'),
                TextEntry::make('tempat_lahir')
                    ->placeholder('-'),
                TextEntry::make('tanggal_lahir')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('alamat_lengkap')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('s1_fakultas')
                    ->placeholder('-'),
                TextEntry::make('s1_prodi')
                    ->placeholder('-'),
                TextEntry::make('s1_tahun_masuk')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('s1_tahun_tamat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('s2_prodi')
                    ->placeholder('-'),
                TextEntry::make('s2_tahun_masuk')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('s2_tahun_tamat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('s3_prodi')
                    ->placeholder('-'),
                TextEntry::make('s3_tahun_masuk')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('s3_tahun_tamat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('role'),
                TextEntry::make('oauth_id')
                    ->placeholder('-'),
                TextEntry::make('oauth_provider')
                    ->placeholder('-'),
                TextEntry::make('last_dashboard_visit')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('email_notifications')
                    ->boolean(),
                TextEntry::make('notification_preference'),
                TextEntry::make('gender')
                    ->placeholder('-'),
                TextEntry::make('domicile')
                    ->placeholder('-'),
                TextEntry::make('occupation')
                    ->placeholder('-'),
                TextEntry::make('organization_id')
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}
