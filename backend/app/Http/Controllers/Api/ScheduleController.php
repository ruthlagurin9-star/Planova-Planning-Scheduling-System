<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    // View schedules belonging to the logged-in user
   
public function index(Request $request)
{
    $query = Schedule::with(['category', 'tasks'])
        ->where('user_id', $request->user()->id);

    // Search by schedule title or description
    if ($request->filled('search')) {
        $search = $request->input('search');

        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Filter by status
    if ($request->filled('status')) {
        $request->validate([
            'status' => ['in:pending,in_progress,completed'],
        ]);

        $query->where('status', $request->input('status'));
    }

    // Filter by category
    if ($request->filled('category_id')) {
        $request->validate([
            'category_id' => ['integer', 'exists:categories,id'],
        ]);

        $query->where('category_id', $request->input('category_id'));
    }

    return response()->json($query->latest()->paginate(10));
}


    // Create a schedule
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'status' => ['sometimes', 'in:pending,in_progress,completed'],
        ]);

        $schedule = $request->user()->schedules()->create($validated);

        return response()->json([
            'message' => 'Schedule created successfully.',
            'data' => $schedule->load(['category', 'tasks']),
        ], 201);
    }

    // View one schedule owned by the logged-in user
    public function show(Request $request, Schedule $schedule)
    {
        if ($schedule->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'data' => $schedule->load(['category', 'tasks']),
        ]);
    }

    // Update a schedule
    public function update(Request $request, Schedule $schedule)
    {
        if ($schedule->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'status' => ['sometimes', 'in:pending,in_progress,completed'],
        ]);

        $schedule->update($validated);

        return response()->json([
            'message' => 'Schedule updated successfully.',
            'data' => $schedule->fresh()->load(['category', 'tasks']),
        ]);
    }

    // Delete a schedule
    public function destroy(Request $request, Schedule $schedule)
    {
        if ($schedule->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully.',
        ]);
    }
}