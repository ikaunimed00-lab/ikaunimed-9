<?php

namespace App\Http\Requests;

use App\Models\PendaftarTeacherOfChampions;
use App\Services\WilayahService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StorePendaftarTeacherOfChampionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return PendaftarTeacherOfChampions::isRegistrationOpen();
    }

    protected function failedAuthorization(): void
    {
        throw ValidationException::withMessages([
            'form' => 'Pendaftaran Teacher of Champions sudah ditutup.',
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'whatsapp' => PendaftarTeacherOfChampions::normalizeWhatsapp($this->input('whatsapp')),
            'instagram' => PendaftarTeacherOfChampions::normalizeInstagram($this->input('instagram')),
            'email' => is_string($this->input('email')) ? strtolower(trim($this->input('email'))) : $this->input('email'),
            'asal_universitas' => is_string($this->input('asal_universitas'))
                ? Str::of($this->input('asal_universitas'))->squish()->toString()
                : $this->input('asal_universitas'),
            'jenjang' => $this->filled('jenjang') ? $this->input('jenjang') : null,
            'status_kepegawaian' => $this->filled('status_kepegawaian') ? $this->input('status_kepegawaian') : null,
        ]);
    }

    public function rules(): array
    {
        $wilayah = app(WilayahService::class);
        $table = (new PendaftarTeacherOfChampions)->getTable();

        return [
            'website' => ['prohibited'],
            'nama' => ['required', 'string', 'min:3', 'max:255'],
            'whatsapp' => ['required', 'string', 'regex:/^62\d{8,13}$/', Rule::unique($table, 'whatsapp')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique($table, 'email')],
            'provinsi' => ['required', 'string', Rule::in($wilayah->provinsi())],
            'kabupaten_kota' => ['required', 'string', Rule::in($wilayah->kabupatenKota($this->input('provinsi')))],
            'asal_instansi' => ['required', 'string', 'max:255'],
            'jenjang' => ['nullable', Rule::in(array_keys(PendaftarTeacherOfChampions::JENJANG))],
            'status_kepegawaian' => ['nullable', Rule::in(PendaftarTeacherOfChampions::STATUS_KEPEGAWAIAN)],
            'bidang_studi' => ['required', 'string', 'max:255'],
            'lama_mengabdi_tahun' => ['required', 'integer', 'min:0', 'max:60'],
            'asal_universitas' => ['required', 'string', 'min:3', 'max:150'],
            'instagram' => ['required', 'string', 'regex:/^@[a-z0-9._]{1,30}$/'],
            'persetujuan_data' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'website.prohibited' => 'Pendaftaran tidak dapat diproses.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
            'whatsapp.unique' => 'Nomor WhatsApp ini sudah terdaftar. Hubungi panitia bila Anda belum menerima nomor pendaftaran.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Hubungi panitia bila Anda belum menerima nomor pendaftaran.',
            'provinsi.required' => 'Pilih provinsi.',
            'provinsi.in' => 'Provinsi tidak valid.',
            'kabupaten_kota.required' => 'Pilih kabupaten/kota.',
            'kabupaten_kota.in' => 'Kabupaten/kota tidak sesuai dengan provinsi.',
            'asal_instansi.required' => 'Asal instansi/sekolah wajib diisi.',
            'jenjang.in' => 'Jenjang tidak valid.',
            'status_kepegawaian.in' => 'Status kepegawaian tidak valid.',
            'bidang_studi.required' => 'Bidang studi wajib diisi.',
            'lama_mengabdi_tahun.required' => 'Lama mengabdi wajib diisi.',
            'lama_mengabdi_tahun.integer' => 'Lama mengabdi diisi angka tahun.',
            'asal_universitas.required' => 'Asal universitas wajib diisi.',
            'asal_universitas.min' => 'Asal universitas minimal 3 karakter.',
            'asal_universitas.max' => 'Asal universitas maksimal 150 karakter.',
            'instagram.required' => 'Akun Instagram wajib diisi (untuk Favorite Teacher Award).',
            'instagram.regex' => 'Username Instagram tidak valid. Contoh: @namaguru.',
            'persetujuan_data.accepted' => 'Anda perlu menyetujui penggunaan data untuk melanjutkan.',
        ];
    }
}
