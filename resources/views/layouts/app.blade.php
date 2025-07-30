<!DOCTYPE html>
<!-- resources/views/layouts/app.blade.php -->
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <title>@yield('title', 'Escape Game Géant')</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/images/icons/icon-32x32.png">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    
    <!-- CSS -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @stack('styles')
    
    <!-- Tailwind CSS (pour le style) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <!-- App Container -->
    <div id="app" class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-gray-800 shadow-lg">
            <div class="container mx-auto px-4 py-3">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl font-bold text-indigo-400">
                        @yield('header', 'Escape Game')
                    </h1>
                    <div class="flex items-center space-x-4">
                        <!-- Timer -->
                        <div id="game-timer" class="hidden text-sm">
                            <span class="text-gray-400">Temps:</span>
                            <span class="font-mono text-yellow-400">00:00</span>
                        </div>
                        <!-- Connection Status -->
                        <div id="connection-status" class="w-2 h-2 rounded-full bg-gray-500"></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 container mx-auto px-4 py-6">
            @yield('content')
        </main>

        <!-- Footer Navigation (pour les équipes) -->
        @auth
        <nav class="bg-gray-800 border-t border-gray-700">
            <div class="container mx-auto px-4">
                <div class="flex justify-around py-2">
                    <a href="/game" class="flex flex-col items-center p-2 text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="text-xs mt-1">Jeu</span>
                    </a>
                    <a href="/team" class="flex flex-col items-center p-2 text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="text-xs mt-1">Équipe</span>
                    </a>
                    <a href="/progress" class="flex flex-col items-center p-2 text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="text-xs mt-1">Progrès</span>
                    </a>
                </div>
            </div>
        </nav>
        @endauth
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-400 mx-auto"></div>
            <p class="mt-4 text-gray-300">Chargement...</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(registration => console.log('SW enregistré:', registration))
                    .catch(error => console.log('Erreur SW:', error));
            });
        }
    </script>

    <!-- WebSocket Connection Status -->
    <script>
        window.Echo.connector.pusher.connection.bind('connected', () => {
            document.getElementById('connection-status').classList.remove('bg-gray-500', 'bg-red-500');
            document.getElementById('connection-status').classList.add('bg-green-500');
        });

        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            document.getElementById('connection-status').classList.remove('bg-gray-500', 'bg-green-500');
            document.getElementById('connection-status').classList.add('bg-red-500');
        });
    </script>

    @stack('scripts')
</body>
</html>