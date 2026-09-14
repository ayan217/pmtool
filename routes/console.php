<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('deadlines:send-reminders')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('db:backup')
    ->dailyAt('02:00')
    ->withoutOverlapping();
