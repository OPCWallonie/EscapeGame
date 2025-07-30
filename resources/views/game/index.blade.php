<!-- resources/views/game/index.blade.php -->
@extends('layouts.app')

@section('title', 'Escape Game - Jeu')
@section('header')
    <span id="team-name">{{ $team->name }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- État du jeu -->
    @if(!$gameStarted)
    <div class="bg-yellow-900 bg-opacity-50 border border-yellow-600 rounded-lg p-6 mb-6">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="text-lg font-semibold text-yellow-300">En attente</h3>
                <p class="text-yellow-100">Le jeu n'a pas encore commencé. Attendez que tous les joueurs soient prêts.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Salle actuelle -->
    @if($currentRoom)
    <div class="bg-gray-800 rounded-lg p-6 mb-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-indigo-400">Salle actuelle</h2>
            <span class="px-3 py-1 bg-indigo-900 text-indigo-300 rounded-full text-sm">
                Salle {{ $currentRoom->order }}
            </span>
        </div>
        
        <h3 class="text-2xl font-bold text-white mb-2">{{ $currentRoom->name }}</h3>
        @if($currentRoom->description)
        <p class="text-gray-300 mb-4">{{ $currentRoom->description }}</p>
        @endif
        
        <div class="flex space-x-3">
            <a href="{{ route('game.room', $currentRoom->slug) }}" 
               class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200">
                Accéder au défi
            </a>
            <a href="{{ route('game.scanner') }}" 
               class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
            </a>
        </div>
    </div>
    @else
    <div class="bg-gray-800 rounded-lg p-6 mb-6 text-center">
        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <p class="text-gray-400 mb-4">Aucune salle active</p>
        @if($gameStarted)
        <a href="{{ route('game.scanner') }}" 
           class="inline-flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
            Scanner un QR Code
        </a>
        @endif
    </div>
    @endif

    <!-- Progression du code -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-indigo-400 mb-4">Code de la boîte</h3>
        <div class="grid grid-cols-4 gap-4">
            @for($i = 1; $i <= 4; $i++)
            <div class="relative">
                <div class="bg-gray-700 rounded-lg h-20 flex items-center justify-center text-3xl font-bold
                    {{ in_array($i, $foundDigits) ? 'text-green-400 ring-2 ring-green-500' : 'text-gray-500' }}">
                    {{ in_array($i, $foundDigits) ? array_search($i, $foundDigits) + 1 : '?' }}
                </div>
                <span class="absolute -top-2 left-2 bg-gray-800 px-2 text-xs text-gray-400">
                    Chiffre {{ $i }}
                </span>
                @if(in_array($i, $foundDigits))
                <svg class="absolute -bottom-2 -right-2 w-6 h-6 text-green-400 bg-gray-800 rounded-full" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                @endif
            </div>
            @endfor
        </div>
        
        <div class="mt-4 bg-gray-700 rounded p-3">
            <p class="text-sm text-gray-300">
                <span class="text-indigo-400 font-semibold">{{ count($foundDigits) }}/4</span> chiffres trouvés
            </p>
            <div class="w-full bg-gray-600 rounded-full h-2 mt-2">
                <div class="bg-indigo-500 h-2 rounded-full transition-all duration-500" 
                     style="width: {{ (count($foundDigits) / 4) * 100 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    @if($gameStarted)
    <div class="grid grid-cols-2 gap-4">
        <a href="{{ route('team.show') }}" 
           class="bg-gray-800 hover:bg-gray-700 rounded-lg p-4 text-center transition duration-200">
            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="text-gray-300">Mon équipe</p>
        </a>
        
        <a href="{{ route('team.progress') }}" 
           class="bg-gray-800 hover:bg-gray-700 rounded-lg p-4 text-center transition duration-200">
            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <p class="text-gray-300">Progression</p>
        </a>
    </div>
    @endif
</div>

<!-- Notifications temps réel -->
<div id="notification-container" class="fixed bottom-20 right-4 z-40 space-y-2">
    <!-- Les notifications seront ajoutées ici -->
</div>
@endsection

@push('scripts')
<script>
// Configuration WebSocket
const teamId = {{ $team->id }};
const playerId = {{ $player->id }};

// Écouter les événements de l'équipe
window.Echo.channel(`team.${teamId}`)
    .listen('.game.state.updated', (e) => {
        console.log('État du jeu mis à jour:', e);
        
        // Afficher une notification
        showNotification(e.action, e.data);
        
        // Recharger la page si nécessaire
        if (e.action === 'game_started' || e.action === 'room_completed') {
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    });

// Fonction pour afficher une notification
function showNotification(action, data) {
    const container = document.getElementById('notification-container');
    const notification = document.createElement('div');
    
    notification.className = 'bg-gray-800 border border-gray-700 rounded-lg p-4 shadow-lg transform transition-all duration-300 translate-x-full';
    
    let content = '';
    switch (action) {
        case 'game_started':
            content = `
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-white">Le jeu commence !</p>
                        <p class="text-sm text-gray-400">Bonne chance à tous</p>
                    </div>
                </div>
            `;
            break;
            
        case 'room_entered':
            content = `
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-indigo-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-white">${data.player_name} a scanné</p>
                        <p class="text-sm text-gray-400">${data.room_name}</p>
                    </div>
                </div>
            `;
            break;
            
        case 'digit_found':
            content = `
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-white">Chiffre trouvé !</p>
                        <p class="text-sm text-gray-400">Chiffre ${data.digit} découvert</p>
                    </div>
                </div>
            `;
            break;
    }
    
    notification.innerHTML = content;
    container.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Supprimer après 5 secondes
    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Timer du jeu
@if($gameStarted && $team->started_at)
const startTime = new Date('{{ $team->started_at->toIso8601String() }}');
const timerElement = document.getElementById('game-timer');
timerElement.classList.remove('hidden');

function updateTimer() {
    const now = new Date();
    const elapsed = Math.floor((now - startTime) / 1000);
    
    const hours = Math.floor(elapsed / 3600);
    const minutes = Math.floor((elapsed % 3600) / 60);
    const seconds = elapsed % 60;
    
    const display = hours > 0 
        ? `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
        : `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    timerElement.querySelector('.font-mono').textContent = display;
}

setInterval(updateTimer, 1000);
updateTimer();
@endif
</script>
@endpush