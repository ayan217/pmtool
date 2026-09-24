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

    public function test_email_template_defaults_include_task_title_and_project_name(): void
    {
        $template = app(StatusReminderTemplateService::class)->forUser($this->user);

        $this->assertStringContainsString('{task.title}', $template['body']);
        $this->assertStringContainsString('{project.name}', $template['body']);

        $this->actingAs($this->user)
            ->get(route('email-templates.edit'))
            ->assertOk()
            ->assertSee('Task: {task.title}', false)
            ->assertSee('Project: {project.name}', false);
    }

    public function test_saved_legacy_template_is_upgraded_to_include_title_and_project(): void
    {
        app(StatusReminderTemplateService::class)->update(
            $this->user,
            '{task.title}',
            "hi team,\n\nplease share the status of this task, the deadline is in {remaining.hours}, if any delay is happening please contact me personally over call or whatsapp.\n\n{task.des}",
        );

        $template = app(StatusReminderTemplateService::class)->forUser($this->user->fresh());

        $this->assertStringContainsString('Task: {task.title}', $template['body']);
        $this->assertStringContainsString('Project: {project.name}', $template['body']);
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

            return str_contains($mail->bodyText, 'Task: API handover')
                && str_contains($mail->bodyText, 'Project: CRM Tool')
                && str_contains($html, 'Task: API handover')
                && str_contains($html, 'Project: CRM Tool');
        });

        $log = EmailLog::query()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Task: API handover', $log->body);
        $this->assertStringContainsString('Project: CRM Tool', $log->body);

        $this->actingAs($this->user)
            ->get(route('email-report.show', $log))
            ->assertOk()
            ->assertSee('Task: API handover', false)
            ->assertSee('Project: CRM Tool', false);
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
