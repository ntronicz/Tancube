@extends('layouts.app')

@section('title', 'Follow-ups - Tancube CRM')
@section('page-title', 'Follow-ups')

@section('content')
<div class="space-y-4 animate-fade-in">
    <!-- Header & Stats -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Follow-ups</h1>
            <p class="text-slate-500 text-sm mt-1">Manage all your scheduled calls and updates</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto md:items-center">
            @if(isset($agents) && $agents->isNotEmpty())
            <form method="GET" action="{{ route('follow-ups.index') }}" class="w-full md:w-auto">
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <select name="agent_id" onchange="this.form.submit()" class="w-full md:w-48 rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ (isset($agentId) && $agentId == $agent->id) ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif

            <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
                <a href="{{ route('follow-ups.index', array_merge(request()->query(), ['filter' => 'overdue'])) }}" 
                   class="whitespace-nowrap px-3 py-2 rounded-lg border text-xs font-bold uppercase transition-colors {{ $filter === 'overdue' ? 'bg-red-50 text-red-600 border-red-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50' }}">
                    Overdue ({{ $overdueCount }})
                </a>
                <a href="{{ route('follow-ups.index', array_merge(request()->query(), ['filter' => 'today'])) }}" 
                   class="whitespace-nowrap px-3 py-2 rounded-lg border text-xs font-bold uppercase transition-colors {{ $filter === 'today' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50' }}">
                    Today ({{ $todayCount }})
                </a>
                <a href="{{ route('follow-ups.index', array_merge(request()->query(), ['filter' => 'tomorrow'])) }}" 
                   class="whitespace-nowrap px-3 py-2 rounded-lg border text-xs font-bold uppercase transition-colors {{ $filter === 'tomorrow' ? 'bg-indigo-50 text-indigo-600 border-indigo-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50' }}">
                    Tomorrow ({{ $tomorrowCount }})
                </a>
                <a href="{{ route('follow-ups.index', array_merge(request()->query(), ['filter' => 'upcoming'])) }}" 
                   class="whitespace-nowrap px-3 py-2 rounded-lg border text-xs font-bold uppercase transition-colors {{ $filter === 'upcoming' ? 'bg-green-50 text-green-600 border-green-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50' }}">
                    Upcoming ({{ $upcomingCount }})
                </a>
                <a href="{{ route('follow-ups.index', ['reset' => 1]) }}" 
                   class="whitespace-nowrap px-3 py-2 rounded-lg border text-xs font-bold uppercase transition-colors {{ $filter === 'all' || !$filter ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50' }}">
                    All
                </a>
            </div>
        </div>
    </div>

    <!-- Follow-ups List -->
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <!-- Desktop Table (Hidden on Mobile) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Lead</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Source/Assigned</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Notes</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Follow-up</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($leads as $lead)
                    @php
                        $isOverdue = $lead->next_follow_up < now();
                        $isToday = $lead->next_follow_up->isToday();
                    @endphp
                    <tr id="followup-row-desktop-{{ $lead->id }}" class="hover:bg-slate-50/80 transition-colors group text-black">
                        <td class="px-4 py-3">
                            <div>
                                <a href="#" 
                                   id="followup-lead-name-desktop-{{ $lead->id }}"
                                   @click.prevent="$dispatch('open-edit-lead-modal', { leadId: '{{ $lead->id }}' })" 
                                   class="font-bold text-slate-900 hover:text-indigo-600 text-sm">{{ $lead->name }}</a>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $lead->course ?? 'No Product' }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center text-xs text-slate-600">
                                <i data-lucide="phone" class="w-3 h-3 mr-1.5 opacity-70"></i>
                                {{ $lead->phone }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-slate-600 flex items-center gap-1" title="Source">
                                    <i data-lucide="globe" class="w-3 h-3 text-slate-400"></i>
                                    {{ $lead->source ?? 'N/A' }}
                                </span>
                                @if($lead->assignedTo)
                                <span class="text-xs text-slate-600 flex items-center gap-1" title="Assigned To">
                                    <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>
                                    {{ $lead->assignedTo->name }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
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
                            <span id="followup-lead-status-desktop-{{ $lead->id }}" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $statusColor }}">
                                {{ $lead->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div id="followup-lead-notes-desktop-{{ $lead->id }}" class="max-w-xs truncate text-xs text-slate-500 italic" title="{{ $lead->notes }}">
                                {{ $lead->notes ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5 {{ $isOverdue ? 'text-red-500' : ($isToday ? 'text-amber-500' : 'text-slate-400') }}"></i>
                                <div>
                                    <p class="text-xs font-medium {{ $isOverdue ? 'text-red-600' : ($isToday ? 'text-amber-600' : 'text-slate-700') }}">
                                        <span id="followup-lead-time-desktop-{{ $lead->id }}">
                                            {{ $lead->next_follow_up->format('M d, h:i A') }}
                                        </span>
                                    </p>
                                    <p class="text-[10px] text-slate-400">{{ $lead->next_follow_up->diffForHumans() }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                             <div class="flex items-center justify-end gap-2">
                                @php
                                    $phoneClean = preg_replace('/[^0-9]/', '', $lead->phone);
                                    $phoneForCall = str_starts_with($lead->phone, '+') ? $lead->phone : '+' . (strlen($phoneClean) === 10 ? '91' . $phoneClean : $phoneClean);
                                     $phoneWithCode = str_starts_with($lead->phone, '+') ? $lead->phone : (strlen($phoneClean) === 10 ? '91' . $phoneClean : $phoneClean);
                                @endphp
                                <a href="https://wa.me/{{ $phoneWithCode }}" target="_blank" onclick="logLeadActivity('{{ $lead->id }}', 'whatsapp')"
                                   class="p-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors" title="WhatsApp">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                </a>
                                <a href="tel:{{ $phoneForCall }}" onclick="logLeadActivity('{{ $lead->id }}', 'call')"
                                   class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors" title="Call">
                                    <i data-lucide="phone" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center">
                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="check-circle" class="w-6 h-6 text-slate-400"></i>
                            </div>
                            <p class="text-sm text-slate-500">No follow-ups found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (Visible on Mobile) -->
        <div class="md:hidden space-y-3 p-3 bg-slate-50">
            @forelse($leads as $lead)
            @php
                $isOverdue = $lead->next_follow_up < now();
                $isToday = $lead->next_follow_up->isToday();
                $statusColors = [
                    'NEW' => 'bg-blue-50 text-blue-700 border-blue-100',
                    'CONTACTED' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
                    'QUALIFIED' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                    'CONVERTED' => 'bg-green-50 text-green-700 border-green-100',
                    'LOST' => 'bg-red-50 text-red-700 border-red-100',
                ];
                $statusColor = $statusColors[$lead->status] ?? 'bg-slate-50 text-slate-700 border-slate-100';
            @endphp
            <div id="followup-row-mobile-{{ $lead->id }}" class="bg-white rounded-xl p-4 border border-slate-100 shadow-sm relative text-black">
                 <!-- Status Badge top right -->
                <div class="absolute top-4 right-4">
                    <span id="followup-lead-status-mobile-{{ $lead->id }}" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $statusColor }}">
                        {{ $lead->status }}
                    </span>
                </div>

                <div class="pr-16"> <!-- Padding for badge -->
                    <a href="#" 
                       id="followup-lead-name-mobile-{{ $lead->id }}"
                       @click.prevent="$dispatch('open-edit-lead-modal', { leadId: '{{ $lead->id }}' })" 
                       class="font-bold text-slate-900 text-sm block mb-1">{{ $lead->name }}</a>
                    <div class="flex items-center text-xs text-slate-500 mb-2">
                        <i data-lucide="book" class="w-3 h-3 mr-1"></i>
                        {{ $lead->course ?? 'No Product' }}
                    </div>

                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-50 text-xs text-slate-600 border border-slate-100">
                            <i data-lucide="globe" class="w-3 h-3 mr-1.5 text-slate-400"></i>
                            {{ $lead->source ?? 'N/A' }}
                        </span>
                        @if($lead->assignedTo)
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-50 text-xs text-slate-600 border border-slate-100">
                            <i data-lucide="user" class="w-3 h-3 mr-1.5 text-slate-400"></i>
                            {{ $lead->assignedTo->name }}
                        </span>
                        @endif
                    </div>

                    @if($lead->notes)
                    <div class="mb-3 p-2.5 bg-yellow-50/50 rounded-lg border border-yellow-100/50">
                        <div class="flex gap-2">
                            <i data-lucide="sticky-note" class="w-3 h-3 text-yellow-400 mt-0.5 shrink-0"></i>
                            <p id="followup-lead-notes-mobile-{{ $lead->id }}" class="text-xs text-slate-600 italic line-clamp-2 leading-relaxed">
                                {{ $lead->notes }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-50">
                    <div class="flex items-center">
                        <i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5 {{ $isOverdue ? 'text-red-500' : ($isToday ? 'text-amber-500' : 'text-slate-400') }}"></i>
                        <div>
                             <p class="text-xs font-bold {{ $isOverdue ? 'text-red-600' : ($isToday ? 'text-amber-600' : 'text-slate-700') }}">
                                <span id="followup-lead-time-mobile-{{ $lead->id }}">
                                    {{ $lead->next_follow_up->format('M d, h:i A') }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                         @php
                            $phoneClean = preg_replace('/[^0-9]/', '', $lead->phone);
                            $phoneForCall = str_starts_with($lead->phone, '+') ? $lead->phone : '+' . (strlen($phoneClean) === 10 ? '91' . $phoneClean : $phoneClean);
                            $phoneWithCode = str_starts_with($lead->phone, '+') ? $lead->phone : (strlen($phoneClean) === 10 ? '91' . $phoneClean : $phoneClean);
                        @endphp
                        <a href="https://wa.me/{{ $phoneWithCode }}" target="_blank" onclick="logLeadActivity('{{ $lead->id }}', 'whatsapp')"
                           class="p-2 rounded-lg bg-green-50 text-green-600 hover:bg-green-100" title="WhatsApp">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                        </a>
                        <a href="tel:{{ $phoneForCall }}" onclick="logLeadActivity('{{ $lead->id }}', 'call')"
                           class="p-2 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100" title="Call">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <p class="text-sm text-slate-500">No follow-ups found.</p>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($leads->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $leads->links() }}
        </div>
        @endif
    </div>


</div>
@endsection

@push('scripts')
<script>
    window.addEventListener('lead-updated', function(e) {
        const leadId = e.detail.id;
        const data = e.detail.data;
        
        // Helper to update elements by ID
        const updateText = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.textContent = text || '-';
        };

        // Update Desktop & Mobile Names
        updateText(`followup-lead-name-desktop-${leadId}`, data.name);
        updateText(`followup-lead-name-mobile-${leadId}`, data.name);
        
        // Update Notes
        updateText(`followup-lead-notes-desktop-${leadId}`, data.notes);
        updateText(`followup-lead-notes-mobile-${leadId}`, data.notes);


        // Update Status Badges
        const statusClasses = {
            'NEW': 'bg-blue-100 text-blue-700',
            'CONTACTED': 'bg-cyan-100 text-cyan-700',
            'QUALIFIED': 'bg-yellow-100 text-yellow-700',
            'CONVERTED': 'bg-green-100 text-green-700',
            'LOST': 'bg-red-100 text-red-700',
        };
        const colorClass = statusClasses[data.status] || 'bg-slate-100 text-slate-700';
        const baseClass = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase';

        ['desktop', 'mobile'].forEach(view => {
            const statusEl = document.getElementById(`followup-lead-status-${view}-${leadId}`);
            if (statusEl) {
                statusEl.textContent = data.status;
                statusEl.className = `${baseClass} ${colorClass}`;
            }
        });

        // Update Time
        if (data.next_follow_up) {
            const date = new Date(data.next_follow_up);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const formattedDate = `${months[date.getMonth()]} ${String(date.getDate()).padStart(2, '0')}, ${date.getHours() % 12 || 12}:${String(date.getMinutes()).padStart(2, '0')} ${date.getHours() >= 12 ? 'PM' : 'AM'}`;
            
            console.log('Updating time for lead', leadId, formattedDate);
            updateText(`followup-lead-time-desktop-${leadId}`, formattedDate);
            updateText(`followup-lead-time-mobile-${leadId}`, formattedDate);
        }

        // Logic to remove row if fitler mismatch
        const urlParams = new URLSearchParams(window.location.search);
        const filter = urlParams.get('filter') || 'all';

        // 1. Check Status Exclusion
        // Controller excludes: ['CONVERTED', 'LOST', 'Not interested', 'NOT INTERESTED']
        const excludedStatuses = ['CONVERTED', 'LOST', 'Not interested', 'NOT INTERESTED']; 
        // Normalize for check
        if (excludedStatuses.map(s => s.toUpperCase()).includes(data.status.toUpperCase())) {
            removeRow(leadId);
            return;
        }

        // 2. Check Date Filters
        if (data.next_follow_up) {
            const date = new Date(data.next_follow_up);
            const now = new Date();
            const today = new Date(); today.setHours(0,0,0,0);
            const dateDay = new Date(date); dateDay.setHours(0,0,0,0);
            
            let shouldRemove = false;
            
            if (filter === 'today') {
                if (dateDay.getTime() !== today.getTime()) shouldRemove = true;
            } else if (filter === 'tomorrow') {
                const tomorrow = new Date(today); tomorrow.setDate(today.getDate() + 1);
                if (dateDay.getTime() !== tomorrow.getTime()) shouldRemove = true;
            } else if (filter === 'overdue') {
                if (date >= now) shouldRemove = true; 
            } else if (filter === 'upcoming') {
                const dayAfterTomorrow = new Date(today); dayAfterTomorrow.setDate(today.getDate() + 2);
                const nextWeek = new Date(today); nextWeek.setDate(today.getDate() + 7);
                // Upcoming is > Tomorrow AND <= Next 7 Days
                if (dateDay < dayAfterTomorrow || dateDay > nextWeek) shouldRemove = true;
            }

            if (shouldRemove) removeRow(leadId);
        }

        function removeRow(id) {
            ['desktop', 'mobile'].forEach(view => {
                const row = document.getElementById(`followup-row-${view}-${id}`);
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s'; // Ensure transition
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)'; // Slide out
                    setTimeout(() => row.remove(), 300);
                }
            });
        }
    });
</script>
@endpush
