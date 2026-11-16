<?php

namespace App\Http\Requests;

use App\Models\TocBerkas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class TocUnggahBerkasRequest extends FormRequest
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
        $jenis = $this->route('jenis');
        $meta = config("toc.berkas.jenis.{$jenis}", []);
        $maxKb = (int) ($meta['max_kb'] ?? 3072);

        $rules = [
            'file' => ['required', 'file', 'max:'.$maxKb],
        ];

        if ($jenis === TocBerkas::JENIS_FOTO) {
            $rules['setuju_publikasi'] = ['accepted'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $maxMb = $this->maxMbForJenis();

        return [
            'file.required' => 'Berkas wajib diunggah.',
            'file.file' => 'Berkas yang diunggah tidak valid.',
            'file.uploaded' => $this->serverLimitMessage($maxMb),
            'file.max' => "Ukuran file melebihi {$maxMb} MB.",
            'setuju_publikasi.accepted' => 'Anda harus menyetujui publikasi nama dan foto.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $maxMb = $this->maxMbForJenis();

            if ($this->contentExceedsPostMaxSize()) {
                $validator->errors()->forget('file');
                $validator->errors()->add('file', $this->serverLimitMessage($maxMb));

                return;
            }

            $file = $this->file('file');

            if ($file instanceof UploadedFile && ! $file->isValid()) {
                $validator->errors()->forget('file');
                $validator->errors()->add('file', $this->uploadErrorMessage($file, $maxMb));

                return;
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $jenis = $this->route('jenis');
            $meta = config("toc.berkas.jenis.{$jenis}");

            if (! is_array($meta)) {
                $validator->errors()->add('file', 'Jenis berkas tidak valid.');

                return;
            }

            if ($file === null) {
                return;
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($file->getRealPath()) ?: '';

            if (! in_array($detected, $meta['mimes'], true)) {
                $validator->errors()->add(
                    'file',
                    'Tipe berkas tidak sesuai. Izinkan: '.implode(', ', $meta['extensions']).'.',
                );

                return;
            }

            if ($jenis === TocBerkas::JENIS_FOTO) {
                $size = @getimagesize($file->getRealPath());

                if ($size === false) {
                    $validator->errors()->add('file', 'File gambar tidak dapat dibaca.');

                    return;
                }

                [$width, $height] = $size;
                $minW = (int) ($meta['min_width'] ?? 1080);
                $minH = (int) ($meta['min_height'] ?? 1350);

                if ($width < $minW || $height < $minH) {
                    $validator->errors()->add(
                        'file',
                        "Resolusi foto minimal {$minW}×{$minH} px (saat ini {$width}×{$height}). Rasio potret 4:5 dianjurkan.",
                    );
                }
            }
        });
    }

    private function maxMbForJenis(): int
    {
        $jenis = $this->route('jenis');
        $maxKb = (int) (config("toc.berkas.jenis.{$jenis}.max_kb") ?? 3072);

        return max(1, (int) round($maxKb / 1024));
    }

    private function serverLimitMessage(int $maxMb): string
    {
        return "Unggahan gagal karena batas server. Pastikan ukuran file tidak melebihi {$maxMb} MB, lalu coba lagi.";
    }

    private function uploadErrorMessage(UploadedFile $file, int $maxMb): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => $this->serverLimitMessage($maxMb),
            UPLOAD_ERR_PARTIAL => 'Unggahan tidak lengkap. Silakan unggah ulang.',
            UPLOAD_ERR_NO_FILE => 'Berkas wajib diunggah.',
            default => $this->serverLimitMessage($maxMb),
        };
    }

    private function contentExceedsPostMaxSize(): bool
    {
        if ($this->hasFile('file') || $this->files->has('file')) {
            return false;
        }

        $contentLength = (int) $this->server('CONTENT_LENGTH', 0);

        if ($contentLength <= 0) {
            return false;
        }

        return $contentLength > $this->iniSizeToBytes((string) ini_get('post_max_size'));
    }

    private function iniSizeToBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
