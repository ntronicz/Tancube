<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    /**
     * Get paginated vendors (Super Admin only)
     */
    public function index(Request $request)
    {
        $query = Vendor::where('is_deleted', false);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('limit', 50), 100);
        $vendors = $query->withCount('users')
            ->with('subscriptions')
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($vendors);
    }

    /**
     * Store a new vendor
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:vendors,email',
            'phone' => 'nullable|string|max:20',
        ]);

        $vendor = Vendor::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => 'ACTIVE',
        ]);

        ActivityLog::log('VENDOR_CREATED', "Created vendor: {$vendor->name}", null, null);

        return response()->json($vendor, 201);
    }

    /**
     * Show a vendor
     */
    public function show(string $id)
    {
        $vendor = Vendor::with(['subscriptions', 'users'])
            ->withCount(['leads', 'tasks'])
            ->findOrFail($id);

        return response()->json($vendor);
    }

    /**
     * Update a vendor
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:vendors,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'status' => 'sometimes|in:ACTIVE,INACTIVE',
        ]);

        $vendor = Vendor::findOrFail($id);
        $vendor->update($request->only(['name', 'email', 'phone', 'status']));

        ActivityLog::log('VENDOR_UPDATED', "Updated vendor: {$vendor->name}", null, null);

        return response()->json($vendor);
    }

    /**
     * Soft delete a vendor
     */
    public function destroy(string $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update(['is_deleted' => true, 'status' => 'INACTIVE']);

        ActivityLog::log('VENDOR_DELETED', "Deleted vendor: {$vendor->name}", null, null);

        return response()->json(['message' => 'Vendor deleted successfully']);
    }

    /**
     * Block a vendor
     */
    public function block(string $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update(['status' => 'INACTIVE']);

        ActivityLog::log('VENDOR_BLOCKED', "Blocked vendor: {$vendor->name}", null, null);

        return response()->json(['message' => 'Vendor blocked successfully']);
    }

    /**
     * Activate a vendor
     */
    public function activate(string $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update(['status' => 'ACTIVE']);

        ActivityLog::log('VENDOR_ACTIVATED', "Activated vendor: {$vendor->name}", null, null);

        return response()->json(['message' => 'Vendor activated successfully']);
    }
}
