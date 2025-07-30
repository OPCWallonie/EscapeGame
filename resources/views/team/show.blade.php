<!-- resources/views/team/show.blade.php -->
@extends('layouts.app')

@section('title', 'Mon équipe')
@section('header', 'Mon équipe')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Info équipe -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6 shadow-xl">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-white mb-2">{{ $team->name }}</h2>
            <div class="inline-flex items-center space-x-2 bg-gray-700 rounded-lg px-6 py-3">
                <span class="text-gray-400">Code :</span>
                <span class="text-2xl font-mono font-bold text-indigo-400">{{ $team->code }}</span>
                <button onclick="copyCode()" class="ml-2 text-gray-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Statut -->
        <div class="grid grid-cols-2 gap-4 text-center">
            <div class="bg-gray-700 rounded-lg p-4">
                <p class="text-gray-400 text-sm">Statut</p>
                <p class="text-lg font-semibold {{ $team->status === 'playing' ? 'text-green-400' : 'text-yellow-400' }}">
                    @if($team->status === 'waiting')
                        En attente
                    @elseif($team->status === 'playing')
                        En jeu
                    @else
                        Terminé
                    @endif
                </p>
            </div>
            <div class="bg-gray-700 rounded-lg p-4">
                <p class="text-gray-400 text-sm">Joueurs</p>
                <p class="text-lg font-semibold text-white">{{ $team->players->count() }} / 10</p>
            </div>
        </div>

        @if($team->is_master)
        <div class="mt-4 bg-indigo-900 bg-opacity-50 rounded-lg p-3 text-center">
            <p class="text-indigo-300">
                <svg class="w-5 h-5 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M19 9l-7 7-7-7" clip-rule="evenodd"/>
                </svg>
                Téléphone maître (avec son)
            </p>
        </div>
        @endif
    </div>

    <!-- Liste des joueurs -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-indigo-400 mb-4">Membres de l'équipe</h3>
        
        <div class="space-y-3">
            @foreach($team->players as $member)
            <div class="flex items-center justify-between bg-gray-700 rounded-lg p-4 {{ $member->id === $player->id ? 'ring-2 ring-indigo-500' : '' }}">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold">{{ substr($member->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="font-medium text-white">
                            {{ $member->name }}
                            @if($member->id === $player->id)
                                <span class="text-xs text-indigo-400 ml-2">(Vous)</span>
                            @endif
                        </p>
                        @if($member->role !== 'none')
                        <p class="text-xs text-gray-400">{{ ucfirst($member->role) }}</p>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    @if($member->last_activity && $member->last_activity->diffInMinutes() < 5)
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-xs text-gray-400">En ligne</span>
                    @else
                    <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                    <span class="text-xs text-gray-500">Hors ligne</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        
        @if($team->players->count() < 10)
        <div class="mt-4 p-4 bg-gray-700 rounded-lg text-center">
            <p class="text-gray-400 text-sm">
                Partagez le code <span class="font-mono font-bold text-indigo-400">{{ $team->code }}</span> pour que d'autres joueurs rejoignent
            </p>
        </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="space-y-3">
        @if($team->status === 'waiting' && $team->players->count() >= 2)
        <button id="start-game" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
            Démarrer le jeu
        </button>
        @endif
        
        <a href="{{ route('game.index') }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200">
            Retour au jeu
        </a>
        
        <form method="POST" action="{{ route('auth.logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                Quitter l'équipe
            </button>
        </form>
    </div>
</div>

<!-- Instructions pour démarrer -->
@if($team->status === 'waiting')
<div class="max-w-4xl mx-auto mt-6">
    <div class="bg-gray-800 rounded-lg p-6">
        <h3 class="font-semibold text-indigo-400 mb-3">Avant de commencer :</h3>
        <ul class="space-y-2 text-gray-300">
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">1.</span>
                <span>Assurez-vous que tous les joueurs ont rejoint l'équipe</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">2.</span>
                <span>Un seul téléphone doit activer le son (connecté à l'enceinte Bluetooth)</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">3.</span>
                <span>Les autres téléphones doivent être en mode silencieux</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">4.</span>
                <span>Restez groupés pendant toute l'aventure</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 mr-2">5.</span>
                <span>Le jeu dure environ 1h à 1h30</span>
            </li>
        </ul>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Copier le code
function copyCode() {
    const code = '{{ $team->code }}';
    navigator.clipboard.writeText(code).then(() => {
        // Notification visuelle
        const notification = document.createElement('div');
        notification.className = 'fixed bottom-20 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full';
        notification.textContent = 'Code copié !';
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    });
}

// Démarrer le jeu
@if($team->status === 'waiting')
document.getElementById('start-game')?.addEventListener('click', async () => {
    if (!confirm('Démarrer le jeu pour votre équipe ?')) return;
    
    try {
        const response = await fetch('/api/team/{{ $team->id }}/start', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Player-ID': '{{ $player->id }}',
                'X-Device-ID': '{{ session("device_id") }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '{{ route("game.index") }}';
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du démarrage du jeu');
    }
});
@endif

// Actualiser la liste des joueurs
setInterval(async () => {
    try {
        const response = await fetch('/api/team/{{ $team->id }}', {
            headers: {
                'X-Player-ID': '{{ $player->id }}',
                'X-Device-ID': '{{ session("device_id") }}'
            }
        });
        
        const data = await response.json();
        
        // Mettre à jour le compteur si nécessaire
        const currentCount = {{ $team->players->count() }};
        if (data.team.player_count !== currentCount) {
            window.location.reload();
        }
    } catch (error) {
        console.error('Erreur actualisation:', error);
    }
}, 5000);
</script>
@endpush