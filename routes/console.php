<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('deadlines:send-reminders')->everyFifteenMinutes();
Schedule::command('db:backup')->dailyAt('02:00');
