<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get paginated users
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = User::query();

        // Super admin sees all users
        if (!$user->isSuperAdmin()) {
            $query->where('organization_id', $user->organization_id);
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
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
        $users = $query->with('organization:id,name')
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:ADMIN,AGENT',
            'organization_id' => 'nullable|uuid|exists:vendors,id',
        ]);

        $authUser = Auth::user();

        // Only super admin can create users for other organizations
        $organizationId = $authUser->isSuperAdmin() 
            ? $request->organization_id 
            : $authUser->organization_id;

        $user = User::create([
            'organization_id' => $organizationId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        ActivityLog::log('USER_CREATED', "Created user: {$user->name}");

        return response()->json($user, 201);
    }

    /**
     * Show a user
     */
    public function show(string $id)
    {
        $user = User::with('organization:id,name')->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update a user
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|in:ADMIN,AGENT',
            'avatar' => 'nullable|string',
        ]);

        $user = User::findOrFail($id);

        $data = $request->only(['name', 'email', 'role', 'avatar']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        ActivityLog::log('USER_UPDATED', "Updated user: {$user->name}");

        return response()->json($user);
    }

    /**
     * Delete a user
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting super admin
        if ($user->isSuperAdmin()) {
            return response()->json(['message' => 'Cannot delete super admin'], 403);
        }

        $userName = $user->name;
        $user->delete();

        ActivityLog::log('USER_DELETED', "Deleted user: {$userName}");

        return response()->json(['message' => 'User deleted successfully']);
    }
}
