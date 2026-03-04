<div x-data="{ open: false }" 
     x-show="open" 
     @open-add-task-modal.window="open = true" 
     @keydown.escape.window="open = false" 
     style="display: none;" 
     class="relative z-50" 
     aria-labelledby="modal-title" 
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
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 @click.outside="open = false" 
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                
                <!-- Close Button -->
                <div class="absolute right-5 top-5 z-20">
                    <button @click="open = false" type="button" class="rounded-full bg-gray-50 p-2 text-gray-400 hover:text-gray-600 focus:outline-none hover:bg-gray-100 transition-colors">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="px-8 py-8">
                    <div class="mb-6">
                        <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-4 shadow-sm shadow-amber-100">
                            <i data-lucide="check-square" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900" id="modal-title">Add New Task</h3>
                        <p class="text-sm text-gray-500 mt-1">Create a new task for yourself or your team.</p>
                    </div>
                    
                    <form action="{{ route('tasks.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <!-- Task Title -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Task Title</label>
                            <input type="text" name="title" required 
                                   placeholder="e.g. Call client about renewal"
                                   class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:outline-none transition-all placeholder-gray-400 font-medium">
                        </div>

                        <!-- Assign To -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Assign To</label>
                            <select name="assigned_to" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:outline-none transition-all cursor-pointer font-medium">
                                <option value="{{ auth()->id() }}">Me ({{ auth()->user()->name }})</option>
                                @foreach($agents as $agent)
                                    @if($agent->id !== auth()->id())
                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Due Date & Quick Chips -->
                        <div x-data="{ 
                            updateDate(minutes) {
                                let now = new Date();
                                let target = new Date(now.getTime() + minutes * 60000);
                                target.setMinutes(target.getMinutes() - target.getTimezoneOffset());
                                this.$refs.dateInput.value = target.toISOString().slice(0, 16);
                            },
                            setTomorrow() {
                                let date = new Date();
                                date.setDate(date.getDate() + 1);
                                date.setHours(9, 0, 0, 0);
                                date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
                                this.$refs.dateInput.value = date.toISOString().slice(0, 16);
                            }
                        }">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Due Date</label>
                            </div>
                            
                            <!-- Quick Chips -->
                            <div class="flex gap-2 mb-3 overflow-x-auto pb-1 scrollbar-hide">
                                <button type="button" @click="updateDate(60)" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-gray-600 text-xs font-bold hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 transition-all whitespace-nowrap shadow-sm">+1h</button>
                                <button type="button" @click="updateDate(180)" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-gray-600 text-xs font-bold hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 transition-all whitespace-nowrap shadow-sm">+3h</button>
                                <button type="button" @click="updateDate(420)" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-gray-600 text-xs font-bold hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 transition-all whitespace-nowrap shadow-sm">+7h</button>
                                <button type="button" @click="setTomorrow()" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-gray-600 text-xs font-bold hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 transition-all whitespace-nowrap shadow-sm">Tmrw</button>
                                <button type="button" @click="updateDate(4320)" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white text-gray-600 text-xs font-bold hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 transition-all whitespace-nowrap shadow-sm">+3d</button>
                            </div>

                            <input type="datetime-local" name="due_date" x-ref="dateInput" required 
                                   class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:outline-none transition-all placeholder-gray-400 font-medium">
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Priority</label>
                            <select name="priority" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 focus:outline-none transition-all cursor-pointer font-medium">
                                <option value="MEDIUM">Medium</option>
                                <option value="HIGH">High</option>
                                <option value="LOW">Low</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" class="w-full rounded-2xl bg-gray-900 px-6 py-4 text-sm font-bold text-white shadow-lg hover:bg-gray-800 hover:shadow-xl hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-gray-900/20 transition-all duration-200">
                                Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
