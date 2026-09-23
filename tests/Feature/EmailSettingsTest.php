<?php

namespace Tests\Feature;

use App\Enums\DeadlineType;
use App\Mail\DeadlineReminderMail;
use App\Mail\StatusReminderMail;
use App\Models\EmailLog;
use App\Models\Task;
use App\Models\User;
use App\Services\DeadlineReminderService;
use App\Services\EmailSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->user = User::factory()->create([
            'name' => 'Ayan',
            'email' => 'login@example.com',
        ]);
    }

    public function test_guests_cannot_view_email_settings(): void
    {
        $this->get(route('email-settings.edit'))->assertRedirect(route('login'));
    }

    public function test_email_settings_form_defaults_to_account_name_and_login_email(): void
    {
        $this->actingAs($this->user)
            ->get(route('email-settings.edit'))
            ->assertOk()
            ->assertSee('Email Settings')
            ->assertSee('From name')
            ->assertSee('Admin email address')
            ->assertSee('Daily reminder time')
            ->assertSee('value="Ayan"', false)
            ->assertSee('value="login@example.com"', false)
            ->assertSee('value="18:00"', false);
    }

    public function test_email_settings_can_be_saved_independently_of_login_email(): void
    {
        $this->actingAs($this->user)
            ->put(route('email-settings.update'), [
                'from_name' => 'Personal PM',
                'admin_email' => 'reports@example.com',
                'daily_reminder_time' => '19:30',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $settings = app(EmailSettingsService::class)->forUser($this->user->fresh());

        $this->assertSame('Personal PM', $settings['from_name']);
        $this->assertSame('reports@example.com', $settings['admin_email']);
        $this->assertSame('19:30', $settings['daily_reminder_time']);
        $this->assertSame('login@example.com', $this->user->fresh()->email);
    }

    public function test_deadline_reminders_go_to_the_admin_email_with_the_from_name(): void
    {
        app(EmailSettingsService::class)->update($this->user, 'Personal PM', 'reports@example.com');

        $task = Task::factory()->create([
            'title' => 'Client landing page',
            'dev_deadline' => now()->addHours(2),
        ]);

        $this->assertTrue(app(DeadlineReminderService::class)->dispatchReminder(
            $this->user,
            $task,
            DeadlineType::Dev,
            3,
        ));

        Mail::assertQueued(DeadlineReminderMail::class, function (DeadlineReminderMail $mail) use ($task) {
            return $mail->task->is($task)
                && $mail->fromName === 'Personal PM'
                && $mail->hasTo('reports@example.com')
                && ! $mail->hasTo('login@example.com')
                && $mail->hasFrom(config('mail.from.address'), 'Personal PM');
        });

        $this->assertSame(['reports@example.com'], EmailLog::query()->first()->recipients);
    }

    public function test_status_reminders_use_the_from_name_and_still_go_to_developers(): void
    {
        app(EmailSettingsService::class)->update($this->user, 'Personal PM', 'reports@example.com');

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

        Mail::assertQueued(StatusReminderMail::class, function (StatusReminderMail $mail) {
            return $mail->fromName === 'Personal PM'
                && $mail->hasTo('rahul@example.com')
                && ! $mail->hasTo('reports@example.com')
                && $mail->hasFrom(config('mail.from.address'), 'Personal PM');
        });
    }

    public function test_email_settings_require_a_valid_admin_email(): void
    {
        $this->actingAs($this->user)
            ->from(route('email-settings.edit'))
            ->put(route('email-settings.update'), [
                'from_name' => 'Personal PM',
                'admin_email' => 'not-an-email',
                'daily_reminder_time' => '18:00',
            ])
            ->assertRedirect(route('email-settings.edit'))
            ->assertSessionHasErrors('admin_email');
    }
}
