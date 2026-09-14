<?php

namespace Tests\Unit;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_dev_and_client_overdue_are_independent(): void
    {
        User::factory()->create();

        $task = Task::factory()->create([
            'status' => TaskStatus::InProgress,
            'dev_deadline' => now()->subHour(),
            'client_deadline' => now()->addDay(),
        ]);

        $this->assertTrue($task->isDevOverdue());
        $this->assertFalse($task->isClientOverdue());
    }

    public function test_completed_tasks_are_not_overdue(): void
    {
        $task = Task::factory()->completed()->create([
            'dev_deadline' => now()->subHour(),
            'client_deadline' => now()->subHour(),
        ]);

        $this->assertFalse($task->isDevOverdue());
        $this->assertFalse($task->isClientOverdue());
    }

    public function test_remaining_hours_label_uses_the_soonest_deadline(): void
    {
        $task = Task::factory()->create([
            'dev_deadline' => now()->addHours(5),
            'client_deadline' => now()->addHours(12),
        ]);

        $this->assertSame('5 hours', $task->remainingHoursLabel());
    }
}
