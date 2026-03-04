<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('app:auto-backup')->hourly();
\Illuminate\Support\Facades\Schedule::command('crm:send-reminders')->everyMinute();

