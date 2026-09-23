<?php

namespace App\Services;

use App\Enums\EmailLogType;
use App\Mail\StatusReminderMail;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class StatusReminderSender
{
    public function __construct(
        protected StatusReminderTemplateService $templates,
        protected EmailSettingsService $emailSettings,
        protected EmailLogService $emailLogs,
    ) {}

    /**
     * @return list<string>
     */
    public function queue(Task $task, User $user, EmailLogType $type = EmailLogType::StatusReminder): array
    {
        $emails = $task->developerEmails();

        if ($emails === []) {
            return [];
        }

        $message = $this->templates->render($user, $task);

        Mail::to($emails)->queue(new StatusReminderMail(
            $task->loadMissing(['project', 'developers', 'attachments']),
            $message['subject'],
            $message['body'],
            $this->emailSettings->fromName($user),
        ));

        $this->emailLogs->record(
            $type,
            $emails,
            $message['subject'],
            $message['body'],
            $task,
            $user,
        );

        return $emails;
    }
}
