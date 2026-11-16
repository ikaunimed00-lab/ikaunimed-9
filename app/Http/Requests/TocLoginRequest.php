<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TocLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nomor_pendaftaran' => ['required', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_pendaftaran.required' => (string) config('toc.login.generic_error'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_pendaftaran' => trim((string) $this->input('nomor_pendaftaran')),
        ]);
    }
}
