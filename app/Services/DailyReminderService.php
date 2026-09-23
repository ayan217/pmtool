<?php

namespace App\Services;

use App\Enums\EmailLogType;
use App\Models\DailyReminderNotification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

class DailyReminderService
{
    public function __construct(
        protected EmailSettingsService $emailSettings,
        protected StatusReminderSender $sender,
    ) {}

    /**
     * @return array{sent: int, skipped: int}
     */
    public function sendDueReminders(?Carbon $now = null): array
    {
        $user = User::query()->orderBy('id')->first();

        if ($user === null) {
            return ['sent' => 0, 'skipped' => 0];
        }

        $now = ($now ?? now())->copy()->timezone((string) config('app.timezone'));
        $scheduled = $now->copy()->setTimeFromTimeString($this->emailSettings->dailyReminderTime($user));

        if ($now->lt($scheduled)) {
            return ['sent' => 0, 'skipped' => 0];
        }

        $sentOn = $now->toDateString();
        $sent = 0;
        $skipped = 0;

        $tasks = Task::query()
            ->with(['project', 'developers', 'attachments'])
            ->active()
            ->get();

        foreach ($tasks as $task) {
            if ($this->dispatch($user, $task, $sentOn)) {
                $sent++;
            } else {
                $skipped++;
            }
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    public function dispatch(User $user, Task $task, ?string $sentOn = null): bool
    {
        if (! $task->isActive() || $task->developerEmails() === []) {
            return false;
        }

        $sentOn ??= now()->timezone((string) config('app.timezone'))->toDateString();

        try {
            $notification = DailyReminderNotification::query()->firstOrCreate(
                [
                    'task_id' => $task->id,
                    'sent_on' => $sentOn,
                ],
                [
                    'sent_at' => null,
                ],
            );
        } catch (QueryException) {
            return false;
        }

        if ($notification->sent_at !== null) {
            return false;
        }

        if ($this->sender->queue($task, $user, EmailLogType::DailyReminder) === []) {
            return false;
        }

        $notification->update(['sent_at' => now()]);

        return true;
    }
}
