<div x-data="{ open: false }" 
     x-show="open" 
     @open-add-lead-modal.window="open = true" 
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
                    <div class="mb-8">
                        <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 mb-4 shadow-sm shadow-primary-100">
                            <i data-lucide="user-plus" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900" id="modal-title">Add New Lead</h3>
                        <p class="text-sm text-gray-500 mt-1">Enter the details to create a new lead.</p>
                    </div>
                    
                    <form action="{{ route('leads.store') }}" method="POST" class="space-y-5">
                        @csrf
                        
                        <!-- Full Name -->
                        <div>
                            <label for="name" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Full Name</label>
                            <input type="text" name="name" id="name" required placeholder="John Doe" 
                                   class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all placeholder-gray-400 font-medium">
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Phone Number</label>
                            <input type="tel" name="phone" id="phone" placeholder="+91 99999 99999" 
                                   class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all placeholder-gray-400 font-medium">
                        </div>

                        <!-- Assign To -->
                        <div x-data="{ open: false, selected: '', selectedName: 'Select Agent' }" class="relative">
                            <label for="assigned_to" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Assign To</label>
                            <select name="assigned_to" id="assigned_to" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all cursor-pointer font-medium">
                                <option value="">Select Agent</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ Auth::id() === $agent->id ? 'selected' : '' }}>{{ $agent->name }} ({{ $agent->role ?? 'Agent' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Course & Source Grid -->
                        <div class="grid grid-cols-2 gap-5">
                            <!-- Course -->
                            <div>
                                <label for="course" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Course</label>
                                <select name="course" id="course" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all cursor-pointer font-medium">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course }}">{{ $course }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Source -->
                            <div>
                                <label for="source" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Source</label>
                                <select name="source" id="source" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all cursor-pointer font-medium">
                                    <option value="">Manual</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source }}">{{ $source }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Notes</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Any initial notes..." 
                                      class="w-full rounded-2xl border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 focus:outline-none transition-all placeholder-gray-400 resize-none font-medium"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" class="w-full rounded-2xl bg-gray-900 px-6 py-4 text-sm font-bold text-white shadow-lg hover:bg-gray-800 hover:shadow-xl hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-gray-900/20 transition-all duration-200">
                                Create Lead
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
