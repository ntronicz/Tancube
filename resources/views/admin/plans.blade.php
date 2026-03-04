@extends('layouts.app')

@section('title', 'Plans Management - Tancube CRM')
@section('page-title', 'Billing Plans')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <p class="text-gray-500">Manage billing plans and subscription limits for institutions</p>
        <button onclick="document.getElementById('addPlanModal').style.display='flex'" 
                class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors flex items-center w-fit">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Create Plan
        </button>
    </div>

    <!-- Error & Success Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <!-- Plans Table -->
    <div class="bg-white rounded-xl shadow-soft border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Price / Freq</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Admins Limit</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Agents Limit</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($plans as $plan)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="text-gray-900 font-medium">{{ $plan->name }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-gray-900 font-medium">₹{{ number_format($plan->price, 2) }}</p>
                        <p class="text-gray-500 text-xs">{{ ucfirst($plan->frequency) }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm">
                        {{ $plan->max_admins }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm">
                        {{ $plan->max_agents }}
                    </td>
                    <td class="px-4 py-3">
                        <form action="{{ route('admin.plans.toggle', $plan->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2 py-1 rounded text-xs font-medium {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <!-- Edit Button -->
                            <button data-plan="{{ json_encode($plan) }}" onclick="editPlan(JSON.parse(this.dataset.plan))" class="p-2 rounded-lg hover:bg-gray-100 text-blue-500 hover:text-blue-700 transition-colors" title="Edit">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <!-- Delete form -->
                            <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this plan entirely?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg hover:bg-gray-100 text-red-500 hover:text-red-700 transition-colors" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="layers" class="w-6 h-6 text-gray-300"></i>
                        </div>
                        <p>No plans found. Create one to get started.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($plans->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $plans->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add/Edit Plan Modal -->
<div id="addPlanModal" class="fixed inset-0 z-50 items-center justify-center" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closePlanModal()"></div>
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-lg mx-4 relative z-10 animate-fade-in lg:mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 id="planModalTitle" class="text-xl font-bold text-gray-900">Create Plan</h3>
            <button onclick="closePlanModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="planForm" action="{{ route('admin.plans.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <!-- Plan Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name *</label>
                <input type="text" name="name" id="planName" required placeholder="e.g. Starter, Pro, Enterprise"
                    class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
            </div>
            
            <!-- Limits -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Admins *</label>
                    <input type="number" name="max_admins" id="planMaxAdmins" required min="1"
                        placeholder="1"
                        class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Agents *</label>
                    <input type="number" name="max_agents" id="planMaxAgents" required min="1"
                        placeholder="10"
                        class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
                </div>
            </div>
            
            <!-- Amount & Frequency -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price (₹) *</label>
                    <input type="number" name="price" id="planPrice" required min="0" step="0.01"
                        placeholder="0.00"
                        class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frequency *</label>
                    <div class="relative">
                        <select name="frequency" id="planFrequency" required class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none appearance-none transition-all">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="one_time">One Time</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>
            
            <!-- Status -->
            <div>
                <label class="flex items-center space-x-2 cursor-pointer mt-4">
                    <input type="checkbox" name="is_active" id="planIsActive" value="1" checked class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                    <span class="text-sm font-medium text-gray-700">Active Plan</span>
                </label>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closePlanModal()" 
                    class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-medium transition-colors shadow-lg shadow-gray-200 flex items-center">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    Save Plan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editPlan(plan) {
    document.getElementById('planModalTitle').innerText = 'Edit Plan';
    document.getElementById('planForm').action = "/admin/plans/" + plan.id;
    document.getElementById('formMethod').value = "PUT";
    
    document.getElementById('planName').value = plan.name;
    document.getElementById('planMaxAdmins').value = plan.max_admins;
    document.getElementById('planMaxAgents').value = plan.max_agents;
    document.getElementById('planPrice').value = plan.price;
    document.getElementById('planFrequency').value = plan.frequency;
    document.getElementById('planIsActive').checked = plan.is_active;
    
    document.getElementById('addPlanModal').style.display = 'flex';
}

function closePlanModal() {
    document.getElementById('planModalTitle').innerText = 'Create Plan';
    document.getElementById('planForm').action = "{{ route('admin.plans.store') }}";
    document.getElementById('formMethod').value = "POST";
    document.getElementById('planForm').reset();
    document.getElementById('addPlanModal').style.display = 'none';
}
</script>
@endpush
@endsection
