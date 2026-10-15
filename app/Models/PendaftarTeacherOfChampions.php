<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PendaftarTeacherOfChampions extends Model
{
    /** @use HasFactory<\Database\Factories\PendaftarTeacherOfChampionsFactory> */
    use HasFactory;

    protected $table = 'pendaftar_teacher_of_champions';

    public const STATUS_BARU = 'baru';

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'baru' => 'Baru',
        'terverifikasi' => 'Terverifikasi',
        'lolos' => 'Lolos',
        'ditolak' => 'Ditolak',
    ];

    /**
     * Stored code => label shown to registrants. The jenjang column holds at most 10 characters.
     *
     * @var array<string, string>
     */
    public const JENJANG = [
        'TK' => 'TK/RA/Sederajat',
        'SD' => 'SD/MI/Sederajat',
        'SMP' => 'SMP/MTs/Sederajat',
        'SMA' => 'SMA/MA/Sederajat',
        'SMK' => 'SMK/MAK/Sederajat',
        'SLB' => 'SLB/Pendidikan Khusus',
    ];

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function jenjangOptions(): array
    {
        return collect(self::JENJANG)
            ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }

    /**
     * @var list<string>
     */
    public const STATUS_KEPEGAWAIAN = ['ASN', 'non-ASN'];

    /**
     * Normalized (lowercase, no punctuation) names that identify UNIMED.
     *
     * @var list<string>
     */
    public const UNIMED_KEYWORDS = ['unimed', 'universitas negeri medan', 'state university of medan'];

    protected $fillable = [
        'nomor_pendaftaran',
        'nama',
        'whatsapp',
        'email',
        'provinsi',
        'kabupaten_kota',
        'asal_instansi',
        'jenjang',
        'status_kepegawaian',
        'bidang_studi',
        'lama_mengabdi_tahun',
        'asal_universitas',
        'instagram',
        'persetujuan_data',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'lama_mengabdi_tahun' => 'integer',
            'persetujuan_data' => 'boolean',
            'alumni_unimed' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $pendaftar): void {
            if ($pendaftar->isDirty('asal_universitas') || $pendaftar->alumni_unimed === null) {
                $pendaftar->alumni_unimed = static::isAlumniUnimed($pendaftar->asal_universitas);
            }
        });
    }

    public static function isAlumniUnimed(?string $asalUniversitas): bool
    {
        $normalized = Str::of((string) $asalUniversitas)
            ->lower()
            ->replaceMatches('/[^\p{L}\p{N}\s]+/u', '')
            ->squish()
            ->toString();

        return Str::contains($normalized, self::UNIMED_KEYWORDS);
    }

    /**
     * Creates the registration and assigns a sequential number (TOC-2026-0001) from its id.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function register(array $attributes): self
    {
        return DB::transaction(function () use ($attributes): self {
            $pendaftar = static::query()->create([
                ...$attributes,
                'nomor_pendaftaran' => 'TMP-'.Str::random(12),
                'status' => self::STATUS_BARU,
            ]);

            $pendaftar->update([
                'nomor_pendaftaran' => sprintf('TOC-2026-%04d', $pendaftar->id),
            ]);

            return $pendaftar;
        });
    }

    public static function registrationClosesAt(): Carbon
    {
        return Carbon::parse(config('homepage.teacher_of_champions.registration_closes_at'), Kegiatan::TIMEZONE)
            ->endOfMinute();
    }

    public static function isRegistrationOpen(): bool
    {
        return now()->lessThanOrEqualTo(static::registrationClosesAt());
    }

    /**
     * Normalizes Indonesian phone numbers to the 62... format.
     */
    public static function normalizeWhatsapp(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * Keeps only the first and last 4 characters, e.g. "6281********5678".
     */
    public static function maskContact(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $length = mb_strlen($value);

        if ($length <= 8) {
            return mb_substr($value, 0, min(2, $length)).str_repeat('*', max($length - 2, 0));
        }

        return mb_substr($value, 0, 4).str_repeat('*', $length - 8).mb_substr($value, -4);
    }

    public static function normalizeInstagram(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $username = trim($value);
        $username = (string) preg_replace('#^(https?://)?(www\.)?instagram\.com/#i', '', $username);
        $username = trim($username, "/@ \t");

        return $username === '' ? null : '@'.strtolower($username);
    }
}
