<?php

namespace App\Http\Controllers;

use App\Enums\EmailLogType;
use App\Models\EmailLog;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('create', Task::class);

        $perPage = (int) $request->input('per_page', config('pm.default_per_page'));

        if (! in_array($perPage, config('pm.per_page_options'), true)) {
            $perPage = (int) config('pm.default_per_page');
        }

        $type = EmailLogType::tryFrom((string) $request->input('type', ''));
        $search = trim((string) $request->input('q', ''));

        $logs = EmailLog::query()
            ->with(['task.project'])
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('subject', 'like', $like)
                        ->orWhere('body', 'like', $like)
                        ->orWhere('recipients', 'like', $like)
                        ->orWhereHas('task', fn ($task) => $task->where('title', 'like', $like));
                });
            })
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('email-report.index', [
            'logs' => $logs,
            'type' => $type?->value,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    public function show(EmailLog $emailLog): View
    {
        $this->authorize('create', Task::class);

        $emailLog->load(['task.project', 'user']);

        return view('email-report.show', [
            'log' => $emailLog,
        ]);
    }
}
