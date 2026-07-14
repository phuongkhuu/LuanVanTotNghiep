<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="user-id" content="{{ Auth::id() ?? 'guest' }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts: Montserrat + Material Icons -->
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        
        <!-- Fallback font Figtree -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Custom Styles -->
        <style>
            * {
                font-family: 'Montserrat', sans-serif;
            }
            
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            }
            
            .sidebar-item:hover,
            .sidebar-item-active {
                background-color: #fff5f2;
                color: #ff6b00;
            }
            
            .glass-header {
                backdrop-filter: blur(12px);
                background-color: rgba(251, 249, 245, 0.95);
            }
            
            .stat-card {
                transition: all 0.2s;
                cursor: pointer;
            }
            
            .stat-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            }
            
            ::-webkit-scrollbar {
                width: 6px;
            }
            
            ::-webkit-scrollbar-track {
                background: #e4e2de;
                border-radius: 10px;
            }
            
            ::-webkit-scrollbar-thumb {
                background: #ff6b00;
                border-radius: 10px;
            }
            
            .rotate-180 {
                transform: rotate(180deg);
            }
        </style>
        
        <!-- Scripts -->
        <script>
            // Đảm bảo window.user được set đúng
            window.user = @json(Auth::user());
            console.log('User set in window:', window.user);
            
            // Nếu không có user, set là null
            if (!window.user) {
                window.user = null;
            }
        </script>

        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="bg-background font-montserrat antialiased">
        @inertia
    </body>
</html>