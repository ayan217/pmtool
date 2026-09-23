<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendTaskReminderRequest;
use App\Models\Task;
use App\Services\StatusReminderSender;
use Illuminate\Http\RedirectResponse;

class TaskReminderController extends Controller
{
    public function store(
        SendTaskReminderRequest $request,
        Task $task,
        StatusReminderSender $sender,
    ): RedirectResponse {
        $this->authorize('remind', $task);

        $channel = $request->validated('channel');

        if ($channel !== 'email') {
            return back()->with('error', 'WhatsApp reminders are not available yet.');
        }

        $emails = $sender->queue($task, $request->user());

        if ($emails === []) {
            return back()->with('error', 'This task has no developer email to send to.');
        }

        return back()->with('success', 'Email reminder queued for '.implode(', ', $emails).'.');
    }
}
