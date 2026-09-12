<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Developer;
use App\Models\Task;
use App\Models\TaskDeveloper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guests_cannot_view_developers(): void
    {
        $this->get(route('developers.index'))->assertRedirect(route('login'));
    }

    public function test_developers_index_lists_profiles(): void
    {
        Developer::factory()->create([
            'name' => 'Rahul',
            'email' => 'rahul@example.com',
            'phone' => '+919876543210',
        ]);

        $this->actingAs($this->user)
            ->get(route('developers.index'))
            ->assertOk()
            ->assertSee('Rahul')
            ->assertSee('rahul@example.com')
            ->assertSee('+919876543210');
    }

    public function test_developer_profile_can_be_created(): void
    {
        $this->actingAs($this->user)
            ->post(route('developers.store'), [
                'name' => 'Amit',
                'email' => 'amit@example.com',
                'phone' => '+918888888888',
            ])
            ->assertRedirect(route('developers.index'));

        $this->assertDatabaseHas('developers', [
            'name' => 'Amit',
            'email' => 'amit@example.com',
            'phone' => '+918888888888',
        ]);
    }

    public function test_developer_profile_can_be_updated(): void
    {
        $developer = Developer::factory()->create([
            'name' => 'Rahul',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($this->user)
            ->put(route('developers.update', $developer), [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => '+919111111111',
            ])
            ->assertRedirect(route('developers.index'));

        $this->assertDatabaseHas('developers', [
            'id' => $developer->id,
            'email' => 'rahul@example.com',
            'phone' => '+919111111111',
        ]);
    }

    public function test_duplicate_developer_names_are_rejected(): void
    {
        Developer::factory()->create(['name' => 'Rahul']);

        $this->actingAs($this->user)
            ->from(route('developers.create'))
            ->post(route('developers.store'), [
                'name' => 'rahul',
            ])
            ->assertRedirect(route('developers.create'))
            ->assertSessionHasErrors('name');
    }

    public function test_existing_task_developers_are_imported_into_the_directory(): void
    {
        $task = Task::factory()->create(['developer' => null]);

        TaskDeveloper::query()->create([
            'task_id' => $task->id,
            'name' => 'Shivam',
            'email' => 'shivam@example.com',
            'phone' => '+917777777777',
            'sort_order' => 0,
        ]);

        Developer::importFromExistingAssignments();

        $this->assertDatabaseHas('developers', [
            'name' => 'Shivam',
            'email' => 'shivam@example.com',
            'phone' => '+917777777777',
        ]);
    }

    public function test_combined_assignment_names_are_split_on_import(): void
    {
        Task::factory()->create(['developer' => 'Akash, Shivam']);

        Developer::query()->delete();
        Developer::importFromExistingAssignments();

        $this->assertNotNull(Developer::findByName('Akash'));
        $this->assertNotNull(Developer::findByName('Shivam'));
        $this->assertNull(Developer::findByName('Akash, Shivam'));
    }

    public function test_assigning_a_new_developer_on_a_task_adds_them_to_the_directory(): void
    {
        $this->actingAs($this->user)
            ->post('/tasks', [
                'title' => 'New API work',
                'priority' => TaskPriority::Medium->value,
                'status' => TaskStatus::Pending->value,
                'developers' => [
                    [
                        'name' => 'Priya',
                        'email' => 'priya@example.com',
                        'phone' => '+919000000000',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('developers', [
            'name' => 'Priya',
            'email' => 'priya@example.com',
            'phone' => '+919000000000',
        ]);
        $this->assertDatabaseCount('developers', 1);
    }

    public function test_assigning_an_existing_developer_does_not_create_a_duplicate(): void
    {
        Developer::factory()->create([
            'name' => 'Rahul',
            'email' => 'rahul@example.com',
            'phone' => '+919876543210',
        ]);

        $this->actingAs($this->user)
            ->post('/tasks', [
                'title' => 'Follow up',
                'priority' => TaskPriority::Medium->value,
                'status' => TaskStatus::Pending->value,
                'developers' => [
                    [
                        'name' => 'Rahul',
                        'email' => 'rahul@example.com',
                        'phone' => '+919876543210',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('developers', 1);
        $this->assertDatabaseHas('task_developers', [
            'name' => 'Rahul',
            'email' => 'rahul@example.com',
        ]);
    }

    public function test_task_form_includes_developer_suggestions(): void
    {
        Developer::factory()->create([
            'name' => 'Abhishek',
            'email' => 'abhishek@example.com',
        ]);

        $this->actingAs($this->user)
            ->get(route('tasks.create'))
            ->assertOk()
            ->assertSee('developerCatalogList')
            ->assertSee('Abhishek')
            ->assertSee('abhishek@example.com');
    }
}
