@if(request()->routeIs('dashboard') || request()->routeIs('leads.*') || request()->routeIs('tasks.*'))
<div x-data="{ open: false }" class="fixed bottom-24 lg:bottom-8 right-6 z-40 flex flex-col items-end gap-3" @click.outside="open = false" @keydown.escape.window="open = false">
    
    <!-- Overlay for mobile to close when clicking outside -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-white/50 backdrop-blur-sm z-30 lg:hidden"
         @click="open = false"></div>

    <!-- Menu Items -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="flex flex-col items-end gap-4 mb-2 z-40 relative">
         
         <!-- Add Task -->
         <button @click="$dispatch('open-add-task-modal'); open = false" 
            class="flex items-center gap-3 pr-1 group">
             <span class="bg-white px-4 py-2 rounded-xl shadow-md border border-slate-100 text-sm font-semibold text-slate-700 whitespace-nowrap">Add Task</span>
             <div class="w-12 h-12 rounded-full bg-amber-500 text-white shadow-lg shadow-amber-500/30 flex items-center justify-center hover:bg-amber-600 transition-colors">
                 <i data-lucide="check-square" class="w-5 h-5"></i>
             </div>
         </button>

         <!-- Add Lead -->
         <button @click="$dispatch('open-add-lead-modal'); open = false" 
                 class="flex items-center gap-3 pr-1 group">
             <span class="bg-white px-4 py-2 rounded-xl shadow-md border border-slate-100 text-sm font-semibold text-slate-700 whitespace-nowrap">Add New Lead</span>
             <div class="w-12 h-12 rounded-full bg-indigo-500 text-white shadow-lg shadow-indigo-500/30 flex items-center justify-center hover:bg-indigo-600 transition-colors">
                 <i data-lucide="user-plus" class="w-5 h-5"></i>
             </div>
         </button>
    </div>

    <!-- Main FAB -->
    <button @click="open = !open" 
            :class="{ 'rotate-45 bg-slate-800': open, 'bg-indigo-600': !open }"
            class="w-14 h-14 rounded-full text-white shadow-xl shadow-indigo-600/30 flex items-center justify-center hover:bg-indigo-700 transition-all duration-300 transform z-40 relative">
        <i data-lucide="plus" class="w-8 h-8"></i>
    </button>
</div>
@endif
