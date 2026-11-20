<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('studies:expire-drafts')->hourly()->withoutOverlapping();
Schedule::command('auth:clear-resets')->daily();
Schedule::command('queue:prune-failed', ['--hours' => 24 * 30])->daily();
