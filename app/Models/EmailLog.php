<?php

namespace App\Models;

use App\Enums\DeadlineType;
use App\Enums\EmailLogType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'task_id',
        'user_id',
        'deadline_type',
        'recipients',
        'subject',
        'body',
        'sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EmailLogType::class,
            'deadline_type' => DeadlineType::class,
            'recipients' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipientList(): string
    {
        return collect($this->recipients ?? [])
            ->filter()
            ->implode(', ') ?: '—';
    }

    public function displayBody(): string
    {
        $body = trim((string) $this->body);

        if ($body === '') {
            return 'No body was stored for this email.';
        }

        if (
            ! $this->task
            || ! in_array($this->type, [EmailLogType::StatusReminder, EmailLogType::DailyReminder], true)
        ) {
            return $body;
        }

        $this->loadMissing('task.project');
        $priorityLine = 'Priority: '.$this->task->priority->label();

        if (str_contains($body, 'Task: '.$this->task->title)) {
            if (str_contains($body, 'Priority: ')) {
                return $body;
            }

            if (preg_match('/^Project: .+$/m', $body) === 1) {
                return (string) preg_replace('/^(Project: .+)$/m', '$1'."\n".$priorityLine, $body, 1);
            }

            return $priorityLine."\n\n".$body;
        }

        $project = trim((string) ($this->task->project?->name ?? ''));

        return implode("\n", [
            'Task: '.$this->task->title,
            'Project: '.($project !== '' ? $project : 'No project'),
            $priorityLine,
        ])."\n\n".$body;
    }
}
