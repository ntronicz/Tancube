<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 flex items-center h-20 z-40 lg:hidden safe-area-bottom pb-2 overflow-x-auto no-scrollbar px-4 shadow-[0_-4px_20px_-4px_rgba(0,0,0,0.05)]">
    <div class="flex justify-between w-full min-w-max px-2 space-x-6 mx-auto">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center min-w-14 h-full group {{ request()->routeIs('dashboard') ? 'text-primary-600' : 'text-gray-400' }}">
            <div class="p-2 rounded-2xl transition-all {{ request()->routeIs('dashboard') ? 'bg-primary-50' : 'group-hover:bg-gray-50' }}">
                <i data-lucide="layout-grid" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-bold mt-1">Home</span>
        </a>
        <a href="{{ route('leads.index') }}" class="flex flex-col items-center justify-center min-w-14 h-full group {{ request()->routeIs('leads.*') && !request()->routeIs('leads.follow-ups') ? 'text-primary-600' : 'text-gray-400' }}">
            <div class="p-2 rounded-2xl transition-all {{ request()->routeIs('leads.*') && !request()->routeIs('leads.follow-ups') ? 'bg-primary-50' : 'group-hover:bg-gray-50' }}">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-bold mt-1">Leads</span>
        </a>
        <a href="{{ route('follow-ups.index') }}" class="flex flex-col items-center justify-center min-w-14 h-full group {{ request()->routeIs('follow-ups.*') ? 'text-primary-600' : 'text-gray-400' }}">
            <div class="p-2 rounded-2xl transition-all {{ request()->routeIs('follow-ups.*') ? 'bg-primary-50' : 'group-hover:bg-gray-50' }}">
                <i data-lucide="phone-call" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-bold mt-1">Calls</span>
        </a>
        <a href="{{ route('tasks.index') }}" class="flex flex-col items-center justify-center min-w-14 h-full group {{ request()->routeIs('tasks.*') ? 'text-primary-600' : 'text-gray-400' }}">
            <div class="p-2 rounded-2xl transition-all {{ request()->routeIs('tasks.*') ? 'bg-primary-50' : 'group-hover:bg-gray-50' }}">
                <i data-lucide="check-square" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-bold mt-1">Tasks</span>
        </a>
        
        <a href="{{ route('insights') }}" class="flex flex-col items-center justify-center min-w-14 h-full group {{ request()->routeIs('insights') ? 'text-primary-600' : 'text-gray-400' }}">
            <div class="p-2 rounded-2xl transition-all {{ request()->routeIs('insights') ? 'bg-primary-50' : 'group-hover:bg-gray-50' }}">
                <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-bold mt-1">Insights</span>
        </a>
        <a href="{{ route('call-metrics') }}" class="flex flex-col items-center justify-center min-w-14 h-full group {{ request()->routeIs('call-metrics') ? 'text-primary-600' : 'text-gray-400' }}">
            <div class="p-2 rounded-2xl transition-all {{ request()->routeIs('call-metrics') ? 'bg-primary-50' : 'group-hover:bg-gray-50' }}">
                <i data-lucide="headphones" class="w-6 h-6"></i>
            </div>
            <span class="text-[10px] font-bold mt-1">Metrics</span>
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
