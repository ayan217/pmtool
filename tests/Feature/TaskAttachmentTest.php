<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->user = User::factory()->create();
    }

    public function test_multiple_documents_can_be_attached_when_creating_a_task(): void
    {
        $this->actingAs($this->user)
            ->post('/tasks', $this->payload([
                'title' => 'Client handover pack',
                'attachments' => [
                    UploadedFile::fake()->create('brief.pdf', 120, 'application/pdf'),
                    UploadedFile::fake()->create('notes.txt', 20, 'text/plain'),
                ],
            ]))
            ->assertRedirect();

        $task = Task::query()->where('title', 'Client handover pack')->first();

        $this->assertNotNull($task);
        $this->assertCount(2, $task->attachments);
        $this->assertEqualsCanonicalizing(
            ['brief.pdf', 'notes.txt'],
            $task->attachments->pluck('original_name')->all()
        );

        foreach ($task->attachments as $attachment) {
            Storage::disk('local')->assertExists($attachment->path);
        }
    }

    public function test_documents_can_be_added_to_an_existing_task(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->user)
            ->post(route('tasks.attachments.store', $task), [
                'attachments' => [
                    UploadedFile::fake()->create('spec.docx', 80, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('task_attachments', [
            'task_id' => $task->id,
            'original_name' => 'spec.docx',
        ]);
    }

    public function test_updating_a_task_with_documents_does_not_delete_it(): void
    {
        $task = Task::factory()->create(['title' => 'Keep this task']);
        $task->storeAttachments([
            UploadedFile::fake()->create('brief.pdf', 20, 'application/pdf'),
        ]);
        $attachment = $task->attachments()->first();

        $html = $this->actingAs($this->user)
            ->get(route('tasks.edit', $task))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="taskForm"', $html);
        $this->assertStringContainsString('form="delete-attachment-'.$attachment->id.'"', $html);
        $this->assertSame(1, preg_match('/<form[^>]*id="taskForm".*?<\/form>/s', $html, $match));
        $this->assertStringNotContainsString('DELETE', $match[0]);

        $this->actingAs($this->user)
            ->put(route('tasks.update', $task), [
                'title' => 'Still here',
                'priority' => TaskPriority::Medium->value,
                'status' => TaskStatus::Pending->value,
                'description' => 'Updated with a document already attached.',
            ])
            ->assertRedirect(route('tasks.show', $task))
            ->assertSessionHas('success', 'Task updated successfully.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Still here',
        ]);
        $this->assertDatabaseCount('task_attachments', 1);
    }

    public function test_an_attached_document_can_be_downloaded(): void
    {
        $task = Task::factory()->create();
        $file = UploadedFile::fake()->create('invoice.pdf', 40, 'application/pdf');
        $task->storeAttachments([$file]);
        $attachment = $task->attachments()->first();

        $this->actingAs($this->user)
            ->get(route('tasks.attachments.download', [$task, $attachment]))
            ->assertOk()
            ->assertDownload('invoice.pdf');
    }

    public function test_pdf_image_and_video_can_be_viewed_in_the_app(): void
    {
        $task = Task::factory()->create();
        $task->storeAttachments([
            UploadedFile::fake()->create('brief.pdf', 40, 'application/pdf'),
            UploadedFile::fake()->image('photo.jpg', 20, 20),
            UploadedFile::fake()->create('clip.mp4', 50, 'video/mp4'),
        ]);

        $pdf = $task->attachments()->where('original_name', 'brief.pdf')->first();
        $image = $task->attachments()->where('original_name', 'photo.jpg')->first();
        $video = $task->attachments()->where('original_name', 'clip.mp4')->first();

        $this->assertTrue($pdf->isPdf());
        $this->assertTrue($image->isImage());
        $this->assertTrue($video->isVideo());

        $this->actingAs($this->user)
            ->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee(route('tasks.attachments.show', [$task, $pdf]), false)
            ->assertSee(route('tasks.attachments.download', [$task, $pdf]), false);

        $this->actingAs($this->user)
            ->get(route('tasks.attachments.show', [$task, $pdf]))
            ->assertOk()
            ->assertSee('brief.pdf')
            ->assertSee('Download')
            ->assertSee('<iframe', false)
            ->assertSee('attachment-pdf-shell', false)
            ->assertSee(route('tasks.attachments.preview', [$task, $pdf]).'#view=FitH', false);

        $this->actingAs($this->user)
            ->get(route('tasks.attachments.show', [$task, $image]))
            ->assertOk()
            ->assertSee('<img', false)
            ->assertSee(route('tasks.attachments.preview', [$task, $image]), false);

        $this->actingAs($this->user)
            ->get(route('tasks.attachments.show', [$task, $video]))
            ->assertOk()
            ->assertSee('<video', false)
            ->assertSee(route('tasks.attachments.preview', [$task, $video]), false);
    }

    public function test_viewer_can_move_to_the_next_and_previous_attachment(): void
    {
        $task = Task::factory()->create();
        $task->storeAttachments([
            UploadedFile::fake()->create('brief.pdf', 40, 'application/pdf'),
            UploadedFile::fake()->image('photo.jpg', 20, 20),
            UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
        ]);

        [$first, $second, $third] = $task->fresh()->attachments->all();

        $this->actingAs($this->user)
            ->get(route('tasks.attachments.show', [$task, $first]))
            ->assertOk()
            ->assertSee('1 of 3', false)
            ->assertSee('Previous')
            ->assertSee('Next')
            ->assertSee(route('tasks.attachments.show', [$task, $second]), false)
            ->assertSee(route('tasks.attachments.show', [$task, $third]), false);

        $this->actingAs($this->user)
            ->get(route('tasks.attachments.show', [$task, $second]))
            ->assertOk()
            ->assertSee('2 of 3', false)
            ->assertSee(route('tasks.attachments.show', [$task, $first]), false)
            ->assertSee(route('tasks.attachments.show', [$task, $third]), false);
    }

    public function test_viewer_hides_navigation_when_there_is_only_one_attachment(): void
    {
        $task = Task::factory()->create();
        $task->storeAttachments([
            UploadedFile::fake()->create('brief.pdf', 40, 'application/pdf'),
        ]);
        $attachment = $task->attachments()->first();

        $this->actingAs($this->user)
            ->get(route('tasks.attachments.show', [$task, $attachment]))
            ->assertOk()
            ->assertDontSee('Previous')
            ->assertDontSee('Next')
            ->assertDontSee('1 of 1', false);
    }

    public function test_preview_streams_the_file_inline(): void
    {
        $task = Task::factory()->create();
        $task->storeAttachments([
            UploadedFile::fake()->create('brief.pdf', 40, 'application/pdf'),
        ]);
        $attachment = $task->attachments()->first();

        $response = $this->actingAs($this->user)
            ->get(route('tasks.attachments.preview', [$task, $attachment]))
            ->assertOk();

        $this->assertStringContainsString('inline', strtolower((string) $response->headers->get('content-disposition')));
    }

    public function test_guests_cannot_view_or_preview_documents(): void
    {
        $task = Task::factory()->create();
        $task->storeAttachments([
            UploadedFile::fake()->create('secret.pdf', 10, 'application/pdf'),
        ]);
        $attachment = $task->attachments()->first();

        $this->get(route('tasks.attachments.show', [$task, $attachment]))
            ->assertRedirect(route('login'));

        $this->get(route('tasks.attachments.preview', [$task, $attachment]))
            ->assertRedirect(route('login'));
    }

    public function test_missing_document_files_redirect_instead_of_downloading(): void
    {
        $task = Task::factory()->create();
        $task->storeAttachments([
            UploadedFile::fake()->create('lost.pdf', 10, 'application/pdf'),
        ]);
        $attachment = $task->attachments()->first();
        Storage::disk('local')->delete($attachment->path);

        $this->actingAs($this->user)
            ->from(route('tasks.show', $task))
            ->get(route('tasks.attachments.download', [$task, $attachment]))
            ->assertRedirect(route('tasks.show', $task))
            ->assertSessionHas('error');
    }

    public function test_guests_cannot_download_documents(): void
    {
        $task = Task::factory()->create();
        $task->storeAttachments([
            UploadedFile::fake()->create('secret.pdf', 10, 'application/pdf'),
        ]);
        $attachment = $task->attachments()->first();

        $this->get(route('tasks.attachments.download', [$task, $attachment]))
            ->assertRedirect(route('login'));
    }

    public function test_an_attached_document_can_be_removed(): void
    {
        $task = Task::factory()->create();
        $task->storeAttachments([
            UploadedFile::fake()->create('old.pdf', 15, 'application/pdf'),
        ]);
        $attachment = $task->attachments()->first();
        $path = $attachment->path;

        $this->actingAs($this->user)
            ->delete(route('tasks.attachments.destroy', [$task, $attachment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('task_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_deleting_a_task_removes_its_documents(): void
    {
        $task = Task::factory()->archived()->create();
        $task->storeAttachments([
            UploadedFile::fake()->create('keep-me.pdf', 15, 'application/pdf'),
        ]);
        $attachment = $task->attachments()->first();
        $path = $attachment->path;

        $this->actingAs($this->user)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseMissing('task_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_executable_files_cannot_be_attached(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->user)
            ->from(route('tasks.show', $task))
            ->post(route('tasks.attachments.store', $task), [
                'attachments' => [
                    UploadedFile::fake()->create('shell.php', 10, 'text/plain'),
                ],
            ])
            ->assertRedirect(route('tasks.show', $task))
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('task_attachments', 0);
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
