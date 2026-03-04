<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Get paginated activity logs
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ActivityLog::query();

        // Organization scope
        if (!$user->isSuperAdmin()) {
            $query->where('organization_id', $user->organization_id);
        }

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('timestamp', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('timestamp', '<=', $request->end_date);
        }

        $perPage = min($request->input('limit', 50), 100);
        $logs = $query->with('user:id,name')
            ->orderBy('timestamp', 'desc')
            ->paginate($perPage);

        return response()->json($logs);
    }
}
