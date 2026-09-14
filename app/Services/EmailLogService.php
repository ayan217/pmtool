<?php

namespace App\Services;

use App\Enums\DeadlineType;
use App\Enums\EmailLogType;
use App\Models\EmailLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

class EmailLogService
{
    /**
     * @param  list<string>  $recipients
     */
    public function record(
        EmailLogType $type,
        array $recipients,
        string $subject,
        ?string $body = null,
        ?Task $task = null,
        ?User $user = null,
        ?DeadlineType $deadlineType = null,
        ?Carbon $sentAt = null,
    ): EmailLog {
        return EmailLog::query()->create([
            'type' => $type,
            'task_id' => $task?->id,
            'user_id' => $user?->id,
            'deadline_type' => $deadlineType,
            'recipients' => array_values(array_filter($recipients)),
            'subject' => $subject,
            'body' => $body,
            'sent_at' => $sentAt ?? now(),
        ]);
    }

    public function deadlineBody(Task $task, DeadlineType $type, Carbon $deadline): string
    {
        $task->loadMissing(['project', 'developers']);

        $developers = $task->developers
            ->map(function ($developer): string {
                $contact = collect([$developer->email, $developer->phone])->filter()->implode(', ');

                return $contact !== '' ? "{$developer->name} ({$contact})" : $developer->name;
            })
            ->filter()
            ->implode(', ');

        if ($developers === '') {
            $developers = $task->developer ?: 'Unassigned';
        }

        return implode("\n", [
            'A task deadline is approaching.',
            '',
            'Task: '.$task->title,
            'Project: '.($task->project?->name ?? 'No project'),
            'Developers: '.$developers,
            'Deadline Type: '.$type->label(),
            'Deadline: '.$deadline->timezone(config('app.timezone'))->format('j M Y, g:i A'),
            'Status: '.$task->status->label(),
            'Priority: '.$task->priority->label(),
        ]);
    }
}
