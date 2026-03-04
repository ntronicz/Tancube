<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Get all settings for the organization
     */
    public function index()
    {
        $user = Auth::user();
        $orgId = $user->organization_id;

        return response()->json([
            'sources' => AppSetting::getSources($orgId),
            'courses' => AppSetting::getCourses($orgId),
            'statuses' => AppSetting::getStatuses($orgId),
        ]);
    }

    /**
     * Get sources
     */
    public function getSources()
    {
        $user = Auth::user();
        return response()->json(AppSetting::getSources($user->organization_id));
    }

    /**
     * Update sources
     */
    public function updateSources(Request $request)
    {
        $request->validate([
            'values' => 'required|array',
            'values.*' => 'string|max:100',
        ]);

        $user = Auth::user();
        AppSetting::setForOrganization($user->organization_id, 'sources', $request->values);

        ActivityLog::log('SETTINGS_UPDATED', 'Updated lead sources');

        return response()->json(['message' => 'Sources updated successfully']);
    }

    /**
     * Get courses
     */
    public function getCourses()
    {
        $user = Auth::user();
        return response()->json(AppSetting::getCourses($user->organization_id));
    }

    /**
     * Update courses
     */
    public function updateCourses(Request $request)
    {
        $request->validate([
            'values' => 'required|array',
            'values.*' => 'string|max:100',
        ]);

        $user = Auth::user();
        AppSetting::setForOrganization($user->organization_id, 'courses', $request->values);

        ActivityLog::log('SETTINGS_UPDATED', 'Updated courses');

        return response()->json(['message' => 'Courses updated successfully']);
    }

    /**
     * Get statuses
     */
    public function getStatuses()
    {
        $user = Auth::user();
        return response()->json(AppSetting::getStatuses($user->organization_id));
    }

    /**
     * Update statuses
     */
    public function updateStatuses(Request $request)
    {
        $request->validate([
            'values' => 'required|array',
            'values.*' => 'string|max:100',
        ]);

        $user = Auth::user();
        AppSetting::setForOrganization($user->organization_id, 'statuses', $request->values);

        ActivityLog::log('SETTINGS_UPDATED', 'Updated lead statuses');

        return response()->json(['message' => 'Statuses updated successfully']);
    }
}
