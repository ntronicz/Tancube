<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Services\WebhookService;
use App\Notifications\LeadAssigned;
use App\Notifications\LeadStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Get paginated leads with filters
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Lead::query();

        // Apply organization scope (unless super admin)
        if ($user->role !== 'SUPER_ADMIN') {
            $query->where('organization_id', $user->organization_id);
        }

        // Role-based visibility
        if ($user->role === 'AGENT') {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Sort by newest first
        $query->orderBy('created_at', 'desc');

        $leads = $query->with('assignedTo:id,name')->paginate($request->input('limit', 15));

        return response()->json($leads);
    }

    /**
     * Store a new lead
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|max:100',
            'course' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|uuid|exists:users,id',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check for duplicate phone number within organization
        if ($request->phone) {
            $normalizedPhone = Lead::normalizePhone($request->phone);
            $existingLead = Lead::where('organization_id', $user->organization_id)
                ->where('phone_normalized', $normalizedPhone)
                ->first();

            if ($existingLead) {
                return response()->json([
                    'message' => 'Lead with this phone number already exists.',
                    'lead_id' => $existingLead->id
                ], 422);
            }
        }

        $lead = Lead::create([
            'organization_id' => $user->organization_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'source' => $request->source,
            'course' => $request->course,
            'notes' => $request->notes,
            'status' => 'NEW',
            'assigned_to' => $request->assigned_to,
            'created_by' => $user->id,
        ]);

        ActivityLog::log('LEAD_CREATED', "Created lead: {$lead->name}");

        // Trigger Webhooks and notifications (isolated - can't crash the operation)
        try {
            if ($lead->assigned_to) {
                $this->webhookService->trigger('lead.assigned', $lead);

                // Send notification to assigned agent
                $assignee = User::find($lead->assigned_to);
                if ($assignee) {
                    $assignee->notify(new LeadAssigned($lead));
                }
            }
            $this->webhookService->trigger('lead.created', $lead);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Lead webhook/notification failed (non-critical): ' . $e->getMessage());
        }

        return response()->json($lead, 201);
    }

    /**
     * Show a lead
     */
    public function show(string $id)
    {
        $lead = Lead::with(['assignedTo:id,name,email', 'organization:id,name'])
            ->findOrFail($id);

        return response()->json($lead);
    }

    /**
     * Update a lead
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|string|max:100',
            'course' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|uuid',
            'notes' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
        ]);

        $lead = Lead::findOrFail($id);
        $oldStatus = $lead->status;
        $oldAssignee = $lead->assigned_to;

        $updateData = $request->only([
            'name', 'phone', 'email', 'source', 'course',
            'status', 'assigned_to', 'notes', 'next_follow_up',
        ]);

        if ($request->has('status') && $request->status === 'CONVERTED' && $oldStatus !== 'CONVERTED') {
            if (empty($lead->date_converted)) {
                $updateData['date_converted'] = now();
            }
        }

        $lead->update($updateData);

        // Reset follow-up alert count when follow-up date changes
        if ($request->has('next_follow_up') && $lead->wasChanged('next_follow_up')) {
            $lead->update(['follow_up_alert_count' => 0]);
        }
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // If status changed, update last_contacted and notify
        if ($request->has('status') && $oldStatus !== $request->status) {
            $lead->update(['last_contacted' => now()]);

            // Notify assigned agent about status change (isolated)
            try {
                if ($lead->assigned_to) {
                    $assignee = User::find($lead->assigned_to);
                    if ($assignee && $assignee->id !== $currentUser->id) {
                        $assignee->notify(new LeadStatusChanged($lead, $oldStatus, $currentUser->name));
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Lead status notification failed (non-critical): ' . $e->getMessage());
            }
        }

        // If assigned_to changed, notify the new assignee (isolated)
        try {
            if ($request->has('assigned_to') && $oldAssignee !== $request->assigned_to && $request->assigned_to) {
                $newAssignee = User::find($request->assigned_to);
                if ($newAssignee) {
                    $newAssignee->notify(new LeadAssigned($lead));
                }
                $this->webhookService->trigger('lead.assigned', $lead);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Lead assignment notification failed (non-critical): ' . $e->getMessage());
        }

        ActivityLog::log('LEAD_UPDATED', "Updated lead: {$lead->name}");

        try {
            $this->webhookService->trigger('lead.updated', $lead);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Lead update webhook failed (non-critical): ' . $e->getMessage());
        }

        return response()->json($lead);
    }

    /**
     * Delete a lead
     */
    public function destroy(string $id)
    {
        $lead = Lead::findOrFail($id);
        $leadName = $lead->name;

        // Trigger webhook before deleting (so data is still available)
        $this->webhookService->trigger('lead.deleted', $lead);

        $lead->delete();

        ActivityLog::log('LEAD_DELETED', "Deleted lead: {$leadName}");

        return response()->json(['message' => 'Lead deleted successfully']);
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
        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');

        if (!$handle) {
            return response()->json(['message' => 'Unable to read file'], 400);
        }

        // Read header row
        $header = fgetcsv($handle);
        $header = array_map('strtolower', array_map('trim', $header));

        // Map columns
        $nameIdx = array_search('name', $header);
        $phoneIdx = array_search('phone', $header);
        $emailIdx = array_search('email', $header);
        $sourceIdx = array_search('source', $header);
        $courseIdx = array_search('course', $header);

        if ($nameIdx === false) {
            fclose($handle);
            return response()->json(['message' => 'CSV must have a "name" column'], 400);
        }

        // Get existing normalized phones for duplicate detection
        $existingPhones = Lead::where('organization_id', $user->organization_id)
            ->whereNotNull('phone_normalized')
            ->pluck('phone_normalized')
            ->toArray();
        $existingSet = array_flip($existingPhones);

        $imported = 0;
        $skipped = 0;
        $csvPhones = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $name = trim($row[$nameIdx] ?? '');
                $phone = $phoneIdx !== false ? trim($row[$phoneIdx] ?? '') : null;
                $email = $emailIdx !== false ? trim($row[$emailIdx] ?? '') : null;
                $source = $sourceIdx !== false ? trim($row[$sourceIdx] ?? '') : null;
                $course = $courseIdx !== false ? trim($row[$courseIdx] ?? '') : null;

                if (empty($name)) {
                    $skipped++;
                    continue;
                }

                // Normalize phone for duplicate check
                $normalizedPhone = Lead::normalizePhone($phone);

                // Skip if duplicate in DB or CSV
                if ($normalizedPhone) {
                    if (isset($existingSet[$normalizedPhone]) || isset($csvPhones[$normalizedPhone])) {
                        $skipped++;
                        continue;
                    }
                    $csvPhones[$normalizedPhone] = true;
                }

                Lead::create([
                    'organization_id' => $user->organization_id,
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'source' => $source,
                    'course' => $course,
                    'status' => 'NEW',
                ]);

                $imported++;
            }

            DB::commit();
            fclose($handle);

            ActivityLog::log('LEADS_IMPORTED', "Imported {$imported} leads, skipped {$skipped} duplicates");

            return response()->json([
                'message' => "Imported {$imported} leads. Skipped {$skipped} duplicates.",
                'imported' => $imported,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update follow-up with preset
     */
    public function updateFollowUp(Request $request, string $id)
    {
        $request->validate([
            'preset' => 'required|in:1h,3h,tomorrow,next_week,custom',
            'datetime' => 'required_if:preset,custom|nullable|date',
        ]);

        $lead = Lead::findOrFail($id);

        $followUp = match ($request->preset) {
            '1h' => now()->addHour(),
            '3h' => now()->addHours(3),
            'tomorrow' => now()->addDay()->startOfDay()->addHours(9),
            'next_week' => now()->addWeek()->startOfDay()->addHours(9),
            'custom' => $request->datetime,
        };

        $lead->update([
            'next_follow_up' => $followUp,
            'follow_up_alert_count' => 0,
        ]);

        ActivityLog::log('FOLLOW_UP_SET', "Set follow-up for lead: {$lead->name}");

        return response()->json($lead);
    }

    /**
     * Export leads to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $query = Lead::query();

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where('organization_id', $user->organization_id);
        }

        $leads = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads_export_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['Name', 'Phone', 'Email', 'Source', 'Course', 'Status', 'Notes', 'Next Follow-up', 'Created At']);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->name,
                    $lead->phone,
                    $lead->email,
                    $lead->source,
                    $lead->course,
                    $lead->status,
                    $lead->notes,
                    $lead->next_follow_up?->format('Y-m-d H:i'),
                    $lead->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        ActivityLog::log('LEADS_EXPORTED', "Exported {$leads->count()} leads");

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get all lead phone numbers for the user's organization.
     * Used by mobile app to filter which calls to sync (CRM leads only).
     */
    public function phoneNumbers()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $phones = Lead::where('organization_id', $user->organization_id)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->pluck('phone')
            ->toArray();

        return response()->json(['phone_numbers' => $phones]);
    }
}
