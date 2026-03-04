@extends('layouts.app')

@section('title', 'Vendor Details - Tancube CRM')
@section('page-title', $vendor->name)

@section('content')
<div class="space-y-6 animate-fade-in pb-48">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.vendors') }}" class="p-2.5 rounded-xl bg-white border border-gray-100 text-gray-500 hover:text-gray-900 hover:bg-gray-50 shadow-soft transition-all">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold text-lg">
                    {{ strtoupper(substr($vendor->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $vendor->name }}</h1>
                    <p class="text-sm text-gray-500 flex items-center mt-0.5">
                        <i data-lucide="mail" class="w-3 h-3 mr-1.5"></i>
                        {{ $vendor->email }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            @if($vendor->status === 'ACTIVE')
                <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-green-50 text-green-600 border border-green-100 flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span>
                    Active
                </span>
            @else
                <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-100 flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-2"></span>
                    Inactive
                </span>
            @endif
            
            <form action="{{ route('admin.vendors.toggle', $vendor->id) }}" method="POST">
                @csrf
                @if($vendor->status === 'ACTIVE')
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 font-semibold text-sm transition-colors border border-amber-100 flex items-center">
                    <i data-lucide="ban" class="w-4 h-4 mr-2"></i>
                    Block Vendor
                </button>
                @else
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-green-50 text-green-600 hover:bg-green-100 font-semibold text-sm transition-colors border border-green-100 flex items-center">
                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                    Activate Vendor
                </button>
                @endif
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl p-5 shadow-soft border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $vendor->users->count() }}</p>
        </div>
        
        <div class="bg-white rounded-3xl p-5 shadow-soft border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-purple-50 text-purple-600 rounded-xl">
                    <i data-lucide="target" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500">Total Leads</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $vendor->leads->count() }}</p>
        </div>
        
        <div class="bg-white rounded-3xl p-5 shadow-soft border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                    <i data-lucide="check-square" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500">Total Tasks</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $vendor->tasks->count() }}</p>
        </div>
        
        <div class="bg-white rounded-3xl p-5 shadow-soft border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-green-50 text-green-600 rounded-xl">
                    <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-sm font-medium text-gray-500">Active Subscription</p>
            @php $activeSub = $vendor->activeSubscription(); @endphp
            @if($activeSub)
                <p class="text-xl font-bold text-gray-900 mt-1 truncate" title="{{ $activeSub->plan_name }}">{{ $activeSub->plan_name }}</p>
                <p class="text-xs text-green-600 font-medium mt-1">Exp: {{ \Carbon\Carbon::parse($activeSub->expiry_date)->format('M d') }}</p>
            @else
                <p class="text-xl font-bold text-gray-400 mt-1">None</p>
                <p class="text-xs text-red-500 font-medium mt-1">Expired</p>
            @endif
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Info & Team -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Organization Info -->
            <div class="bg-white rounded-3xl shadow-soft border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Organization Info</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</span>
                        <span class="text-sm font-mono text-gray-900">{{ Str::limit($vendor->id, 12) }}</span>
                    </div>
                    
                    <div class="space-y-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider ml-1">Contact Details</p>
                        <div class="p-3 bg-gray-50 rounded-2xl space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-gray-400 shadow-sm">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                </div>
                                <span class="text-sm text-gray-700 truncate">{{ $vendor->email }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-gray-400 shadow-sm">
                                    <i data-lucide="phone" class="w-4 h-4"></i>
                                </div>
                                <span class="text-sm text-gray-700">{{ $vendor->phone ?? 'Not provided' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm text-gray-500">Joined</span>
                        <span class="text-sm font-medium text-gray-900">{{ $vendor->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Team Members -->
            <div class="bg-white rounded-3xl shadow-soft border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Team Members</h3>
                    <span class="px-2 py-1 rounded-lg bg-gray-100 text-xs font-bold text-gray-600">{{ $vendor->users->count() }}</span>
                </div>
                
                <div class="space-y-3">
                    @forelse($vendor->users as $user)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-white border-2 border-white shadow-sm flex items-center justify-center text-gray-500 font-bold text-xs shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $user->role === 'ADMIN' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $user->role }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-6 text-gray-400">
                        <p class="text-sm">No team members found</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Subscriptions & Activity -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Subscriptions -->
            <div class="bg-white rounded-3xl shadow-soft border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Subscription History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($vendor->subscriptions as $sub)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900">{{ $sub->plan_name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($sub->start_date)->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-400">to {{ \Carbon\Carbon::parse($sub->expiry_date)->format('M d, Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    ₹{{ number_format($sub->amount) }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusStyles = [
                                            'ACTIVE' => 'bg-green-50 text-green-600 border-green-100',
                                            'EXPIRED' => 'bg-red-50 text-red-600 border-red-100',
                                            'CANCELLED' => 'bg-gray-100 text-gray-500 border-gray-200',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $statusStyles[$sub->status] ?? 'bg-gray-100 text-gray-500' }}">
                                        {{ $sub->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i data-lucide="credit-card" class="w-6 h-6 text-gray-300"></i>
                                    </div>
                                    <p>No subscription history</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
