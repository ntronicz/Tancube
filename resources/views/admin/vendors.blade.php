@extends('layouts.app')

@section('title', 'Vendors - Tancube CRM')
@section('page-title', 'Vendor Management')

@section('content')
<div class="space-y-6 animate-fade-in pb-48">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center gap-4">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Vendors <span class="text-gray-400 text-lg font-medium">({{ $vendors->total() }})</span></h1>
        </div>
        <button onclick="document.getElementById('addVendorModal').style.display='flex'" 
                class="px-5 py-2.5 rounded-2xl bg-gray-900 text-white hover:bg-gray-800 shadow-lg shadow-gray-200 text-sm font-semibold transition-all flex items-center">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add New Vendor
        </button>
    </div>
    
    <!-- Controls & Filters -->
    <div class="bg-white rounded-3xl p-5 shadow-soft border border-gray-100">
        <form method="GET" action="{{ route('admin.vendors') }}" class="flex flex-col md:flex-row gap-4 justify-between">
            <!-- Search -->
            <div class="relative flex-1 max-w-lg" x-data="{ searchQuery: '{{ request('search') }}' }">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                <input 
                    type="text" 
                    name="search" 
                    x-model="searchQuery"
                    placeholder="Search name, email, phone..."
                    class="w-full pl-11 pr-10 py-3 rounded-2xl bg-gray-50 border-none text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none"
                >
                <button type="button" 
                        x-show="searchQuery.length > 0" 
                        x-cloak
                        @click="searchQuery = ''; $nextTick(() => { $el.closest('form').submit(); });" 
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Vendors Table -->
    <div class="bg-white rounded-3xl shadow-soft border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Organization</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Subscription</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($vendors as $vendor)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold text-sm mr-3">
                                    {{ strtoupper(substr($vendor->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-gray-900 font-semibold">{{ $vendor->name }}</p>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">ID: {{ Str::limit($vendor->id, 8) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 mr-2 text-gray-400"></i>
                                    {{ $vendor->email }}
                                </div>
                                @if($vendor->phone)
                                <div class="flex items-center text-sm text-gray-600">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 mr-2 text-gray-400"></i>
                                    {{ $vendor->phone }}
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php 
                                $activeSub = $vendor->subscriptions->first(function($sub) {
                                    return $sub->status === 'ACTIVE' && \Carbon\Carbon::parse($sub->expiry_date)->gte(now());
                                });
                            @endphp
                            @if($activeSub)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 border border-green-100 flex items-center w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span>
                                    {{ $activeSub->plan_name }}
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200 flex items-center w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-2"></span>
                                    No Active Plan
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($vendor->status === 'ACTIVE')
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 border border-green-100">Active</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-100">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.vendors.show', $vendor->id) }}" 
                                   class="p-2 rounded-xl hover:bg-gray-100 text-gray-400 hover:text-gray-900 transition-colors" title="View Details">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <button onclick="openEditModal('{{ $vendor->id }}', '{{ $vendor->name }}', '{{ $vendor->email }}', '{{ $vendor->phone }}', '{{ $vendor->status }}')" 
                                        class="p-2 rounded-xl hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this vendor? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.vendors.toggle', $vendor->id) }}" method="POST" class="inline">
                                    @csrf
                                    @if($vendor->status === 'ACTIVE')
                                    <button type="submit" class="p-2 rounded-xl hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors" title="Block">
                                        <i data-lucide="ban" class="w-4 h-4"></i>
                                    </button>
                                    @else
                                    <button type="submit" class="p-2 rounded-xl hover:bg-green-50 text-gray-400 hover:text-green-600 transition-colors" title="Activate">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="building-2" class="w-8 h-8 text-gray-300"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">No vendors found</h3>
                            <p class="text-sm">Get started by creating a new vendor organization.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($vendors->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $vendors->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Vendor Modal -->
<div id="addVendorModal" class="fixed inset-0 z-50 items-center justify-center" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="document.getElementById('addVendorModal').style.display='none'"></div>
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-lg mx-4 relative z-10 animate-fade-in lg:mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Add New Vendor</h3>
                <p class="text-sm text-gray-500 mt-1">Create a new organization workspace</p>
            </div>
            <button onclick="document.getElementById('addVendorModal').style.display='none'" class="p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('admin.vendors.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Organization Info Section -->
            <div class="space-y-5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Organization Info</h4>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Organization Name *</label>
                    <input type="text" name="name" required 
                        placeholder="Enter organization name"
                        class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" required 
                            placeholder="contact@company.com"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                        <input type="text" name="phone" 
                            placeholder="+91 9876543210"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <div class="relative">
                        <select name="status" class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:bg-white outline-none appearance-none transition-all cursor-pointer">
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>
            
            <!-- Admin Credentials Section -->
            <div class="space-y-5 pt-6 border-t border-gray-100">
                <div class="flex items-center space-x-3">
                    <input type="checkbox" id="createAdmin" name="create_admin" value="1" checked 
                        class="w-5 h-5 rounded-lg border-gray-300 text-gray-900 focus:ring-gray-900 transition-all checked:bg-gray-900"
                        onchange="document.getElementById('adminCredentials').classList.toggle('hidden', !this.checked)">
                    <label for="createAdmin" class="text-xs font-bold text-gray-400 uppercase tracking-wider cursor-pointer select-none">Create Admin User</label>
                </div>
                
                <div id="adminCredentials" class="space-y-5 animate-fade-in">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Admin Email</label>
                        <input type="email" name="admin_email" 
                            placeholder="admin@company.com"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                        <p class="text-xs text-gray-500 mt-2 ml-1">Leave empty to use organization email</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Admin Password *</label>
                        <input type="password" name="admin_password" required 
                            placeholder="Min 8 characters"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="document.getElementById('addVendorModal').style.display='none'" 
                    class="px-6 py-3 rounded-2xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors font-bold text-sm">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-6 py-3 rounded-2xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-sm transition-colors shadow-lg shadow-gray-200 flex items-center">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Create Vendor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Vendor Modal -->
<div id="editVendorModal" class="fixed inset-0 z-50 items-center justify-center" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="document.getElementById('editVendorModal').style.display='none'"></div>
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-lg mx-4 relative z-10 animate-fade-in lg:mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Edit Vendor</h3>
                <p class="text-sm text-gray-500 mt-1">Update organization details</p>
            </div>
            <button onclick="document.getElementById('editVendorModal').style.display='none'" class="p-2 rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="editVendorForm" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Organization Info Section -->
            <div class="space-y-5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Organization Info</h4>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Organization Name *</label>
                    <input type="text" name="name" id="edit_name" required 
                        class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" id="edit_email" required 
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                        <input type="text" name="phone" id="edit_phone"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <div class="relative">
                        <select name="status" id="edit_status" class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:bg-white outline-none appearance-none transition-all cursor-pointer">
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>
            
            <!-- Update Password Section -->
            <div class="space-y-5 pt-6 border-t border-gray-100">
                <div class="flex items-center space-x-3">
                    <input type="checkbox" id="updateAdminPass" name="update_admin" value="1" 
                        class="w-5 h-5 rounded-lg border-gray-300 text-gray-900 focus:ring-gray-900 transition-all checked:bg-gray-900"
                        onchange="document.getElementById('editAdminCredentials').classList.toggle('hidden', !this.checked)">
                    <label for="updateAdminPass" class="text-xs font-bold text-gray-400 uppercase tracking-wider cursor-pointer select-none">Update Admin Password</label>
                </div>
                
                <div id="editAdminCredentials" class="space-y-5 hidden animate-fade-in">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                        <input type="password" name="admin_password" 
                            placeholder="Leave empty to keep current"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border-none text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="document.getElementById('editVendorModal').style.display='none'" 
                    class="px-6 py-3 rounded-2xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors font-bold text-sm">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-6 py-3 rounded-2xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-sm transition-colors shadow-lg shadow-gray-200 flex items-center">
                    <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, email, phone, status) {
        document.getElementById('editVendorForm').action = "/admin/vendors/" + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_status').value = status;
        document.getElementById('editVendorModal').style.display = 'flex';
    }
</script>
@endsection
