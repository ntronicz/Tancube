<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Lead;
use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Super Admin Dashboard
     */
    public function index()
    {
        $totalVendors = Vendor::where('is_deleted', false)->count();
        $activeVendors = Vendor::where('is_deleted', false)
            ->where('status', 'ACTIVE')
            ->whereHas('subscriptions', function ($query) {
                $query->where('status', 'ACTIVE')
                      ->where('expiry_date', '>=', now());
            })
            ->count();
            
        $totalLeads = Lead::count();
        
        // Get recent vendors
        $recentVendors = Vendor::where('is_deleted', false)
            ->with(['subscriptions' => function($q) {
                $q->where('status', 'ACTIVE')
                  ->where('expiry_date', '>=', now());
            }])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get expiring subscriptions (next 30 days)
        $expiringSubscriptions = Subscription::with('vendor')
            ->where('status', 'ACTIVE')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->take(5)
            ->get();

        // Leads Chart Data (Last 12 Months)
        $leadsData = Lead::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $chartLabels = [];
        $chartValues = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $chartLabels[] = $date->format('M');
            $chartValues[] = $leadsData[$monthKey] ?? 0;
        }

        return view('admin.dashboard', compact(
            'totalVendors',
            'activeVendors',
            'totalLeads',
            'recentVendors',
            'expiringSubscriptions',
            'chartLabels',
            'chartValues'
        ));
    }

    /**
     * Show vendors list
     */
    public function vendors(Request $request)
    {
        $query = Vendor::where('is_deleted', false);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $vendors = $query->with('subscriptions')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.vendors', compact('vendors'));
    }

    /**
     * Store new vendor
     */
    public function storeVendor(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('vendors')->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
        ];

        // Validate admin credentials if checkbox is checked
        if ($request->has('create_admin')) {
            $rules['admin_password'] = 'required|string|min:8';
            $rules['admin_email'] = [
                'nullable', 
                'email', 
                'unique:users,email', // Check if explicit admin email is unique
            ];
            
            // If admin_email is empty, the vendor email will be used.
            // We must check if the vendor email is already taken in the users table.
            if (!$request->filled('admin_email')) {
                $rules['email'][] = 'unique:users,email';
            }
        }

        $request->validate($rules);

        $vendor = Vendor::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status ?? 'ACTIVE',
        ]);

        // Create admin user for vendor if requested
        if ($request->has('create_admin') && $request->filled('admin_password')) {
            $adminEmail = $request->admin_email ?: $request->email;
            
            User::create([
                'organization_id' => $vendor->id,
                'name' => $request->name . ' Admin',
                'email' => $adminEmail,
                'password' => Hash::make($request->admin_password),
                'role' => 'ADMIN',
            ]);
        }

        return back()->with('success', 'Vendor created successfully!');
    }

    /**
     * Show vendor details
     */
    public function showVendor(string $id)
    {
        $vendor = Vendor::with(['users', 'subscriptions', 'leads', 'tasks'])->findOrFail($id);
        return view('admin.vendor-show', compact('vendor'));
    }

    /**
     * Toggle vendor status
     */
    public function toggleVendor(string $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->status = $vendor->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $vendor->save();

        return back()->with('success', 'Vendor status updated!');
    }

    /**
     * Show subscriptions list
     */
    public function subscriptions(Request $request)
    {
        $subscriptions = Subscription::with(['vendor', 'plan' => function($q) { }]) // assuming relationship added later, otherwise we'll fetch explicitly
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $vendors = Vendor::where('is_deleted', false)->get(['id', 'name']);
        $plans = Plan::where('is_active', true)->get();

        return view('admin.subscriptions', compact('subscriptions', 'vendors', 'plans'));
    }

    /**
     * Store new subscription
     */
    public function storeSubscription(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|uuid|exists:vendors,id',
            'plan_id' => 'required|exists:plans,id',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after:start_date',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:MONTHLY,QUARTERLY,YEARLY,ONE_TIME',
            'status' => 'nullable|in:ACTIVE,EXPIRED,CANCELLED',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        Subscription::create([
            'vendor_id' => $request->vendor_id,
            'plan_id' => $plan->id,
            'plan_name' => $plan->name, // Keep for backward compatibility or display
            'max_admins' => $plan->max_admins,
            'max_agents' => $plan->max_agents,
            'start_date' => Carbon::parse($request->start_date),
            'expiry_date' => Carbon::parse($request->expiry_date),
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'status' => $request->status ?? 'ACTIVE',
        ]);

        return back()->with('success', 'Subscription created successfully!');
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(string $id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->status = 'CANCELLED';
        $subscription->save();

        return back()->with('success', 'Subscription cancelled!');
    }

    /**
     * Update Subscription Limits manually
     */
    public function updateSubscriptionLimits(Request $request, string $id)
    {
        $request->validate([
            'max_admins' => 'required|integer|min:1',
            'max_agents' => 'required|integer|min:1',
        ]);

        $subscription = Subscription::findOrFail($id);
        $subscription->update([
            'max_admins' => $request->max_admins,
            'max_agents' => $request->max_agents,
        ]);

        return back()->with('success', 'Subscription limits updated successfully!');
    }

    /**
     * Renew subscription
     */
    public function renewSubscription(string $id)
    {
        $subscription = Subscription::findOrFail($id);

        $months = match ($subscription->frequency) {
            'MONTHLY' => 1,
            'QUARTERLY' => 3,
            'YEARLY' => 12,
            default => 1,
        };

        Subscription::create([
            'vendor_id' => $subscription->vendor_id,
            'plan_name' => $subscription->plan_name,
            'start_date' => now(),
            'expiry_date' => now()->addMonths($months),
            'amount' => $subscription->amount,
            'frequency' => $subscription->frequency,
            'status' => 'ACTIVE',
        ]);

        return back()->with('success', 'Subscription renewed!');
    }
    /**
     * Update vendor details
     */
    public function updateVendor(Request $request, string $id)
    {
        $vendor = Vendor::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('vendors')->ignore($id)->where(function ($query) {
                    return $query->where('is_deleted', false);
                }),
            ],
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
        ];
        
        // Admin credentials validation
        if ($request->filled('admin_password')) {
            $rules['admin_password'] = 'required|string|min:8';
        }
        
        // If updating admin email (optional)
        if ($request->filled('admin_email')) {
             $rules['admin_email'] = 'email|unique:users,email'; // Assuming we don't know the admin ID easily here to ignore.
             // Ideally we should find the admin user and ignore their ID, but simpler to just require unique for now on change.
             // If they keep it same, they shouldn't send 'admin_email' field unless they changed it.
             // But the form probably sends it.
             
             // Better: Find the admin user first
             $admin = User::where('organization_id', $vendor->id)->where('role', 'ADMIN')->first();
             if ($admin) {
                 $rules['admin_email'] = ['email', Rule::unique('users', 'email')->ignore($admin->id)];
             } else {
                 $rules['admin_email'] = 'email|unique:users,email';
             }
        }

        $request->validate($rules);

        $vendor->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
        ]);

        // Update Admin User if password provided
        if ($request->filled('admin_password')) {
            $admin = User::where('organization_id', $vendor->id)
                ->where('role', 'ADMIN')
                ->first();
            
            if ($admin) {
                $admin->update([
                    'password' => Hash::make($request->admin_password),
                    'email' => $request->admin_email ?: $request->email, // Optional update admin email
                ]);
            }
        }

        return back()->with('success', 'Vendor updated successfully!');
    }

    /**
     * Delete vendor
     */
    public function destroyVendor(string $id)
    {
        $vendor = Vendor::findOrFail($id);
        
        // Soft delete vendor (and related data via cascading if configured, or manually)
        // Since we check is_deleted in queries, we can just set that flag
        $vendor->is_deleted = true;
        
        // Append timestamp to email to release unique constraint
        $vendor->email = $vendor->email . '::deleted_' . time();
        $vendor->save();
        
        // Ideally we should soft-delete or disable the users too
        User::where('organization_id', $vendor->id)->delete(); // Soft delete users
        Subscription::where('vendor_id', $vendor->id)->update(['status' => 'CANCELLED']);

        return back()->with('success', 'Vendor deleted successfully!');
    }

    /**
     * Show Plans List
     */
    public function plans()
    {
        $plans = \App\Models\Plan::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.plans', compact('plans'));
    }

    /**
     * Store new Plan
     */
    public function storePlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:plans,name',
            'max_admins' => 'required|integer|min:1',
            'max_agents' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,yearly,one_time',
            'is_active' => 'nullable|boolean',
        ]);

        \App\Models\Plan::create([
            'name' => $request->name,
            'max_admins' => $request->max_admins,
            'max_agents' => $request->max_agents,
            'price' => $request->price,
            'frequency' => $request->frequency,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return back()->with('success', 'Plan created successfully!');
    }

    /**
     * Update an existing Plan
     */
    public function updatePlan(Request $request, string $id)
    {
        $plan = \App\Models\Plan::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('plans')->ignore($plan->id)],
            'max_admins' => 'required|integer|min:1',
            'max_agents' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,yearly,one_time',
            'is_active' => 'nullable|boolean',
        ]);

        $plan->update([
            'name' => $request->name,
            'max_admins' => $request->max_admins,
            'max_agents' => $request->max_agents,
            'price' => $request->price,
            'frequency' => $request->frequency,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Plan updated successfully!');
    }

    /**
     * Toggle plan status
     */
    public function togglePlan(string $id)
    {
        $plan = Plan::findOrFail($id);
        $plan->is_active = !$plan->is_active;
        $plan->save();

        return back()->with('success', 'Plan status updated!');
    }

    /**
     * Delete Plan
     */
    public function destroyPlan(string $id)
    {
        $plan = Plan::findOrFail($id);
        
        // Prevent deleting plan if it is used by any active subscriptions
        $isUsed = Subscription::where('plan_id', $plan->id)->exists();
        if ($isUsed) {
            return back()->with('error', 'Cannot delete plan as it is linked to existing subscriptions.');
        }

        $plan->delete();
        return back()->with('success', 'Plan deleted successfully!');
    }
}
