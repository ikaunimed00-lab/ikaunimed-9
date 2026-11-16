<?php

namespace App\Http\Requests;

use App\Models\PendaftarTeacherOfChampions;
use Illuminate\Foundation\Http\FormRequest;

class CheckTocRegistrationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'whatsapp' => PendaftarTeacherOfChampions::normalizeWhatsapp($this->input('whatsapp')),
            'email' => is_string($this->input('email')) ? strtolower(trim($this->input('email'))) : $this->input('email'),
        ]);
    }

    public function rules(): array
    {
        return [
            'website' => ['prohibited'],
            'whatsapp' => ['required', 'string', 'regex:/^62\d{8,13}$/'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'website.prohibited' => 'Permintaan tidak dapat diproses.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}
