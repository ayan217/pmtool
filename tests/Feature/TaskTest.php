<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_task_creation(): void
    {
        $response = $this->actingAs($this->user)->post('/tasks', [
            'title' => 'Fix WhatsApp Notification',
            'developer' => 'Rahul',
            'priority' => TaskPriority::High->value,
            'status' => TaskStatus::Pending->value,
            'description' => 'Implement WhatsApp notification.',
            'notes' => 'Check the existing template.',
        ]);

        $task = Task::query()->first();

        $response->assertRedirect(route('tasks.show', $task));
        $this->assertDatabaseHas('tasks', [
            'title' => 'Fix WhatsApp Notification',
            'developer' => 'Rahul',
            'project_id' => null,
        ]);
    }

    public function test_standalone_task_creation(): void
    {
        $this->actingAs($this->user)->post('/tasks', $this->payload([
            'title' => 'Fix Production Login Issue',
            'project_id' => '',
        ]))->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Fix Production Login Issue',
            'project_id' => null,
        ]);
    }

    public function test_project_associated_task_creation(): void
    {
        $project = Project::factory()->create(['name' => 'CRM Tool']);

        $this->actingAs($this->user)->post('/tasks', $this->payload([
            'title' => 'Add Customer Import',
            'project_id' => $project->id,
        ]))->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Add Customer Import',
            'project_id' => $project->id,
        ]);
    }

    public function test_task_update(): void
    {
        $task = Task::factory()->create(['title' => 'Old title']);

        $this->actingAs($this->user)
            ->put(route('tasks.update', $task), $this->payload([
                'title' => 'Updated title',
                'priority' => TaskPriority::Urgent->value,
                'status' => TaskStatus::InProgress->value,
            ]))
            ->assertRedirect(route('tasks.show', $task));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated title',
            'priority' => TaskPriority::Urgent->value,
            'status' => TaskStatus::InProgress->value,
        ]);
    }

    public function test_task_completion(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->user)
            ->post(route('tasks.complete', $task))
            ->assertRedirect();

        $task->refresh();

        $this->assertSame(TaskStatus::Completed, $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_tasks_are_ordered_by_nearest_submission(): void
    {
        Task::factory()->create([
            'title' => 'No deadline',
            'dev_deadline' => null,
            'client_deadline' => null,
        ]);
        Task::factory()->create([
            'title' => 'Later deadline',
            'dev_deadline' => now()->addDays(5),
        ]);
        Task::factory()->create([
            'title' => 'Soon deadline',
            'client_deadline' => now()->addHours(6),
        ]);
        Task::factory()->create([
            'title' => 'Overdue deadline',
            'dev_deadline' => now()->subDay(),
        ]);

        $this->actingAs($this->user)
            ->get('/tasks')
            ->assertOk()
            ->assertSeeInOrder([
                'Overdue deadline',
                'Soon deadline',
                'Later deadline',
                'No deadline',
            ]);
    }

    public function test_multiple_developers_can_be_assigned(): void
    {
        $this->actingAs($this->user)->post('/tasks', $this->payload([
            'title' => 'Shared API work',
            'developers' => [
                [
                    'name' => 'Rahul',
                    'email' => 'rahul@example.com',
                    'phone' => '+919876543210',
                ],
                [
                    'name' => 'Amit',
                    'email' => 'amit@example.com',
                    'phone' => '+918888888888',
                ],
            ],
        ]))->assertRedirect();

        $task = Task::query()->where('title', 'Shared API work')->first();

        $this->assertNotNull($task);
        $this->assertSame('Rahul, Amit', $task->developer);
        $this->assertDatabaseHas('task_developers', [
            'task_id' => $task->id,
            'name' => 'Rahul',
            'email' => 'rahul@example.com',
            'phone' => '+919876543210',
        ]);
        $this->assertDatabaseHas('task_developers', [
            'task_id' => $task->id,
            'name' => 'Amit',
            'email' => 'amit@example.com',
            'phone' => '+918888888888',
        ]);
    }

    public function test_task_archiving_and_restore(): void
    {
        $task = Task::factory()->create(['status' => TaskStatus::InProgress]);

        $this->actingAs($this->user)
            ->post(route('tasks.archive', $task))
            ->assertRedirect();

        $task->refresh();
        $this->assertTrue($task->isArchived());
        $this->assertSame(TaskStatus::InProgress, $task->previous_status);

        $this->actingAs($this->user)
            ->post(route('tasks.restore', $task))
            ->assertRedirect();

        $task->refresh();
        $this->assertSame(TaskStatus::InProgress, $task->status);
        $this->assertNull($task->archived_at);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Sample task',
            'developer' => 'Rahul',
            'priority' => TaskPriority::Medium->value,
            'status' => TaskStatus::Pending->value,
            'description' => 'Do the work.',
            'notes' => 'Internal note.',
        ], $overrides);
    }
}
