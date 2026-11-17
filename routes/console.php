<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tasks
Schedule::command('hris:propose-retirements')->monthlyOn(1, '01:00');
Schedule::command('hris:archive-rejected-applicants')->monthlyOn(1, '02:00');
