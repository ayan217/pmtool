<?php

namespace App\Models;

use App\Enums\DeadlineType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeadlineNotification extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'task_id',
        'deadline_type',
        'deadline_at',
        'scheduled_for',
        'sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline_type' => DeadlineType::class,
            'deadline_at' => 'datetime',
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
