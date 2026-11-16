<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('toc:berkas-bersihkan-ekspor')->hourly();

// Shared hosting has no Supervisor: drain the database queue every minute.
Schedule::command('queue:work --stop-when-empty --max-time=55 --memory=256 --tries=1 --timeout=240')
    ->everyMinute()
    ->withoutOverlapping(5);
