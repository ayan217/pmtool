<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Task $task): JsonResponse|RedirectResponse
    {
        $this->authorize('view', $task);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $request->validated('comment'),
        ]);

        $comment->load('user');

        $task->touch();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Comment added.',
                'comment' => $this->present($comment),
            ]);
        }

        return back()->with('success', 'Comment added.');
    }

    public function update(UpdateCommentRequest $request, Task $task, TaskComment $comment): JsonResponse|RedirectResponse
    {
        abort_unless($comment->task_id === $task->id, 404);

        $this->authorize('update', $comment);

        $comment->update($request->validated());
        $comment->load('user');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Comment updated.',
                'comment' => $this->present($comment),
            ]);
        }

        return back()->with('success', 'Comment updated.');
    }

    public function destroy(Task $task, TaskComment $comment): JsonResponse|RedirectResponse
    {
        abort_unless($comment->task_id === $task->id, 404);

        $this->authorize('delete', $comment);

        $comment->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Comment deleted.']);
        }

        return back()->with('success', 'Comment deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(TaskComment $comment): array
    {
        return [
            'id' => $comment->id,
            'comment' => $comment->comment,
            'author' => $comment->user?->name ?? 'Unknown',
            'created_at' => $comment->created_at?->timezone(config('app.timezone'))->format('M j, Y, g:i A'),
            'updated_at' => $comment->updated_at?->timezone(config('app.timezone'))->format('M j, Y, g:i A'),
            'update_url' => route('tasks.comments.update', [$comment->task_id, $comment]),
            'delete_url' => route('tasks.comments.destroy', [$comment->task_id, $comment]),
        ];
    }
}
