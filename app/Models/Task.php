<?php

namespace App\Models;

use App\Enums\DeadlineType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'notes',
        'developer',
        'status',
        'priority',
        'previous_status',
        'dev_deadline',
        'client_deadline',
        'completed_at',
        'archived_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'previous_status' => TaskStatus::class,
            'dev_deadline' => 'datetime',
            'client_deadline' => 'datetime',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function developers(): HasMany
    {
        return $this->hasMany(TaskDeveloper::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  list<array<string, mixed>>  $developers
     */
    public function syncDevelopers(array $developers): void
    {
        $this->developers()->delete();

        $rows = collect($developers)
            ->map(function (array $developer, int $index): ?array {
                $name = trim((string) ($developer['name'] ?? ''));
                $email = trim((string) ($developer['email'] ?? ''));
                $phone = trim((string) ($developer['phone'] ?? ''));

                if ($name === '' && $email === '' && $phone === '') {
                    return null;
                }

                return [
                    'name' => $name !== '' ? $name : 'Developer',
                    'email' => $email !== '' ? $email : null,
                    'phone' => $phone !== '' ? $phone : null,
                    'sort_order' => $index,
                ];
            })
            ->filter()
            ->values();

        if ($rows->isNotEmpty()) {
            $this->developers()->createMany($rows->all());
        }

        $this->forceFill([
            'developer' => $rows->pluck('name')->filter()->implode(', ') ?: null,
        ])->save();
    }

    public function deadlineNotifications(): HasMany
    {
        return $this->hasMany(DeadlineNotification::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('archived_at')
            ->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Archived]);
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query
            ->whereNull('archived_at')
            ->where('status', '!=', TaskStatus::Archived);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', TaskStatus::Completed)->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder
                ->whereNotNull('archived_at')
                ->orWhere('status', TaskStatus::Archived);
        });
    }

    public function scopeOverdue(Builder $query): Builder
    {
        $now = now();

        return $query->active()->where(function (Builder $builder) use ($now) {
            $builder
                ->where(function (Builder $inner) use ($now) {
                    $inner->whereNotNull('dev_deadline')->where('dev_deadline', '<', $now);
                })
                ->orWhere(function (Builder $inner) use ($now) {
                    $inner->whereNotNull('client_deadline')->where('client_deadline', '<', $now);
                });
        });
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::Completed;
    }

    public function isArchived(): bool
    {
        return $this->status === TaskStatus::Archived || $this->archived_at !== null;
    }

    public function isActive(): bool
    {
        return ! $this->isCompleted() && ! $this->isArchived();
    }

    public function isDevOverdue(): bool
    {
        return $this->isDeadlineOverdue(DeadlineType::Dev);
    }

    public function isClientOverdue(): bool
    {
        return $this->isDeadlineOverdue(DeadlineType::Client);
    }

    public function isDeadlineOverdue(DeadlineType $type): bool
    {
        $deadline = $this->deadlineFor($type);

        return $deadline !== null
            && $deadline->lt(now())
            && $this->isActive();
    }

    public function deadlineFor(DeadlineType $type): ?Carbon
    {
        return $type === DeadlineType::Dev ? $this->dev_deadline : $this->client_deadline;
    }

    public function complete(): void
    {
        $this->update([
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function archive(): void
    {
        $this->update([
            'previous_status' => $this->status === TaskStatus::Archived
                ? ($this->previous_status ?? TaskStatus::Pending)
                : $this->status,
            'status' => TaskStatus::Archived,
            'archived_at' => now(),
        ]);
    }

    public function restore(): void
    {
        $restoredStatus = $this->previous_status ?? TaskStatus::Pending;

        if ($restoredStatus === TaskStatus::Archived) {
            $restoredStatus = TaskStatus::Pending;
        }

        $this->update([
            'status' => $restoredStatus,
            'archived_at' => null,
            'previous_status' => null,
            'completed_at' => $restoredStatus === TaskStatus::Completed
                ? ($this->completed_at ?? now())
                : null,
        ]);
    }
}
