<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login - Tancube CRM')</title>
    
    <!-- Fonts (self-hosted) -->
    <link rel="stylesheet" href="{{ asset('fonts/inter.css') }}">
    
    <!-- Tailwind CSS (self-hosted) -->
    <script src="{{ asset('vendor/tailwind.js') }}"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
                        },
                        dark: {
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons (self-hosted) -->
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
        }
        .glass {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(51, 65, 85, 0.5);
        }
    </style>
</head>
<body class="gradient-bg font-sans antialiased min-h-screen flex items-center justify-center p-4">
    @yield('content')
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
