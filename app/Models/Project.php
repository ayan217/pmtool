<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
        ];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::Active);
    }

    /**
     * @return array<string, int>
     */
    public function taskCounts(): array
    {
        $counts = $this->tasks()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => (int) $counts->sum(),
            'pending' => (int) $counts->get(TaskStatus::Pending->value, 0),
            'in_progress' => (int) $counts->get(TaskStatus::InProgress->value, 0),
            'blocked' => (int) $counts->get(TaskStatus::Blocked->value, 0),
            'completed' => (int) $counts->get(TaskStatus::Completed->value, 0),
            'archived' => (int) $counts->get(TaskStatus::Archived->value, 0),
        ];
    }

    public function markCompleted(): void
    {
        $this->update(['status' => ProjectStatus::Completed]);
    }

    public function archive(): void
    {
        $this->update(['status' => ProjectStatus::Archived]);
    }
}
