@extends('layouts.app')

@section('title', 'Subscriptions - Tancube CRM')
@section('page-title', 'Subscription Management')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <p class="text-gray-500">Manage vendor subscriptions and billing</p>
        <button onclick="document.getElementById('addSubModal').style.display='flex'" 
                class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors flex items-center w-fit">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            + Plan
        </button>
    </div>
    
    <!-- Subscriptions Table -->
    <div class="bg-white rounded-xl shadow-soft border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Limits (Adm/Agt)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Period</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($subscriptions as $sub)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="text-gray-900 font-medium">{{ $sub->vendor->name ?? 'N/A' }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs bg-primary-50 text-primary-600 font-medium">{{ $sub->plan_name ?: ($sub->plan->name ?? 'Custom') }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm">
                        {{ $sub->max_admins }} / {{ $sub->max_agents }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm">
                        {{ \Carbon\Carbon::parse($sub->start_date)->format('M d, Y') }} - 
                        {{ \Carbon\Carbon::parse($sub->expiry_date)->format('M d, Y') }}
                    </td>
                    <td class="px-4 py-3 text-gray-900 font-medium">
                        ₹{{ number_format($sub->amount) }} / {{ $sub->frequency }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $statusColors = [
                                'ACTIVE' => 'bg-green-100 text-green-700',
                                'EXPIRED' => 'bg-red-100 text-red-700',
                                'CANCELLED' => 'bg-gray-100 text-gray-500',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $statusColors[$sub->status] ?? '' }}">{{ $sub->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            @if($sub->status === 'ACTIVE')
                            <form action="{{ route('admin.subscriptions.cancel', $sub->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this subscription?')">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg hover:bg-gray-100 text-red-500 hover:text-red-700 transition-colors" title="Cancel">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                                </button>
                            </form>
                            <button data-id="{{ $sub->id }}" data-admins="{{ $sub->max_admins }}" data-agents="{{ $sub->max_agents }}" onclick="editLimits(this.dataset.id, this.dataset.admins, this.dataset.agents)" class="p-2 rounded-lg hover:bg-gray-100 text-blue-500 hover:text-blue-700 transition-colors" title="Edit Limits">
                                <i data-lucide="sliders" class="w-4 h-4"></i>
                            </button>
                            @elseif($sub->status === 'EXPIRED')
                            <form action="{{ route('admin.subscriptions.renew', $sub->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg hover:bg-gray-100 text-green-500 hover:text-green-700 transition-colors" title="Renew">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="credit-card" class="w-6 h-6 text-gray-300"></i>
                        </div>
                        <p>No subscriptions found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($subscriptions->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $subscriptions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Manage Subscription Modal -->
<div id="addSubModal" class="fixed inset-0 z-50 items-center justify-center" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="document.getElementById('addSubModal').style.display='none'"></div>
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-lg mx-4 relative z-10 animate-fade-in lg:mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Manage Subscription</h3>
            <button onclick="document.getElementById('addSubModal').style.display='none'" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form action="{{ route('admin.subscriptions.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <!-- Vendor Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
                <div class="relative">
                    <select name="vendor_id" required class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none appearance-none transition-all">
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                </div>
            </div>
            
            <!-- Plan Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plan *</label>
                <div class="relative">
                    <select name="plan_id" required class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none appearance-none transition-all">
                        <option value="">Select Plan</option>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" data-price="{{ $plan->price }}" data-freq="{{ strtoupper($plan->frequency) }}">{{ $plan->name }} (Up to {{ $plan->max_admins }} Admins, {{ $plan->max_agents }} Agents)</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                </div>
            </div>
            
            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                    <input type="date" name="start_date" required 
                        value="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date *</label>
                    <input type="date" name="expiry_date" required 
                        class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
                </div>
            </div>
            
            <!-- Amount & Frequency -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₹) *</label>
                    <input type="number" name="amount" required min="0" step="0.01"
                        placeholder="0.00"
                        class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frequency *</label>
                    <div class="relative">
                        <select name="frequency" required class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none appearance-none transition-all">
                            <option value="MONTHLY">Monthly</option>
                            <option value="YEARLY">Yearly</option>
                            <option value="ONE_TIME">One Time</option>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>
            
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="flex items-center space-x-4 mt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="status" value="ACTIVE" checked class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                        <span class="text-gray-700 font-medium">Active</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="status" value="EXPIRED" class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                        <span class="text-gray-700 font-medium">Expired</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="status" value="CANCELLED" class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                        <span class="text-gray-700 font-medium">Cancelled</span>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('addSubModal').style.display='none'" 
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
// Auto-calculate end date based on frequency
document.querySelector('select[name="frequency"]').addEventListener('change', function() {
    var startDate = document.querySelector('input[name="start_date"]').value;
    if (startDate) {
        var start = new Date(startDate);
        var months = { 'MONTHLY': 1, 'QUARTERLY': 3, 'YEARLY': 12, 'ONE_TIME': 1200 }[this.value] || 1;
        start.setMonth(start.getMonth() + months);
        document.querySelector('input[name="expiry_date"]').value = start.toISOString().split('T')[0];
    }
});

document.querySelector('input[name="start_date"]').addEventListener('change', function() {
    var freq = document.querySelector('select[name="frequency"]').value;
    if (freq) {
        var start = new Date(this.value);
        var months = { 'MONTHLY': 1, 'QUARTERLY': 3, 'YEARLY': 12, 'ONE_TIME': 1200 }[freq] || 1;
        start.setMonth(start.getMonth() + months);
        document.querySelector('input[name="expiry_date"]').value = start.toISOString().split('T')[0];
    }
});

document.querySelector('select[name="plan_id"]').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        document.querySelector('input[name="amount"]').value = selectedOption.getAttribute('data-price');
        document.querySelector('select[name="frequency"]').value = selectedOption.getAttribute('data-freq');
        // trigger frequency change to re-calc expiry
        document.querySelector('select[name="frequency"]').dispatchEvent(new Event('change'));
    }
});

function editLimits(subId, maxAdmins, maxAgents) {
    document.getElementById('editLimitsForm').action = "/admin/subscriptions/" + subId + "/limits";
    document.getElementById('editMaxAdmins').value = maxAdmins;
    document.getElementById('editMaxAgents').value = maxAgents;
    document.getElementById('editLimitsModal').style.display = 'flex';
}
</script>

<!-- Edit Limits Modal -->
<div id="editLimitsModal" class="fixed inset-0 z-50 items-center justify-center" style="display: none;">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="document.getElementById('editLimitsModal').style.display='none'"></div>
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 relative z-10 animate-fade-in lg:mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Edit Vendor Limits</h3>
            <button onclick="document.getElementById('editLimitsModal').style.display='none'" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="editLimitsForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Admins *</label>
                <input type="number" name="max_admins" id="editMaxAdmins" required min="1"
                    class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Agents *</label>
                <input type="number" name="max_agents" id="editMaxAgents" required min="1"
                    class="w-full px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('editLimitsModal').style.display='none'" 
                    class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-medium transition-colors shadow-lg shadow-gray-200 flex items-center">
                    <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                    Update Limits
                </button>
            </div>
        </form>
    </div>
</div>
@endpush
@endsection
