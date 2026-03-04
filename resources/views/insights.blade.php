@extends('layouts.app')

@section('title', 'Insights - Tancube CRM')
@section('page-title', 'Insights')

@section('content')
<div class="space-y-6 animate-fade-in pb-20"
    id="insightsData"
    data-status="{{ json_encode($statusDistribution) }}"
    data-sources="{{ json_encode($leadsBySource) }}"
    data-trend="{{ json_encode($leadTrend) }}"
    data-conversion="{{ min($conversionRate, 100) }}">
    
    <!-- Date Range Filter -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
        <form method="GET" action="{{ route('insights') }}" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-slate-500">From:</label>
                <input 
                    type="date" 
                    name="start_date" 
                    value="{{ $startDate }}"
                    class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
            </div>
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-slate-500">To:</label>
                <input 
                    type="date" 
                    name="end_date" 
                    value="{{ $endDate }}"
                    class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                >
            </div>
            
            @if(isset($agents) && $agents->count() > 1)
            <div class="flex items-center space-x-2">
                 <label class="text-sm font-medium text-slate-500">Agent:</label>
                 <select name="agent_id" onchange="this.form.submit()" class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ (isset($selectedAgentId) && $selectedAgentId == $agent->id) ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                 </select>
            </div>
            @endif
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">
                Apply
            </button>
        </form>
    </div>
    
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @include('components.stats-card', [
            'title' => 'Total Leads',
            'value' => number_format($totalLeads),
            'icon' => 'users',
            'color' => 'primary'
        ])
        
        @include('components.stats-card', [
            'title' => 'Converted Leads',
            'value' => number_format($convertedLeads),
            'icon' => 'trophy',
            'color' => 'success'
        ])
        
        <div class="bg-cyan-500 rounded-2xl p-6 shadow-sm border border-cyan-500 text-white">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider mb-2 text-white/80">Conversion Rate</p>
                    <p class="text-3xl font-bold text-white">{{ $conversionRate }}%</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <i data-lucide="percent" class="w-5 h-5 text-white"></i>
                </div>
            </div>
            <!-- Progress bar -->
            <div class="mt-4 h-1.5 bg-black/20 rounded-full overflow-hidden">
                <div id="conversionBar" class="h-full bg-white rounded-full transition-all" style="width: 0"></div>
            </div>
        </div>
    </div>
    
    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Status Distribution (Pie) -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Status Distribution</h3>
            <div class="h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        
        <!-- Leads by Source (Bar) -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Leads by Source</h3>
            <div class="h-64">
                <canvas id="sourceChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Lead Trend (Line) -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Lead Trend</h3>
        <div class="h-64">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
    
    <!-- Leads by Course -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
        <h3 class="text-lg font-bold text-slate-900 mb-4">Leads by Course</h3>
        <div class="space-y-4">
            @forelse($leadsByCourse as $item)
            @php
                $widthPercent = $totalLeads > 0 ? round(($item->count / $totalLeads) * 100, 1) : 0;
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-slate-700">{{ $item->course }}</span>
                    <span class="text-sm font-bold text-slate-900">{{ $item->count }}</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-100">
                    <div class="h-full bg-indigo-500 rounded-full course-bar transition-all duration-500" data-width="{{ $widthPercent }}"></div>
                </div>
            </div>
            @empty
            <p class="text-slate-400 text-center py-4 text-sm">No course data available</p>
            @endforelse
        </div>
    </div>
    <!-- Agent Performance Section -->
    <div class="mt-8">
        <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center">
            <i data-lucide="users" class="w-5 h-5 mr-2 text-indigo-600"></i>
            Agent Performance
        </h2>
        
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-slate-700">Agent Name</th>
                            <th class="px-6 py-4 font-semibold text-slate-700 text-center">Assigned Leads</th>
                            <th class="px-6 py-4 font-semibold text-slate-700 text-center">Total Activities</th>
                            <th class="px-6 py-4 font-semibold text-slate-700 text-center">Status Changes</th>
                            <th class="px-6 py-4 font-semibold text-center text-indigo-600">Calls Dialed</th>
                            <th class="px-6 py-4 font-semibold text-center text-green-600">WhatsApp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($agentPerformance as $agent)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500">
                                        {{ substr($agent['name'], 0, 1) }}
                                    </div>
                                    {{ $agent['name'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-bold text-xs">
                                    {{ $agent['assigned_leads'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-semibold text-slate-700">{{ $agent['leads_worked'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center text-slate-600">
                                {{ $agent['status_changes'] }}
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-indigo-600 bg-indigo-50/50">
                                {{ $agent['calls'] }}
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-green-600 bg-green-50/50">
                                {{ $agent['whatsapp'] }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                No agent performance data available.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // Get data from HTML data attributes
    var dataEl = document.getElementById('insightsData');
    var statusData = JSON.parse(dataEl.dataset.status || '{}');
    var sourceData = JSON.parse(dataEl.dataset.sources || '[]');
    var trendData = JSON.parse(dataEl.dataset.trend || '[]');
    var conversionRate = parseFloat(dataEl.dataset.conversion || '0');
    
    // Set conversion bar width
    document.getElementById('conversionBar').style.width = conversionRate + '%';
    
    // Set course bar widths
    document.querySelectorAll('.course-bar').forEach(function(bar) {
        setTimeout(() => {
            bar.style.width = bar.dataset.width + '%';
        }, 100);
    });
    
    // Chart colors
    // Expanded Color Palette (20 distinct colors)
    var palette = [
        '#3b82f6', // Blue
        '#06b6d4', // Cyan
        '#eab308', // Yellow
        '#a855f7', // Purple
        '#22c55e', // Green
        '#ef4444', // Red
        '#f97316', // Orange
        '#ec4899', // Pink
        '#6366f1', // Indigo
        '#14b8a6', // Teal
        '#84cc16', // Lime
        '#f43f5e', // Rose
        '#0ea5e9', // Sky
        '#d946ef', // Fuchsia
        '#8b5cf6', // Violet
        '#10b981', // Emerald
        '#f59e0b', // Amber
        '#64748b', // Slate
        '#78716c', // Stone
        '#71717a'  // Zinc
    ];

    Chart.defaults.color = '#64748b';
    Chart.defaults.font.family = "'Inter', sans-serif";

    // Status Distribution Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: Object.keys(statusData).map(function(s, index) { 
                    return palette[index % palette.length]; 
                }),
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 12, usePointStyle: true }
                }
            }
        }
    });
    
    // Leads by Source Chart
    new Chart(document.getElementById('sourceChart'), {
        type: 'bar',
        data: {
            labels: sourceData.map(function(s) { return s.source; }),
            datasets: [{
                label: 'Leads',
                data: sourceData.map(function(s) { return s.count; }),
                backgroundColor: '#6366f1',
                borderRadius: 4,
                barThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#f1f5f9' }, beginAtZero: true }
            }
        }
    });
    
    // Lead Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendData.map(function(t) { return t.date; }),
            datasets: [{
                label: 'Leads',
                data: trendData.map(function(t) { return t.count; }),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6366f1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#f1f5f9' }, beginAtZero: true }
            }
        }
    });
})();
</script>
@endpush
@endsection
