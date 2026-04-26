<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        $news = $this->route('news');

        if (! $user) {
            return false;
        }

        if ($user->can('cms.news.publish')) {
            return true;
        }

        return $user->can('cms.news.edit') && $news->user_id === $user->id;
    }

    public function rules(): array
    {
        $newsId = $this->route('news')->id;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:10',
                "unique:news,title,{$newsId}",
                // Allow common characters including hyphen, ampersand, etc
                'regex:/^[\p{L}\p{N}\s\-\.\,\:\;\(\)\'\"&\/?]+$/u',
            ],
            'excerpt' => [
                'required',
                'string',
                'min:20',
                'max:500',
            ],
            'content' => [
                'required',
                'string',
                'min:100',
                'max:50000',
            ],
            'image' => [
                'nullable',
                'image',
                'max:5120',
                'dimensions:min_width=600,min_height=400,ratio=3/2',
            ],
            'categories' => [
                'required',
                'array',
                'min:1',
                'max:3',
            ],
            'categories.*' => 'required|integer|exists:categories,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'video_urls' => 'nullable|array|max:10',
            'video_urls.*' => 'required|url|max:2048',
            'published_at' => 'nullable|date_format:Y-m-d\TH:i',
            // Kontrak status `news` (sinkron dengan Filament NewsForm dan
            // News::scopePublished). Hanya 2 nilai literal: draft & published.
            // "Terjadwal" = status=published + published_at di masa depan
            // (turunan, BUKAN nilai status terpisah). Lihat
            // documentations/18._opus_4.7/04._news_item_2.md.
            'status' => 'required|in:draft,published',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul berita wajib diisi',
            'title.min' => 'Judul minimal 10 karakter',
            'title.unique' => 'Judul sudah pernah digunakan',
            'excerpt.required' => 'Ringkasan wajib diisi',
            'excerpt.min' => 'Ringkasan minimal 20 karakter',
            'content.min' => 'Konten minimal 100 karakter untuk kualitas terbaik',
            'image.dimensions' => 'Rasio foto harus 3:2 (contoh: 1200x800px)',
            'categories.required' => 'Pilih minimal 1 kategori',
            'categories.max' => 'Maksimal 3 kategori',
            'video_urls.max' => 'Maksimal 10 URL video per berita',
            'video_urls.*.url' => 'Format URL video tidak valid',
            'video_urls.*.max' => 'URL video terlalu panjang (maksimal 2048 karakter)',
            'status.in' => 'Status berita hanya boleh draft atau published',
        ];
    }
}
