<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\WebhookService;
use App\Notifications\TaskCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }
    /**
     * Display tasks list
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $isSuperAdmin = $user->role === 'SUPER_ADMIN';

        // Build query - Super Admin sees all tasks
        if ($isSuperAdmin) {
            $query = Task::query();
        } else {
            $query = Task::where('organization_id', $organizationId);
        }

        // Role-based filter
        if ($user->role === 'AGENT') {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->with(['assignedTo:id,name', 'createdBy:id,name'])
            ->orderByRaw("FIELD(status, 'PENDING', 'COMPLETED')")
            ->orderByRaw("FIELD(priority, 'HIGH', 'MEDIUM', 'LOW')")
            ->orderBy('due_date')
            ->paginate(50)
            ->withQueryString();

        if ($isSuperAdmin) {
            $agents = User::whereIn('role', ['ADMIN', 'AGENT'])->get(['id', 'name']);
        } else {
            $agents = User::where('organization_id', $organizationId)
                ->whereIn('role', ['ADMIN', 'AGENT'])
                ->get(['id', 'name']);
        }

        return view('tasks.index', compact('tasks', 'agents'));
    }

    /**
     * Show create task form
     */
    public function create()
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $isSuperAdmin = $user->role === 'SUPER_ADMIN';

        if ($isSuperAdmin) {
            $agents = User::whereIn('role', ['ADMIN', 'AGENT'])->get(['id', 'name']);
        } else {
            $agents = User::where('organization_id', $organizationId)
                ->whereIn('role', ['ADMIN', 'AGENT'])
                ->get(['id', 'name']);
        }

        return view('tasks.create', compact('agents'));
    }

    /**
     * Store new task
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:LOW,MEDIUM,HIGH',
            'assigned_to' => 'nullable|uuid',
        ]);

        try {
            $user = Auth::user();
            $organizationId = $user->organization_id;

            // If Super Admin and assigning to an agent, use agent's organization
            if (!$organizationId && $request->assigned_to) {
                $agent = User::find($request->assigned_to);
                if ($agent && $agent->organization_id) {
                    $organizationId = $agent->organization_id;
                }
            }

            $task = Task::create([
                'organization_id' => $organizationId,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'status' => 'PENDING',
                'assigned_to' => $request->assigned_to,
                'created_by' => $user->id,
            ]);

            ActivityLog::log('TASK_CREATED', "Created task: {$task->title}");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Task creation failed: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Failed to create task. Please try again or contact support.');
        }

        // Webhook and notification dispatch - isolated so they can't crash the operation
        try {
            $this->webhookService->trigger('task.created', $task);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Task webhook failed (non-critical): ' . $e->getMessage());
        }

        try {
            if ($task->assigned_to && $task->assigned_to !== $user->id) {
                $assignee = User::find($task->assigned_to);
                if ($assignee) {
                    $assignee->notify(new TaskCreated($task, $user->name));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Task notification failed (non-critical): ' . $e->getMessage());
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully!');
    }

    /**
     * Update task
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'priority' => 'sometimes|in:LOW,MEDIUM,HIGH',
            'status' => 'sometimes|in:PENDING,COMPLETED',
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

        return back()->with('success', 'Task updated successfully!');
    }

    /**
     * Delete task
     */
    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        
        // Authorization: Only Admin/Super Admin or Task Creator can delete
        $user = Auth::user();
        if ($user->role !== 'SUPER_ADMIN' && $user->role !== 'ADMIN' && $task->created_by !== $user->id) {
            abort(403, 'Unauthorized action. Only admins or the task creator can delete this task.');
        }

        $taskTitle = $task->title;
        $task->delete();

        ActivityLog::log('TASK_DELETED', "Deleted task: {$taskTitle}");

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully!');
    }

    /**
     * Mark task as complete
     */
    /**
     * Toggle task status (Complete/Pending)
     */
    public function toggleStatus(string $id)
    {
        $task = Task::findOrFail($id);
        
        if ($task->status === 'COMPLETED') {
            $task->update(['status' => 'PENDING']);
            ActivityLog::log('TASK_UPDATED', "Marked task as pending: {$task->title}");
            $message = 'Task marked as pending!';
        } else {
            $task->update(['status' => 'COMPLETED']);
            ActivityLog::log('TASK_COMPLETED', "Completed task: {$task->title}");
            $this->webhookService->trigger('task.completed', $task);
            $message = 'Task marked as complete!';
        }

        return back()->with('success', $message);
    }
}
