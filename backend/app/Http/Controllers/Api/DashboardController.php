<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $scheduleQuery = Schedule::where('user_id', $userId);

        $taskQuery = Task::whereHas('schedule', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });

        return response()->json([
            'total_schedules' => (clone $scheduleQuery)->count(),
            'pending_schedules' => (clone $scheduleQuery)
                ->where('status', 'pending')->count(),
            'total_tasks' => (clone $taskQuery)->count(),
            'pending_tasks' => (clone $taskQuery)
                ->where('status', 'pending')->count(),
            'completed_tasks' => (clone $taskQuery)
                ->where('status', 'completed')->count(),
        ]);
    }
}
