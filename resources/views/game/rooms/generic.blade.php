<!-- resources/views/game/rooms/generic.blade.php -->
@extends('layouts.app')

@section('title', $room->name)
@section('header', $room->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Description de la salle -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <p class="text-gray-300 text-lg">{{ $room->description }}</p>
        
        @if($room->digit_reward)
        <div class="mt-4 p-3 bg-indigo-900 bg-opacity-50 rounded">
            <p class="text-sm text-indigo-300">
                🔢 Cette salle contient le chiffre {{ $room->digit_reward }} du code
            </p>
        </div>
        @endif
    </div>

    <!-- Informations sur la salle -->
    <div class="bg-gray-900 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Informations</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-800 rounded-lg p-4">
                <p class="text-sm text-gray-400">Type de salle</p>
                <p class="text-lg font-semibold text-white">
                    {{ $room->type === 'main' ? 'Principale' : 'Embranchement' }}
                </p>
            </div>
            
            <div class="bg-gray-800 rounded-lg p-4">
                <p class="text-sm text-gray-400">Temps estimé</p>
                <p class="text-lg font-semibold text-white">
                    {{ intval($room->estimated_time / 60) }} minutes
                </p>
            </div>
        </div>
        
        @if($progression)
        <div class="mt-4 bg-gray-800 rounded-lg p-4">
            <p class="text-sm text-gray-400">Statut</p>
            <p class="text-lg font-semibold {{ $progression->status === 'completed' ? 'text-green-400' : 'text-yellow-400' }}">
                @switch($progression->status)
                    @case('entered')
                        Exploration en cours
                        @break
                    @case('in_progress')
                        Défi en cours
                        @break
                    @case('completed')
                        ✅ Complété
                        @break
                @endswitch
            </p>
        </div>
        @endif
    </div>

    <!-- Zone de contenu personnalisé -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            <p class="text-gray-400 mb-4">Cette salle n'a pas de défi numérique spécifique</p>
            
            @if($progression && $progression->status !== 'completed')
            <button id="complete-room" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                Marquer comme complété
            </button>
            @endif
        </div>
    </div>

    <!-- Navigation -->
    <div class="flex space-x-3">
        <a href="{{ route('game.scanner') }}" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200">
            Scanner un autre QR
        </a>
        <a href="{{ route('game.index') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200">
            Retour au jeu
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('complete-room')?.addEventListener('click', async () => {
    const playerId = localStorage.getItem('player_id');
    const deviceId = localStorage.getItem('device_id');
    
    try {
        const response = await fetch('/api/game/complete-challenge', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Player-ID': playerId,
                'X-Device-ID': deviceId
            },
            body: JSON.stringify({
                room_id: {{ $room->id }},
                success: true,
                data: {
                    completed_manually: true
                }
            })
        });
        
        if (response.ok) {
            window.location.href = '{{ route("game.index") }}';
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
});
</script>
@endpush