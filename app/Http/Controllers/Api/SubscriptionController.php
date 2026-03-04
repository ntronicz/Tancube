<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Vendor;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Get paginated subscriptions (Super Admin only)
     */
    public function index(Request $request)
    {
        $query = Subscription::query();

        // Filter by vendor
        if ($request->has('vendor_id') && $request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $perPage = min($request->input('limit', 50), 100);
        $subscriptions = $query->with('vendor:id,name,email')
            ->orderBy('expiry_date', 'desc')
            ->paginate($perPage);

        return response()->json($subscriptions);
    }

    /**
     * Store a new subscription
     */
    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|uuid|exists:vendors,id',
            'plan_name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after:start_date',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:MONTHLY,QUARTERLY,YEARLY',
        ]);

        $subscription = Subscription::create([
            'vendor_id' => $request->vendor_id,
            'plan_name' => $request->plan_name,
            'start_date' => $request->start_date,
            'expiry_date' => $request->expiry_date,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'status' => 'ACTIVE',
        ]);

        $vendor = Vendor::find($request->vendor_id);
        ActivityLog::log('SUBSCRIPTION_CREATED', "Created subscription for vendor: {$vendor->name}", null, null);

        return response()->json($subscription, 201);
    }

    /**
     * Show a subscription
     */
    public function show(string $id)
    {
        $subscription = Subscription::with('vendor')->findOrFail($id);
        return response()->json($subscription);
    }

    /**
     * Update a subscription
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'plan_name' => 'sometimes|string|max:100',
            'start_date' => 'sometimes|date',
            'expiry_date' => 'sometimes|date',
            'amount' => 'sometimes|numeric|min:0',
            'frequency' => 'sometimes|in:MONTHLY,QUARTERLY,YEARLY',
            'status' => 'sometimes|in:ACTIVE,EXPIRED,CANCELLED',
        ]);

        $subscription = Subscription::findOrFail($id);
        $subscription->update($request->only([
            'plan_name', 'start_date', 'expiry_date', 'amount', 'frequency', 'status',
        ]));

        ActivityLog::log('SUBSCRIPTION_UPDATED', "Updated subscription ID: {$id}", null, null);

        return response()->json($subscription);
    }

    /**
     * Delete a subscription
     */
    public function destroy(string $id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();

        ActivityLog::log('SUBSCRIPTION_DELETED', "Deleted subscription ID: {$id}", null, null);

        return response()->json(['message' => 'Subscription deleted successfully']);
    }

    /**
     * Cancel a subscription
     */
    public function cancel(string $id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->update(['status' => 'CANCELLED']);

        ActivityLog::log('SUBSCRIPTION_CANCELLED', "Cancelled subscription ID: {$id}", null, null);

        return response()->json(['message' => 'Subscription cancelled successfully']);
    }

    /**
     * Renew a subscription
     */
    public function renew(Request $request, string $id)
    {
        $request->validate([
            'expiry_date' => 'required|date|after:today',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $subscription = Subscription::findOrFail($id);
        $subscription->update([
            'start_date' => now(),
            'expiry_date' => $request->expiry_date,
            'amount' => $request->amount ?? $subscription->amount,
            'status' => 'ACTIVE',
        ]);

        ActivityLog::log('SUBSCRIPTION_RENEWED', "Renewed subscription ID: {$id}", null, null);

        return response()->json($subscription);
    }
}
