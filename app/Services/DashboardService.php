<?php

namespace App\Services;

use App\Enums\DeadlineType;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $base = Task::query()->notArchived();

        return [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', TaskStatus::Pending)->count(),
            'in_progress' => (clone $base)->where('status', TaskStatus::InProgress)->count(),
            'blocked' => (clone $base)->where('status', TaskStatus::Blocked)->count(),
            'completed' => (clone $base)->where('status', TaskStatus::Completed)->count(),
            'overdue' => Task::query()->overdue()->count(),
        ];
    }

    /**
     * @return Collection<int, Task>
     */
    public function overdueTasks(int $limit = 8): Collection
    {
        return Task::query()
            ->with(['project', 'developers'])
            ->overdue()
            ->orderByRaw('CASE
                WHEN dev_deadline IS NULL THEN client_deadline
                WHEN client_deadline IS NULL THEN dev_deadline
                WHEN dev_deadline < client_deadline THEN dev_deadline
                ELSE client_deadline
            END')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function upcomingDeadlines(int $limit = 8): Collection
    {
        return $this->deadlineRows(now(), null, $limit);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function todayDeadlines(): Collection
    {
        return $this->deadlineRows(now()->startOfDay(), now()->endOfDay(), 20);
    }

    /**
     * @return Collection<int, Task>
     */
    public function recentlyUpdated(int $limit = 8): Collection
    {
        return Task::query()
            ->with(['project', 'developers'])
            ->notArchived()
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Task>
     */
    public function recentlyCompleted(int $limit = 8): Collection
    {
        return Task::query()
            ->with(['project', 'developers'])
            ->completed()
            ->orderByDesc('completed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function deadlineRows(?Carbon $from, ?Carbon $to, int $limit): Collection
    {
        $rows = collect();

        foreach ([DeadlineType::Dev, DeadlineType::Client] as $type) {
            $column = $type === DeadlineType::Dev ? 'dev_deadline' : 'client_deadline';

            $tasks = Task::query()
                ->with(['project', 'developers'])
                ->active()
                ->whereNotNull($column)
                ->when($from, fn ($query) => $query->where($column, '>=', $from))
                ->when($to, fn ($query) => $query->where($column, '<=', $to))
                ->orderBy($column)
                ->limit($limit)
                ->get();

            foreach ($tasks as $task) {
                $deadline = $task->deadlineFor($type);

                if ($deadline === null) {
                    continue;
                }

                $rows->push([
                    'task' => $task,
                    'type' => $type,
                    'deadline' => $deadline,
                ]);
            }
        }

        return $rows
            ->sortBy('deadline')
            ->values()
            ->take($limit);
    }
}
