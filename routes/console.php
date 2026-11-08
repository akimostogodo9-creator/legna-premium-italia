<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// F-SYS-01 (sprint 9) : suppression des messages de contact trop anciens, chaque nuit à 3 h 15 (heure de Rome)
Schedule::command('legna:purge-contact-messages')->dailyAt('03:15')->timezone('Europe/Rome')->withoutOverlapping();
