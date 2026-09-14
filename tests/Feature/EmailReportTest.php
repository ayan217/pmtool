<?php

namespace Tests\Feature;

use App\Enums\DeadlineType;
use App\Enums\EmailLogType;
use App\Mail\DeadlineReminderMail;
use App\Mail\StatusReminderMail;
use App\Models\EmailLog;
use App\Models\Task;
use App\Models\User;
use App\Services\DeadlineReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->user = User::factory()->create();
    }

    public function test_guests_cannot_view_email_report(): void
    {
        $this->get(route('email-report.index'))->assertRedirect(route('login'));
    }

    public function test_email_report_empty_state(): void
    {
        $this->actingAs($this->user)
            ->get(route('email-report.index'))
            ->assertOk()
            ->assertSee('Email Report')
            ->assertSee('No emails sent yet.');
    }

    public function test_status_reminder_appears_on_the_email_report(): void
    {
        $task = Task::factory()->create(['title' => 'API handover']);
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

        Mail::assertQueued(StatusReminderMail::class);
        $this->assertDatabaseHas('email_logs', [
            'type' => EmailLogType::StatusReminder->value,
            'task_id' => $task->id,
            'subject' => 'API handover',
        ]);

        $this->actingAs($this->user)
            ->get(route('email-report.index'))
            ->assertOk()
            ->assertSee('Status reminder')
            ->assertSee('API handover')
            ->assertSee('rahul@example.com');
    }

    public function test_deadline_reminder_appears_on_the_email_report(): void
    {
        $task = Task::factory()->create([
            'title' => 'Client landing page',
            'dev_deadline' => now()->addHours(2),
        ]);

        $this->assertTrue(app(DeadlineReminderService::class)->dispatchReminder($this->user, $task, DeadlineType::Dev, 3));

        Mail::assertQueued(DeadlineReminderMail::class);
        $this->assertSame(1, EmailLog::query()->count());

        $log = EmailLog::query()->first();
        $this->assertSame(EmailLogType::DeadlineReminder, $log->type);
        $this->assertSame([$this->user->email], $log->recipients);
        $this->assertSame('Deadline approaching: Client landing page', $log->subject);

        $this->actingAs($this->user)
            ->get(route('email-report.show', $log))
            ->assertOk()
            ->assertSee('Deadline approaching: Client landing page')
            ->assertSee($this->user->email)
            ->assertSee('Development Deadline');
    }

    public function test_email_report_can_filter_by_type(): void
    {
        $task = Task::factory()->create(['title' => 'API handover']);
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => null,
            ],
        ]);

        $this->actingAs($this->user)->post(route('tasks.reminders.store', $task), [
            'channel' => 'email',
        ]);

        Task::factory()->create([
            'title' => 'Client landing page',
            'dev_deadline' => now()->addHours(2),
        ]);
        app(DeadlineReminderService::class)->dispatchReminder(
            $this->user,
            Task::query()->where('title', 'Client landing page')->first(),
            DeadlineType::Dev,
            3,
        );

        $this->actingAs($this->user)
            ->get(route('email-report.index', ['type' => EmailLogType::StatusReminder->value]))
            ->assertOk()
            ->assertSee('API handover')
            ->assertDontSee('Client landing page');
    }
}
