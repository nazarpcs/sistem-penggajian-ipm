<?php

use App\Jobs\CheckKontrakKadaluarsaJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Task 5.3: Scheduler — Cek kontrak PT Klien yang akan berakhir
|--------------------------------------------------------------------------
|
| Menjalankan CheckKontrakKadaluarsaJob setiap hari untuk mendeteksi
| kontrak PT Klien yang akan berakhir dalam 30 hari ke depan.
|
| @see Req 4.4
*/
Schedule::job(new CheckKontrakKadaluarsaJob())->daily();
