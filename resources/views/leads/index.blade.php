@extends('layouts.app')

@section('title', 'Leads - Edvube')

@section('content')
<script>
    function leadsManager(initialState) {
        return {
            selectedLeads: [],
            showBulkAssignModal: false,
            showMobileFilters: false,
            assignToAgent: '',
            bulkStatus: '',
            isLoading: false,
            
            searchQuery: initialState.searchQuery,
            dateFilter: initialState.dateFilter,
            mobileFilters: initialState.mobileFilters,

            init() {
                this.initPagination();
            },

            initPagination() {
                const container = document.getElementById('leads-list-container');
                if (!container) return;
                
                // Use a single listener for the container (Event Delegation)
                container.addEventListener('click', (e) => {
                    // Check if click is on a pagination link
                    const link = e.target.closest('a');
                    // Laravel pagination usually uses <nav role="navigation">
                    if (link && link.href && link.closest('nav[role="navigation"]')) {
                        e.preventDefault();
                        this.fetchLeads(link.href);
                    }
                });
            },

            async fetchLeads(url) {
                if (this.isLoading) return;
                this.isLoading = true;
                
                try {
                    const res = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    
                    if (!res.ok) throw new Error('Network response was not ok');
                    
                    const html = await res.text();
                    document.getElementById('leads-list-container').innerHTML = html;
                    
                    // Update URL
                    window.history.pushState({}, '', url);
                    
                    // Update Header Count if hidden span exists
                    const hiddenCount = document.getElementById('leads-total-hidden');
                    const headerCount = document.getElementById('leads-total-count');
                    if (hiddenCount && headerCount) {
                        headerCount.textContent = `(${hiddenCount.textContent})`;
                    }
                    
                    // Re-init icons
                    if (window.lucide) window.lucide.createIcons();
                    
                    // Scroll to top of list
                     const listTop = document.getElementById('leads-list-container').offsetTop;
                     window.scrollTo({ top: listTop - 150, behavior: 'smooth' });

                } catch (e) {
                    console.error('Fetch error', e);
                    window.location.href = url; // Fallback to full reload
                } finally {
                    this.isLoading = false;
                }
            },

            async applyFilters() {
                 const params = new URLSearchParams(window.location.search);
                 
                 // Update params from mobile filters
                 if (this.mobileFilters.assigned_to) params.set('assigned_to', this.mobileFilters.assigned_to); else params.delete('assigned_to');
                 if (this.mobileFilters.source) params.set('source', this.mobileFilters.source); else params.delete('source');
                 if (this.mobileFilters.course) params.set('course', this.mobileFilters.course); else params.delete('course');
                 if (this.mobileFilters.date_filter) params.set('date_filter', this.mobileFilters.date_filter); else params.delete('date_filter');
                 
                 // Reset page
                 params.set('page', 1);
                 
                 const url = `${window.location.pathname}?${params.toString()}`;
                 
                 await this.fetchLeads(url);
                 
                 // Collapse panel only on manual apply
                 this.showMobileFilters = false;
            },
            
            async clearFilters() {
                 this.mobileFilters = {
                     assigned_to: '',
                     source: '',
                     course: '',
                     date_filter: ''
                 };
                 // Also clear search? Usually yes dealing with "Clear All"
                 // But the user might want to keep search.
                 // The requested feature is "Clear" in the filter panel.
                 // I'll keep searchQuery but reset panel filters.
                 
                 await this.applyFilters();
            },

            toggleSelectAll(event) {
                if (event.target.checked) {
                    this.selectedLeads = Array.from(document.querySelectorAll('input[type="checkbox"][x-model="selectedLeads"]')).map(cb => cb.value);
                } else {
                    this.selectedLeads = [];
                }
            },
            
            async bulkAssign() {
                if (!this.assignToAgent) return;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                try {
                    const response = await fetch('{{ route("leads.bulk-assign") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ lead_ids: this.selectedLeads, assigned_to: this.assignToAgent })
                    });
                    if (response.ok) window.location.reload();
                    else alert('Failed to assign leads');
                } catch (error) { console.error('Error:', error); alert('An error occurred'); }
            },
            
            confirmBulkDelete() {
                if (confirm('Delete ' + this.selectedLeads.length + ' leads? This cannot be undone.')) {
                    this.bulkDelete();
                }
            },
            
            async bulkDelete() {
                 const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                try {
                    const response = await fetch('{{ route("leads.bulk-delete") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ lead_ids: this.selectedLeads })
                    });
                    if (response.ok) window.location.reload();
                    else alert('Failed to delete leads');
                } catch (error) { console.error('Error:', error); alert('An error occurred'); }
            },

            async bulkStatusUpdate() {
                if (!this.bulkStatus) return;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                try {
                    const response = await fetch('{{ route("leads.bulk-status-update") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ lead_ids: this.selectedLeads, status: this.bulkStatus })
                    });
                    if (response.ok) window.location.reload();
                    else alert('Failed to update status');
                } catch (error) { console.error('Error:', error); alert('An error occurred'); }
            }
        }
    }
