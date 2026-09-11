<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboard): View
    {
        return view('dashboard.index', [
            'stats' => $dashboard->stats(),
            'overdueTasks' => $dashboard->overdueTasks(),
            'todayDeadlines' => $dashboard->todayDeadlines(),
            'upcomingDeadlines' => $dashboard->upcomingDeadlines(),
            'recentlyUpdated' => $dashboard->recentlyUpdated(),
            'recentlyCompleted' => $dashboard->recentlyCompleted(),
        ]);
    }
}
