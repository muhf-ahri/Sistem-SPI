<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Notifikasi peringatan otomatis: jadwal audit terlewat, hitung mundur H-3,
// temuan belum diperbaiki, verifikasi tindak lanjut, dan audit belum ditutup.
Schedule::command('notifications:alerts')->dailyAt('08:00');