</script>

<div class="space-y-3 md:space-y-6 animate-fade-in pb-48" 
    x-data="leadsManager({
        searchQuery: '{{ request('search') }}',
        dateFilter: '{{ request('date_filter') }}',
        mobileFilters: {
            assigned_to: '{{ request('assigned_to') }}',
            source: '{{ request('source') }}',
            course: '{{ request('course') }}',
            date_filter: '{{ request('date_filter') }}'
        }
    })">
    <!-- Sticky Header Wrapper -->
    <div class="sticky md:static top-0 z-20 bg-slate-50 pb-2 -mx-2 px-2 pt-2 md:-mx-4 md:px-4 md:pt-2 -mt-2 md:-mt-4 shadow-sm md:shadow-none transition-all">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 px-2 mb-2 md:mb-4">
            <div class="flex items-center gap-4">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                    Leads 
                    <span id="leads-total-count" class="text-gray-400 text-lg font-medium">({{ $leads->total() }})</span>
                </h1>
                @if(auth()->user()->role !== 'AGENT')
                <div class="flex items-center ml-4 mt-2">
                    <input type="checkbox" @change="toggleSelectAll($event)" id="selectAll" class="w-5 h-5 rounded-lg border-gray-300 text-primary-600 focus:ring-primary-500 mr-2 transition-all">
                    <label for="selectAll" class="text-sm font-medium text-gray-500 cursor-pointer hover:text-gray-700">Select All</label>
                </div>
                @endif
            </div>
            <div class="flex items-center space-x-3">
                @if(auth()->user()->role !== 'AGENT')
                <a href="{{ route('leads.export', request()->query()) }}" class="px-5 py-2.5 rounded-2xl border border-gray-100 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 shadow-soft text-sm font-semibold transition-all flex items-center">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i> Export
                </a>
                <button type="button" onclick="document.getElementById('import-modal').classList.remove('hidden')" class="px-5 py-2.5 rounded-2xl bg-gray-900 text-white hover:bg-gray-800 shadow-lg shadow-gray-200 text-sm font-semibold transition-all flex items-center">
                    <i data-lucide="upload" class="w-4 h-4 mr-2"></i> Import
                </button>
                @endif
            </div>
        </div>
        
        <!-- Filters & Controls -->
        @php
            $activeFiltersCount = 0;
            if(request('assigned_to')) $activeFiltersCount++;
            if(request('source')) $activeFiltersCount++;
            if(request('course')) $activeFiltersCount++;
            if(request('date_filter') && request('date_filter') !== 'all') $activeFiltersCount++;
        @endphp
        <div class="bg-white rounded-3xl p-3 md:p-5 shadow-soft border border-gray-100 transition-all duration-300">
            <form method="GET" action="{{ route('leads.index') }}" class="space-y-4">
                <div class="flex flex-col md:flex-row gap-4 justify-between">
                    <div class="flex gap-2 flex-1 max-w-lg">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                            <input type="text" name="search" x-model="searchQuery" placeholder="Search leads..." class="w-full pl-11 pr-10 py-3 rounded-2xl bg-gray-50 border-none text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-primary-500/20 focus:bg-white transition-all outline-none">
                            <button type="button" x-show="searchQuery && searchQuery.length > 0" x-cloak @click="searchQuery = ''; $nextTick(() => { $el.closest('form').submit(); });" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <button type="button" @click="showMobileFilters = !showMobileFilters" class="md:hidden px-4 rounded-2xl bg-primary-50 border border-primary-100 text-primary-600 flex items-center justify-center relative hover:bg-primary-100 transition-colors shadow-sm active:scale-95">
                            <i data-lucide="filter" class="w-5 h-5"></i>
                            @if($activeFiltersCount > 0)
                            <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-primary-600 text-white text-[10px] font-bold flex items-center justify-center shadow-lg ring-2 ring-white">{{ $activeFiltersCount }}</span>
                            @endif
                        </button>
                    </div>
                    <div class="hidden md:flex items-center gap-3">
                        <label for="per_page" class="text-xs text-gray-400 font-bold uppercase tracking-wider whitespace-nowrap">Show</label>
                        <select name="per_page" onchange="this.form.submit()" class="rounded-xl bg-gray-50 border-none text-gray-700 py-2 pl-3 pr-8 text-sm font-medium focus:ring-2 focus:ring-primary-500/20 outline-none cursor-pointer">
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                            <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000</option>
                        </select>
                    </div>
                </div>

                <!-- Desktop Grid (Hidden on Mobile) -->
                <div class="hidden md:grid md:grid-cols-5 md:gap-3">
                    @if(auth()->user()->role !== 'AGENT')
                    <select name="assigned_to" onchange="this.form.submit()" class="w-full rounded-xl bg-gray-50 border-none text-gray-600 px-3 py-2.5 text-xs font-medium focus:ring-2 focus:ring-primary-500/20 outline-none cursor-pointer hover:bg-gray-100 transition-colors">
                        <option value="">All Agents</option>
                        @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ request('assigned_to') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    @endif
                    <select name="source" onchange="this.form.submit()" class="w-full rounded-xl bg-gray-50 border-none text-gray-600 px-3 py-2.5 text-xs font-medium focus:ring-2 focus:ring-primary-500/20 outline-none cursor-pointer hover:bg-gray-100 transition-colors">
                        <option value="">All Sources</option>
                        @foreach($sources as $source)
                        <option value="{{ $source }}" {{ request('source') === $source ? 'selected' : '' }}>{{ $source }}</option>
                        @endforeach
                    </select>
                    <select name="course" onchange="this.form.submit()" class="w-full rounded-xl bg-gray-50 border-none text-gray-600 px-3 py-2.5 text-xs font-medium focus:ring-2 focus:ring-primary-500/20 outline-none cursor-pointer hover:bg-gray-100 transition-colors">
                        <option value="">All Products</option>
                        @foreach($courses as $courseOption)
                        <option value="{{ $courseOption }}" {{ request('course') === $courseOption ? 'selected' : '' }}>{{ $courseOption }}</option>
                        @endforeach
                    </select>
                    <select name="date_filter" x-model="dateFilter" onchange="if(this.value !== 'custom') this.form.submit()" class="w-full rounded-xl bg-gray-50 border-none text-gray-600 px-3 py-2.5 text-xs font-medium focus:ring-2 focus:ring-primary-500/20 outline-none cursor-pointer hover:bg-gray-100 transition-colors">
                        <option value="">All Dates</option>
                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="last_30_days" {{ request('date_filter') === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom Date</option>
                    </select>
                    <button type="submit" class="hidden"></button>
                </div>
                
                <!-- Custom Date Inputs (Desktop) -->
                <div x-show="dateFilter === 'custom'" x-cloak class="hidden md:grid grid-cols-2 gap-4 pt-4 border-t border-gray-50 md:col-span-5 md:pt-0 md:border-t-0">
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl bg-gray-50 border-none text-gray-700 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500/20">
                    </div>
                     <div>
                        <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">End Date</label>
                        <div class="flex gap-2">
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl bg-gray-50 border-none text-gray-700 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500/20">
                            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-xl text-xs font-bold hover:bg-primary-700 transition-colors shadow-lg shadow-primary-200">Go</button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Collapsible Panel -->
                <div class="md:hidden overflow-hidden transition-all duration-300 ease-in-out" 
                     x-show="showMobileFilters" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2">
                    
                    <div class="bg-gray-50 rounded-2xl p-4 mt-2 border border-gray-100 space-y-4 shadow-inner">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Refine Results</h4>
                            <button type="button" @click="showMobileFilters = false" class="text-xs text-gray-400 font-medium hover:text-gray-600">Close</button>
                        </div>
                        
                        <!-- Stacked Filters -->
                        <div class="space-y-3">
                             @if(auth()->user()->role !== 'AGENT')
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Assigned To</label>
                                <select x-model="mobileFilters.assigned_to" class="w-full rounded-xl bg-white border-gray-200 text-gray-700 py-2.5 px-3 text-sm focus:ring-2 focus:ring-primary-500/20 outline-none">
                                    <option value="">All Agents</option>
                                    @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Source</label>
                                <select x-model="mobileFilters.source" class="w-full rounded-xl bg-white border-gray-200 text-gray-700 py-2.5 px-3 text-sm focus:ring-2 focus:ring-primary-500/20 outline-none">
                                    <option value="">All Sources</option>
                                    @foreach($sources as $source)
                                    <option value="{{ $source }}">{{ $source }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Product</label>
                                <select x-model="mobileFilters.course" class="w-full rounded-xl bg-white border-gray-200 text-gray-700 py-2.5 px-3 text-sm focus:ring-2 focus:ring-primary-500/20 outline-none">
                                    <option value="">All Products</option>
                                    @foreach($courses as $courseOption)
                                    <option value="{{ $courseOption }}">{{ $courseOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase">Date</label>
                                <select x-model="mobileFilters.date_filter" class="w-full rounded-xl bg-white border-gray-200 text-gray-700 py-2.5 px-3 text-sm focus:ring-2 focus:ring-primary-500/20 outline-none">
                                    <option value="">All Dates</option>
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="last_30_days">Last 30 Days</option>
                                    <option value="this_month">This Month</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-2">
                             <button type="button" @click="clearFilters()" class="flex-1 py-2.5 bg-white text-gray-500 rounded-xl text-sm font-bold border border-gray-200 hover:bg-gray-50 transition-colors">
                                Clear
                            </button>
                            <button type="button" @click="applyFilters()" class="flex-1 py-2.5 bg-primary-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-primary-200 hover:bg-primary-700 transition-colors">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar pt-2">
                    <a href="{{ route('leads.index', array_merge(request()->query(), ['status' => ''])) }}" class="px-4 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap transition-all border {{ !request('status') ? 'bg-gray-900 text-white border-gray-900 shadow-md transform scale-105' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">All</a>
                    @foreach($statuses as $status)
                    @php
                        $isActive = request('status') === $status;
                        $statusColorMap = [
                            'NEW' => $isActive ? 'bg-blue-600 border-blue-600 text-white' : 'text-blue-600 border-blue-100 bg-blue-50 hover:bg-blue-100',
                            'CONTACTED' => $isActive ? 'bg-cyan-600 border-cyan-600 text-white' : 'text-cyan-600 border-cyan-100 bg-cyan-50 hover:bg-cyan-100',
                            'QUALIFIED' => $isActive ? 'bg-amber-500 border-amber-500 text-white' : 'text-amber-600 border-amber-100 bg-amber-50 hover:bg-amber-100',
                            'CONVERTED' => $isActive ? 'bg-emerald-600 border-emerald-600 text-white' : 'text-emerald-600 border-emerald-100 bg-emerald-50 hover:bg-emerald-100',
                            'LOST' => $isActive ? 'bg-red-500 border-red-500 text-white' : 'text-red-500 border-red-100 bg-red-50 hover:bg-red-100',
                        ];
                        $classes = $statusColorMap[$status] ?? ($isActive ? 'bg-gray-800 border-gray-800 text-white' : 'text-gray-600 border-gray-200 bg-white');
                    @endphp
                    <a href="{{ route('leads.index', array_merge(request()->query(), ['status' => $status])) }}" class="px-4 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap transition-all border uppercase tracking-wide {{ $classes }} {{ $isActive ? 'shadow-md transform scale-105' : '' }}">{{ $status }}</a>
                    @endforeach
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bulk Actions Bar -->
    <div x-show="selectedLeads.length > 0" x-cloak class="sticky top-24 z-40 bg-gray-900 text-white rounded-2xl p-3 md:p-4 shadow-xl shadow-gray-200 mx-1 animate-fade-in mb-6 ring-1 ring-black/5 md:relative md:flex md:items-center md:justify-between">
        <!-- Row 1: Count + Clear -->
        <div class="flex items-center justify-between md:justify-start md:gap-4">
            <span class="font-bold text-sm flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs">
                    <span x-text="selectedLeads.length"></span>
                </div>
                Selected
            </span>
            <button type="button" @click="selectedLeads = []; bulkStatus = ''; document.getElementById('selectAll').checked = false;" class="text-gray-400 hover:text-white text-xs font-medium transition-colors md:hidden">Clear</button>
            <button type="button" @click="selectedLeads = []; bulkStatus = ''; document.getElementById('selectAll').checked = false;" class="hidden md:inline text-gray-400 hover:text-white text-xs font-medium transition-colors border-b border-dashed border-gray-600 hover:border-white">Clear Selection</button>
        </div>

        <!-- Row 2: Status update (full width mobile) -->
        <div class="flex items-center gap-2 mt-2.5 md:mt-0">
            <select x-model="bulkStatus" class="flex-1 md:flex-none md:w-40 rounded-xl bg-white/10 border border-white/20 text-white text-xs font-medium px-3 py-2.5 md:py-1.5 outline-none focus:ring-2 focus:ring-white/30 cursor-pointer">
                <option value="" class="text-gray-900">Change Status...</option>
                @foreach($statuses as $status)
                <option value="{{ $status }}" class="text-gray-900">{{ $status }}</option>
                @endforeach
            </select>
            <button type="button" @click="bulkStatusUpdate()" :disabled="!bulkStatus" class="px-4 py-2.5 md:py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold tracking-wide transition-all whitespace-nowrap">Update</button>
        </div>

        <!-- Row 3: Admin actions (full width mobile buttons) -->
        @if(auth()->user()->role !== 'AGENT')
        <div class="flex items-center gap-2 mt-2 md:mt-0">
            <button type="button" @click="showBulkAssignModal = true" class="flex-1 md:flex-none px-4 py-2.5 md:py-1.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold tracking-wide transition-all text-center">Assign</button>
            <button type="button" @click="confirmBulkDelete()" class="flex-1 md:flex-none px-4 py-2.5 md:py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold tracking-wide transition-all text-center">Delete</button>
        </div>
        @endif
    </div>
    
    <!-- Leads List Container for AJAX -->
    <div id="leads-list-container" :class="{ 'opacity-50 pointer-events-none': isLoading }">
        @include('leads.partials.lead-list')
    </div>

    <!-- Modals -->
    @if(auth()->user()->role !== 'AGENT')
    <div x-show="showBulkAssignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showBulkAssignModal = false" x-transition.opacity></div>
        <div class="bg-white rounded-3xl p-8 w-full max-w-md mx-4 relative z-10 shadow-2xl transform transition-all" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Assign Leads to Agent</h3>
            <form @submit.prevent="bulkAssign()">
                <div class="mb-8">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Select Agent</label>
                    <select x-model="assignToAgent" required class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200 text-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-all">
                        <option value="">Choose agent...</option>
                        @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="showBulkAssignModal = false" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold transition-colors shadow-lg shadow-primary-200">Assign <span x-text="selectedLeads.length"></span> Leads</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    
    <div id="import-modal" class="fixed inset-0 z-50 hidden" x-data="{ open: true }">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="document.getElementById('import-modal').classList.add('hidden')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
             <div class="w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl transform transition-all">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Import Leads</h3>
                    <button onclick="document.getElementById('import-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1 bg-gray-50 rounded-full hover:bg-gray-100 transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <form action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">CSV File</label>
                        <div class="relative">
                            <input type="file" name="file" accept=".csv,.txt" required class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200 text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 file:font-bold hover:file:bg-primary-100 file:cursor-pointer transition-all">
                        </div>
                        <p class="text-xs text-gray-400 mt-3 flex items-center"><i data-lucide="info" class="w-3 h-3 mr-1.5"></i>Required: name. Optional: phone, email, source, course.</p>
                    </div>
                    <div class="flex items-center justify-between bg-primary-50 p-4 rounded-2xl">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-primary-600 mr-3 shadow-sm"><i data-lucide="file-spreadsheet" class="w-4 h-4"></i></div>
                            <span class="text-sm font-medium text-primary-900">Need a template?</span>
                        </div>
                        <a href="{{ route('leads.sample') }}" class="text-xs font-bold text-primary-600 hover:text-primary-700 bg-white px-3 py-1.5 rounded-lg shadow-sm hover:shadow transition-all">Download</a>
                    </div>
                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold transition-colors shadow-lg">Import Leads</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('lead-updated', function(e) {
        const leadId = e.detail.id;
        const data = e.detail.data;
        const nameEl = document.getElementById(`lead-name-${leadId}`);
        if (nameEl) nameEl.textContent = data.name;
        const statusEl = document.getElementById(`lead-status-${leadId}`);
        if (statusEl) {
            statusEl.textContent = data.status;
            const statusClasses = {
                'NEW': 'bg-blue-50 text-blue-700 border-blue-100',
                'CONTACTED': 'bg-cyan-50 text-cyan-700 border-cyan-100',
                'QUALIFIED': 'bg-amber-50 text-amber-700 border-amber-100',
                'CONVERTED': 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'LOST': 'bg-red-50 text-red-700 border-red-100',
            };
            const baseClasses = 'px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase border';
            const colorClasses = statusClasses[data.status] || 'bg-gray-50 text-gray-700 border-gray-100';
            statusEl.className = `${baseClasses} ${colorClasses}`;
        }
        const notesEl = document.getElementById(`lead-notes-${leadId}`);
        if (notesEl) notesEl.textContent = `"${data.notes}"`;
        const followUpEl = document.getElementById(`lead-next-follow-up-${leadId}`);
        if (followUpEl && data.next_follow_up) {
            const date = new Date(data.next_follow_up);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const formattedDate = `${months[date.getMonth()]} ${String(date.getDate()).padStart(2, '0')}, ${date.getHours() % 12 || 12}:${String(date.getMinutes()).padStart(2, '0')} ${date.getHours() >= 12 ? 'PM' : 'AM'}`;
            followUpEl.innerHTML = `<i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5"></i> ${formattedDate}`;
            if (window.lucide) window.lucide.createIcons();
        }
        const urlParams = new URLSearchParams(window.location.search);
        const statusFilter = urlParams.get('status');
        if (statusFilter && statusFilter !== data.status) {
            const row = document.getElementById(`lead-row-${leadId}`);
            if (row) {
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
        }
    });
</script>
@endsection
