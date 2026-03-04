
<div x-data="editLeadModal()" 
     x-show="open" 
     @open-edit-lead-modal.window="openModal($event.detail.leadId)" 
     @keydown.escape.window="closeModal()" 
     style="display: none;" 
     class="relative z-50" 
     aria-labelledby="edit-lead-modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Backdrop -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-0 sm:items-center sm:p-4">
            <!-- Modal Panel -->
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95" 
                 @click.outside="closeModal()" 
                 class="relative transform overflow-hidden rounded-t-3xl sm:rounded-3xl bg-white text-left shadow-2xl transition-all w-full sm:max-w-4xl border-t sm:border border-gray-100 max-h-[90vh] flex flex-col">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur z-20">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900" id="edit-lead-modal-title">Edit Lead</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="lead ? lead.name : 'Loading...'"></p>
                    </div>
                    <div class="flex items-center gap-3">
                         @if(auth()->user()->role !== 'AGENT')
                        <button type="button" 
                                x-show="lead"
                                @click="confirmDelete()"
                                class="p-2 rounded-full text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete Lead">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                        @endif
                        
                        <button type="button" 
                                @click="updateLead()" 
                                :disabled="isSaving || !lead"
                                class="rounded-xl bg-gray-900 px-5 py-2 text-sm font-bold text-white shadow-lg hover:bg-gray-800 hover:shadow-xl hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-gray-900/20 transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex items-center">
                            <span x-show="!isSaving">Save</span>
                            <span x-show="isSaving" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>

                        <button @click="closeModal()" type="button" class="rounded-full bg-gray-50 p-2 text-gray-400 hover:text-gray-600 focus:outline-none hover:bg-gray-100 transition-colors ml-2">
                            <span class="sr-only">Close</span>
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Content (Scrollable) -->
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    
                    <!-- Loading State -->
                    <div x-show="isLoading" class="flex flex-col items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary-600 mb-4"></div>
                        <p class="text-gray-500 font-medium">Loading details...</p>
                    </div>

                    <!-- Main Content Grid -->
                    <div x-show="!isLoading && lead" class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-gray-100 min-h-[500px]">
                        
                        <!-- Left Column: Form -->
                        <div class="lg:col-span-2 px-6 py-6 space-y-6">
                            <form @submit.prevent="updateLead()" class="space-y-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <!-- Name -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Name</label>
                                        <input type="text" x-model="formData.name" 
                                               {{ auth()->user()->role === 'AGENT' ? 'readonly' : '' }}
                                               class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all font-medium {{ auth()->user()->role === 'AGENT' ? 'opacity-70 cursor-not-allowed' : '' }}">
                                    </div>

                                    <!-- Phone -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Phone</label>
                                        <input type="tel" x-model="formData.phone" 
                                               {{ auth()->user()->role === 'AGENT' ? 'readonly' : '' }}
                                               class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all font-medium {{ auth()->user()->role === 'AGENT' ? 'opacity-70 cursor-not-allowed' : '' }}">
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Email</label>
                                        <input type="email" x-model="formData.email" 
                                               {{ auth()->user()->role === 'AGENT' ? 'readonly' : '' }}
                                               class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all font-medium {{ auth()->user()->role === 'AGENT' ? 'opacity-70 cursor-not-allowed' : '' }}">
                                    </div>
                                    
                                     <!-- Course -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Course</label>
                                        @if(auth()->user()->role === 'AGENT')
                                            <input type="text" x-model="formData.course" readonly 
                                                   class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 opacity-70 cursor-not-allowed font-medium">
                                        @else
                                        <select x-model="formData.course"
                                                class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all cursor-pointer font-medium">
                                            <option value="">Select Course</option>
                                            @foreach(\App\Models\AppSetting::getCourses(auth()->user()->organization_id) as $course)
                                                <option value="{{ $course }}">{{ $course }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </div>

                                    <!-- Source -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Source</label>
                                         @if(auth()->user()->role === 'AGENT')
                                            <input type="text" x-model="formData.source" readonly 
                                                   class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 opacity-70 cursor-not-allowed font-medium">
                                        @else
                                        <select x-model="formData.source"
                                                class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all cursor-pointer font-medium">
                                            <option value="">Select Source</option>
                                            @foreach(\App\Models\AppSetting::getSources(auth()->user()->organization_id) as $source)
                                                <option value="{{ $source }}">{{ $source }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                    </div>

                                    <!-- Assigned To (Admin Only) -->
                                    @if(auth()->user()->role !== 'AGENT')
                                    <div x-show="agents && agents.length > 0">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Assigned To</label>
                                        <select x-model="formData.assigned_to"
                                                class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all cursor-pointer font-medium">
                                            <option value="">Select Agent</option>
                                            <template x-for="agent in agents" :key="agent.id">
                                                <option :value="agent.id" x-text="agent.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    @endif

                                    <!-- Status (Editable by All) -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Status</label>
                                        <select x-model="formData.status"
                                                class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all cursor-pointer font-medium">
                                            @foreach(\App\Models\AppSetting::getStatuses(auth()->user()->organization_id) as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Notes (Editable by All) -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Notes</label>
                                    <textarea x-model="formData.notes" rows="4" 
                                              class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all resize-none font-medium"></textarea>
                                </div>

                                <!-- Next Follow-up (Editable by All) -->
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Next Follow-up</label>
                                    <input type="datetime-local" x-model="formData.next_follow_up" class="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none mb-2">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" @click="setQuickFollowUp(1, 'h')" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+1h</button>
                                        <button type="button" @click="setQuickFollowUp(3, 'h')" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+3h</button>
                                        <button type="button" @click="setQuickFollowUp(5, 'h')" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+5h</button>
                                        <button type="button" @click="setQuickFollowUp(1, 'd')" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">Tmrw</button>
                                        <button type="button" @click="setQuickFollowUp(3, 'd')" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">+3 Days</button>
                                        <button type="button" @click="setQuickFollowUp(7, 'd')" class="px-2 py-1 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 rounded border border-slate-200 transition-colors">1 Week</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Right Column: Activity History -->
                        <div class="px-6 py-6 bg-gray-50/50">
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <i data-lucide="history" class="w-4 h-4"></i>
                                Activity History
                            </h4>
                            
                            <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                                <template x-if="activities && activities.length > 0">
                                    <template x-for="activity in activities" :key="activity.id">
                                        <div class="relative pl-4 border-l-2 border-gray-200 pb-2 last:pb-0">
                                            <div class="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full bg-gray-300 ring-4 ring-white"></div>
                                            <p class="text-xs text-gray-500 mb-0.5" x-text="new Date(activity.created_at).toLocaleString()"></p>
                                            <p class="text-sm text-gray-800 font-medium" x-text="activity.description || activity.type"></p>
                                            <p class="text-xs text-gray-500 mt-0.5" x-text="activity.details"></p>
                                        </div>
                                    </template>
                                </template>
                                <template x-if="!activities || activities.length === 0">
                                    <div class="text-center py-8">
                                        <p class="text-sm text-gray-400 italic">No activity recorded yet.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form (Hidden) -->
<form id="delete-lead-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function editLeadModal() {
    return {
        open: false,
        isLoading: false,
        isSaving: false,
        leadId: null,
        lead: null,
        activities: [],
        agents: [],
        formData: {
            name: '',
            phone: '',
            email: '',
            status: '',
            course: '',
            source: '',
            assigned_to: '',
            notes: '',
            next_follow_up: ''
        },
        
        openModal(id) {
            this.open = true;
            this.leadId = id;
            this.loadLead(id);
        },
        
        closeModal() {
            this.open = false;
            setTimeout(() => {
                this.lead = null;
                this.leadId = null;
                this.activities = [];
                this.agents = [];
            }, 300);
        },
        
        async loadLead(id) {
            this.isLoading = true;
            try {
                const response = await fetch(`/leads/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) throw new Error('Failed to load lead');
                
                const data = await response.json();
                this.lead = data.lead;
                this.activities = data.activities;
                this.agents = data.agents || [];
                
                // Populate form data
                this.formData = {
                    name: this.lead.name || '',
                    phone: this.lead.phone || '',
                    email: this.lead.email || '',
                    status: this.lead.status || '',
                    course: this.lead.course || '',
                    source: this.lead.source || '',
                    assigned_to: (this.lead.assigned_to && typeof this.lead.assigned_to === 'object') ? this.lead.assigned_to.id : (this.lead.assigned_to || ''),
                    notes: this.lead.notes || '',
                    next_follow_up: this.lead.next_follow_up ? this.lead.next_follow_up.substring(0, 16) : ''
                };
            } catch (error) {
                console.error('Error:', error);
                window.showToast('Failed to load lead details', 'error');
                this.closeModal();
            } finally {
                this.isLoading = false;
            }
        },
        
        async updateLead() {
            this.isSaving = true;
            try {
                const response = await fetch(`/leads/${this.leadId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    window.showToast('Lead updated successfully!');
                    this.closeModal();
                    // Dispatch event to update the list row without reloading
                    window.dispatchEvent(new CustomEvent('lead-updated', { 
                        detail: { 
                            id: this.leadId, 
                            data: this.formData 
                        } 
                    }));
                } else {
                    window.showToast(data.message || 'Failed to update lead', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                window.showToast('An error occurred while saving', 'error');
            } finally {
                this.isSaving = false;
            }
        },
        
        confirmDelete() {
            if (confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
                const form = document.getElementById('delete-lead-form');
                form.action = `/leads/${this.leadId}`;
                form.submit();
            }
        },

        setQuickFollowUp(amount, unit) {
            const now = new Date();
            
            if (unit === 'h') {
                now.setHours(now.getHours() + amount);
            } else if (unit === 'd') {
                now.setDate(now.getDate() + amount);
                now.setHours(10, 0, 0, 0);
            }
            
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            this.formData.next_follow_up = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    }
}
</script>
