<?php

namespace App\Http\Controllers;

use App\Enums\EmailLogType;
use App\Http\Requests\SendTaskReminderRequest;
use App\Mail\StatusReminderMail;
use App\Models\Task;
use App\Services\EmailLogService;
use App\Services\StatusReminderTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class TaskReminderController extends Controller
{
    public function store(
        SendTaskReminderRequest $request,
        Task $task,
        StatusReminderTemplateService $templates,
        EmailLogService $emailLogs,
    ): RedirectResponse {
        $this->authorize('remind', $task);

        $channel = $request->validated('channel');

        if ($channel !== 'email') {
            return back()->with('error', 'WhatsApp reminders are not available yet.');
        }

        $emails = $task->developerEmails();

        if ($emails === []) {
            return back()->with('error', 'This task has no developer email to send to.');
        }

        $message = $templates->render($request->user(), $task);

        Mail::to($emails)->queue(new StatusReminderMail(
            $task->loadMissing(['project', 'developers']),
            $message['subject'],
            $message['body'],
        ));

        $emailLogs->record(
            EmailLogType::StatusReminder,
            $emails,
            $message['subject'],
            $message['body'],
            $task,
            $request->user(),
        );

        return back()->with('success', 'Email reminder queued for '.implode(', ', $emails).'.');
    }
}
