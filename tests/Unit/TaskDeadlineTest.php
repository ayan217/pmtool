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

        $this->assertSame('out of 5 hours, 5 hours are remaining', $task->remainingHoursLabel());
    }

    public function test_remaining_hours_label_uses_days_after_twenty_four_hours(): void
    {
        $this->assertSame('out of 23 hours, 23 hours are remaining', Task::factory()->create([
            'dev_deadline' => now()->addHours(23),
        ])->remainingHoursLabel());

        $this->assertSame('out of 1 day, 1 day is remaining', Task::factory()->create([
            'dev_deadline' => now()->addHours(24),
        ])->remainingHoursLabel());

        $this->assertSame('out of 11 days, 11 days are remaining', Task::factory()->create([
            'dev_deadline' => now()->addHours(270),
        ])->remainingHoursLabel());
    }

    public function test_remaining_hours_label_shows_elapsed_window(): void
    {
        $this->assertSame('out of 8 hours, 1 hour is remaining', Task::factory()->create([
            'created_at' => now()->subHours(7),
            'dev_deadline' => now()->addHour(),
        ])->remainingHoursLabel());

        $this->assertSame('out of 7 days, 1 day is remaining', Task::factory()->create([
            'created_at' => now()->subDays(6),
            'dev_deadline' => now()->addDay(),
        ])->remainingHoursLabel());
    }

    public function test_completed_tasks_are_ordered_after_open_work(): void
    {
        $openLater = Task::factory()->create([
            'title' => 'Open later',
            'dev_deadline' => now()->addDays(5),
        ]);
        $completedSoon = Task::factory()->completed()->create([
            'title' => 'Completed soon',
            'dev_deadline' => now()->subDay(),
        ]);
        $openSoon = Task::factory()->create([
            'title' => 'Open soon',
            'dev_deadline' => now()->subHours(2),
        ]);

        $this->assertSame(
            [$openSoon->id, $openLater->id, $completedSoon->id],
            Task::query()->orderBySubmission()->pluck('id')->all(),
        );
    }
}
