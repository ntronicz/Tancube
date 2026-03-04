<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Vendor;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'SUPER_ADMIN') {
            return redirect()->route('admin.dashboard');
        }

        $organizationId = $user->organization_id;

        // Get dashboard stats
        $stats = $this->getOrganizationStats($organizationId, $user);
        
        $todayFollowUps = $this->getTodayFollowUps($organizationId, $user);
        $priorityTasks = $this->getPriorityTasks($organizationId, $user);

        // Check for expiring subscription
        $activeSubscription = Subscription::where('vendor_id', $organizationId)
            ->where('status', 'ACTIVE')
            ->latest('expiry_date')
            ->first();
            
        $subscriptionWarning = null;
        if ($activeSubscription && $activeSubscription->expiry_date) {
            $daysLeft = (int) round(now()->diffInDays(\Carbon\Carbon::parse($activeSubscription->expiry_date), false));
            if ($daysLeft >= 0 && $daysLeft <= 3) {
                $subscriptionWarning = [
                    'days_left' => $daysLeft,
                    'expiry_date' => $activeSubscription->expiry_date
                ];
            }
        }

        return view('dashboard', compact('stats', 'todayFollowUps', 'priorityTasks', 'subscriptionWarning'));
    }

    /**
     * Super Admin Dashboard
     */
    protected function superAdminDashboard()
    {
        $totalVendors = Vendor::where('is_deleted', false)->count();
        $activeVendors = Vendor::where('is_deleted', false)->where('status', 'ACTIVE')->count();
        $totalLeads = Lead::count();
        $activeSubscriptions = Subscription::where('status', 'ACTIVE')->count();

        // Growth rate calculation (compare this month vs last month)
        $thisMonthLeads = Lead::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonthLeads = Lead::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        $leadsGrowth = $lastMonthLeads > 0 ? round((($thisMonthLeads - $lastMonthLeads) / $lastMonthLeads) * 100, 1) : 0;

        $thisMonthVendors = Vendor::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $lastMonthVendors = Vendor::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count();
        $vendorsGrowth = $lastMonthVendors > 0 ? round((($thisMonthVendors - $lastMonthVendors) / $lastMonthVendors) * 100, 1) : 0;

        // Monthly leads trend (last 6 months)
        $leadsGrowthData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Lead::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $leadsGrowthData[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }

        // Recent vendors
        $recentVendors = Vendor::where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'email', 'status', 'created_at']);

        // Vendor by status breakdown
        $vendorsByStatus = Vendor::where('is_deleted', false)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.dashboard', compact(
            'totalVendors',
            'activeVendors',
            'totalLeads',
            'activeSubscriptions',
            'leadsGrowth',
            'vendorsGrowth',
            'leadsGrowthData',
            'recentVendors',
            'vendorsByStatus'
        ));
    }

    /**
     * Get stats for organization
     */
    protected function getOrganizationStats($organizationId, $user = null)
    {
        $baseLeadQuery = Lead::where('organization_id', $organizationId);
        $baseTaskQuery = Task::where('organization_id', $organizationId);

        if ($user && $user->role === 'AGENT') {
            $baseLeadQuery->where('assigned_to', $user->id);
            $baseTaskQuery->where('assigned_to', $user->id);
        }

        return [
            'totalLeads' => (clone $baseLeadQuery)->count(),
            'todayLeads' => (clone $baseLeadQuery)
                ->whereDate('created_at', today())
                ->count(),
            'pendingTasks' => (clone $baseTaskQuery)
                ->where('status', 'PENDING')
                ->count(),
            'pendingFollowUps' => (clone $baseLeadQuery)
                ->whereNotNull('next_follow_up')
                ->where('next_follow_up', '<=', now())
                ->whereNotIn('status', ['CONVERTED', 'LOST', 'Not interested', 'NOT INTERESTED'])
                ->count(),
            'convertedLeads' => (clone $baseLeadQuery)
                ->where('status', 'CONVERTED')
                ->count(),
        ];
    }

    /**
     * Get Super Admin stats (all organizations)
     */
    protected function getSuperAdminStats()
    {
        return [
            'totalLeads' => Lead::count(),
            'todayLeads' => Lead::whereDate('created_at', today())->count(),
            'pendingTasks' => Task::where('status', 'PENDING')->count(),
            'pendingFollowUps' => Lead::whereNotNull('next_follow_up')
                ->where('next_follow_up', '<=', now())
                ->whereNotIn('status', ['CONVERTED', 'LOST'])
                ->count(),
            'convertedLeads' => Lead::where('status', 'CONVERTED')->count(),
        ];
    }

    /**
     * Get today's follow-ups
     */
    protected function getTodayFollowUps($organizationId, $user)
    {
        $query = Lead::where('organization_id', $organizationId)
            ->whereNotNull('next_follow_up')
            ->where('next_follow_up', '<=', now()->endOfDay())
            ->whereNotIn('status', ['CONVERTED', 'LOST', 'Not interested', 'NOT INTERESTED']);

        if ($user->role === 'AGENT') {
            $query->where('assigned_to', $user->id);
        }

        return $query->with('assignedTo:id,name')
            ->orderBy('next_follow_up')
            ->limit(10)
            ->get();
    }

    /**
     * Get priority tasks
     */
    protected function getPriorityTasks($organizationId, $user)
    {
        $query = Task::where('organization_id', $organizationId)
            ->where('status', 'PENDING')
            ->where('priority', 'HIGH');

        if ($user->role === 'AGENT') {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        return $query->orderBy('due_date')
            ->limit(10)
            ->get();
    }
}
