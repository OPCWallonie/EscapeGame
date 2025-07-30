<!-- resources/views/team/progress.blade.php -->
@extends('layouts.app')

@section('title', 'Progression')
@section('header', 'Progression de l\'équipe')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Vue d'ensemble -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Salles complétées -->
            <div class="bg-gray-700 rounded-lg p-4 text-center">
                <svg class="w-8 h-8 text-green-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-2xl font-bold text-white">{{ $completedRooms->count() }}/11</p>
                <p class="text-sm text-gray-400">Salles complétées</p>
            </div>
            
            <!-- Temps total -->
            <div class="bg-gray-700 rounded-lg p-4 text-center">
                <svg class="w-8 h-8 text-blue-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-2xl font-bold text-white">{{ gmdate('H:i:s', $totalTime) }}</p>
                <p class="text-sm text-gray-400">Temps total</p>
            </div>
            
            <!-- Chiffres trouvés -->
            <div class="bg-gray-700 rounded-lg p-4 text-center">
                <svg class="w-8 h-8 text-yellow-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                <p class="text-2xl font-bold text-white">{{ $foundDigits->count() }}/4</p>
                <p class="text-sm text-gray-400">Chiffres trouvés</p>
            </div>
            
            <!-- Pénalités -->
            <div class="bg-gray-700 rounded-lg p-4 text-center">
                <svg class="w-8 h-8 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <p class="text-2xl font-bold text-white">{{ $team->penalties }}</p>
                <p class="text-sm text-gray-400">Pénalités</p>
            </div>
        </div>
    </div>

    <!-- Timeline de progression -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-indigo-400 mb-4">Timeline de l'aventure</h3>
        
        <div class="relative">
            <!-- Ligne verticale -->
            <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-700"></div>
            
            <!-- Événements -->
            <div class="space-y-6">
                @foreach($progressions as $progression)
                <div class="relative flex items-start">
                    <!-- Point sur la timeline -->
                    <div class="absolute left-8 w-4 h-4 rounded-full transform -translate-x-1/2 
                        {{ $progression->status === 'completed' ? 'bg-green-500' : 'bg-yellow-500' }}">
                    </div>
                    
                    <!-- Contenu -->
                    <div class="ml-16 bg-gray-700 rounded-lg p-4 flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-semibold text-white">{{ $progression->room->name }}</h4>
                                <p class="text-sm text-gray-400 mt-1">
                                    Entrée : {{ $progression->entered_at->format('H:i:s') }}
                                    @if($progression->completed_at)
                                        | Complété : {{ $progression->completed_at->format('H:i:s') }}
                                    @endif
                                </p>
                            </div>
                            
                            <div class="text-right">
                                @if($progression->digit_found && $progression->room->digit_reward)
                                <span class="inline-block bg-yellow-600 text-white text-sm px-2 py-1 rounded">
                                    Chiffre {{ $progression->room->digit_reward }}
                                </span>
                                @endif
                                
                                @if($progression->time_spent)
                                <p class="text-sm text-gray-400 mt-1">
                                    {{ gmdate('i:s', $progression->time_spent) }}
                                </p>
                                @endif
                            </div>
                        </div>
                        
                        @if($progression->status === 'completed')
                        <div class="mt-2 flex items-center text-green-400 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Complété
                        </div>
                        @elseif($progression->status === 'in_progress')
                        <div class="mt-2 flex items-center text-yellow-400 text-sm">
                            <svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            En cours
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Graphique de progression -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-indigo-400 mb-4">Progression par type de défi</h3>
        
        <div class="space-y-4">
            @php
                $challengeTypes = [
                    'puzzle' => ['name' => 'Puzzles', 'color' => 'bg-purple-500', 'count' => 3],
                    'memory' => ['name' => 'Mémoire', 'color' => 'bg-blue-500', 'count' => 2],
                    'observation' => ['name' => 'Observation', 'color' => 'bg-green-500', 'count' => 3],
                    'physical' => ['name' => 'Physique', 'color' => 'bg-yellow-500', 'count' => 3]
                ];
            @endphp
            
            @foreach($challengeTypes as $type => $info)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-400">{{ $info['name'] }}</span>
                    <span class="text-gray-400">{{ rand(0, $info['count']) }}/{{ $info['count'] }}</span>
                </div>
                <div class="bg-gray-700 rounded-full h-4 overflow-hidden">
                    <div class="{{ $info['color'] }} h-full transition-all duration-500" 
                         style="width: {{ rand(0, 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Navigation -->
    <div class="mt-6 flex space-x-3">
        <a href="{{ route('team.show') }}" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200">
            Voir l'équipe
        </a>
        <a href="{{ route('game.index') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-200">
            Retour au jeu
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Actualisation automatique toutes les 10 secondes
setInterval(() => {
    window.location.reload();
}, 10000);

// Animation des barres de progression au chargement
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[style*="width"]').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
});
</script>
@endpush