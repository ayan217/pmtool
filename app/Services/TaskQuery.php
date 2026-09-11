<?php

namespace App\Services;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TaskQuery
{
    public function fromRequest(Request $request, ?string $defaultView = 'active'): Builder
    {
        return $this->apply(Task::query()->with('project'), $request->all(), $defaultView);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function apply(Builder $query, array $filters, ?string $defaultView = 'active'): Builder
    {
        $quick = $this->stringValue($filters['quick'] ?? null);

        $this->applyDefaultView($query, $filters, $defaultView, $quick);
        $this->applyQuickFilter($query, $quick);
        $this->applyProject($query, $filters['project'] ?? null);
        $this->applyDeveloper($query, $filters['developer'] ?? null);
        $this->applyStatus($query, $filters['status'] ?? null, $quick);
        $this->applyPriority($query, $filters['priority'] ?? null);
        $this->applyDeadlineWindow($query, 'dev_deadline', $filters['dev_deadline'] ?? null);
        $this->applyDeadlineWindow($query, 'client_deadline', $filters['client_deadline'] ?? null);
        $this->applyDateRange($query, $filters['from'] ?? null, $filters['to'] ?? null);
        $this->applyCompletedRange($query, $filters['completed_from'] ?? null, $filters['completed_to'] ?? null);
        $this->applySearch($query, $filters['q'] ?? null);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyDefaultView(Builder $query, array $filters, ?string $defaultView, ?string $quick): void
    {
        if ($defaultView === 'archived') {
            $query->archived();

            return;
        }

        if ($this->hasExplicitVisibilityFilter($filters, $quick)) {
            return;
        }

        if ($defaultView === 'active') {
            $query->active();
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function hasExplicitVisibilityFilter(array $filters, ?string $quick): bool
    {
        $status = $this->stringValue($filters['status'] ?? null);

        return in_array($quick, ['all', 'today', 'tomorrow', 'this_week', 'overdue', 'in_progress', 'completed', 'archived', 'no_project'], true)
            || in_array($status, TaskStatus::values(), true)
            || filled($filters['completed_from'] ?? null)
            || filled($filters['completed_to'] ?? null);
    }

    protected function applyQuickFilter(Builder $query, ?string $quick): void
    {
        match ($quick) {
            'all' => $query->notArchived(),
            'today' => $this->whereDeadlineOn($query, now()->toDateString()),
            'tomorrow' => $this->whereDeadlineOn($query, now()->addDay()->toDateString()),
            'this_week' => $this->whereDeadlineBetween($query, now()->startOfWeek(), now()->endOfWeek()),
            'overdue' => $query->overdue(),
            'in_progress' => $query->notArchived()->where('status', TaskStatus::InProgress),
            'completed' => $query->completed(),
            'archived' => $query->archived(),
            'no_project' => $query->notArchived()->whereNull('project_id'),
            default => null,
        };
    }

    protected function applyProject(Builder $query, mixed $project): void
    {
        $value = $this->stringValue($project);

        if ($value === null) {
            return;
        }

        if ($value === 'none' || $value === '0') {
            $query->whereNull('project_id');

            return;
        }

        $query->where('project_id', $value);
    }

    protected function applyDeveloper(Builder $query, mixed $developer): void
    {
        $value = $this->stringValue($developer);

        if ($value === null) {
            return;
        }

        $query->where('developer', 'like', '%'.$this->escapeLike($value).'%');
    }

    protected function applyStatus(Builder $query, mixed $status, ?string $quick): void
    {
        $value = $this->stringValue($status);

        if ($value === null || $quick === 'in_progress' || $quick === 'completed' || $quick === 'archived') {
            return;
        }

        if (! in_array($value, TaskStatus::values(), true)) {
            return;
        }

        if ($value === TaskStatus::Archived->value) {
            $query->archived();

            return;
        }

        $query->where('status', $value)->whereNull('archived_at');
    }

    protected function applyPriority(Builder $query, mixed $priority): void
    {
        $value = $this->stringValue($priority);

        if ($value === null || ! in_array($value, TaskPriority::values(), true)) {
            return;
        }

        $query->where('priority', $value);
    }

    protected function applyDeadlineWindow(Builder $query, string $column, mixed $window): void
    {
        $value = $this->stringValue($window);

        if ($value === null) {
            return;
        }

        $now = now();

        match ($value) {
            'overdue' => $query->whereNotNull($column)->where($column, '<', $now),
            'today' => $query->whereDate($column, $now->toDateString()),
            'tomorrow' => $query->whereDate($column, $now->copy()->addDay()->toDateString()),
            'this_week' => $query->whereBetween($column, [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]),
            'upcoming' => $query->whereNotNull($column)->where($column, '>=', $now),
            default => null,
        };
    }

    protected function applyDateRange(Builder $query, mixed $from, mixed $to): void
    {
        $fromDate = $this->parseDate($from)?->startOfDay();
        $toDate = $this->parseDate($to)?->endOfDay();

        if ($fromDate === null && $toDate === null) {
            return;
        }

        $query->where(function (Builder $builder) use ($fromDate, $toDate) {
            foreach (['dev_deadline', 'client_deadline'] as $column) {
                $builder->orWhere(function (Builder $inner) use ($column, $fromDate, $toDate) {
                    $inner->whereNotNull($column);

                    if ($fromDate) {
                        $inner->where($column, '>=', $fromDate);
                    }

                    if ($toDate) {
                        $inner->where($column, '<=', $toDate);
                    }
                });
            }
        });
    }

    protected function applyCompletedRange(Builder $query, mixed $from, mixed $to): void
    {
        $fromDate = $this->parseDate($from)?->startOfDay();
        $toDate = $this->parseDate($to)?->endOfDay();

        if ($fromDate === null && $toDate === null) {
            return;
        }

        $query->whereNotNull('completed_at');

        if ($fromDate) {
            $query->where('completed_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('completed_at', '<=', $toDate);
        }
    }

    protected function applySearch(Builder $query, mixed $search): void
    {
        $term = $this->stringValue($search);

        if ($term === null) {
            return;
        }

        $like = '%'.$this->escapeLike($term).'%';

        $query->where(function (Builder $builder) use ($like) {
            $builder
                ->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('notes', 'like', $like)
                ->orWhere('developer', 'like', $like)
                ->orWhereHas('project', fn (Builder $project) => $project->where('name', 'like', $like))
                ->orWhereHas('comments', fn (Builder $comments) => $comments->where('comment', 'like', $like));
        });
    }

    protected function whereDeadlineOn(Builder $query, string $date): void
    {
        $query->notArchived()->where(function (Builder $builder) use ($date) {
            $builder
                ->whereDate('dev_deadline', $date)
                ->orWhereDate('client_deadline', $date);
        });
    }

    protected function whereDeadlineBetween(Builder $query, Carbon $from, Carbon $to): void
    {
        $query->notArchived()->where(function (Builder $builder) use ($from, $to) {
            $builder
                ->whereBetween('dev_deadline', [$from, $to])
                ->orWhereBetween('client_deadline', [$from, $to]);
        });
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        $value = $this->stringValue($value);

        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function stringValue(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
