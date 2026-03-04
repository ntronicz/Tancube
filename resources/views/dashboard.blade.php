@extends('layouts.app')

@section('title', 'Dashboard - Tancube CRM')

@section('content')
<div class="animate-fade-in relative pb-20">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-2">
                Hello, {{ explode(' ', auth()->user()->name)[0] }} 
                <span class="text-3xl">👋</span>
            </h1>
            <p class="text-slate-500 mt-1">Let's hit today's targets!</p>
        </div>
        <!-- Profile Avatar Moved to Topnav -->
    </div>

    @if(isset($subscriptionWarning))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-r-xl p-4 shadow-sm flex items-start sm:items-center gap-3 animate-fade-in relative overflow-hidden">
        <div class="absolute -right-4 -top-4 text-red-100 opacity-50 transform rotate-12">
            <i data-lucide="alert-triangle" class="w-24 h-24"></i>
        </div>
        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0 z-10">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
        </div>
        <div class="z-10 flex-1">
            <h3 class="text-red-800 font-bold text-base">Subscription Expiring Soon</h3>
            <p class="text-red-600 text-sm mt-0.5">
                Your organization's subscription will expire in <strong>{{ $subscriptionWarning['days_left'] }} day{{ $subscriptionWarning['days_left'] !== 1 ? 's' : '' }}</strong> on {{ \Carbon\Carbon::parse($subscriptionWarning['expiry_date'])->format('M d, Y') }}. Please contact your administrator to renew it and avoid service interruption.
            </p>
        </div>
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Leads: White Clean -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Today's Leads</p>
            <h3 class="text-4xl font-bold text-gray-800 tracking-tight">{{ $stats['todayLeads'] ?? 0 }}</h3>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-primary-100 transition-colors">
                <i data-lucide="users" class="w-8 h-8"></i>
            </div>
        </div>
        
        <!-- Follow Ups: White Clean -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Follow Ups</p>
            <h3 class="text-4xl font-bold text-gray-800 tracking-tight">{{ $stats['pendingFollowUps'] ?? 0 }}</h3>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-accent-100 transition-colors">
                <i data-lucide="phone-call" class="w-8 h-8"></i>
            </div>
        </div>
        
        <!-- Pending Tasks: White Clean -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Pending Tasks</p>
            <h3 class="text-4xl font-bold text-gray-800 tracking-tight">{{ $stats['pendingTasks'] ?? 0 }}</h3>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-primary-100 transition-colors">
                <i data-lucide="check-square" class="w-8 h-8"></i>
            </div>
        </div>
        
        <!-- Total Leads: White Clean -->
        <div class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-soft p-6 group transition-all hover:-translate-y-1">
            <p class="text-gray-500 font-medium text-sm mb-2">Total Leads</p>
            <h3 class="text-4xl font-bold text-gray-800 tracking-tight">{{ $stats['totalLeads'] ?? 0 }}</h3>
            <div class="absolute bottom-5 right-5 text-gray-100 group-hover:text-primary-100 transition-colors">
                <i data-lucide="database" class="w-8 h-8"></i>
            </div>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Up Next to Call (Left 2/3) - Order 2 on Mobile -->
        <div class="lg:col-span-2 space-y-6 order-2 lg:order-1">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    Up Next to Call
                </h2>
                <span class="text-sm font-medium text-gray-400">
                    {{ now()->format('l, M d') }}
                </span>
            </div>
            
            <div class="space-y-4">
                @forelse($todayFollowUps as $lead)
                <div id="dashboard-lead-row-{{ $lead->id }}" class="bg-white rounded-3xl p-5 border border-gray-100 shadow-soft hover:shadow-lg transition-all duration-300 flex flex-col sm:flex-row sm:items-center justify-between gap-6 group">
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center shrink-0 text-primary-600 font-bold text-lg group-hover:bg-primary-600 group-hover:text-white transition-colors">
                            {{ substr($lead->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900 mb-1 flex items-center gap-2">
                                <a href="#" 
                                   id="dashboard-lead-name-{{ $lead->id }}"
                                   @click.prevent="$dispatch('open-edit-lead-modal', { leadId: '{{ $lead->id }}' })" 
                                   class="hover:text-primary-600 transition-colors">{{ $lead->name }}</a>
                                @php
                                    $statusColors = [
                                        'NEW' => 'bg-blue-100 text-blue-700',
                                        'CONTACTED' => 'bg-cyan-100 text-cyan-700',
                                        'QUALIFIED' => 'bg-yellow-100 text-yellow-700',
                                        'CONVERTED' => 'bg-green-100 text-green-700',
                                        'LOST' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusColor = $statusColors[$lead->status] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span id="dashboard-lead-status-{{ $lead->id }}" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $statusColor }}">
                                    {{ $lead->status }}
                                </span>
                            </h3>
                            <div class="flex flex-col gap-1 text-xs text-gray-500 mt-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700 bg-gray-50 px-1.5 py-0.5 rounded">{{ $lead->course ?? 'No Course' }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span>{{ $lead->source ?? 'No Source' }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <div class="flex items-center text-accent-600 font-medium bg-accent-50 px-1.5 py-0.5 rounded">
                                        <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                                        <span id="dashboard-lead-time-{{ $lead->id }}">
                                            {{ $lead->next_follow_up ? $lead->next_follow_up->format('h:i A') : 'Today' }}
                                        </span>
                                    </div>
                                    @if($lead->assignedTo)
                                    <span class="text-gray-300">•</span>
                                    <span class="flex items-center text-gray-600">
                                        <i data-lucide="user" class="w-3 h-3 mr-1"></i>
                                        {{ $lead->assignedTo->name }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        @php
                            $phoneClean = preg_replace('/[^0-9]/', '', $lead->phone);
                            $phoneForCall = str_starts_with($lead->phone, '+') ? $lead->phone : '+'.(strlen($phoneClean) === 10 ? '91' . $phoneClean : $phoneClean);
                             $phoneWithCode = str_starts_with($lead->phone, '+') ? $lead->phone : (strlen($phoneClean) === 10 ? '91' . $phoneClean : $phoneClean);
                        @endphp
                        <a href="tel:{{ $phoneForCall }}" onclick="logLeadActivity('{{ $lead->id }}', 'call')"
                           class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-primary-50 hover:bg-primary-600 text-primary-700 hover:text-white text-sm font-semibold transition-all flex items-center justify-center">
                            <i data-lucide="phone" class="w-4 h-4 mr-2"></i>
                            Call
                        </a>
                        <a href="https://wa.me/{{ $phoneWithCode }}" target="_blank" onclick="logLeadActivity('{{ $lead->id }}', 'whatsapp')"
                           class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-green-50 hover:bg-green-500 text-green-700 hover:text-white text-sm font-semibold transition-all flex items-center justify-center">
                            <i data-lucide="message-circle" class="w-4 h-4 mr-2"></i>
                            WhatsApp
                        </a>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-3xl p-10 border border-gray-100 text-center shadow-soft">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle" class="w-8 h-8 text-primary-500"></i>
                    </div>
                    <p class="text-gray-900 font-semibold text-lg">All caught up!</p>
                    <p class="text-gray-400 mt-1">No pending follow-ups for today.</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Priority Tasks (Right 1/3) - Order 1 on Mobile -->
        <div class="space-y-6 order-1 lg:order-2">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                Priority Tasks
            </h2>
            
            <div class="bg-white rounded-3xl border border-gray-100 shadow-soft overflow-hidden">
                <div class="divide-y divide-gray-50">
                    @forelse($priorityTasks as $task)
                    <div class="p-5 hover:bg-gray-50 transition-colors group cursor-pointer">
                        <div class="flex items-start gap-4">
                            <form action="{{ route('tasks.toggle', $task->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    class="mt-1 w-6 h-6 rounded-lg border-2 border-gray-200 hover:border-primary-500 flex items-center justify-center transition-all group-hover:scale-110">
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-primary-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                </button>
                            </form>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-800 line-clamp-1 group-hover:text-primary-600 transition-colors text-base">{{ $task->title }}</p>
                                <div class="flex items-center mt-2 text-xs">
                                    <span class="text-red-600 font-bold bg-red-50 px-2.5 py-1 rounded-lg mr-3">High Priority</span>
                                    <span class="text-gray-400 font-medium">Due {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d') : 'Today' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-10 text-center">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="list-checks" class="w-6 h-6 text-gray-300"></i>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">No high priority tasks.</p>
                    </div>
                    @endforelse
                </div>
                <div class="p-4 bg-gray-50/50 border-t border-gray-100 text-center">
                    <a href="{{ route('tasks.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-bold flex items-center justify-center gap-2">
                        View All Tasks
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('lead-updated', function(e) {
        const leadId = e.detail.id;
        const data = e.detail.data;
        
        // Update Name
        const nameEl = document.getElementById(`dashboard-lead-name-${leadId}`);
        if (nameEl) nameEl.textContent = data.name;

        // Update Status Badge
        const statusEl = document.getElementById(`dashboard-lead-status-${leadId}`);
        if (statusEl) {
            statusEl.textContent = data.status;
            const statusColors = {
                'NEW': 'bg-blue-100 text-blue-700',
                'CONTACTED': 'bg-cyan-100 text-cyan-700',
                'QUALIFIED': 'bg-yellow-100 text-yellow-700',
                'CONVERTED': 'bg-green-100 text-green-700',
                'LOST': 'bg-red-100 text-red-700',
            };
            const colorClass = statusColors[data.status] || 'bg-slate-100 text-slate-700';
            statusEl.className = `px-2 py-0.5 rounded text-[10px] font-bold uppercase ${colorClass}`;
        }
        
        // Update Time (Simple update, formatting might be tricky without a library, using raw value or keeping as is if format complexity is high. 
        // For 'next_follow_up', data.next_follow_up is 'YYYY-MM-DDTHH:mm'. We need 'h:i A'.
        const timeEl = document.getElementById(`dashboard-lead-time-${leadId}`);
        if (timeEl && data.next_follow_up) {
            const date = new Date(data.next_follow_up);
            const hours = date.getHours();
            const minutes = date.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const formattedHours = hours % 12 || 12;
            const formattedMinutes = minutes < 10 ? '0'+minutes : minutes;
            timeEl.textContent = `${formattedHours}:${formattedMinutes} ${ampm}`;

            // Logic to remove row if no longer "Up Next" (<= Today)
            // or if status is excluded
            const dateObj = new Date(data.next_follow_up);
            const todayEnd = new Date();
            todayEnd.setHours(23, 59, 59, 999);
            
            let shouldRemove = false;

            // 1. Check Date
            if (dateObj > todayEnd) {
                shouldRemove = true;
            }

            // 2. Check Status
            const excludedStatuses = ['CONVERTED', 'LOST', 'NOT INTERESTED', 'Not Interested'];
            if (excludedStatuses.map(s => s.toUpperCase()).includes(data.status.toUpperCase())) {
                shouldRemove = true;
            }

            if (shouldRemove) {
                const row = document.getElementById(`dashboard-lead-row-${leadId}`);
                if (row) {
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
            }
        }
    });
</script>
@endpush
