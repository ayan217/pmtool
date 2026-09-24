<?php

namespace Tests\Feature;

use App\Enums\EmailLogType;
use App\Mail\StatusReminderMail;
use App\Models\DailyReminderNotification;
use App\Models\EmailLog;
use App\Models\Task;
use App\Models\User;
use App\Services\DailyReminderService;
use App\Services\EmailSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DailyReminderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.timezone' => 'Asia/Kolkata']);
        Mail::fake();
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_daily_reminders_do_not_send_before_the_configured_time(): void
    {
        $this->openTask();
        Carbon::setTestNow(Carbon::parse('2026-09-23 17:59:00', 'Asia/Kolkata'));

        $result = $this->reminders()->sendDueReminders();

        $this->assertSame(0, $result['sent']);
        Mail::assertNothingQueued();
    }

    public function test_daily_reminders_send_at_six_pm_ist_by_default(): void
    {
        $task = $this->openTask();
        Carbon::setTestNow(Carbon::parse('2026-09-23 18:05:00', 'Asia/Kolkata'));

        $result = $this->reminders()->sendDueReminders();

        $this->assertSame(1, $result['sent']);
        Mail::assertQueued(StatusReminderMail::class, function (StatusReminderMail $mail) use ($task) {
            return $mail->task->is($task) && $mail->hasTo('rahul@example.com');
        });
        $this->assertDatabaseHas('email_logs', [
            'type' => EmailLogType::DailyReminder->value,
            'task_id' => $task->id,
        ]);
        $this->assertSame(['rahul@example.com'], EmailLog::query()->first()->recipients);
        $this->assertSame(1, DailyReminderNotification::query()->count());
    }

    public function test_daily_reminder_time_can_be_changed_in_email_settings(): void
    {
        app(EmailSettingsService::class)->update($this->user, 'Personal PM', $this->user->email, '20:00');
        $this->openTask();

        Carbon::setTestNow(Carbon::parse('2026-09-23 19:59:00', 'Asia/Kolkata'));
        $this->assertSame(0, $this->reminders()->sendDueReminders()['sent']);
        Mail::assertNothingQueued();

        Carbon::setTestNow(Carbon::parse('2026-09-23 20:00:00', 'Asia/Kolkata'));
        $this->assertSame(1, $this->reminders()->sendDueReminders()['sent']);
        Mail::assertQueued(StatusReminderMail::class, 1);
    }

    public function test_daily_reminders_are_not_sent_twice_on_the_same_day(): void
    {
        $this->openTask();
        Carbon::setTestNow(Carbon::parse('2026-09-23 18:05:00', 'Asia/Kolkata'));

        $this->assertSame(1, $this->reminders()->sendDueReminders()['sent']);
        $this->assertSame(0, $this->reminders()->sendDueReminders()['sent']);
        Mail::assertQueued(StatusReminderMail::class, 1);
    }

    public function test_completed_tasks_do_not_get_daily_reminders(): void
    {
        $task = Task::factory()->completed()->create();
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => null,
            ],
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-23 18:05:00', 'Asia/Kolkata'));

        $this->assertSame(0, $this->reminders()->sendDueReminders()['sent']);
        Mail::assertNothingQueued();
    }

    public function test_on_client_review_tasks_do_not_get_daily_reminders(): void
    {
        $task = Task::factory()->onClientReview()->create();
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => null,
            ],
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-23 18:05:00', 'Asia/Kolkata'));

        $this->assertSame(0, $this->reminders()->sendDueReminders()['sent']);
        Mail::assertNothingQueued();
        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_command_sends_daily_reminders_after_six_pm(): void
    {
        $this->openTask();
        Carbon::setTestNow(Carbon::parse('2026-09-23 18:10:00', 'Asia/Kolkata'));

        $this->artisan('deadlines:send-reminders')->assertSuccessful();

        Mail::assertQueued(StatusReminderMail::class, 1);
    }

    protected function openTask(): Task
    {
        $task = Task::factory()->create(['title' => 'Evening status']);
        $task->syncDevelopers([
            [
                'name' => 'Rahul',
                'email' => 'rahul@example.com',
                'phone' => null,
            ],
        ]);

        return $task;
    }

    protected function reminders(): DailyReminderService
    {
        return app(DailyReminderService::class);
    }
}
