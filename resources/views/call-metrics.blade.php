@extends('layouts.app')

@section('title', 'Call Metrics - Tancube CRM')

@section('content')
<div class="animate-fade-in relative pb-20">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-2">
                Call Metrics
                <span class="text-3xl">📊</span>
            </h1>
            <p class="text-slate-500 mt-1">Track your team's telecalling performance</p>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('call-metrics') }}" class="flex flex-wrap items-center gap-3">
            <input type="date" name="start_date" value="{{ $startDate }}"
                class="px-3 py-2 rounded-xl border border-gray-200 text-sm bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <span class="text-gray-400 text-sm">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}"
                class="px-3 py-2 rounded-xl border border-gray-200 text-sm bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">

            @if(auth()->user()->role === 'ADMIN')
            <select name="agent_id"
                class="px-3 py-2 rounded-xl border border-gray-200 text-sm bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <option value="">All Agents</option>
                @foreach($agents as $agent)
                <option value="{{ $agent->id }}" {{ $agentFilter == $agent->id ? 'selected' : '' }}>
                    {{ $agent->name }}
                </option>
                @endforeach
            </select>
            @endif

            <button type="submit"
                class="px-4 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition-colors flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Apply
            </button>
        </form>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
        <!-- Total Dials -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Total Dials</p>
            <h3 class="text-4xl font-bold text-gray-800 tracking-tight">{{ number_format($totalDials) }}</h3>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-primary-100 transition-colors">
                <i data-lucide="phone-outgoing" class="w-8 h-8"></i>
            </div>
        </div>

        <!-- Connected -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Connected</p>
            <h3 class="text-4xl font-bold text-green-600 tracking-tight">{{ number_format($connectedCalls) }}</h3>
            <p class="text-xs text-green-600 font-semibold mt-1">{{ $connectRate }}% rate</p>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-green-100 transition-colors">
                <i data-lucide="phone" class="w-8 h-8"></i>
            </div>
        </div>

        <!-- Not Connected -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Not Connected</p>
            <h3 class="text-4xl font-bold text-orange-500 tracking-tight">{{ number_format($notConnectedCalls) }}</h3>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-orange-100 transition-colors">
                <i data-lucide="phone-off" class="w-8 h-8"></i>
            </div>
        </div>

        <!-- Missed -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Missed</p>
            <h3 class="text-4xl font-bold text-red-500 tracking-tight">{{ number_format($missedCalls) }}</h3>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-red-100 transition-colors">
                <i data-lucide="phone-missed" class="w-8 h-8"></i>
            </div>
        </div>

        <!-- Total Duration -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Total Duration</p>
            @php
                $hours = intdiv($totalDuration, 3600);
                $mins = intdiv($totalDuration % 3600, 60);
                $durationDisplay = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
            @endphp
            <h3 class="text-4xl font-bold text-gray-800 tracking-tight">{{ $durationDisplay }}</h3>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-accent-100 transition-colors">
                <i data-lucide="clock" class="w-8 h-8"></i>
            </div>
        </div>

        <!-- Avg Duration -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Avg Duration</p>
            @php
                $avgMins = intdiv($avgDuration, 60);
                $avgSecs = $avgDuration % 60;
                $avgDisplay = $avgMins > 0 ? "{$avgMins}m {$avgSecs}s" : "{$avgSecs}s";
            @endphp
            <h3 class="text-4xl font-bold text-gray-800 tracking-tight">{{ $avgDisplay }}</h3>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-primary-100 transition-colors">
                <i data-lucide="timer" class="w-8 h-8"></i>
            </div>
        </div>
    </div>

    <!-- Charts & Leaderboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Call Status Breakdown (Pie Chart) -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-soft p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="pie-chart" class="w-5 h-5 text-primary-500"></i>
                Call Status
            </h2>
            <div class="relative h-[220px]">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Hourly Distribution (Bar Chart) -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-soft p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-primary-500"></i>
                Hourly Activity
            </h2>
            <div class="relative h-[220px]">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Agent Leaderboard -->
        @if(auth()->user()->role === 'ADMIN' && count($agentStats) > 0)
        <div class="bg-white rounded-3xl border border-gray-100 shadow-soft p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="trophy" class="w-5 h-5 text-accent-500"></i>
                Agent Leaderboard
            </h2>
            <div class="space-y-3">
                @foreach($agentStats as $index => $stat)
                <div class="flex items-center gap-3 p-3 rounded-2xl {{ $index === 0 ? 'bg-accent-50 border border-accent-100' : 'bg-gray-50' }}">
                    <div class="w-8 h-8 rounded-xl {{ $index === 0 ? 'bg-accent-500 text-white' : 'bg-gray-200 text-gray-600' }} flex items-center justify-center text-sm font-bold shrink-0">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $stat->user->name ?? 'Unknown' }}</p>
                        <div class="flex items-center gap-3 text-xs text-gray-500 mt-0.5">
                            <span>{{ $stat->total_dials }} dials</span>
                            <span class="text-green-600 font-semibold">{{ $stat->connected }} connected</span>
                            <span>{{ $stat->duration_formatted }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold {{ $stat->connect_rate >= 50 ? 'text-green-600' : ($stat->connect_rate >= 30 ? 'text-accent-600' : 'text-red-500') }}">
                            {{ $stat->connect_rate }}%
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-white rounded-3xl border border-gray-100 shadow-soft p-6 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <i data-lucide="phone-off" class="w-8 h-8 text-gray-300"></i>
            </div>
            <p class="text-gray-400 text-sm font-medium">
                @if(auth()->user()->role !== 'ADMIN')
                    Your personal call summary is shown above.
                @else
                    No call data for this period.
                @endif
            </p>
        </div>
        @endif
    </div>

    <!-- Recent Call Logs Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-soft overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i data-lucide="list" class="w-5 h-5 text-primary-500"></i>
                Recent Calls
            </h2>
            <span class="text-sm text-gray-400">{{ $recentCalls->total() }} calls</span>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Agent</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Lead</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentCalls as $call)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-800">{{ $call->user->name ?? 'Unknown' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-700 font-mono text-xs">{{ $call->phone_number }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($call->lead)
                                <a href="#" @click.prevent="$dispatch('open-edit-lead-modal', { leadId: '{{ $call->lead->id }}' })"
                                    class="text-primary-600 hover:text-primary-700 font-medium">
                                    {{ $call->lead->name }}
                                </a>
                            @else
                                <span class="text-gray-400 text-xs">No match</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $typeColors = [
                                    'OUTBOUND' => 'bg-blue-100 text-blue-700',
                                    'INBOUND' => 'bg-green-100 text-green-700',
                                    'MISSED' => 'bg-red-100 text-red-700',
                                ];
                                $typeIcons = [
                                    'OUTBOUND' => 'phone-outgoing',
                                    'INBOUND' => 'phone-incoming',
                                    'MISSED' => 'phone-missed',
                                ];
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold {{ $typeColors[$call->call_type] ?? '' }}">
                                <i data-lucide="{{ $typeIcons[$call->call_type] ?? 'phone' }}" class="w-3 h-3"></i>
                                {{ $call->call_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'CONNECTED' => 'bg-green-100 text-green-700',
                                    'REJECTED' => 'bg-red-100 text-red-700',
                                    'NO_ANSWER' => 'bg-yellow-100 text-yellow-700',
                                    'BUSY' => 'bg-orange-100 text-orange-700',
                                    'UNKNOWN' => 'bg-gray-100 text-gray-700',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold {{ $statusColors[$call->call_status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ str_replace('_', ' ', $call->call_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-700 font-medium">{{ $call->formatted_duration }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-500 text-xs">
                                {{ $call->call_timestamp ? $call->call_timestamp->format('h:i A') : '-' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="phone-off" class="w-6 h-6 text-gray-300"></i>
                            </div>
                            <p class="text-gray-400 text-sm font-medium">No call logs for this period.</p>
                            <p class="text-gray-300 text-xs mt-1">Call data will appear once agents sync from the mobile app.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-50">
            @forelse($recentCalls as $call)
            <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        @php
                            $mTypeColors = [
                                'OUTBOUND' => 'bg-blue-100 text-blue-700',
                                'INBOUND' => 'bg-green-100 text-green-700',
                                'MISSED' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $mTypeColors[$call->call_type] ?? '' }}">
                            {{ $call->call_type }}
                        </span>
                        <span class="text-sm font-semibold text-gray-800">{{ $call->phone_number }}</span>
                    </div>
                    <span class="text-xs text-gray-400">{{ $call->call_timestamp ? $call->call_timestamp->format('h:i A') : '' }}</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span class="font-medium text-gray-700">{{ $call->user->name ?? '' }}</span>
                    @if($call->lead)
                    <span class="text-primary-600">→ {{ $call->lead->name }}</span>
                    @endif
                    <span class="ml-auto font-semibold text-gray-700">{{ $call->formatted_duration }}</span>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <p class="text-gray-400 text-sm">No call logs yet.</p>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($recentCalls->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $recentCalls->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Call Status Pie Chart
const statusData = JSON.parse('{!! json_encode($byStatus) !!}');
const statusLabels = Object.keys(statusData).map(s => s.replace('_', ' '));
const statusValues = Object.values(statusData);
const statusColorMap = {
    'CONNECTED': '#10b981',
    'REJECTED': '#ef4444',
    'NO_ANSWER': '#f59e0b',
    'BUSY': '#f97316',
    'UNKNOWN': '#9ca3af',
};
const statusColors = Object.keys(statusData).map(s => statusColorMap[s] || '#9ca3af');

if (document.getElementById('statusChart')) {
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: statusColors,
                borderWidth: 0,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyleWidth: 10,
                        font: { size: 11, family: 'Inter', weight: '600' }
                    }
                }
            }
        }
    });
}

// Hourly Distribution Bar Chart
const hourlyData = JSON.parse('{!! json_encode($hourlyData) !!}');
const hours = Array.from({ length: 24 }, (_, i) => i);
const hourLabels = hours.map(h => {
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hr = h % 12 || 12;
    return `${hr}${ampm}`;
});
const hourValues = hours.map(h => hourlyData[h] || 0);

if (document.getElementById('hourlyChart')) {
    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: hourLabels,
            datasets: [{
                label: 'Calls',
                data: hourValues,
                backgroundColor: '#10b98133',
                borderColor: '#10b981',
                borderWidth: 1.5,
                borderRadius: 6,
                barPercentage: 0.6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 10 } },
                    grid: { color: '#f3f4f6' }
                },
                x: {
                    ticks: { font: { size: 8 }, maxRotation: 45 },
                    grid: { display: false }
                }
            }
        }
    });
}
</script>
@endpush
