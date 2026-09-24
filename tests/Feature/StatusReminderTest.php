<?php

namespace Tests\Feature;

use App\Mail\StatusReminderMail;
use App\Models\EmailLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\StatusReminderTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StatusReminderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Mail::fake();
        $this->user = User::factory()->create();
    }

    public function test_email_template_can_be_updated(): void
    {
        $this->actingAs($this->user)
            ->put(route('email-templates.update'), [
                'subject' => 'Status: {task.title}',
                'body' => 'Please update {task.des} in {remaining.hours}.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $template = app(StatusReminderTemplateService::class)->forUser($this->user);

        $this->assertSame('Status: {task.title}', $template['subject']);
        $this->assertSame('Please update {task.des} in {remaining.hours}.', $template['body']);
    }

    public function test_email_reminder_is_queued_to_task_developers(): void
    {
        $task = Task::factory()->create([
            'title' => 'API handover',
            'description' => 'Finish the remaining endpoints.',
            'dev_deadline' => now()->addHours(5),
        ]);
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => null,
            ],
        ]);

        $this->actingAs($this->user)
            ->post(route('tasks.reminders.store', $task), [
                'channel' => 'email',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertQueued(StatusReminderMail::class, function (StatusReminderMail $mail) use ($task) {
            return $mail->task->is($task)
                && $mail->subjectLine === 'API handover'
                && str_contains($mail->bodyText, 'Finish the remaining endpoints.')
                && str_contains($mail->bodyText, '5 hours')
                && $mail->hasTo('rahul@example.com');
        });

        $this->assertDatabaseHas('email_logs', [
            'type' => 'status_reminder',
            'task_id' => $task->id,
            'subject' => 'API handover',
        ]);
        $this->assertSame(['rahul@example.com'], EmailLog::query()->first()->recipients);
    }

    public function test_email_reminder_includes_task_title_and_project_name(): void
    {
        $project = Project::factory()->create(['name' => 'CRM Tool']);
        $task = Task::factory()->create([
            'title' => 'API handover',
            'project_id' => $project->id,
        ]);
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => null,
            ],
        ]);

        $this->actingAs($this->user)
            ->post(route('tasks.reminders.store', $task), [
                'channel' => 'email',
            ])
            ->assertRedirect();

        Mail::assertQueued(StatusReminderMail::class, function (StatusReminderMail $mail) {
            $html = $mail->render();

            return str_contains($html, 'API handover')
                && str_contains($html, 'CRM Tool')
                && str_contains($html, 'Task:')
                && str_contains($html, 'Project:');
        });
    }

    public function test_email_reminder_attaches_task_documents(): void
    {
        $task = Task::factory()->create(['title' => 'Handover pack']);
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => null,
            ],
        ]);
        $task->storeAttachments([
            UploadedFile::fake()->create('brief.pdf', 40, 'application/pdf'),
            UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
        ]);

        $this->actingAs($this->user)
            ->post(route('tasks.reminders.store', $task), [
                'channel' => 'email',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $files = $task->fresh()->attachments;

        Mail::assertQueued(StatusReminderMail::class, function (StatusReminderMail $mail) use ($files) {
            return $files->every(fn ($file) => $mail->hasAttachment(
                Attachment::fromStorageDisk($file->disk, $file->path)
                    ->as($file->original_name)
                    ->withMime($file->mime_type),
            ));
        });
    }

    public function test_email_reminder_skips_documents_that_are_missing_from_disk(): void
    {
        $task = Task::factory()->create();
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => null,
            ],
        ]);
        $task->storeAttachments([
            UploadedFile::fake()->create('brief.pdf', 40, 'application/pdf'),
        ]);

        $missing = $task->attachments()->first();
        Storage::disk('local')->delete($missing->path);

        $this->actingAs($this->user)
            ->post(route('tasks.reminders.store', $task), [
                'channel' => 'email',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertQueued(StatusReminderMail::class, function (StatusReminderMail $mail) use ($missing) {
            return $mail->attachments() === []
                && ! $mail->hasAttachment(
                    Attachment::fromStorageDisk($missing->disk, $missing->path)
                        ->as($missing->original_name)
                        ->withMime($missing->mime_type),
                );
        });
    }

    public function test_email_reminder_is_not_sent_when_task_is_on_client_review(): void
    {
        $task = Task::factory()->onClientReview()->create();
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => null,
            ],
        ]);

        $this->actingAs($this->user)
            ->from(route('tasks.show', $task))
            ->post(route('tasks.reminders.store', $task), [
                'channel' => 'email',
            ])
            ->assertRedirect(route('tasks.show', $task))
            ->assertSessionHas('error', 'Reminders are paused while this task is on client review.');

        Mail::assertNothingQueued();
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_email_reminder_requires_a_developer_email(): void
    {
        $task = Task::factory()->create([
            'developer' => null,
        ]);

        $this->actingAs($this->user)
            ->from(route('tasks.show', $task))
            ->post(route('tasks.reminders.store', $task), [
                'channel' => 'email',
            ])
            ->assertRedirect(route('tasks.show', $task))
            ->assertSessionHas('error');

        Mail::assertNothingQueued();
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_whatsapp_reminder_is_not_sent_yet(): void
    {
        $task = Task::factory()->create();
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => '+919876543210',
            ],
        ]);

        $this->actingAs($this->user)
            ->from(route('tasks.show', $task))
            ->post(route('tasks.reminders.store', $task), [
                'channel' => 'whatsapp',
            ])
            ->assertRedirect(route('tasks.show', $task))
            ->assertSessionHas('error');

        Mail::assertNothingQueued();
        $this->assertDatabaseCount('email_logs', 0);
    }
}
