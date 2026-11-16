<?php

namespace App\Observers;

use App\Models\Kegiatan;
use App\Models\News;
use App\Models\PendaftarTeacherOfChampions;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Audit trail for Teacher of Champions: registrant reviews, the ToC kegiatan and admin-toc news.
 */
class TocAuditObserver
{
    /**
     * @var list<string>
     */
    public const PENDAFTAR_FIELDS = ['status', 'catatan', 'catatan_internal'];

    public function __construct(private AuditLogger $auditLogger) {}

    public function created(Model $model): void
    {
        if ($model instanceof News && $this->isTocNewsAuthor()) {
            $this->auditLogger->record('news.created', $model, [
                'new' => $model->only(['title', 'slug', 'status', 'published_at']),
            ]);

            if ($model->status === 'published') {
                $this->auditLogger->record('news.published', $model, ['new' => $model->only(['status', 'published_at'])]);
            }
        }
    }

    public function updated(Model $model): void
    {
        match (true) {
            $model instanceof PendaftarTeacherOfChampions => $this->pendaftarUpdated($model),
            $model instanceof Kegiatan => $this->kegiatanUpdated($model),
            $model instanceof News => $this->newsUpdated($model),
            default => null,
        };
    }

    private function pendaftarUpdated(PendaftarTeacherOfChampions $pendaftar): void
    {
        $changes = $this->auditLogger->changesOf($pendaftar, self::PENDAFTAR_FIELDS);

        if ($changes !== []) {
            $this->auditLogger->record('pendaftar.updated', $pendaftar, $changes);
        }
    }

    private function kegiatanUpdated(Kegiatan $kegiatan): void
    {
        if (! $kegiatan->isTeacherOfChampions()) {
            return;
        }

        $changes = $this->auditLogger->changesOf($kegiatan);

        if ($changes !== []) {
            $this->auditLogger->record('kegiatan.updated', $kegiatan, $changes);
        }
    }

    private function newsUpdated(News $news): void
    {
        if (! $this->isTocNewsAuthor()) {
            return;
        }

        $changes = $this->auditLogger->changesOf($news, ['title', 'slug', 'excerpt', 'content', 'image', 'status', 'published_at', 'video_urls']);

        if ($changes === []) {
            return;
        }

        $this->auditLogger->record('news.updated', $news, $changes);

        if ($news->wasChanged('status') && $news->status === 'published') {
            $this->auditLogger->record('news.published', $news, ['new' => $news->only(['status', 'published_at'])]);
        }
    }

    private function isTocNewsAuthor(): bool
    {
        return Auth::user()?->isTocNewsAuthor() ?? false;
    }
}
