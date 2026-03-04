<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     * Uses optimized COUNT queries for performance
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;

        // For super admin, show system-wide stats
        if ($user->isSuperAdmin()) {
            return $this->superAdminStats();
        }

        // Get counts using efficient SQL aggregation
        $totalLeads = Lead::where('organization_id', $organizationId)->count();
        
        $todayLeads = Lead::where('organization_id', $organizationId)
            ->whereDate('created_at', today())
            ->count();

        $pendingTasks = Task::where('organization_id', $organizationId)
            ->where('status', 'PENDING')
            ->count();

        $pendingFollowUps = Lead::where('organization_id', $organizationId)
            ->whereNotNull('next_follow_up')
            ->where('next_follow_up', '<=', now())
            ->whereNotIn('status', ['CONVERTED', 'LOST'])
            ->count();

        $convertedLeads = Lead::where('organization_id', $organizationId)
            ->where('status', 'CONVERTED')
            ->count();

        // Today's follow-ups for the quick list
        $todayFollowUps = Lead::where('organization_id', $organizationId)
            ->whereDate('next_follow_up', today())
            ->with('assignedTo:id,name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'next_follow_up', 'assigned_to', 'status']);

        // Priority tasks for the quick list
        $priorityTasks = Task::where('organization_id', $organizationId)
            ->where('status', 'PENDING')
            ->where('priority', 'HIGH')
            ->with('assignedTo:id,name')
            ->orderBy('due_date')
            ->limit(10)
            ->get(['id', 'title', 'due_date', 'priority', 'assigned_to']);

        return response()->json([
            'totalLeads' => $totalLeads,
            'todayLeads' => $todayLeads,
            'pendingTasks' => $pendingTasks,
            'pendingFollowUps' => $pendingFollowUps,
            'convertedLeads' => $convertedLeads,
            'todayFollowUps' => $todayFollowUps,
            'priorityTasks' => $priorityTasks,
        ]);
    }

    /**
     * Super admin system-wide statistics
     */
    protected function superAdminStats()
    {
        $totalLeads = Lead::count();
        $totalVendors = DB::table('vendors')->where('is_deleted', false)->count();
        $activeSubscriptions = DB::table('subscriptions')
            ->where('status', 'ACTIVE')
            ->where('expiry_date', '>=', now())
            ->count();
        $totalUsers = DB::table('users')->count();

        // Lead growth for last 30 days
        $leadGrowth = Lead::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'totalLeads' => $totalLeads,
            'totalVendors' => $totalVendors,
            'activeSubscriptions' => $activeSubscriptions,
            'totalUsers' => $totalUsers,
            'leadGrowth' => $leadGrowth,
        ]);
    }

    /**
     * Get insights data with groupBy queries
     */
    public function insights(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;

        // Date range filter
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $query = Lead::query();
        
        if (!$user->isSuperAdmin()) {
            $query->where('organization_id', $organizationId);
        }

        $query->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        // Leads by status (for pie chart)
        $leadsByStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Leads by source (for bar chart)
        $leadsBySource = (clone $query)
            ->select('source', DB::raw('COUNT(*) as count'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->get();

        // Calculate conversion rate
        $totalLeads = (clone $query)->count();
        $convertedLeads = (clone $query)->where('status', 'CONVERTED')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;

        // Leads by course
        $leadsByCourse = (clone $query)
            ->select('course', DB::raw('COUNT(*) as count'))
            ->whereNotNull('course')
            ->groupBy('course')
            ->get();

        // Leads trend over time
        $leadsTrend = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'leadsByStatus' => $leadsByStatus,
            'leadsBySource' => $leadsBySource,
            'leadsByCourse' => $leadsByCourse,
            'leadsTrend' => $leadsTrend,
            'conversionRate' => $conversionRate,
            'totalLeads' => $totalLeads,
            'convertedLeads' => $convertedLeads,
        ]);
    }
}
