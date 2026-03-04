    <!-- Leads List (Compact Cards with High Whitespace) -->
    <span id="leads-total-hidden" class="hidden">{{ $leads->total() }}</span>
    <div class="space-y-3">
        @forelse($leads as $lead)
        <div id="lead-row-{{ $lead->id }}" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-soft relative hover:shadow-lg transition-all duration-300 group hover:-translate-y-0.5">
            <div class="flex items-center gap-4">
                @if(auth()->user()->role !== 'AGENT')
                <div class="">
                    <input type="checkbox" value="{{ $lead->id }}" x-model="selectedLeads"
                           class="w-5 h-5 rounded-lg border-gray-300 text-primary-600 focus:ring-primary-500 cursor-pointer transition-all">
                </div>
                @endif
                
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <!-- Left Info -->
                        <div class="flex-1 min-w-0 pr-6">
                            <div class="flex items-center gap-3 mb-1">
                                <a href="#" 
                                   id="lead-name-{{ $lead->id }}"
                                   @click.prevent="$dispatch('open-edit-lead-modal', { leadId: '{{ $lead->id }}' })" 
                                   class="hover:text-primary-600 transition-colors">{{ $lead->name }}</a>
                                @php
                                    $statusColors = [
                                        'NEW' => 'bg-blue-50 text-blue-700 border-blue-100',
                                        'CONTACTED' => 'bg-cyan-50 text-cyan-700 border-cyan-100',
                                        'QUALIFIED' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'CONVERTED' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'LOST' => 'bg-red-50 text-red-700 border-red-100',
                                    ];
                                    $statusColor = $statusColors[$lead->status] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                @endphp
                                <span id="lead-status-{{ $lead->id }}" class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase border {{ $statusColor }}">
                                    {{ $lead->status }}
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs font-medium text-gray-500 mt-2">
                                <span class="flex items-center group-hover:text-gray-700 transition-colors" title="Course">
                                    <i data-lucide="book" class="w-3.5 h-3.5 mr-1.5 text-gray-400 group-hover:text-primary-500"></i>
                                    {{ $lead->course ?? 'No Course' }}
                                </span>
                                <span class="flex items-center group-hover:text-gray-700 transition-colors" title="Source">
                                    <i data-lucide="globe" class="w-3.5 h-3.5 mr-1.5 text-gray-400 group-hover:text-primary-500"></i>
                                    {{ $lead->source ?? 'Unknown Source' }}
                                </span>
                                @if($lead->assignedTo)
                                <span class="flex items-center group-hover:text-gray-700 transition-colors" title="Assigned To">
                                    <i data-lucide="user" class="w-3.5 h-3.5 mr-1.5 text-gray-400 group-hover:text-primary-500"></i>
                                    {{ $lead->assignedTo->name }}
                                </span>
                                @endif
                                @if($lead->next_follow_up)
                                <span id="lead-next-follow-up-{{ $lead->id }}" class="flex items-center text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded-lg">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5"></i>
                                    {{ $lead->next_follow_up->format('M d, h:i A') }}
                                </span>
                                @endif
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex items-center gap-2">
                             @php
                                $phoneClean = preg_replace('/[^0-9]/', '', $lead->phone);
                                $phoneWithCode = str_starts_with($lead->phone, '+') ? $lead->phone : (strlen($phoneClean) === 10 ? '91' . $phoneClean : $phoneClean);
                            @endphp
                            <a href="https://wa.me/{{ $phoneClean }}" target="_blank" onclick="logLeadActivity('{{ $lead->id }}', 'whatsapp')"
                               class="p-2.5 rounded-xl bg-green-50 text-green-600 hover:bg-green-500 hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-green-200 transform hover:scale-105" title="WhatsApp">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                            </a>
                            <a href="tel:+{{ $phoneClean }}" onclick="logLeadActivity('{{ $lead->id }}', 'call')"
                               class="p-2.5 rounded-xl bg-primary-50 text-primary-600 hover:bg-primary-600 hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-primary-200 transform hover:scale-105" title="Call">
                                <i data-lucide="phone" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>

                    @if($lead->notes)
                    <div id="lead-notes-{{ $lead->id }}" class="mt-3 pl-3 border-l-2 border-gray-100 text-xs text-gray-400 italic truncate max-w-2xl group-hover:text-gray-500 transition-colors">
                        "{{ $lead->notes }}"
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16 bg-white rounded-3xl border border-gray-100 shadow-soft">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="search-x" class="w-8 h-8 text-gray-300"></i>
            </div>
            <h3 class="text-base font-bold text-gray-900">No leads found</h3>
            <p class="text-sm text-gray-500 mt-2 max-w-xs mx-auto">We couldn't find any leads matching your current filters. Try removing some filters.</p>
            <div class="mt-6 flex justify-center gap-3">
                 <a href="{{ route('leads.index', ['reset' => 1]) }}" class="px-5 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Clear Filters
                </a>
                <a href="{{ route('leads.create') }}" class="px-5 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition-colors shadow-lg shadow-primary-200">
                    Add Lead
                </a>
            </div>
        </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4">
        {{ $leads->appends(request()->query())->links() }}

        <!-- Mobile Rows Per Page -->
        <div class="md:hidden flex items-center gap-3 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-100">
            <label for="per_page_mobile" class="text-xs text-gray-400 font-bold uppercase tracking-wider whitespace-nowrap">Show</label>
            <form method="GET" action="{{ route('leads.index') }}">
                @foreach(request()->except('per_page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <select name="per_page" id="per_page_mobile" onchange="this.form.submit()" class="bg-transparent border-none text-gray-700 text-sm font-medium focus:ring-0 outline-none cursor-pointer">
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000</option>
                </select>
            </form>
        </div>
    </div>
