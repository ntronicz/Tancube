<!-- Top Navigation Component -->
<header class="h-24 px-8 relative z-30 flex items-center justify-between {{ request()->routeIs('dashboard') ? '' : 'hidden lg:flex' }}">
    <!-- Left Side -->
    <div class="flex items-center space-x-4">
        <!-- Mobile Menu Toggle -->
        <button 
            @click="mobileMenuOpen = !mobileMenuOpen" 
            class="{{ request()->routeIs('dashboard') ? 'hidden border-0' : 'lg:hidden' }} p-2 text-gray-400 hover:text-gray-900 rounded-xl hover:bg-gray-100 transition-colors"
        >
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
    </div>
    
    <!-- Right Side -->
    <div class="flex items-center gap-6">
        
        <!-- Notifications -->
        <div x-data="{ open: false }" class="relative {{ request()->routeIs('dashboard') ? 'hidden lg:block' : '' }}">
            <button 
                @click="open = !open" 
                class="relative p-2.5 text-gray-400 hover:text-primary-600 rounded-xl hover:bg-white hover:shadow-soft transition-all"
                title="Notifications"
            >
                <i data-lucide="bell" class="w-5 h-5"></i>
                @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="absolute top-2 right-2.5 w-2 h-2 bg-accent-500 rounded-full border-2 border-gray-50"></span>
                @endif
            </button>
            
            <div 
                x-show="open" 
                @click.away="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-xl py-2 z-50 border border-gray-100"
                x-cloak
            >
                <div class="px-5 py-3 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-800">Notifications</h3>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="text-xs bg-accent-100 text-accent-700 font-bold px-2 py-0.5 rounded-full">{{ auth()->user()->unreadNotifications->count() }} New</span>
                    @endif
                </div>
                <div class="max-h-80 overflow-y-auto">
                    @forelse(auth()->user()->unreadNotifications as $notification)
                    <div class="px-5 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <p class="text-sm text-gray-800 font-medium">{{ $notification->data['message'] ?? 'New Notification' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @empty
                    <!-- Clean empty state -->
                    <div class="flex flex-col items-center justify-center py-10 px-4">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                            <i data-lucide="bell-off" class="w-5 h-5 text-gray-300"></i>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">No new notifications</p>
                        <p class="text-xs text-gray-400 mt-1">We'll notify you when something happens</p>
                    </div>
                    @endforelse
                </div>
                @if(auth()->user()->unreadNotifications->count() > 0)
                <div class="p-2 border-t border-gray-50">
                    <form method="POST" action="{{ route('notifications.read') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 text-xs font-medium text-primary-600 hover:text-primary-700 text-center">
                            Mark all as read
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button 
                @click="open = !open" 
                class="flex items-center gap-3 pl-1 pr-2 py-1 rounded-full hover:bg-white hover:shadow-soft transition-all duration-200"
            >
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border-2 border-white shadow-soft flex items-center justify-center text-gray-600 font-bold text-sm">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
            </button>
            
            <div 
                x-show="open" 
                @click.away="open = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                class="absolute right-0 mt-4 w-56 bg-white rounded-2xl shadow-xl py-2 z-50 border border-gray-100"
                x-cloak
            >
                <div class="px-5 py-3 border-b border-gray-50">
                    <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                
                <div class="py-1">
                    {{-- Admin Settings Link - only visible to admins --}}
                    @if(in_array(auth()->user()->role, ['ADMIN', 'SUPER_ADMIN']))
                    <a href="{{ route('settings.index') }}" class="px-5 py-2.5 text-sm text-gray-600 hover:bg-gray-50 hover:text-primary-600 flex items-center transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 mr-3 text-gray-400"></i>
                        Settings
                    </a>
                    @endif
    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-5 py-2.5 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 flex items-center transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4 mr-3 text-red-300"></i>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
