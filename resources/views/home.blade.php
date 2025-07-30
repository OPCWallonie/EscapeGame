<!-- resources/views/home.blade.php -->
@extends('layouts.app')

@section('title', 'Escape Game - Accueil')
@section('header', 'Escape Game Géant')

@section('content')
<div class="max-w-md mx-auto">
    <!-- Messages de session -->
    @if(session('error'))
    <div class="bg-red-900 bg-opacity-50 border border-red-600 text-red-300 px-4 py-3 rounded-lg mb-4">
        {{ session('error') }}
    </div>
    @endif
    
    @if(session('success'))
    <div class="bg-green-900 bg-opacity-50 border border-green-600 text-green-300 px-4 py-3 rounded-lg mb-4">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('info'))
    <div class="bg-blue-900 bg-opacity-50 border border-blue-600 text-blue-300 px-4 py-3 rounded-lg mb-4">
        {{ session('info') }}
    </div>
    @endif

    <!-- Logo/Image -->
    <div class="text-center mb-8">
        <div class="w-32 h-32 mx-auto bg-indigo-600 rounded-full flex items-center justify-center mb-4">
            <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-100">Bienvenue dans l'Escape Game</h2>
        <p class="text-gray-400 mt-2">Prêt pour l'aventure ?</p>
    </div>

    <!-- Formulaire de connexion -->
    <div class="bg-gray-800 rounded-lg p-6 shadow-xl">
        <form method="POST" action="{{ route('auth.join-team') }}" class="space-y-4">
            @csrf
            
            <!-- Nom du joueur -->
            <div>
                <label for="player_name" class="block text-sm font-medium text-gray-300 mb-2">
                    Votre nom
                </label>
                <input 
                    type="text" 
                    id="player_name" 
                    name="player_name" 
                    required
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Entrez votre nom"
                >
            </div>

            <!-- Code équipe -->
            <div>
                <label for="team_code" class="block text-sm font-medium text-gray-300 mb-2">
                    Code de l'équipe
                </label>
                <input 
                    type="text" 
                    id="team_code" 
                    name="team_code" 
                    required
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white uppercase focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Ex: TEAM01"
                    maxlength="6"
                >
            </div>

            <!-- Boutons -->
            <div class="space-y-3 pt-4">
                <button 
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200"
                >
                    Rejoindre l'équipe
                </button>
                
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-800 text-gray-400">ou</span>
                    </div>
                </div>

                <button 
                    type="button"
                    id="create-team-btn"
                    class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200"
                >
                    Créer une nouvelle équipe
                </button>
            </div>
        </form>
    </div>

    <!-- Instructions -->
    <div class="mt-6 bg-gray-800 rounded-lg p-4">
        <h3 class="font-semibold text-indigo-400 mb-2">Instructions :</h3>
        <ul class="text-sm text-gray-300 space-y-1">
            <li>• Un seul téléphone doit activer le son (connecté à l'enceinte)</li>
            <li>• Les autres téléphones doivent être en silencieux</li>
            <li>• Restez groupés pendant toute l'aventure</li>
            <li>• Durée estimée : 1h à 1h30</li>
        </ul>
    </div>
</div>

<!-- Modal création d'équipe -->
<div id="create-team-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 px-4">
    <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full">
        <h3 class="text-xl font-bold mb-4">Créer une nouvelle équipe</h3>
        
        <form method="POST" action="{{ route('auth.create-team') }}" class="space-y-4">
            @csrf
            
            <div>
                <label for="new_team_name" class="block text-sm font-medium text-gray-300 mb-2">
                    Nom de l'équipe
                </label>
                <input 
                    type="text" 
                    id="new_team_name" 
                    name="team_name" 
                    required
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500"
                    placeholder="Ex: Les Explorateurs"
                >
            </div>

            <div>
                <label for="new_player_name" class="block text-sm font-medium text-gray-300 mb-2">
                    Votre nom
                </label>
                <input 
                    type="text" 
                    id="new_player_name" 
                    name="player_name" 
                    required
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-indigo-500"
                    placeholder="Entrez votre nom"
                >
            </div>

            <div>
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="is_master" 
                        id="is_master"
                        class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500"
                    >
                    <span class="text-sm text-gray-300">
                        Je suis le téléphone maître (son activé)
                    </span>
                </label>
            </div>

            <div class="flex space-x-3 pt-4">
                <button 
                    type="submit"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg"
                >
                    Créer
                </button>
                <button 
                    type="button"
                    id="cancel-create"
                    class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg"
                >
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la modal de création d'équipe
    const modal = document.getElementById('create-team-modal');
    const createBtn = document.getElementById('create-team-btn');
    const cancelBtn = document.getElementById('cancel-create');
    
    createBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
    });
    
    cancelBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });
});
</script>
@endpush