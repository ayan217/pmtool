<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDeveloper;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'notes' => fake()->optional()->sentence(),
            'developer' => fake()->optional()->firstName(),
            'status' => TaskStatus::Pending,
            'priority' => TaskPriority::Medium,
            'dev_deadline' => null,
            'client_deadline' => null,
        ];
    }

    public function forProject(?Project $project = null): static
    {
        return $this->state(fn () => [
            'project_id' => $project?->id ?? Project::factory(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Archived,
            'previous_status' => TaskStatus::Pending,
            'archived_at' => now(),
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Task $task) {
            if (! filled($task->developer) || $task->developers()->exists()) {
                return;
            }

            TaskDeveloper::query()->create([
                'task_id' => $task->id,
                'name' => $task->developer,
                'sort_order' => 0,
            ]);
        });
    }
}
