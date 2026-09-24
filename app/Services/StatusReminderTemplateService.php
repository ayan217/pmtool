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
        $body = $this->normalizeLineEndings(trim((string) ($stored->get(self::KEY_BODY) ?? '')));

        if ($this->isLegacyDefaultBody($body)) {
            $body = '';
        }

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
        $body = strtr($template['body'], $replacements);

        if (! $this->templateIncludesIdentity($template['body'])) {
            $body = $this->identityHeader($task)."\n\n".$body;
        }

        return [
            'subject' => strtr($template['subject'], $replacements),
            'body' => $body,
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

    protected function identityHeader(Task $task): string
    {
        $task->loadMissing('project');
        $project = trim((string) ($task->project?->name ?? ''));

        return implode("\n", [
            'Task: '.$task->title,
            'Project: '.($project !== '' ? $project : 'No project'),
        ]);
    }

    protected function templateIncludesIdentity(string $body): bool
    {
        return str_contains($body, '{task.title}')
            && (str_contains($body, '{project.name}') || str_contains($body, '{task.project}'));
    }

    protected function isLegacyDefaultBody(string $body): bool
    {
        return $body === $this->legacyDefaultBody();
    }

    protected function legacyDefaultBody(): string
    {
        return "hi team,\n\nplease share the status of this task, the deadline is in {remaining.hours}, if any delay is happening please contact me personally over call or whatsapp.\n\n{task.des}";
    }

    protected function normalizeLineEndings(string $value): string
    {
        return str_replace(["\r\n", "\r"], "\n", $value);
    }
}
