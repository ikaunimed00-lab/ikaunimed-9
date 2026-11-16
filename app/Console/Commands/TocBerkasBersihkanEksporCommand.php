<?php

namespace App\Console\Commands;

use App\Models\TocBerkasExportArtifact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TocBerkasBersihkanEksporCommand extends Command
{
    protected $signature = 'toc:berkas-bersihkan-ekspor';

    protected $description = 'Hapus ZIP ekspor berkas ToC yang sudah kedaluwarsa';

    public function handle(): int
    {
        $disk = Storage::disk(config('toc.berkas.disk'));
        $deleted = 0;

        TocBerkasExportArtifact::query()
            ->where('expires_at', '<=', now())
            ->each(function (TocBerkasExportArtifact $artifact) use ($disk, &$deleted): void {
                if ($disk->exists($artifact->path)) {
                    $disk->delete($artifact->path);
                }

                $artifact->delete();
                $deleted++;
            });

        $this->info("{$deleted} ekspor kedaluwarsa dihapus.");

        return self::SUCCESS;
    }
}
