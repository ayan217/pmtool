<?php

namespace App\Http\Controllers;

use App\Enums\DeadlineType;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        return view('calendar.index');
    }

    public function events(Request $request): JsonResponse
    {
        $start = $request->date('start')?->startOfDay();
        $end = $request->date('end')?->endOfDay();

        $tasks = Task::query()
            ->with('project')
            ->notArchived()
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($inner) use ($start, $end) {
                    $inner->whereNotNull('dev_deadline');

                    if ($start) {
                        $inner->where('dev_deadline', '>=', $start);
                    }

                    if ($end) {
                        $inner->where('dev_deadline', '<=', $end);
                    }
                })->orWhere(function ($inner) use ($start, $end) {
                    $inner->whereNotNull('client_deadline');

                    if ($start) {
                        $inner->where('client_deadline', '>=', $start);
                    }

                    if ($end) {
                        $inner->where('client_deadline', '<=', $end);
                    }
                });
            })
            ->get();

        $events = [];

        foreach ($tasks as $task) {
            foreach (DeadlineType::cases() as $type) {
                $deadline = $task->deadlineFor($type);

                if ($deadline === null) {
                    continue;
                }

                if ($start && $deadline->lt($start)) {
                    continue;
                }

                if ($end && $deadline->gt($end)) {
                    continue;
                }

                $events[] = [
                    'id' => $task->id.'-'.$type->value,
                    'title' => $task->title,
                    'start' => $deadline->toIso8601String(),
                    'url' => route('tasks.show', $task),
                    'color' => $type === DeadlineType::Dev ? '#2457d6' : '#c05621',
                    'extendedProps' => [
                        'deadline_type' => $type->shortLabel(),
                        'project' => $task->project?->name,
                        'developer' => $task->developer,
                        'deadline_time' => $deadline->timezone(config('app.timezone'))->format('M j, Y, g:i A'),
                    ],
                ];
            }
        }

        return response()->json($events);
    }
}
