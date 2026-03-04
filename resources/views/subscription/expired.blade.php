<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Access Restricted - Tancube CRM</title>
    
    <!-- Fonts (self-hosted) -->
    <link rel="stylesheet" href="{{ asset('fonts/inter.css') }}">
    
    <!-- Tailwind CSS (self-hosted) -->
    <script src="{{ asset('vendor/tailwind.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons (self-hosted) -->
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen flex items-center justify-center p-6">
    
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden animate-fade-in relative z-10 border border-gray-100">
        <!-- Status Indicator -->
        <div class="h-2 w-full {{ auth()->user()->organization->status === 'INACTIVE' ? 'bg-red-500' : 'bg-amber-500' }}"></div>
        
        <div class="p-8 text-center">
            <!-- Icon -->
            <div class="mb-6 flex justify-center">
                <div class="p-4 rounded-full {{ auth()->user()->organization->status === 'INACTIVE' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-500' }}">
                    @if(auth()->user()->organization->status === 'INACTIVE')
                        <i data-lucide="ban" class="w-12 h-12"></i>
                    @else
                        <i data-lucide="calendar-off" class="w-12 h-12"></i>
                    @endif
                </div>
            </div>

            <!-- Title & Message -->
            @if(auth()->user()->organization->status === 'INACTIVE')
                <h2 class="text-2xl font-bold text-gray-900 mb-3">Account Inactive</h2>
                <p class="text-gray-500 mb-8 leading-relaxed">
                    Your organization's account has been deactivated by the administrator. Please contact support to restore access.
                </p>
            @else
                <h2 class="text-2xl font-bold text-gray-900 mb-3">Subscription Expired</h2>
                <p class="text-gray-500 mb-8 leading-relaxed">
                    Your current plan has expired. To continue using the dashboard and managing leads, please renew your subscription.
                </p>
            @endif

            <!-- Organization Details (Mini Card) -->
            <div class="bg-gray-50 rounded-2xl p-4 mb-8 text-left border border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center font-bold text-gray-700 shadow-sm text-xs border border-gray-100">
                        {{ strtoupper(substr(auth()->user()->organization->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ auth()->user()->organization->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->organization->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-gray-900 hover:bg-gray-800 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                        Sign Out
                    </button>
                </form>
                
                <p class="text-xs text-gray-400 mt-6">
                    Need help? <a href="#" class="text-gray-600 hover:text-gray-900 underline decoration-gray-300">Contact Support</a>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-primary-500/5 blur-3xl"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-blue-500/5 blur-3xl"></div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
