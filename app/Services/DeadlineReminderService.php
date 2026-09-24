<?php

namespace App\Services;

use App\Enums\DeadlineType;
use App\Enums\EmailLogType;
use App\Enums\TaskStatus;
use App\Mail\DeadlineReminderMail;
use App\Models\DeadlineNotification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class DeadlineReminderService
{
    public function __construct(
        protected SettingsService $settings,
        protected EmailSettingsService $emailSettings,
        protected EmailLogService $emailLogs,
    ) {}

    /**
     * @return array{sent: int, skipped: int}
     */
    public function sendDueReminders(?Carbon $now = null): array
    {
        $now ??= now();
        $sent = 0;
        $skipped = 0;

        foreach (User::query()->get() as $user) {
            foreach (DeadlineType::cases() as $type) {
                $result = $this->sendForUserAndType($user, $type, $now);
                $sent += $result['sent'];
                $skipped += $result['skipped'];
            }
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    /**
     * @return array{sent: int, skipped: int}
     */
    protected function sendForUserAndType(User $user, DeadlineType $type, Carbon $now): array
    {
        if (! $this->settings->notificationsEnabled($user, $type)) {
            return ['sent' => 0, 'skipped' => 0];
        }

        $hours = $this->settings->reminderHours($user, $type);
        $windowEnd = $now->copy()->addHours($hours);
        $column = $type === DeadlineType::Dev ? 'dev_deadline' : 'client_deadline';

        $tasks = Task::query()
            ->with(['project', 'developers', 'attachments'])
            ->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Archived, TaskStatus::OnClientReview])
            ->whereNull('archived_at')
            ->whereNull('completed_at')
            ->whereNotNull($column)
            ->where($column, '>', $now)
            ->where($column, '<=', $windowEnd)
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($tasks as $task) {
            if ($this->dispatchReminder($user, $task, $type, $hours, $now)) {
                $sent++;
            } else {
                $skipped++;
            }
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    public function dispatchReminder(User $user, Task $task, DeadlineType $type, int $hours, ?Carbon $now = null): bool
    {
        $now ??= now();

        if (! $task->acceptsEmails()) {
            return false;
        }

        $deadline = $task->deadlineFor($type);

        if ($deadline === null || $deadline->lte($now)) {
            return false;
        }

        $scheduledFor = $deadline->copy()->subHours($hours);

        if ($now->lt($scheduledFor)) {
            return false;
        }

        try {
            $notification = DeadlineNotification::query()->firstOrCreate(
                [
                    'task_id' => $task->id,
                    'deadline_type' => $type,
                    'deadline_at' => $deadline,
                ],
                [
                    'scheduled_for' => $scheduledFor,
                    'sent_at' => null,
                ],
            );
        } catch (QueryException) {
            return false;
        }

        if ($notification->sent_at !== null) {
            return false;
        }

        $adminEmail = $this->emailSettings->adminEmail($user);

        Mail::to($adminEmail)->queue(new DeadlineReminderMail(
            $task,
            $type,
            $deadline,
            $this->emailSettings->fromName($user),
        ));

        $notification->update(['sent_at' => now()]);

        $this->emailLogs->record(
            EmailLogType::DeadlineReminder,
            [$adminEmail],
            'Deadline approaching: '.$task->title,
            $this->emailLogs->deadlineBody($task, $type, $deadline),
            $task,
            $user,
            $type,
        );

        return true;
    }
}
