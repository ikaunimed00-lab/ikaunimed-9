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
            'name' => ['required', 'string', 'max:125'],

            'gender' => ['nullable', 'string', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:125'],
            'tanggal_lahir' => ['nullable', 'date'],
            'nik' => ['nullable', 'string', 'max:20'],
            'wa' => ['nullable', 'string', 'max:20'],
            'domicile' => ['nullable', 'string', 'max:255'],
            'alamat_lengkap' => ['nullable', 'string', 'max:5000'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'bidang_pekerjaan' => ['nullable', 'string', 'max:100'],
            'posisi_saat_ini' => ['nullable', 'string', 'max:150'],
            'perusahaan' => ['nullable', 'string', 'max:150'],
            'kota_profesional' => ['nullable', 'string', 'max:150'],
            'status_pekerjaan' => ['nullable', 'string', 'max:50'],
            'ringkasan_profesional' => ['nullable', 'string', 'max:5000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'public_profile' => ['sometimes', 'boolean'],

            // Legacy academic fields (tetap didukung agar tidak hilang)
            's1_fakultas' => ['nullable', 'string', 'max:125'],
            's1_prodi' => ['nullable', 'string', 'max:125'],
            's1_tahun_masuk' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],
            's1_tahun_tamat' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],

            's2_prodi' => ['nullable', 'string', 'max:125'],
            's2_tahun_masuk' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],
            's2_tahun_tamat' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],

            's3_prodi' => ['nullable', 'string', 'max:125'],
            's3_tahun_masuk' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],
            's3_tahun_tamat' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],

            'educations' => ['nullable', 'array'],
            'educations.*.level' => ['required', 'string', 'max:255'],
            'educations.*.university' => ['nullable', 'required_with:educations.*.major', 'string', 'max:255'],
            'educations.*.faculty' => ['nullable', 'string', 'max:255'],
            'educations.*.major' => ['nullable', 'required_with:educations.*.university', 'string', 'max:255'],
            'educations.*.admission_year' => ['nullable', 'numeric', 'min:1990', 'max:'.now()->year],
            'educations.*.graduation_year' => ['nullable', 'numeric', 'min:1990', 'max:'.(now()->year + 10)],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:125',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }

    /**
     * @return array<string, string|array<string, string>>
     */
    public function messages(): array
    {
        return [
            'max' => [
                'string' => ':Attribute maksimal :max karakter.',
                'numeric' => ':Attribute tidak boleh lebih dari :max.',
                'array' => ':Attribute maksimal :max item.',
                'file' => ':Attribute maksimal :max kilobyte.',
            ],
            'educations.*.university.required_with' => 'Nama universitas wajib diisi bila jurusan/prodi diisi.',
            'educations.*.major.required_with' => 'Jurusan/prodi wajib diisi bila nama universitas diisi.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'tempat_lahir' => 'tempat lahir',
            'nik' => 'NIK',
            'wa' => 'nomor WhatsApp',
            'domicile' => 'domisili',
            'alamat_lengkap' => 'alamat lengkap',
            'occupation' => 'pekerjaan',
            'posisi_saat_ini' => 'posisi saat ini',
            'perusahaan' => 'perusahaan/instansi',
            'kota_profesional' => 'kota/lokasi profesional',
            'ringkasan_profesional' => 'ringkasan profil profesional',
            'linkedin_url' => 'URL LinkedIn',
            'website_url' => 'URL website',
            'skills' => 'keahlian utama',
            'educations.*.university' => 'nama universitas',
            'educations.*.faculty' => 'fakultas',
            'educations.*.major' => 'jurusan/prodi',
        ];
    }
}
