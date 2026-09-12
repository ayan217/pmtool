<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskAttachmentRequest;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskAttachmentController extends Controller
{
    public function store(StoreTaskAttachmentRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->storeAttachments($request->file('attachments', []));
        $task->touch();

        return back()->with('success', 'Documents attached.');
    }

    public function download(Task $task, TaskAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->task_id === $task->id, 404);

        $this->authorize('view', $task);

        return $attachment->download();
    }

    public function destroy(Task $task, TaskAttachment $attachment): RedirectResponse
    {
        abort_unless($attachment->task_id === $task->id, 404);

        $this->authorize('update', $task);

        $attachment->delete();
        $task->touch();

        return back()->with('success', 'Document removed.');
    }
}
