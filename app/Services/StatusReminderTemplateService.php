<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

class StatusReminderTemplateService
{
    public const KEY_SUBJECT = 'status_reminder_subject';

    public const KEY_BODY = 'status_reminder_body';

    public function __construct(protected SettingsService $settings) {}

    /**
     * @return array{subject: string, body: string}
     */
    public function defaults(): array
    {
        return [
            'subject' => (string) config('pm.status_reminder.subject'),
            'body' => (string) config('pm.status_reminder.body'),
        ];
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function forUser(User $user): array
    {
        $stored = $user->settings()->pluck('value', 'key');
        $defaults = $this->defaults();

        $subject = trim((string) ($stored->get(self::KEY_SUBJECT) ?? ''));
        $body = trim((string) ($stored->get(self::KEY_BODY) ?? ''));

        return [
            'subject' => $subject !== '' ? $subject : $defaults['subject'],
            'body' => $body !== '' ? $body : $defaults['body'],
        ];
    }

    public function update(User $user, string $subject, string $body): void
    {
        $this->settings->set($user, self::KEY_SUBJECT, $subject);
        $this->settings->set($user, self::KEY_BODY, $body);
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function render(User $user, Task $task): array
    {
        $template = $this->forUser($user);
        $replacements = $this->replacements($task);

        return [
            'subject' => strtr($template['subject'], $replacements),
            'body' => strtr($template['body'], $replacements),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function replacements(Task $task): array
    {
        $task->loadMissing('project');
        $description = trim((string) $task->description);
        $project = trim((string) ($task->project?->name ?? ''));

        return [
            '{task.title}' => $task->title,
            '{task.project}' => $project !== '' ? $project : 'No project',
            '{project.name}' => $project !== '' ? $project : 'No project',
            '{task.des}' => $description !== '' ? $description : 'No description yet.',
            '{task.description}' => $description !== '' ? $description : 'No description yet.',
            '{remaining.hours}' => $task->remainingHoursLabel(),
        ];
    }
}
