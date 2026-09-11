<?php

namespace App\Console\Commands;

use App\Services\DeadlineReminderService;
use Illuminate\Console\Command;

class SendDeadlineRemindersCommand extends Command
{
    protected $signature = 'deadlines:send-reminders';

    protected $description = 'Send queued email reminders for upcoming task deadlines';

    public function handle(DeadlineReminderService $reminders): int
    {
        $result = $reminders->sendDueReminders();

        $this->info("Reminders sent: {$result['sent']}. Already recorded: {$result['skipped']}.");

        return self::SUCCESS;
    }
}
