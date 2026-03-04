<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InsightsController extends Controller
{
    /**
     * Show insights page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $isSuperAdmin = $user->role === 'SUPER_ADMIN';

        // Date range filter
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Agent Filter (Admin Only)
        $selectedAgentId = null;
        $agents = collect([]);

        if ($isSuperAdmin || $user->role === 'ADMIN') {
             $agentsQuery = \App\Models\User::whereIn('role', ['AGENT', 'ADMIN']);
            if (!$isSuperAdmin) {
                $agentsQuery->where('organization_id', $organizationId);
            }
            $agents = $agentsQuery->get();

            if ($request->has('agent_id') && $request->agent_id) {
                $selectedAgentId = $request->agent_id;
            }
        } else {
             // Agents see only themselves
            $agents = collect([$user]);
            $selectedAgentId = $user->id;
        }

        // Base query
        $baseQuery = function() use ($organizationId, $isSuperAdmin, $startDate, $endDate, $selectedAgentId) {
            $query = Lead::query();
            if (!$isSuperAdmin) {
                $query->where('organization_id', $organizationId);
            }
            
            // Apply Agent Filter
            if ($selectedAgentId) {
                $query->where('assigned_to', $selectedAgentId);
            }

            return $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        };

        // Status distribution
        $statusDistribution = $baseQuery()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Leads by source
        $leadsBySource = $baseQuery()
            ->select('source', DB::raw('count(*) as count'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Leads by course
        $leadsByCourse = $baseQuery()
            ->select('course', DB::raw('count(*) as count'))
            ->whereNotNull('course')
            ->groupBy('course')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Lead trend (daily)
        $leadTrend = $baseQuery()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Conversion rate
        $totalLeads = $baseQuery()->count();
        $convertedLeads = $baseQuery()->where('status', 'CONVERTED')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // Agent Performance Logic
        $agentPerformance = [];
        
        // Filter agents list for performance table if a specific agent is selected
        $performanceAgents = $selectedAgentId ? $agents->where('id', $selectedAgentId) : $agents;

        foreach ($performanceAgents as $agent) {
            // 1. Leads currently assigned
            $assignedLeadsCount = Lead::where('assigned_to', $agent->id)->count();

            // 2. Activity Metrics (Date Range)
            $activityBaseQuery = \App\Models\ActivityLog::where('user_id', $agent->id)
                ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);

            $statusChanges = (clone $activityBaseQuery)->where('action', 'STATUS_CHANGED')->count();
            $calls = (clone $activityBaseQuery)->where('action', 'CALL_INITIATED')->count();
            $whatsapp = (clone $activityBaseQuery)->where('action', 'WHATSAPP_CLICKED')->count();
            
            // "Leads Worked" - encompassing edits/updates.
            $leadsWorkedActions = (clone $activityBaseQuery)
                ->whereIn('action', ['STATUS_CHANGED', 'LEAD_UPDATED', 'NOTE_ADDED', 'FOLLOW_UP_SET', 'CALL_INITIATED', 'WHATSAPP_CLICKED'])
                ->count();

            $agentPerformance[] = [
                'name' => $agent->name,
                'assigned_leads' => $assignedLeadsCount,
                'leads_worked' => $leadsWorkedActions, // "Total Activities" in this period
                'status_changes' => $statusChanges,
                'calls' => $calls,
                'whatsapp' => $whatsapp,
            ];
        }

        return view('insights', compact(
            'statusDistribution',
            'leadsBySource',
            'leadsByCourse',
            'leadTrend',
            'totalLeads',
            'convertedLeads',
            'conversionRate',
            'startDate',
            'endDate',
            'agentPerformance',
            'agents',
            'selectedAgentId'
        ));
    }
}
