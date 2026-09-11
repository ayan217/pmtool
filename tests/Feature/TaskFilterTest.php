<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_status_filtering(): void
    {
        Task::factory()->create(['title' => 'Pending task', 'status' => TaskStatus::Pending]);
        Task::factory()->create(['title' => 'Blocked task', 'status' => TaskStatus::Blocked]);

        $this->actingAs($this->user)
            ->get('/tasks?status=blocked')
            ->assertOk()
            ->assertSee('Blocked task')
            ->assertDontSee('Pending task');
    }

    public function test_project_filtering(): void
    {
        $crm = Project::factory()->create(['name' => 'CRM Tool']);
        $other = Project::factory()->create(['name' => 'Website']);

        Task::factory()->forProject($crm)->create(['title' => 'CRM Dashboard']);
        Task::factory()->forProject($other)->create(['title' => 'Homepage']);

        $this->actingAs($this->user)
            ->get('/tasks?project='.$crm->id)
            ->assertOk()
            ->assertSee('CRM Dashboard')
            ->assertDontSee('Homepage');
    }

    public function test_developer_filtering(): void
    {
        Task::factory()->create(['title' => 'Rahul task', 'developer' => 'Rahul']);
        Task::factory()->create(['title' => 'Amit task', 'developer' => 'Amit']);

        $this->actingAs($this->user)
            ->get('/tasks?developer=Rahul')
            ->assertOk()
            ->assertSee('Rahul task')
            ->assertDontSee('Amit task');
    }

    public function test_search(): void
    {
        $match = Task::factory()->create([
            'title' => 'Fix login timeout',
            'description' => 'Ordinary description',
        ]);
        Task::factory()->create(['title' => 'Unrelated billing issue']);
        TaskComment::factory()->create([
            'task_id' => $match->id,
            'user_id' => $this->user->id,
            'comment' => 'WhatsApp webhook still pending.',
        ]);
        Task::factory()->create([
            'title' => 'Notification copy',
            'notes' => 'Use the WhatsApp template',
        ]);

        $this->actingAs($this->user)
            ->get('/tasks?quick=all&q=WhatsApp')
            ->assertOk()
            ->assertSee('Fix login timeout')
            ->assertSee('Notification copy')
            ->assertDontSee('Unrelated billing issue');
    }

    public function test_priority_filtering(): void
    {
        Task::factory()->create(['title' => 'Urgent fix', 'priority' => TaskPriority::Urgent]);
        Task::factory()->create(['title' => 'Low polish', 'priority' => TaskPriority::Low]);

        $this->actingAs($this->user)
            ->get('/tasks?priority=urgent')
            ->assertOk()
            ->assertSee('Urgent fix')
            ->assertDontSee('Low polish');
    }
}
