<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('deadlines:send-reminders')->everyFifteenMinutes();
