<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('keluhan:health', function (): void {
    $this->info('LaporKota GIS siap digunakan.');
})->purpose('Memeriksa bootstrap aplikasi');
