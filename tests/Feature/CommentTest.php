<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Ayan']);
        $this->task = Task::factory()->create();
    }

    public function test_add_comment(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('tasks.comments.store', $this->task), [
                'comment' => 'API integration is completed.',
            ])
            ->assertOk()
            ->assertJsonPath('comment.comment', 'API integration is completed.');

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => 'API integration is completed.',
        ]);
    }

    public function test_edit_comment(): void
    {
        $comment = TaskComment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => 'Original note',
        ]);

        $this->actingAs($this->user)
            ->putJson(route('tasks.comments.update', [$this->task, $comment]), [
                'comment' => 'Updated note',
            ])
            ->assertOk();

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'comment' => 'Updated note',
        ]);
    }

    public function test_delete_comment(): void
    {
        $comment = TaskComment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->deleteJson(route('tasks.comments.destroy', [$this->task, $comment]))
            ->assertOk();

        $this->assertDatabaseMissing('task_comments', ['id' => $comment->id]);
    }
}
