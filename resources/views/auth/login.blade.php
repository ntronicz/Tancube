<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tancube CRM</title>
    
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
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        },
                        amber: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    },
                    borderRadius: {
                        '3xl': '24px',
                        '4xl': '32px',
                    },
                    boxShadow: {
                        'soft': '0 10px 40px -10px rgba(0,0,0,0.08)',
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons (self-hosted) -->
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <!-- Alpine.js (self-hosted) -->
    <script defer src="{{ asset('vendor/alpine.min.js') }}"></script>

    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-800 bg-white min-h-screen flex">

    <!-- Left Side: Branding & Hero -->
    <div class="hidden lg:flex w-1/2 relative bg-slate-50 items-center justify-center p-12 overflow-hidden">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-0 left-0 w-full h-full">
            <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-emerald-100/50 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-amber-100/40 rounded-full blur-[100px]"></div>
        </div>

        <div class="relative z-10 max-w-lg w-full">
            <!-- Floating Stats Cards -->
            <div class="mb-12 relative h-64 w-full">
                <!-- Card 1: Conversion -->
                <div class="absolute top-0 right-4 bg-white p-5 rounded-3xl shadow-soft flex items-center gap-4 animate-bounce-slow" style="animation-duration: 4s;">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                        <i data-lucide="trending-up" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Growth</p>
                        <p class="text-xl font-bold text-slate-900">2x <span class="text-sm font-medium text-slate-500">Conversion Rate</span></p>
                    </div>
                </div>

                <!-- Card 2: Admin Time -->
                <div class="absolute bottom-0 left-4 bg-white p-5 rounded-3xl shadow-soft flex items-center gap-4 animate-bounce-slow" style="animation-duration: 5s;">
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Efficiency</p>
                        <p class="text-xl font-bold text-slate-900">45% <span class="text-sm font-medium text-slate-500">Less Admin Time</span></p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Lead Management System
                </div>
                <h1 class="text-5xl font-bold tracking-tight text-slate-900 leading-tight">
                    Manage your leads <br>
                    <span class="text-emerald-600">like a pro.</span>
                </h1>
                <p class="text-lg text-slate-500 leading-relaxed">
                    Boost your sales team productivity with our mobile-first lead management system. Track, follow-up, and convert faster.
                </p>
                
                <div class="flex items-center gap-2 mt-8">
                    <!-- Avatars -->
                    <div class="flex -space-x-3">
                        <img src="https://ui-avatars.com/api/?name=Alex&background=random" class="w-10 h-10 rounded-full border-4 border-slate-50" alt="">
                        <img src="https://ui-avatars.com/api/?name=Sarah&background=random" class="w-10 h-10 rounded-full border-4 border-slate-50" alt="">
                        <img src="https://ui-avatars.com/api/?name=Mike&background=random" class="w-10 h-10 rounded-full border-4 border-slate-50" alt="">
                    </div>
                    <div class="text-sm font-medium text-slate-600 ml-2">
                        Trusted by top sales teams
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-12 relative bg-white">
        <div class="w-full max-w-md space-y-8">
            <!-- Mobile Logo (visible only on mobile) -->
            <div class="lg:hidden text-center mb-8">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-600 mb-4 shadow-lg shadow-emerald-500/30">
                    <span class="text-xl font-bold text-white">T</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-900">Tancube CRM</h2>
            </div>
            
            <div class="text-center lg:text-left">
                <h2 class="text-3xl font-bold text-slate-900">Welcome back</h2>
                <p class="mt-2 text-slate-500">Please enter your details to sign in.</p>
            </div>

            <!-- Error Messages -->
            @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 text-red-700 text-sm border border-red-100 flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email address</label>
                    <div class="relative">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none transition-all duration-200"
                            placeholder="Enter your email"
                        >
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i data-lucide="mail" class="w-5 h-5 text-slate-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <input 
                            :type="show ? 'text' : 'password'" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-5 py-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none transition-all duration-200"
                            placeholder="Enter your password"
                        >
                        <button 
                            type="button" 
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            <i data-lucide="eye" class="w-5 h-5" x-show="!show"></i>
                            <i data-lucide="eye-off" class="w-5 h-5" x-show="show" x-cloak></i>
                        </button>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer group">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-5 h-5 rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 transition-colors"
                        >
                        <span class="ml-2 text-sm text-slate-500 group-hover:text-slate-700 transition-colors">Remember for 30 days</span>
                    </label>
                    
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                        Forgot password?
                    </a>
                    @endif
                </div>
                
                <!-- Submit -->
                <button 
                    type="submit"
                    class="w-full py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-lg shadow-lg shadow-emerald-500/30 transform hover:-translate-y-0.5 transition-all duration-200 focus:ring-4 focus:ring-emerald-500/20 focus:outline-none"
                >
                    Sign in
                </button>
            </form>

            <p class="text-center text-sm text-slate-500">
                Don't have an account? 
                <a href="#" class="font-medium text-emerald-600 hover:text-emerald-700 transition-colors">Contact Support</a>
            </p>
        </div>
        
        <!-- Bottom text -->
        <div class="absolute bottom-8 text-center w-full text-xs text-slate-400">
            &copy; {{ date('Y') }} Tancube CRM. All rights reserved.
        </div>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>

