@extends('layouts.app')

@section('title', $lead->name . ' - Tancube CRM')
@section('page-title', 'Lead Details')

@section('content')
<div class="max-w-5xl mx-auto animate-fade-in space-y-6">
    <!-- Lead Header -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-200">
                    <span class="text-2xl font-bold text-white">{{ strtoupper(substr($lead->name, 0, 1)) }}</span>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">{{ $lead->name }}</h2>
                    <div class="flex items-center space-x-4 mt-1">
                        @php
                            $statusColors = [
                                'NEW' => 'bg-blue-100 text-blue-700',
                                'CONTACTED' => 'bg-cyan-100 text-cyan-700',
                                'QUALIFIED' => 'bg-yellow-100 text-yellow-700',
                                'NEGOTIATION' => 'bg-purple-100 text-purple-700',
                                'CONVERTED' => 'bg-green-100 text-green-700',
                                'LOST' => 'bg-red-100 text-red-700',
                            ];
                            $statusColor = $statusColors[$lead->status] ?? 'bg-slate-100 text-slate-600';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-sm font-bold {{ $statusColor }}">{{ $lead->status }}</span>
                        @if($lead->source)
                        <span class="text-slate-500 text-sm">via {{ $lead->source }}</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex items-center space-x-2">
                @if($lead->phone)
                @php
                    $phoneClean = preg_replace('/\D/', '', $lead->phone);
                    $phoneWithCode = strlen($phoneClean) === 10 ? '+91' . $phoneClean : '+' . $phoneClean;
                @endphp
                <a href="https://wa.me/{{ ltrim($phoneWithCode, '+') }}" 
                   target="_blank"
                   onclick="logActivity('whatsapp')"
                   class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors flex items-center shadow-sm">
                    <i data-lucide="message-circle" class="w-4 h-4 mr-2"></i>
                    WhatsApp
                </a>
                <a href="tel:{{ $phoneWithCode }}" 
                   onclick="logActivity('call')"
                   class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors flex items-center shadow-sm">
                    <i data-lucide="phone" class="w-4 h-4 mr-2"></i>
                    Call
                </a>
                @endif
                <a href="{{ route('leads.index') }}" class="px-4 py-2 rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 transition-colors shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            @if(auth()->user()->role !== 'AGENT')
            <!-- Edit Form - Admin Only -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Lead Information</h3>
                
                <form action="{{ route('leads.update', $lead->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Name</label>
                            <input type="text" name="name" value="{{ $lead->name }}" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Phone</label>
                            <input type="tel" name="phone" value="{{ $lead->phone }}" placeholder="+91XXXXXXXXXX" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                            <input type="email" name="email" value="{{ $lead->email }}" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Course</label>
                            <select name="course" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                <option value="{{ $course }}" {{ $lead->course === $course ? 'selected' : '' }}>{{ $course }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Source</label>
                            <select name="source" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                                <option value="">Select Source</option>
                                @foreach($sources as $source)
                                <option value="{{ $source }}" {{ $lead->source === $source ? 'selected' : '' }}>{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                            <select name="status" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                                @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ $lead->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Assigned To</label>
                            <select name="assigned_to" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                                <option value="">Unassigned</option>
                                @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ $lead->assigned_to === $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Next Follow-up</label>
                            <input type="datetime-local" name="next_follow_up" value="{{ $lead->next_follow_up?->format('Y-m-d\TH:i') }}" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none mb-2">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="setQuickFollowUp(1, 'h', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+1h</button>
                                <button type="button" onclick="setQuickFollowUp(3, 'h', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+3h</button>
                                <button type="button" onclick="setQuickFollowUp(5, 'h', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+5h</button>
                                <button type="button" onclick="setQuickFollowUp(1, 'd', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">Tmrw</button>
                                <button type="button" onclick="setQuickFollowUp(3, 'd', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+3 Days</button>
                                <button type="button" onclick="setQuickFollowUp(7, 'd', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">1 Week</button>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none resize-none">{{ $lead->notes }}</textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
            @else
            <!-- Agent View - Can edit notes and follow-up only -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Lead Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6">
                    <div>
                        <dt class="text-slate-500">Name</dt>
                        <dd class="text-slate-900 font-medium mt-1">{{ $lead->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="text-slate-900 font-medium mt-1">{{ $lead->phone ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Email</dt>
                        <dd class="text-slate-900 font-medium mt-1">{{ $lead->email ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Course</dt>
                        <dd class="text-slate-900 font-medium mt-1">{{ $lead->course ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Source</dt>
                        <dd class="text-slate-900 font-medium mt-1">{{ $lead->source ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Status</dt>
                        <dd class="text-slate-900 font-medium mt-1">{{ $lead->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Assigned To</dt>
                        <dd class="text-slate-900 font-medium mt-1">{{ $lead->assignedTo->name ?? 'Unassigned' }}</dd>
                    </div>
                </dl>
                
                <!-- Editable Notes and Follow-up for Agents -->
                <form action="{{ route('leads.update', $lead->id) }}" method="POST" class="space-y-4 border-t border-slate-100 pt-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ $lead->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Notes</label>
                        <textarea name="notes" rows="3" placeholder="Add your notes here..." class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none resize-none">{{ $lead->notes }}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Next Follow-up</label>
                        <input type="datetime-local" name="next_follow_up" value="{{ $lead->next_follow_up?->format('Y-m-d\TH:i') }}" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none mb-2">
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="setQuickFollowUp(1, 'h', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+1h</button>
                            <button type="button" onclick="setQuickFollowUp(3, 'h', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+3h</button>
                            <button type="button" onclick="setQuickFollowUp(5, 'h', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+5h</button>
                            <button type="button" onclick="setQuickFollowUp(1, 'd', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">Tmrw</button>
                            <button type="button" onclick="setQuickFollowUp(3, 'd', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+3 Days</button>
                            <button type="button" onclick="setQuickFollowUp(7, 'd', this)" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">1 Week</button>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors shadow-sm shadow-indigo-200">
                            Save Notes
                        </button>
                    </div>
                </form>
            </div>
            @endif
            

        
        <!-- Activity History -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200" x-data="{ expandedNote: null }">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Activity History</h3>
                <div class="space-y-4" id="activity-list">
                    @forelse($activities as $index => $activity)
                    <div class="flex items-start space-x-3 py-3 border-b border-slate-100 last:border-0 hover:bg-slate-50 -mx-3 px-3 rounded-lg transition-colors">
                        <div class="shrink-0">
                            @php
                                $iconClass = match($activity->action) {
                                    'CALL_INITIATED' => 'phone text-green-600',
                                    'WHATSAPP_CLICKED' => 'message-circle text-green-600',
                                    'NOTE_UPDATED' => 'edit-3 text-yellow-600',
                                    'STATUS_CHANGED' => 'refresh-cw text-blue-600',
                                    'FOLLOW_UP_SET' => 'clock text-purple-600',
                                    'LEAD_CREATED' => 'plus-circle text-indigo-600',
                                    default => 'activity text-slate-400',
                                };
                            @endphp
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                <i data-lucide="{{ explode(' ', $iconClass)[0] }}" class="w-4 h-4 {{ explode(' ', $iconClass)[1] ?? 'text-slate-400' }}"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            @if(strlen($activity->details) > 100)
                            <div>
                                <p class="text-slate-900 text-sm" x-show="expandedNote !== {{ $index }}">
                                    {{ Str::limit($activity->details, 100) }}
                                    <button type="button" @click="expandedNote = {{ $index }}" class="text-indigo-600 hover:text-indigo-700 ml-1 font-medium">Show more</button>
                                </p>
                                <p class="text-slate-900 text-sm whitespace-pre-wrap" x-show="expandedNote === {{ $index }}" x-cloak>
                                    {{ $activity->details }}
                                    <button type="button" @click="expandedNote = null" class="text-indigo-600 hover:text-indigo-700 ml-1 block mt-1 font-medium">Show less</button>
                                </p>
                            </div>
                            @else
                            <p class="text-slate-900 text-sm">{{ $activity->details }}</p>
                            @endif
                            <div class="flex items-center space-x-2 mt-1 text-xs text-slate-400">
                                <span class="font-medium text-slate-500">{{ $activity->user_name }}</span>
                                <span>•</span>
                                <span>{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-400 text-sm text-center py-4">No activity yet</p>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">

            
            <!-- Lead Meta -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-slate-50 pb-2">
                        <dt class="text-slate-500">Created</dt>
                        <dd class="text-slate-900 font-medium">{{ $lead->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-50 pb-2">
                        <dt class="text-slate-500">Last Contacted</dt>
                        <dd class="text-slate-900 font-medium">{{ $lead->last_contacted ? $lead->last_contacted->format('M d, Y') : '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Assigned To</dt>
                        <dd class="text-slate-900 font-medium">{{ $lead->assignedTo->name ?? 'Unassigned' }}</dd>
                    </div>
                </dl>
            </div>
            
            @if(auth()->user()->role !== 'AGENT')
            <!-- Delete - Admin Only -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-4">Danger Zone</h3>
                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 transition-colors border border-red-100 font-medium">
                        Delete Lead
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function logActivity(type) {
    fetch('{{ route("leads.log-activity", $lead->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ type: type })
    });
}

function setQuickFollowUp(amount, unit, btn) {
    const input = btn.closest('div').previousElementSibling; // The input is the sibling before the button container
    const now = new Date();
    
    if (unit === 'h') {
        now.setHours(now.getHours() + amount);
    } else if (unit === 'd') {
        now.setDate(now.getDate() + amount);
        now.setHours(10, 0, 0, 0); // Default to 10 AM for future days
    }
    
    // Format to YYYY-MM-DDTHH:mm
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    input.value = `${year}-${month}-${day}T${hours}:${minutes}`;
}
</script>
@endpush
@endsection
