<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Get a lead with proper authorization checks.
     * Ensures users can only access leads within their organization,
     * and agents can only access leads assigned to them.
     */
    private function authorizedLead(string $id): Lead
    {
        $user = Auth::user();
        $query = Lead::where('id', $id);
        
        // Organization check (skip for Super Admin)
        if ($user->role !== 'SUPER_ADMIN') {
            $query->where('organization_id', $user->organization_id);
        }
        
        // Agent can only access assigned leads
        if ($user->role === 'AGENT') {
            $query->where('assigned_to', $user->id);
        }
        
        return $query->firstOrFail();
    }

    /**
     * Display leads list
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $isSuperAdmin = $user->role === 'SUPER_ADMIN';

        // Build query - Super Admin sees all leads
        if ($isSuperAdmin) {
            $query = Lead::query();
        } else {
            $query = Lead::where('organization_id', $organizationId);
        }

        // persistence logic
        if ($request->has('reset')) {
            session()->forget('leads_filters');
            return redirect()->route('leads.index');
        }

        // If request has filters, save to session
        // We check for specific filter keys to determine if 'filtering' is happening
        $filterKeys = ['source', 'course', 'status', 'assigned_to', 'search', 'date_filter', 'start_date', 'end_date', 'per_page', 'follow_up'];
        
        $hasFilters = false;
        foreach ($filterKeys as $key) {
            if ($request->has($key)) {
                $hasFilters = true;
                break;
            }
        }

        if ($hasFilters) {
            session(['leads_filters' => $request->only($filterKeys)]);
        } elseif (session()->has('leads_filters')) {
            // If no filters in request but session has them, redirect to apply them
            // This ensures the URL always reflects the state (good for bookmarks/sharing)
            return redirect()->route('leads.index', session('leads_filters'));
        }

        // Apply filters
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('course')) {
            $query->where('course', $request->course);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        // Date filter presets
        if ($request->filled('date_filter')) {
            $dateFilter = $request->date_filter;
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'last_7_days':
                    $query->whereDate('created_at', '>=', today()->subDays(7));
                    break;
                case 'last_30_days':
                    $query->whereDate('created_at', '>=', today()->subDays(30));
                    break;
                case 'this_month':
                    $query->whereYear('created_at', now()->year)
                          ->whereMonth('created_at', now()->month);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->whereDate('created_at', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('created_at', '<=', $request->end_date);
                    }
                    break;
            }
        }
        if ($request->follow_up === 'today') {
            $query->whereDate('next_follow_up', today());
        }

        // Agent filter
        if ($user->role === 'AGENT') {
            $query->where('assigned_to', $user->id);
        }

        $perPage = $request->input('per_page', 50);
        $leads = $query->with('assignedTo:id,name')
            ->orderByRaw('next_follow_up IS NULL, next_follow_up ASC')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Get filter options - use defaults for Super Admin
        $sources = AppSetting::getSources($organizationId);
        $courses = AppSetting::getCourses($organizationId);
        $statuses = AppSetting::getStatuses($organizationId);
        
        if ($isSuperAdmin) {
            $agents = User::whereIn('role', ['ADMIN', 'AGENT'])->get(['id', 'name']);
        } else {
            $agents = User::where('organization_id', $organizationId)
                ->whereIn('role', ['ADMIN', 'AGENT'])
                ->get(['id', 'name']);
        }

        if ($request->ajax()) {
            return view('leads.partials.lead-list', compact('leads', 'sources', 'courses', 'statuses', 'agents'));
        }

        return view('leads.index', compact('leads', 'sources', 'courses', 'statuses', 'agents'));
    }

    /**
     * Show create lead form
     */
    public function create()
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $isSuperAdmin = $user->role === 'SUPER_ADMIN';

        $sources = AppSetting::getSources($organizationId);
        $courses = AppSetting::getCourses($organizationId);
        $statuses = AppSetting::getStatuses($organizationId);
        
        if ($isSuperAdmin) {
            $agents = User::whereIn('role', ['ADMIN', 'AGENT'])->get(['id', 'name']);
        } else {
            $agents = User::where('organization_id', $organizationId)
                ->whereIn('role', ['ADMIN', 'AGENT'])
                ->get(['id', 'name']);
        }

        return view('leads.create', compact('sources', 'courses', 'statuses', 'agents'));
    }

    /**
     * Store new lead
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string',
            'course' => 'nullable|string',
            'status' => 'nullable|string',
            'assigned_to' => 'nullable|uuid',
            'notes' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
        ]);

        $user = Auth::user();
        $organizationId = $user->organization_id;

        // If Super Admin and assigning to an agent, use agent's organization
        if (!$organizationId && $request->assigned_to) {
            $agent = User::find($request->assigned_to);
            if ($agent && $agent->organization_id) {
                $organizationId = $agent->organization_id;
            }
        }

        // Check for duplicate
        if ($request->phone) {
            $normalizedPhone = Lead::normalizePhone($request->phone);
            $duplicate = Lead::where('organization_id', $organizationId)
                ->where('phone_normalized', $normalizedPhone)
                ->first();

            if ($duplicate) {
                return back()
                    ->withInput()
                    ->withErrors(['phone' => 'A lead with this phone number already exists.']);
            }
        }

        // Default to Admin/Creator if not assigned
        $assignedTo = $request->assigned_to;
        if (empty($assignedTo)) {
            if ($organizationId) {
                // Find first admin of this organization
                $admin = User::where('organization_id', $organizationId)
                             ->where('role', 'ADMIN')
                             ->orderBy('created_at', 'asc')
                             ->first();
                $assignedTo = $admin ? $admin->id : $user->id;
            } else {
                 // Fallback for Super Admin or no-org
                $assignedTo = $user->id;
            }
        }

        $lead = Lead::create([
            'organization_id' => $organizationId,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'source' => $request->source,
            'course' => $request->course,
            'status' => $request->status ?? 'NEW',
            'assigned_to' => $assignedTo,
            'notes' => $request->notes,
            'next_follow_up' => $request->next_follow_up,
        ]);

        ActivityLog::log('LEAD_CREATED', "Created lead: {$lead->name}");

    // Trigger webhooks (isolated - can't crash the operation)
try {
    if ($lead->assigned_to) {
        $this->webhookService->trigger('lead.assigned', $lead);
    }
    $this->webhookService->trigger('lead.created', $lead);
} catch (\Throwable $e) {
    \Illuminate\Support\Facades\Log::warning('Lead webhook failed (non-critical): ' . $e->getMessage());
}

// Notify Assigned User (isolated - can't crash the operation)
try {
    if ($lead->assigned_to) {
        $assignedUser = User::find($lead->assigned_to);
        if ($assignedUser) {
            // Import notification class at top or use full path
            $assignedUser->notify(new \App\Notifications\LeadAssigned($lead));
        }
    }
} catch (\Throwable $e) {
    \Illuminate\Support\Facades\Log::warning('Lead notification failed (non-critical): ' . $e->getMessage());
}

        return redirect()->route('leads.index')
            ->with('success', 'Lead created successfully!');
    }

    /**
     * Show lead details
     */
    public function show(string $id)
    {
        $lead = $this->authorizedLead($id);
        $lead->load('assignedTo:id,name,email');

        $user = Auth::user();
        $organizationId = $user->organization_id;
        $isSuperAdmin = $user->role === 'SUPER_ADMIN';

        $sources = AppSetting::getSources($organizationId);
        $courses = AppSetting::getCourses($organizationId);
        $statuses = AppSetting::getStatuses($organizationId);
        
        if ($isSuperAdmin) {
            $agents = User::whereIn('role', ['ADMIN', 'AGENT'])->get(['id', 'name']);
        } else {
            $agents = User::where('organization_id', $organizationId)
                ->whereIn('role', ['ADMIN', 'AGENT'])
                ->get(['id', 'name']);
        }

        // Get activity history for this lead
        $activities = ActivityLog::where('details', 'like', "%{$lead->name}%")
            ->orWhere('details', 'like', "%lead ID: {$lead->id}%")
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        if (request()->wantsJson()) {
            $response = [
                'lead' => $lead,
                'activities' => $activities
            ];

            if ($user->role === 'ADMIN' || $user->role === 'SUPER_ADMIN') {
                $response['agents'] = $agents;
            }

            return response()->json($response);
        }

        return view('leads.show', compact('lead', 'sources', 'courses', 'statuses', 'agents', 'activities'));
    }

    /**
     * Update lead
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string',
            'course' => 'nullable|string',
            'status' => 'nullable|string',
            'assigned_to' => 'nullable|uuid',
            'notes' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
        ]);

        $lead = $this->authorizedLead($id);
        $oldStatus = $lead->status;
        $oldNotes = $lead->notes;
        $oldAssignedTo = $lead->assigned_to;

        $lead->fill($request->only([
            'name', 'phone', 'email', 'source', 'course',
            'status', 'assigned_to', 'notes', 'next_follow_up',
        ]));

        if ($lead->isDirty('status')) {
            $lead->last_contacted = now();
            ActivityLog::log('STATUS_CHANGED', "Changed status for {$lead->name} from {$oldStatus} to {$lead->status}");

            // Notify assigned agent about status change (isolated - can't crash the operation)
            try {
                if ($lead->assigned_to) {
                    $assignee = User::find($lead->assigned_to);
                    $currentUser = Auth::user();
                    if ($assignee && $assignee->id !== $currentUser->id) {
                        $assignee->notify(new \App\Notifications\LeadStatusChanged($lead, $oldStatus, $currentUser->name));
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Lead status notification failed (non-critical): ' . $e->getMessage());
            }
        }

        if ($lead->isDirty('notes')) {
            ActivityLog::log('NOTE_UPDATED', "Updated note for {$lead->name}: \"{$lead->notes}\"");
        }

        if ($lead->isDirty('next_follow_up')) {
            $formattedDate = $lead->next_follow_up ? $lead->next_follow_up->format('M d, H:i') : 'None';
            ActivityLog::log('FOLLOW_UP_SET', "Set follow-up for {$lead->name}: {$formattedDate}");
            // Reset alert count so follow-up reminders fire again
            $lead->follow_up_alert_count = 0;
        }

        $lead->save();

    if (!$lead->wasChanged(['status', 'notes', 'next_follow_up'])) {
         // If other fields changed but not specific ones
         ActivityLog::log('LEAD_UPDATED', "Updated lead details: {$lead->name}");
    }

    // Trigger webhooks (isolated - can't crash the operation)
try {
    $this->webhookService->trigger('lead.updated', $lead);
    if ($lead->assigned_to && $lead->assigned_to !== $oldAssignedTo) {
        $this->webhookService->trigger('lead.assigned', $lead);
    }
} catch (\Throwable $e) {
    \Illuminate\Support\Facades\Log::warning('Lead update webhook failed (non-critical): ' . $e->getMessage());
}

// Notify if assigned user changed (isolated - can't crash the operation)
try {
    if ($lead->assigned_to && $lead->assigned_to !== $oldAssignedTo) {
         $assignedUser = User::find($lead->assigned_to);
         if ($assignedUser) {
             $assignedUser->notify(new \App\Notifications\LeadAssigned($lead));
         }
    }
} catch (\Throwable $e) {
    \Illuminate\Support\Facades\Log::warning('Lead assignment notification failed (non-critical): ' . $e->getMessage());
}

    if ($request->wantsJson()) {
        return response()->json(['success' => true, 'message' => 'Lead updated successfully!']);
    }

    return back()->with('success', 'Lead updated successfully!');
    }

    /**
     * Delete lead
     */
    public function destroy(string $id)
    {
        $lead = $this->authorizedLead($id);
    $leadName = $lead->name;

    // Trigger webhook before deleting
    $this->webhookService->trigger('lead.deleted', $lead);

    $lead->delete();

    ActivityLog::log('LEAD_DELETED', "Deleted lead: {$leadName}");

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully!');
    }

    /**
     * Import leads from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $user = Auth::user();

        if ($user->role === 'AGENT') {
            abort(403, 'Unauthorized action.');
        }

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        if (!$handle) {
            return back()->with('error', 'Unable to read file');
        }

        // Get header
        $header = fgetcsv($handle);
        // Normalize header: lowercase, trim, remove quotes
        $header = array_map(function($h) {
            return strtolower(trim(str_replace('"', '', $h)));
        }, $header);

        // Map column names to indexes
        $colMap = [
            'name' => array_search('name', $header),
            'phone' => array_search('phone', $header),
            'email' => array_search('email', $header),
            'source' => array_search('source', $header),
            'course' => array_search('course', $header),
            'status' => array_search('status', $header),
            'assigned_to' => array_search('assigned to', $header),
            'notes' => array_search('notes', $header),
            'next_follow_up' => array_search('next follow-up', $header),
            'created_at' => array_search('created at', $header),
        ];

        if ($colMap['name'] === false) {
            fclose($handle);
            return back()->with('error', 'CSV must have a "name" column');
        }

        $existingPhones = Lead::where('organization_id', $user->organization_id)
            ->whereNotNull('phone_normalized')
            ->pluck('phone_normalized')
            ->flip()
            ->toArray();

        // Cache agents for name lookup
        $agents = User::where('organization_id', $user->organization_id)
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])
            ->toArray();

        // Determine default assignee (Admin or Uploader)
        $defaultAssigneeId = $user->id; // Default to uploader
        $admin = User::where('organization_id', $user->organization_id)
                     ->where('role', 'ADMIN')
                     ->orderBy('created_at', 'asc')
                     ->first();
        if ($admin) {
             $defaultAssigneeId = $admin->id;
        }

        $imported = 0;
        $skipped = 0;
        $csvPhones = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = [];
                foreach ($colMap as $key => $index) {
                    $data[$key] = ($index !== false && isset($row[$index])) ? trim($row[$index]) : null;
                }

                if (empty($data['name'])) {
                    $skipped++;
                    continue;
                }

                $normalizedPhone = Lead::normalizePhone($data['phone']);

                if ($normalizedPhone) {
                    if (isset($existingPhones[$normalizedPhone]) || isset($csvPhones[$normalizedPhone])) {
                        $skipped++;
                        continue;
                    }
                    $csvPhones[$normalizedPhone] = true;
                }

                // Handle Assigned To
                $assignedToId = null;
                if (!empty($data['assigned_to'])) {
                    $agentName = strtolower($data['assigned_to']);
                    $assignedToId = $agents[$agentName] ?? $defaultAssigneeId;
                } else {
                    $assignedToId = $defaultAssigneeId;
                }

                // Handle Dates
                $nextFollowUp = null;
                if (!empty($data['next_follow_up'])) {
                    try {
                        $nextFollowUp = \Carbon\Carbon::parse($data['next_follow_up']);
                    } catch (\Exception $e) {}
                }

                $createdAt = now();
                if (!empty($data['created_at'])) {
                    try {
                        $createdAt = \Carbon\Carbon::parse($data['created_at']);
                    } catch (\Exception $e) {}
                }

                $lead = new Lead([
                    'organization_id' => $user->organization_id,
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'source' => $data['source'],
                    'course' => $data['course'],
                    'status' => !empty($data['status']) ? strtoupper($data['status']) : 'NEW',
                    'assigned_to' => $assignedToId,
                    'notes' => $data['notes'],
                    'next_follow_up' => $nextFollowUp,
                ]);
                
                $lead->created_at = $createdAt;
                $lead->save();

                $imported++;
            }

            DB::commit();
            fclose($handle);

            ActivityLog::log('LEADS_IMPORTED', "Imported {$imported} leads, skipped {$skipped} duplicates");

            return back()->with('success', "Imported {$imported} leads. Skipped {$skipped} duplicates.");
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Update follow-up
     */
    public function updateFollowUp(Request $request, string $id)
    {
        $request->validate([
            'preset' => 'required|in:1h,3h,5h,7h,tomorrow,next_week',
        ]);

        $lead = $this->authorizedLead($id);

        $followUp = match ($request->preset) {
            '1h' => now()->addHour(),
            '3h' => now()->addHours(3),
            '5h' => now()->addHours(5),
            '7h' => now()->addHours(7),
            'tomorrow' => now()->addDay()->startOfDay()->addHours(9),
            'next_week' => now()->addWeek()->startOfDay()->addHours(9),
        };

        $lead->update([
            'next_follow_up' => $followUp,
            'follow_up_alert_count' => 0,
        ]);

        ActivityLog::log('FOLLOW_UP_SET', "Set follow-up for lead: {$lead->name}");

        return back()->with('success', 'Follow-up scheduled!');
    }

    /**
     * Export leads to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'AGENT') {
            abort(403, 'Unauthorized action.');
        }

        $organizationId = $user->organization_id;
        $isSuperAdmin = $user->role === 'SUPER_ADMIN';

        // Build query with same filters as index
        if ($isSuperAdmin) {
            $query = Lead::query();
        } else {
            $query = Lead::where('organization_id', $organizationId);
        }

        // Apply filters
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('date_filter')) {
            $dateFilter = $request->date_filter;
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'last_7_days':
                    $query->whereDate('created_at', '>=', today()->subDays(7));
                    break;
                case 'last_30_days':
                    $query->whereDate('created_at', '>=', today()->subDays(30));
                    break;
                case 'this_month':
                    $query->whereYear('created_at', now()->year)
                          ->whereMonth('created_at', now()->month);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->whereDate('created_at', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('created_at', '<=', $request->end_date);
                    }
                    break;
            }
        }

        // Note: Agent check is at line 577, so this code path is admin/super-admin only

        $leads = $query->with('assignedTo:id,name')->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'leads_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($leads) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Name', 'Phone', 'Email', 'Source', 'Course', 'Status', 'Assigned To', 'Notes', 'Next Follow-up', 'Created At']);
            
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->name,
                    $lead->phone,
                    $lead->email,
                    $lead->source,
                    $lead->course,
                    $lead->status,
                    $lead->assignedTo->name ?? '',
                    $lead->notes,
                    $lead->next_follow_up ? $lead->next_follow_up->format('Y-m-d H:i') : '',
                    $lead->created_at->format('Y-m-d H:i'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download sample CSV for import
     */
    public function downloadSample()
    {
        if (Auth::user()->role === 'AGENT') {
            abort(403, 'Unauthorized action.');
        }

        $filename = 'leads_import_sample.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header row - EXACTLY AS REQUESTED
            fputcsv($file, ['Name', 'Phone', 'Email', 'Source', 'Course', 'Status', 'Assigned To', 'Notes', 'Next Follow-up', 'Created At']);
            
            // Sample data rows
            fputcsv($file, ['John Doe', '+919876543210', 'john@example.com', 'Website', 'MBA', 'NEW', '', 'Interested in admission', '', '2023-01-01 10:00']);
            fputcsv($file, ['Jane Smith', '+919876543211', 'jane@example.com', 'Facebook', 'BBA', 'CONTACTED', 'Admin User', 'Callback requested', '2023-12-25 10:00', '2023-01-02 14:30']);
            fputcsv($file, ['Bob Wilson', '+919876543212', 'bob@example.com', 'Google Ads', 'MBA', 'NEW', '', '', '', '']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk assign leads to an agent
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'uuid',
            'assigned_to' => 'required|uuid|exists:users,id',
        ]);

        $user = Auth::user();
        
        // Only Admin and Super Admin can bulk assign
        if ($user->role === 'AGENT') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Ensure leads belong to user's organization
        $query = Lead::whereIn('id', $request->lead_ids);
        if ($user->role !== 'SUPER_ADMIN') {
            $query->where('organization_id', $user->organization_id);
        }
        
        $count = $query->update([
            'assigned_to' => $request->assigned_to,
        ]);

        ActivityLog::log('BULK_ASSIGN', "Assigned {$count} leads to agent");

        // Notify the assigned agent (isolated - can't crash the operation)
        try {
            if ($count > 0) {
                $assignee = User::find($request->assigned_to);
                if ($assignee && $assignee->id !== $user->id) {
                    // Send a single summary notification for bulk assign
                    $sampleLead = Lead::where('assigned_to', $request->assigned_to)
                        ->orderBy('updated_at', 'desc')
                        ->first();
                    if ($sampleLead) {
                        $sampleLead->name = "{$count} leads assigned to you";
                        $assignee->notify(new \App\Notifications\LeadAssigned($sampleLead));
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Bulk assign notification failed (non-critical): ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'count' => $count]);
    }

    /**
     * Bulk delete leads
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'uuid',
        ]);

        $user = Auth::user();
        
        // Only Admin and Super Admin can bulk delete
        if ($user->role === 'AGENT') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Ensure leads belong to user's organization
        $query = Lead::whereIn('id', $request->lead_ids);
        if ($user->role !== 'SUPER_ADMIN') {
            $query->where('organization_id', $user->organization_id);
        }
        
        $count = $query->delete();

        ActivityLog::log('BULK_DELETE', "Deleted {$count} leads");

        return response()->json(['success' => true, 'count' => $count]);
    }

    /**
     * Bulk update status of leads
     */
    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'uuid',
            'status' => 'required|string|max:50',
        ]);

        $user = Auth::user();

        // Build query scoped to user's organization
        $query = Lead::whereIn('id', $request->lead_ids);
        if ($user->role === 'AGENT') {
            // Agents can only update their own assigned leads
            $query->where('assigned_to', $user->id);
        } elseif ($user->role !== 'SUPER_ADMIN') {
            $query->where('organization_id', $user->organization_id);
        }

        $count = $query->update([
            'status' => $request->status,
        ]);

        ActivityLog::log('BULK_STATUS_UPDATE', "Updated {$count} leads to status: {$request->status}");

        return response()->json(['success' => true, 'count' => $count]);
    }

    /**
     * Log activity for a lead (call, WhatsApp click)
     */
    public function logActivity(Request $request, string $id)
    {
        $request->validate([
            'type' => 'required|in:call,whatsapp,note',
            'notes' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
        ]);

        $lead = $this->authorizedLead($id);

        $action = match ($request->type) {
            'call' => 'CALL_INITIATED',
            'whatsapp' => 'WHATSAPP_CLICKED',
            'note' => 'NOTE_ADDED',
        };

        $details = match ($request->type) {
            'call' => "Initiated call to lead: {$lead->name}",
            'whatsapp' => "Opened WhatsApp for lead: {$lead->name}",
            'note' => "Added note: {$request->notes}",
        };
        
        // If notes provided for call/whatsapp, append them
        if ($request->type !== 'note' && $request->filled('notes')) {
             $details .= " - Note: {$request->notes}";
        }

        // Update lead data
        if ($request->filled('notes')) {
            $lead->notes = $request->notes;
        }

        if ($request->filled('next_follow_up')) {
            $lead->next_follow_up = $request->next_follow_up;
            // Reset alert count so follow-up reminders fire again
            $lead->follow_up_alert_count = 0;
        }

        $lead->last_contacted = now();
        $lead->save();

        ActivityLog::log($action, $details);

        return response()->json(['success' => true]);
    }

    /**
     * Follow-ups tab - show all leads with follow-ups
     */
    public function followUps(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $isSuperAdmin = $user->role === 'SUPER_ADMIN';
        $isAdmin = $user->role === 'ADMIN' || $isSuperAdmin;

        // Build query
        if ($isSuperAdmin) {
            $query = Lead::query();
        } else {
            $query = Lead::where('organization_id', $organizationId);
        }

        // Agent logic:
        // 1. If user is AGENT, force their ID
        // 2. If user is ADMIN/SUPER_ADMIN, check if they requested a specific agent
        $agentId = null;

        if ($user->role === 'AGENT') {
            $query->where('assigned_to', $user->id);
        } elseif ($isAdmin && $request->filled('agent_id')) {
            $agentId = $request->agent_id;
            $query->where('assigned_to', $agentId);
        }

        // Persistence logic for filters
        if ($request->has('reset')) {
            session()->forget('followup_filters');
            return redirect()->route('follow-ups.index');
        }

        // We want to persist 'filter' (status) and 'agent_id'
        if ($request->hasAny(['filter', 'agent_id'])) {
             session(['followup_filters' => $request->only(['filter', 'agent_id'])]);
        } elseif (session()->has('followup_filters')) {
             // If NO params in URL but session has them, redirect (unless we are explicitly clearing)
             // But careful about infinite loops. 
             // Ideally we only redirect if the request is "empty" of these params
             if (!$request->has('filter') && !$request->has('agent_id')) {
                 return redirect()->route('follow-ups.index', session('followup_filters'));
             }
        }

        // Filter by follow-up status
        $filter = $request->input('filter', 'all');
        
        switch ($filter) {
            case 'today':
                $query->whereDate('next_follow_up', today());
                break;
            case 'tomorrow':
                $query->whereDate('next_follow_up', today()->addDay());
                break;
            case 'overdue':
                $query->whereNotNull('next_follow_up')
                      ->where('next_follow_up', '<', now());
                break;
            case 'upcoming':
                $query->whereNotNull('next_follow_up')
                      ->whereDate('next_follow_up', '>', today()->addDay())
                      ->whereDate('next_follow_up', '<=', today()->addDays(7));
                break;
            default: // 'all'
                $query->whereNotNull('next_follow_up');
        }

        // Exclude statuses that shouldn't have follow-ups
        $query->whereNotIn('status', ['CONVERTED', 'LOST', 'Not interested', 'NOT INTERESTED']);

        $perPage = $request->input('per_page', 50);
        $leads = $query->with('assignedTo:id,name')
            ->orderBy('next_follow_up', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        // Get filter options (Agents list for Admins)
        $agents = collect();
        if ($isAdmin) {
             if ($isSuperAdmin) {
                $agents = User::whereIn('role', ['ADMIN', 'AGENT'])->orderBy('name')->get(['id', 'name']);
            } else {
                $agents = User::where('organization_id', $organizationId)
                    ->whereIn('role', ['ADMIN', 'AGENT'])
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
        }

        // Get stats
        // We need to respect the generic "assigned_to" filter for stats IF it's applied?
        // Usually stats (badges) show the counts for "My View".
        // If I am Admin and I filter by Agent A, badges should probably reflect Agent A's stats?
        // Or should they stay global? Usually they reflect the current "context".
        // Let's make them reflect the current filtered context (Agent A).

        $excludeStatuses = ['CONVERTED', 'LOST', 'Not interested', 'NOT INTERESTED'];
        
        $statsQuery = Lead::whereNotNull('next_follow_up')
            ->whereNotIn('status', $excludeStatuses);
        
        // Apply Organization Scope
        if (!$isSuperAdmin) {
            $statsQuery->where('organization_id', $organizationId);
        }

        // Apply Agent Scope (either forced or selected)
        if ($user->role === 'AGENT') {
            $statsQuery->where('assigned_to', $user->id);
        } elseif ($agentId) {
             $statsQuery->where('assigned_to', $agentId);
        }
        
        $stats = $statsQuery->selectRaw("
            SUM(CASE WHEN DATE(next_follow_up) = CURDATE() THEN 1 ELSE 0 END) as today_count,
            SUM(CASE WHEN DATE(next_follow_up) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as tomorrow_count,
            SUM(CASE WHEN next_follow_up < NOW() THEN 1 ELSE 0 END) as overdue_count,
            SUM(CASE WHEN DATE(next_follow_up) > DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND DATE(next_follow_up) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as upcoming_count
        ")->first();
        
        $todayCount = $stats->today_count ?? 0;
        $tomorrowCount = $stats->tomorrow_count ?? 0;
        $overdueCount = $stats->overdue_count ?? 0;
        $upcomingCount = $stats->upcoming_count ?? 0;
        
        return view('followups.index', compact(
            'leads', 'agents', 'filter', 'agentId', 
            'todayCount', 'tomorrowCount', 'overdueCount', 'upcomingCount'
        ));
    }
}
