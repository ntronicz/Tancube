<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use App\Notifications\TaskCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Get paginated tasks
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Task::query();

        // Apply organization scope (unless super admin)
        if (!$user->isSuperAdmin()) {
            $query->where('organization_id', $user->organization_id);
        }

        // Role-based visibility
        if ($user->isAgent()) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('due_date') && $request->due_date) {
            $query->whereDate('due_date', $request->due_date);
        }

        // Server-side pagination
        $perPage = min($request->input('limit', 50), 100);
        $tasks = $query->with(['assignedTo:id,name', 'createdBy:id,name'])
            ->orderByRaw("FIELD(priority, 'HIGH', 'MEDIUM', 'LOW')")
            ->orderBy('due_date')
            ->paginate($perPage);

        return response()->json($tasks);
    }

    /**
     * Store a new task
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'nullable|in:LOW,MEDIUM,HIGH',
            'assigned_to' => 'nullable|uuid',
        ]);

        $user = Auth::user();

        $task = Task::create([
            'organization_id' => $user->organization_id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority ?? 'MEDIUM',
            'status' => 'PENDING',
            'assigned_to' => $request->assigned_to,
            'created_by' => $user->id,
        ]);

        ActivityLog::log('TASK_CREATED', "Created task: {$task->title}");

    // Notify assigned user (isolated - can't crash the operation)
    try {
        if ($task->assigned_to) {
            $assignee = User::find($task->assigned_to);
            if ($assignee && $assignee->id !== $user->id) {
                $assignee->notify(new TaskCreated($task, $user->name));
            }
        }
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('Task notification failed (non-critical): ' . $e->getMessage());
    }

    return response()->json($task, 201);
    }

    /**
     * Show a task
     */
    public function show(string $id)
    {
        $task = Task::with(['assignedTo:id,name,email', 'createdBy:id,name'])
            ->findOrFail($id);

        return response()->json($task);
    }

    /**
     * Update a task
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'priority' => 'nullable|in:LOW,MEDIUM,HIGH',
            'status' => 'nullable|in:PENDING,COMPLETED',
            'assigned_to' => 'nullable|uuid',
        ]);

        $task = Task::findOrFail($id);
        $oldAssignee = $task->assigned_to;

        $task->update($request->only([
            'title', 'description', 'due_date', 'priority', 'status', 'assigned_to',
        ]));

        ActivityLog::log('TASK_UPDATED', "Updated task: {$task->title}");

        // Notify new assignee if task was reassigned (isolated - can't crash the operation)
        try {
            $user = Auth::user();
            if ($request->has('assigned_to') && $oldAssignee !== $request->assigned_to && $request->assigned_to) {
                $newAssignee = User::find($request->assigned_to);
                if ($newAssignee && $newAssignee->id !== $user->id) {
                    $newAssignee->notify(new TaskCreated($task, $user->name));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Task reassignment notification failed (non-critical): ' . $e->getMessage());
        }

        return response()->json($task);
    }

    /**
     * Delete a task
     */
    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        $taskTitle = $task->title;
        $task->delete();

        ActivityLog::log('TASK_DELETED', "Deleted task: {$taskTitle}");

        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * Mark task as completed
     */
    public function markComplete(string $id)
    {
        $task = Task::findOrFail($id);
        $task->update(['status' => 'COMPLETED']);

        ActivityLog::log('TASK_COMPLETED', "Completed task: {$task->title}");

        return response()->json($task);
    }
}
