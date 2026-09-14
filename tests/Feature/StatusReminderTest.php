<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Mail\StatusReminderMail;
use App\Models\Task;
use App\Models\User;
use App\Services\StatusReminderTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StatusReminderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

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
    }
}
