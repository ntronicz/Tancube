<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Lead;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallLogController extends Controller
{
    /**
     * Sync call log from mobile app
     * POST /api/call-logs/sync
     */
    public function sync(Request $request)
    {
        $request->validate([
            'calls' => 'required|array|min:1|max:50',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $synced = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($request->calls as $callData) {
                // Manually validate individual calls so a bad call doesn't fail the whole batch
                if (!isset($callData['phone_number']) || trim($callData['phone_number']) === '') {
                    $skipped++;
                    continue;
                }
                // Duplicate check: same user, phone, duration within ±60 second window
                // This catches duplicates from different sync sources with slightly different timestamps
                $callTimestamp = $callData['call_timestamp'];
                $exists = CallLog::where('user_id', $user->id)
                    ->where('phone_number', $callData['phone_number'])
                    ->where('duration', $callData['duration'] ?? 0)
                    ->whereBetween('call_timestamp', [
                        date('Y-m-d H:i:s', strtotime($callTimestamp) - 60),
                        date('Y-m-d H:i:s', strtotime($callTimestamp) + 60),
                    ])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Auto-match lead by phone
                $leadId = CallLog::matchLead($callData['phone_number'], $user->organization_id);

                try {
                    CallLog::create([
                        'user_id' => $user->id,
                        'organization_id' => $user->organization_id,
                        'phone_number' => $callData['phone_number'],
                        'call_type' => $callData['call_type'],
                        'call_status' => $callData['call_status'],
                        'duration' => $callData['duration'],
                        'call_timestamp' => $callData['call_timestamp'],
                        'lead_id' => $leadId,
                        'notes' => $callData['notes'] ?? null,
                        'device_id' => $callData['device_id'] ?? null,
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to sync individual call log for user {$user->id}: " . $e->getMessage(), ['callData' => $callData]);
                    $skipped++;
                    continue;
                }

                // Update lead's last_contacted if matched
                if ($leadId) {
                    Lead::where('id', $leadId)->update([
                        'last_contacted' => $callData['call_timestamp'],
                    ]);
                }

                $synced++;
            }

            DB::commit();

            ActivityLog::log('CALLS_SYNCED', "Synced {$synced} call logs from mobile");

            return response()->json([
                'message' => "Synced {$synced} calls. Skipped {$skipped} duplicates.",
                'synced' => $synced,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get call logs (paginated)
     * GET /api/call-logs
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = CallLog::query();

        if (!$user->isSuperAdmin()) {
            $query->where('organization_id', $user->organization_id);
        }

        // Agents see only their own calls
        if ($user->isAgent()) {
            $query->where('user_id', $user->id);
        }

        // Filter by agent
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by call type
        if ($request->has('call_type') && $request->call_type) {
            $query->where('call_type', $request->call_type);
        }

        // Filter by call status
        if ($request->has('call_status') && $request->call_status) {
            $query->where('call_status', $request->call_status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('call_timestamp', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('call_timestamp', '<=', $request->end_date);
        }

        $perPage = min($request->input('limit', 25), 100);
        $callLogs = $query->with(['user:id,name', 'lead:id,name,phone'])
            ->orderBy('call_timestamp', 'desc')
            ->paginate($perPage);

        return response()->json($callLogs);
    }

    /**
     * Get call metrics/stats
     * GET /api/call-logs/stats
     */
    public function stats(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $startDate = $request->input('start_date', today()->toDateString());
        $endDate = $request->input('end_date', today()->toDateString());

        $query = CallLog::query();

        if (!$user->isSuperAdmin()) {
            $query->where('organization_id', $user->organization_id);
        }

        if ($user->isAgent()) {
            $query->where('user_id', $user->id);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $query->whereDate('call_timestamp', '>=', $startDate)
            ->whereDate('call_timestamp', '<=', $endDate);

        // Aggregate stats
        $totalDials = (clone $query)->count();
        $connectedCalls = (clone $query)->where('call_status', 'CONNECTED')->count();
        $totalDuration = (clone $query)->whereNotNull('lead_id')->sum('duration');
        $missedCalls = (clone $query)->where('call_type', 'MISSED')->count();
        $leadConnected = (clone $query)->where('call_status', 'CONNECTED')->whereNotNull('lead_id')->count();
        $avgDuration = $leadConnected > 0 ? round($totalDuration / $leadConnected) : 0;

        // Outbound vs Inbound breakdown
        $outbound = (clone $query)->where('call_type', 'OUTBOUND')->count();
        $inbound = (clone $query)->where('call_type', 'INBOUND')->count();

        // By status breakdown
        $byStatus = (clone $query)
            ->select('call_status', DB::raw('COUNT(*) as count'))
            ->groupBy('call_status')
            ->get();

        // Agent leaderboard (Admin view)
        $agentStats = [];
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $agentQuery = CallLog::query();
            if (!$user->isSuperAdmin()) {
                $agentQuery->where('organization_id', $user->organization_id);
            }
            $agentQuery->whereDate('call_timestamp', '>=', $startDate)
                ->whereDate('call_timestamp', '<=', $endDate);

            $agentStats = $agentQuery
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as total_dials'),
                    DB::raw("SUM(CASE WHEN call_status = 'CONNECTED' THEN 1 ELSE 0 END) as connected"),
                    DB::raw('SUM(duration) as total_duration')
                )
                ->groupBy('user_id')
                ->with('user:id,name')
                ->orderByDesc('total_dials')
                ->get();
        }

        return response()->json([
            'totalDials' => $totalDials,
            'connectedCalls' => $connectedCalls,
            'totalDuration' => $totalDuration,
            'totalDurationFormatted' => $this->formatDuration($totalDuration),
            'missedCalls' => $missedCalls,
            'avgDuration' => $avgDuration,
            'avgDurationFormatted' => $this->formatDuration($avgDuration),
            'outbound' => $outbound,
            'inbound' => $inbound,
            'byStatus' => $byStatus,
            'agentStats' => $agentStats,
            'connectRate' => $totalDials > 0 ? round(($connectedCalls / $totalDials) * 100, 1) : 0,
        ]);
    }

    /**
     * Register/update FCM device token
     * POST /api/device-token
     */
    public function registerDeviceToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|max:500',
            'device_id' => 'nullable|string|max:100',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['fcm_token' => $request->token]);

        return response()->json(['message' => 'Device token registered']);
    }

    /**
     * Get user notifications
     * GET /api/notifications
     */
    public function notifications(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $perPage = min($request->input('limit', 20), 50);
        $notifications = $user->notifications()
            ->paginate($perPage);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark notifications as read
     * POST /api/notifications/read
     */
    public function markNotificationsRead(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->has('ids') && is_array($request->ids)) {
            $user->notifications()->whereIn('id', $request->ids)->update(['read_at' => now()]);
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['message' => 'Notifications marked as read']);
    }

    /**
     * Format seconds to human-readable
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
