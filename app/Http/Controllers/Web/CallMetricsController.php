<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CallMetricsController extends Controller
{
    /**
     * Call Metrics Dashboard
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $organizationId = $user->organization_id;

        // Date range
        $startDate = $request->input('start_date', today()->toDateString());
        $endDate = $request->input('end_date', today()->toDateString());
        $agentFilter = $request->input('agent_id', null);

        // Base query
        $query = CallLog::where('organization_id', $organizationId);

        if ($user->role === 'AGENT') {
            $query->where('user_id', $user->id);
        } elseif ($agentFilter) {
            $query->where('user_id', $agentFilter);
        }

        $query->whereDate('call_timestamp', '>=', $startDate)
            ->whereDate('call_timestamp', '<=', $endDate);

        // Stats
        $totalDials = (clone $query)->count();
        $connectedCalls = (clone $query)->where('call_status', 'CONNECTED')->count();
        $notConnectedCalls = (clone $query)->where('call_status', '!=', 'CONNECTED')->count();
        $totalDuration = (clone $query)->whereNotNull('lead_id')->sum('duration');
        $missedCalls = (clone $query)->where('call_type', 'MISSED')->count();
        $leadConnected = (clone $query)->where('call_status', 'CONNECTED')->whereNotNull('lead_id')->count();
        $avgDuration = $leadConnected > 0 ? round($totalDuration / $leadConnected) : 0;
        $connectRate = $totalDials > 0 ? round(($connectedCalls / $totalDials) * 100, 1) : 0;

        // By status breakdown
        $byStatus = (clone $query)
            ->select('call_status', DB::raw('COUNT(*) as count'))
            ->groupBy('call_status')
            ->pluck('count', 'call_status')
            ->toArray();

        // Agent leaderboard (for Admins)
        $agentStats = [];
        if ($user->role === 'ADMIN') {
            $agentStats = CallLog::where('organization_id', $organizationId)
                ->whereDate('call_timestamp', '>=', $startDate)
                ->whereDate('call_timestamp', '<=', $endDate)
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as total_dials'),
                    DB::raw("SUM(CASE WHEN call_status = 'CONNECTED' THEN 1 ELSE 0 END) as connected"),
                    DB::raw('SUM(duration) as total_duration')
                )
                ->groupBy('user_id')
                ->orderByDesc('total_dials')
                ->get()
                ->map(function ($row) {
                    $row->user = User::find($row->user_id);
                    $row->connect_rate = $row->total_dials > 0
                        ? round(($row->connected / $row->total_dials) * 100, 1) : 0;
                    $row->duration_formatted = $this->formatDuration($row->total_duration);
                    return $row;
                });
        }

        // Recent call logs
        $recentCallsQuery = (clone $query)
            ->with(['user:id,name', 'lead:id,name,phone'])
            ->orderBy('call_timestamp', 'desc');

        if ($user->role === 'AGENT') {
            $recentCallsQuery->whereNotNull('lead_id');
        }

        $recentCalls = $recentCallsQuery
            ->paginate(50)
            ->withQueryString();

        // Hourly distribution (for chart)
        $hourlyData = (clone $query)
            ->select(DB::raw('HOUR(call_timestamp) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Agents list for filter dropdown
        $agents = User::where('organization_id', $organizationId)
            ->where('role', 'AGENT')
            ->get(['id', 'name']);

        return view('call-metrics', compact(
            'totalDials', 'connectedCalls', 'notConnectedCalls', 'totalDuration', 'missedCalls',
            'avgDuration', 'connectRate', 'byStatus', 'agentStats',
            'recentCalls', 'hourlyData', 'agents',
            'startDate', 'endDate', 'agentFilter'
        ));
    }

    /**
     * Format seconds to readable duration
     */
    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        if ($minutes > 0) {
            return "{$minutes}m {$secs}s";
        }
        return "{$secs}s";
    }
}
