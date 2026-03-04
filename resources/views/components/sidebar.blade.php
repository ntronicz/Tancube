<aside 
    class="hidden lg:block fixed inset-y-0 left-0 z-50 w-72 transform transition-transform duration-300 bg-white border-r border-gray-100 lg:relative lg:translate-x-0"
    :class="{ 
        '-translate-x-full': !sidebarOpen && !mobileMenuOpen,
        'translate-x-0': sidebarOpen || mobileMenuOpen 
    }"
>
    <div class="flex flex-col h-full py-6 px-4">
        <!-- Logo -->
        <div class="px-4 mb-10 flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="Tancube CRM" class="h-8 w-auto">
        </div>
        
        <!-- User Card (Minimal) -->
        <div class="px-2 mb-8">
            <div class="p-1 pl-2 pr-4 rounded-3xl bg-gray-50 flex items-center gap-3 border border-gray-100">
                <div class="w-10 h-10 rounded-full bg-white border-2 border-white shadow-soft flex items-center justify-center text-primary-700 font-bold overflow-hidden">
                    {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0 py-2">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name ?? 'Super Admin' }}</p>
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">{{ auth()->user()->role ?? 'ADMIN' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 space-y-2 overflow-y-auto px-2">
            @php
                $currentRoute = request()->route()->getName() ?? '';
                $user = auth()->user();
                $isSuperAdmin = $user && $user->role === 'SUPER_ADMIN';
            @endphp
            
            @if($isSuperAdmin)
                <!-- Super Admin Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Vendors -->
                <a href="{{ route('admin.vendors') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'admin.vendors') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="building-2" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'admin.vendors') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Vendors</span>
                </a>

                <!-- Subscriptions -->
                <a href="{{ route('admin.subscriptions') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'admin.subscriptions') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="credit-card" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'admin.subscriptions') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Subscriptions</span>
                </a>

                <!-- Plans -->
                <a href="{{ route('admin.plans') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'admin.plans') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="layers" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'admin.plans') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Plans</span>
                </a>
            @else
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'dashboard') || $currentRoute === '' ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="layout-grid" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'dashboard') || $currentRoute === '' ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- Leads -->
                <a href="{{ route('leads.index') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'leads') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="users" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'leads') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Leads</span>
                </a>

                <!-- Follow-ups -->
                <a href="{{ route('follow-ups.index') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'follow-ups') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="phone-call" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'follow-ups') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Follow-ups</span>
                </a>
                
                <!-- Tasks -->
                <a href="{{ route('tasks.index') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'tasks') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="check-square" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'tasks') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Tasks</span>
                </a>
                
                <!-- Insights -->
                <a href="{{ route('insights') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'insights') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'insights') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Insights</span>
                </a>

                <!-- Call Metrics -->
                <a href="{{ route('call-metrics') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'call-metrics') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="headphones" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'call-metrics') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Call Metrics</span>
                </a>
                
                @if($user && $user->role === 'ADMIN')
                <!-- Settings -->
                <a href="{{ route('settings.index') }}" 
                   class="flex items-center px-5 py-3.5 rounded-2xl transition-all duration-200 group {{ str_starts_with($currentRoute, 'settings') ? 'bg-primary-50 text-primary-700 font-semibold shadow-soft' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i data-lucide="settings" class="w-5 h-5 mr-3.5 {{ str_starts_with($currentRoute, 'settings') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
                    <span>Settings</span>
                </a>
                @endif
            @endif
        </nav>
        
        <!-- Logout -->
        <div class="px-2 pb-2">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center w-full px-5 py-3.5 rounded-2xl text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors group">
                    <i data-lucide="log-out" class="w-5 h-5 mr-3.5 text-gray-400 group-hover:text-red-500"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Mobile Overlay -->
<div 
    x-show="mobileMenuOpen" 
    @click="mobileMenuOpen = false"
    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
    x-cloak
></div>
