<?php

namespace Tests\Feature;

use App\Enums\DeadlineType;
use App\Enums\TaskStatus;
use App\Mail\DeadlineReminderMail;
use App\Models\DeadlineNotification;
use App\Models\EmailLog;
use App\Models\Task;
use App\Models\User;
use App\Services\DeadlineReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeadlineReminderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->user = User::factory()->create();
        Mail::fake();
    }

    public function test_dev_deadline_detection(): void
    {
        $task = Task::factory()->create([
            'dev_deadline' => now()->addHours(2),
            'client_deadline' => now()->addDays(2),
        ]);

        $this->assertFalse($task->isDevOverdue());
        $this->assertTrue($this->reminders()->dispatchReminder($this->user, $task, DeadlineType::Dev, 3));

        Mail::assertQueued(DeadlineReminderMail::class, function (DeadlineReminderMail $mail) use ($task) {
            return $mail->task->is($task) && $mail->deadlineType === DeadlineType::Dev;
        });
    }

    public function test_client_deadline_detection(): void
    {
        $task = Task::factory()->create([
            'client_deadline' => now()->addHours(2),
        ]);

        $this->assertTrue($this->reminders()->dispatchReminder($this->user, $task, DeadlineType::Client, 3));

        Mail::assertQueued(DeadlineReminderMail::class, function (DeadlineReminderMail $mail) {
            return $mail->deadlineType === DeadlineType::Client;
        });
    }

    public function test_completed_tasks_do_not_generate_reminders(): void
    {
        $task = Task::factory()->completed()->create([
            'dev_deadline' => now()->addHours(2),
            'status' => TaskStatus::Completed,
        ]);

        $this->assertFalse($this->reminders()->dispatchReminder($this->user, $task, DeadlineType::Dev, 3));
        Mail::assertNothingQueued();
    }

    public function test_duplicate_reminders_are_not_generated(): void
    {
        $task = Task::factory()->create([
            'dev_deadline' => now()->addHours(2),
        ]);

        $this->assertTrue($this->reminders()->dispatchReminder($this->user, $task, DeadlineType::Dev, 3));
        $this->assertFalse($this->reminders()->dispatchReminder($this->user, $task, DeadlineType::Dev, 3));

        Mail::assertQueued(DeadlineReminderMail::class, 1);
        $this->assertSame(1, DeadlineNotification::query()->count());
        $this->assertSame(1, EmailLog::query()->count());
    }

    public function test_deadline_reminders_attach_task_documents(): void
    {
        $task = Task::factory()->create([
            'dev_deadline' => now()->addHours(2),
        ]);
        $task->storeAttachments([
            UploadedFile::fake()->create('brief.pdf', 40, 'application/pdf'),
        ]);
        $file = $task->attachments()->first();

        $this->assertTrue($this->reminders()->dispatchReminder($this->user, $task, DeadlineType::Dev, 3));

        Mail::assertQueued(DeadlineReminderMail::class, function (DeadlineReminderMail $mail) use ($file) {
            return $mail->hasAttachment(
                Attachment::fromStorageDisk($file->disk, $file->path)
                    ->as($file->original_name)
                    ->withMime($file->mime_type),
            );
        });
    }

    public function test_command_sends_due_reminders(): void
    {
        Task::factory()->create(['dev_deadline' => now()->addHours(2)]);
        Task::factory()->create(['dev_deadline' => now()->addDays(2)]);

        $this->artisan('deadlines:send-reminders')->assertSuccessful();

        Mail::assertQueued(DeadlineReminderMail::class, 1);
    }

    protected function reminders(): DeadlineReminderService
    {
        return app(DeadlineReminderService::class);
    }
}
