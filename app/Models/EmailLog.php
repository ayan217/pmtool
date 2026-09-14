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
}
