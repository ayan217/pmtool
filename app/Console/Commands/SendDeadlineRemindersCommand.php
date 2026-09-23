<?php

namespace App\Console\Commands;

use App\Services\DailyReminderService;
use App\Services\DeadlineReminderService;
use Illuminate\Console\Command;

class SendDeadlineRemindersCommand extends Command
{
    protected $signature = 'deadlines:send-reminders';

    protected $description = 'Send deadline and daily task reminder emails that are due';

    public function handle(DeadlineReminderService $reminders, DailyReminderService $dailyReminders): int
    {
        $deadline = $reminders->sendDueReminders();
        $daily = $dailyReminders->sendDueReminders();

        $this->info("Deadline reminders sent: {$deadline['sent']}. Already recorded: {$deadline['skipped']}.");
        $this->info("Daily reminders sent: {$daily['sent']}. Already recorded: {$daily['skipped']}.");

        return self::SUCCESS;
    }
}
