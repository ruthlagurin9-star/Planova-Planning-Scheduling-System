<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Schedule;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Display tasks belonging to the logged-in user's schedules
    
public function index(Request $request)
{
    $query = Task::with(['schedule', 'assignee'])
        ->whereHas('schedule', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        });

    if ($request->filled('search')) {
        $search = $request->input('search');

        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    if ($request->filled('status')) {
        $request->validate([
            'status' => ['in:pending,in_progress,completed'],
        ]);

        $query->where('status', $request->input('status'));
    }

    if ($request->filled('priority')) {
        $request->validate([
            'priority' => ['in:low,medium,high'],
        ]);

        $query->where('priority', $request->input('priority'));
    }

    return response()->json($query->latest()->paginate(10));
}


    // Create a task under one of the user's schedules
    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'integer', 'exists:schedules,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['sometimes', 'in:pending,in_progress,completed'],
        ]);

        $schedule = Schedule::where('id', $validated['schedule_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$schedule) {
            return response()->json([
                'message' => 'You can only add tasks to your own schedules.'
            ], 403);
        }

        $task = $schedule->tasks()->create($validated);

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $task->load(['schedule', 'assignee']),
        ], 201);
    }

    // Display one task
    public function show(Request $request, Task $task)
    {
        if ($task->schedule->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'data' => $task->load(['schedule', 'assignee']),
        ]);
    }

    // Update a task
    public function update(Request $request, Task $task)
    {
        if ($task->schedule->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['sometimes', 'in:pending,in_progress,completed'],
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => $task->fresh()->load(['schedule', 'assignee']),
        ]);
    }

    // Delete a task
    public function destroy(Request $request, Task $task)
    {
        if ($task->schedule->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }
}
