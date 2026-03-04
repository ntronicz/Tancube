<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tancube CRM')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    
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
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981', // Emerald 500
                            600: '#059669', // Emerald 600
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        accent: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24', // Amber 400
                            500: '#f59e0b', // Amber 500
                            600: '#d97706',
                        },
                        gray: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            200: '#e5e7eb',
                            300: '#d1d5db',
                            400: '#9ca3af',
                            500: '#6b7280',
                            600: '#4b5563',
                            700: '#374151',
                            800: '#1f2937',
                            900: '#111827',
                        }
                    },
                    borderRadius: {
                        'xl': '1rem',
                        '2xl': '1.5rem', // 24px
                        '3xl': '2rem',
                    },
                    boxShadow: {
                        'nuero': '20px 20px 60px #d1d5db, -20px -20px 60px #ffffff',
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>
    
    <!-- Chart.js (self-hosted) -->
    <script src="{{ asset('vendor/chart.min.js') }}"></script>
    
    <!-- Lucide Icons (self-hosted) -->
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Animation */
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Mobile UI Scaling */
        @media (max-width: 768px) {
            html {
                font-size: 13px; /* ~80% scale of default 16px */
            }
        }
    </style>
    
    <!-- Alpine.js (self-hosted) -->
    <script defer src="{{ asset('vendor/alpine.min.js') }}"></script>
    
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen">
    <div class="flex h-dvh overflow-hidden" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
        <!-- Sidebar -->
        @include('components.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            @include('components.topnav')
            
            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-2 md:p-4 lg:pb-6">
                <!-- Toast Notifications -->
                @include('components.toast')
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Modal Container -->
    <div id="modal-container"></div>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Global Activity Logger
        function logLeadActivity(leadId, type, notes = null) {
            fetch(`/leads/${leadId}/log-activity`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ type: type, notes: notes })
            }).then(response => {
                console.log('Activity logged:', type);
            }).catch(error => {
                console.error('Error logging activity:', error);
            });
        }
        
        // CSRF token for AJAX requests
        window.csrfToken = '{{ csrf_token() }}';
        
        // Toast notification function
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `flex items-center p-4 mb-4 rounded-lg shadow-lg animate-fade-in ${
                type === 'success' ? 'bg-green-800 text-green-100' :
                type === 'error' ? 'bg-red-800 text-red-100' :
                type === 'warning' ? 'bg-yellow-800 text-yellow-100' :
                'bg-blue-800 text-blue-100'
            }`;
            const msgSpan = document.createElement('span');
            msgSpan.className = 'flex-1';
            msgSpan.textContent = message;
            const closeBtn = document.createElement('button');
            closeBtn.className = 'ml-4 text-current opacity-70 hover:opacity-100';
            closeBtn.innerHTML = '<i data-lucide="x" class="w-4 h-4"></i>';
            closeBtn.onclick = function() { toast.remove(); };
            toast.appendChild(msgSpan);
            toast.appendChild(closeBtn);
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.remove(), 5000);
        };
    </script>
    
    @include('components.bottom-nav')
    
    @stack('scripts')
    <!-- FAB & Modals -->
    @if(request()->routeIs('dashboard') || request()->routeIs('leads.*') || request()->routeIs('tasks.*') || request()->routeIs('follow-ups.*') || request()->routeIs('call-metrics'))
        <x-fab />
        <x-modals.add-lead />
        <x-modals.edit-lead />
        <x-modals.add-task />
    @endif
</body>
</html>
