@extends('layouts.app')

@section('title', 'Admin Dashboard - Tancube CRM')
@section('page-title', 'Overview')

@section('content')
<div class="space-y-6 animate-fade-in">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Vendors -->
        <div class="bg-white rounded-xl shadow-soft p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                    <i data-lucide="building-2" class="w-6 h-6"></i>
                </div>
                <span class="text-sm font-medium text-gray-400">Total Vendors</span>
            </div>
            <div class="flex items-end justify-between">
                <div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($totalVendors) }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Registered Organizations</p>
                </div>
            </div>
        </div>

        <!-- Active Vendors -->
        <div class="bg-white rounded-xl shadow-soft p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-green-50 text-green-600">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <span class="text-sm font-medium text-gray-400">Active Vendors</span>
            </div>
            <div class="flex items-end justify-between">
                <div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($activeVendors) }}</h3>
                    <p class="text-sm text-gray-500 mt-1">With Active Subscription</p>
                </div>
            </div>
        </div>

        <!-- Total Leads -->
        <div class="bg-white rounded-xl shadow-soft p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <span class="text-sm font-medium text-gray-400">Total Leads</span>
            </div>
            <div class="flex items-end justify-between">
                <div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($totalLeads) }}</h3>
                    <p class="text-sm text-gray-500 mt-1">Across All Organizations</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads Growth Chart -->
    <div class="bg-white rounded-3xl shadow-soft p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Leads Growth</h3>
                <p class="text-sm text-gray-500">New leads acquired over the last 12 months</p>
            </div>
            <div class="p-2 bg-gray-50 rounded-lg">
                <i data-lucide="trending-up" class="w-5 h-5 text-green-500"></i>
            </div>
        </div>
        <div class="relative h-80 w-full">
            <canvas id="leadsChart"></canvas>
        </div>
    </div>

    <!-- Expiring Subscriptions -->
    <div class="bg-white rounded-xl shadow-soft border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Upcoming Renewals</h3>
            <a href="{{ route('admin.subscriptions') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">View All</a>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Expiry Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Days Left</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($expiringSubscriptions as $sub)
                @php
                    $daysLeft = (int) round(now()->diffInDays(\Carbon\Carbon::parse($sub->expiry_date), false));
                    $colorClass = 'bg-green-100 text-green-800';
                    if ($daysLeft <= 3) {
                        $colorClass = 'bg-red-100 text-red-800';
                    } elseif ($daysLeft <= 7) {
                        $colorClass = 'bg-orange-100 text-orange-800';
                    }
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900">{{ $sub->vendor->name ?? 'N/A' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $sub->plan_name ?: 'Custom' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($sub->expiry_date)->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                            {{ $daysLeft }} days
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        No upcoming expirations in the next 30 days.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Recent Vendors -->
    <div class="bg-white rounded-xl shadow-soft border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Recent Vendors</h3>
            <a href="{{ route('admin.vendors') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">View All</a>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subscription</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentVendors as $vendor)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900">{{ $vendor->name }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600">{{ $vendor->email }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @php $activeSub = $vendor->subscriptions->first(); @endphp
                        @if($activeSub)
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $activeSub->plan_name }}
                            </span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                No Plan
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($vendor->status === 'ACTIVE')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $vendor->status }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm text-gray-500">
                        {{ $vendor->created_at->diffForHumans() }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        No vendors found recently.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('leadsChart').getContext('2d');
        const chartLabels = JSON.parse('{!! addslashes(json_encode($chartLabels)) !!}');
        const chartValues = JSON.parse('{!! addslashes(json_encode($chartValues)) !!}');

        // Gradient fill
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)'); // Primary-500 equivalent with opacity
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'New Leads',
                    data: chartValues,
                    borderColor: '#6366f1', // Primary-500
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Smooth curves
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: {
                            family: 'Inter',
                            size: 13
                        },
                        bodyFont: {
                            family: 'Inter',
                            size: 13
                        },
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                size: 11
                            },
                            color: '#64748b',
                            padding: 10
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                size: 11
                            },
                            color: '#64748b',
                            padding: 10
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    });
</script>
@endpush
