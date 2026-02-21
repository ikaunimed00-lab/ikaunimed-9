<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'gender' => ['nullable', 'string', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'nik' => ['nullable', 'string', 'max:20'],
            'wa' => ['nullable', 'string', 'max:20'],
            'domicile' => ['nullable', 'string', 'max:255'],
            'alamat_lengkap' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'bidang_pekerjaan' => ['nullable', 'string', 'max:100'],
            'posisi_saat_ini' => ['nullable', 'string', 'max:150'],
            'perusahaan' => ['nullable', 'string', 'max:150'],
            'kota_profesional' => ['nullable', 'string', 'max:150'],
            'status_pekerjaan' => ['nullable', 'string', 'max:50'],
            'ringkasan_profesional' => ['nullable', 'string'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'skills' => ['nullable', 'string'],
            'public_profile' => ['sometimes', 'boolean'],

            // Legacy academic fields (tetap didukung agar tidak hilang)
            's1_fakultas' => ['nullable', 'string', 'max:150'],
            's1_prodi' => ['nullable', 'string', 'max:150'],
            's1_tahun_masuk' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],
            's1_tahun_tamat' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],

            's2_prodi' => ['nullable', 'string', 'max:150'],
            's2_tahun_masuk' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],
            's2_tahun_tamat' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],

            's3_prodi' => ['nullable', 'string', 'max:150'],
            's3_tahun_masuk' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],
            's3_tahun_tamat' => ['nullable', 'integer', 'min:1990', 'max:' . now()->year],

            'educations' => ['nullable', 'array'],
            'educations.*.level' => ['required', 'string'],
            'educations.*.university' => ['nullable', 'string'],
            'educations.*.faculty' => ['nullable', 'string'],
            'educations.*.major' => ['nullable', 'string'],
            'educations.*.admission_year' => ['nullable', 'numeric', 'min:1990', 'max:' . now()->year],
            'educations.*.graduation_year' => ['nullable', 'numeric', 'min:1990', 'max:' . (now()->year + 10)],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
