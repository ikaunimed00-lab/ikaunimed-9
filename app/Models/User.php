<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Core\Traits\HasProgramRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasProgramRoles, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->isWriter();
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'organization_id',
        'oauth_id',
        'oauth_provider',

        'wa',
        'nik',
        'gender',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat_lengkap',
        'domicile',
        'occupation',

        'bidang_pekerjaan',
        'posisi_saat_ini',
        'perusahaan',
        'kota_profesional',
        'status_pekerjaan',
        'ringkasan_profesional',
        'linkedin_url',
        'website_url',
        'skills',
        'public_profile',
        'profile_level',
        'profile_completion_score',

        's1_fakultas',
        's1_prodi',
        's1_tahun_masuk',
        's1_tahun_tamat',

        's2_prodi',
        's2_tahun_masuk',
        's2_tahun_tamat',

        's3_prodi',
        's3_tahun_masuk',
        's3_tahun_tamat',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'tanggal_lahir' => 'date',
            'public_profile' => 'boolean',
            'profile_level' => 'integer',
            'profile_completion_score' => 'integer',
        ];
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function hasSystemRole(array|string $roles): bool
    {
        if ($this->roles()->count() > 0) {
            return $this->hasRole($roles);
        }

        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }

        return $this->role === $roles;
    }

    public function systemRoleLabel(): ?string
    {
        if ($this->roles()->count() > 0) {
            if ($this->hasRole('super_admin')) {
                return 'super_admin';
            }

            foreach (['admin', 'editor', 'writer', 'subscriber'] as $role) {
                if ($this->hasRole($role)) {
                    return $role;
                }
            }
        }

        return $this->role;
    }

    public function isSubscriber(): bool
    {
        return $this->hasSystemRole('subscriber');
    }

    public function isWriter(): bool
    {
        return $this->hasSystemRole(['admin', 'editor', 'writer']);
    }

    public function isEditor(): bool
    {
        return $this->hasSystemRole(['admin', 'editor']);
    }

    public function isAdmin(): bool
    {
        return $this->hasSystemRole('admin');
    }

    /**
     * Relasi ke organisasi
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Relasi ke program organisasi (Many-to-Many)
     */
    public function organizationPrograms()
    {
        return $this->belongsToMany(\App\Core\Models\OrganizationProgram::class, 'program_members', 'user_id', 'program_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Cek apakah user adalah Admin Pusat (Super Admin)
     */
    public function isCentralAdmin(): bool
    {
        // Gunakan organization_id langsung untuk menghindari memuat relasi organization
        return $this->isAdmin() && is_null($this->organization_id);
    }

    /**
     * Cek apakah user terikat scope organisasi tertentu
     */
    public function hasOrganizationScope(): bool
    {
        return !is_null($this->organization_id);
    }

    public function legalizations()
    {
        return $this->hasMany(Legalization::class);
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotificationsCount(): int
    {
        return cache()->remember("user_{$this->id}_unread_notifications_count", 60, function () {
            return $this->notifications()->whereNull('read_at')->count();
        });
    }

    public function alumniPosts()
    {
        return $this->hasMany(AlumniPost::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function moderatedAlumniPosts()
    {
        return $this->hasMany(AlumniPost::class, 'moderated_by');
    }

    public function interestedJobVacancies()
    {
        return $this->belongsToMany(JobVacancy::class, 'job_vacancy_user_interests')->withTimestamps();
    }

    public function scopeAppearInDirectory($query)
    {
        return $query
            ->where('public_profile', true)
            ->where('profile_level', '>=', 1);
    }

    public function canAppearInDirectory(): bool
    {
        if (! $this->public_profile) {
            return false;
        }

        if (! $this->hasMinimumAcademicRecord()) {
            return false;
        }

        if (! $this->hasProfessionalCoreFields()) {
            return false;
        }

        return true;
    }

    public function recalculateProfileMeta(): void
    {
        $this->profile_level = $this->calculateProfileLevel();
        $this->profile_completion_score = $this->calculateProfileCompletionScore();

        $this->save();
    }

    protected function hasMinimumAcademicRecord(): bool
    {
        $hasLegacyS1 = ! empty($this->s1_prodi) && ! empty($this->s1_tahun_masuk);

        $hasEducation = $this->educations()
            ->whereNotNull('major')
            ->whereNotNull('admission_year')
            ->exists();

        return $hasLegacyS1 || $hasEducation;
    }

    protected function hasProfessionalCoreFields(): bool
    {
        return ! empty($this->bidang_pekerjaan)
            && ! empty($this->posisi_saat_ini)
            && ! empty($this->perusahaan)
            && ! empty($this->kota_profesional)
            && ! empty($this->status_pekerjaan);
    }

    protected function calculateProfileLevel(): int
    {
        if (! $this->hasMinimumAcademicRecord() || ! $this->hasProfessionalCoreFields()) {
            return 0;
        }

        $hasSummary = ! empty($this->ringkasan_profesional);
        $hasLinkedIn = ! empty($this->linkedin_url);

        if ($hasSummary && $hasLinkedIn) {
            return 2;
        }

        return 1;
    }

    protected function calculateProfileCompletionScore(): int
    {
        $fields = [
            'name',
            'email',
            'wa',
            'nik',
            'gender',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat_lengkap',
            'domicile',
            'occupation',
            'bidang_pekerjaan',
            'posisi_saat_ini',
            'perusahaan',
            'kota_profesional',
            'status_pekerjaan',
            'ringkasan_profesional',
            'linkedin_url',
        ];

        $filled = 0;

        foreach ($fields as $field) {
            if (! empty($this->{$field})) {
                $filled++;
            }
        }

        $score = (int) round(($filled / count($fields)) * 100);

        return max(0, min(100, $score));
    }
}
