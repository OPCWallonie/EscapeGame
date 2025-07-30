<!-- resources/views/game/rooms/onze_caves.blade.php -->
@extends('layouts.app')

@section('title', 'Les 11 Caves')
@section('header', $room->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Description -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <p class="text-gray-300 text-lg">{{ $room->description }}</p>
        <div class="mt-4 p-3 bg-gray-700 rounded">
            <p class="text-sm text-indigo-300">
                🗺️ Collectez les fragments de carte dans chaque cave, mais attention aux faux fragments !
            </p>
            <p class="text-xs text-yellow-400 mt-2">
                ⚠️ Les caves 10 et 11 contiennent des fragments piégés
            </p>
        </div>
    </div>

    <!-- Vue d'ensemble des caves -->
    <div class="bg-gray-900 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Les 11 Caves</h3>
        
        <!-- Grille des caves -->
        <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
            @for($i = 1; $i <= 11; $i++)
            <div class="cave-card" data-cave="{{ $i }}">
                <div class="bg-gray-800 hover:bg-gray-700 rounded-lg p-4 text-center cursor-pointer transition-all duration-200 border-2 border-transparent">
                    <div class="cave-icon mb-2">
                        <svg class="w-12 h-12 mx-auto text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <p class="font-semibold">Cave {{ $i }}</p>
                    <p class="text-xs text-gray-500 mt-1 cave-status">Non explorée</p>
                    
                    <!-- Indicateur de fragment -->
                    <div class="fragment-indicator mt-2 hidden">
                        <span class="inline-block w-6 h-6 rounded-full"></span>
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    <!-- Zone de mini-jeu active -->
    <div id="minigame-container" class="hidden bg-gray-800 rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 id="current-cave-title" class="text-lg font-semibold text-indigo-400"></h3>
            <button id="close-minigame" class="text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="minigame-content">
            <!-- Le contenu du mini-jeu sera injecté ici -->
        </div>
    </div>

    <!-- Carte assemblée -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Carte en cours d'assemblage</h3>
        
        <div class="relative bg-gray-900 rounded-lg p-8" style="min-height: 400px;">
            <!-- Grille pour les fragments -->
            <div id="map-grid" class="grid grid-cols-3 gap-2 max-w-md mx-auto">
                @for($i = 1; $i <= 9; $i++)
                <div class="map-slot" data-slot="{{ $i }}">
                    <div class="bg-gray-700 rounded-lg aspect-square flex items-center justify-center border-2 border-dashed border-gray-600">
                        <span class="text-gray-500 text-2xl">?</span>
                    </div>
                </div>
                @endfor
            </div>
            
            <!-- Message caché (révélé quand complet) -->
            <div id="hidden-message" class="hidden mt-6 text-center">
                <p class="text-lg text-green-400 font-semibold">La carte révèle le 2ème chiffre :</p>
                <p class="text-6xl font-bold text-green-300 mt-2">{{ $room->digit_reward }}</p>
            </div>
        </div>
        
        <!-- Compteur de fragments -->
        <div class="mt-4 flex justify-between items-center">
            <div>
                <span class="text-gray-400">Fragments collectés :</span>
                <span id="fragment-count" class="text-white font-semibold ml-2">0 / 9</span>
            </div>
            <div>
                <span class="text-gray-400">Faux fragments :</span>
                <span id="fake-count" class="text-red-400 font-semibold ml-2">0</span>
            </div>
        </div>
    </div>
</div>

<!-- Templates des mini-jeux -->
<template id="memory-game">
    <div class="text-center">
        <p class="text-gray-300 mb-4">Mémorisez ces symboles pendant 3 secondes</p>
        <div id="memory-symbols" class="flex justify-center space-x-4 text-4xl mb-6">
            <!-- Les symboles seront affichés ici -->
        </div>
        <div id="memory-input" class="hidden">
            <p class="text-gray-300 mb-4">Reproduisez la séquence</p>
            <div class="flex justify-center space-x-2">
                <!-- Les boutons de symboles seront ici -->
            </div>
        </div>
    </div>
</template>

<template id="rotation-game">
    <div class="text-center">
        <p class="text-gray-300 mb-4">Alignez correctement le fragment</p>
        <div class="relative inline-block">
            <div id="rotation-piece" class="w-32 h-32 bg-indigo-600 rounded-lg flex items-center justify-center text-white text-2xl transition-transform duration-300">
                <span>⬆️</span>
            </div>
        </div>
        <div class="mt-4">
            <button id="rotate-left" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg mr-2">↺ Gauche</button>
            <button id="rotate-right" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg">↻ Droite</button>
        </div>
        <button id="validate-rotation" class="mt-4 bg-indigo-600 hover:bg-indigo-700 px-6 py-2 rounded-lg">Valider</button>
    </div>
</template>

<template id="intruder-game">
    <div class="text-center">
        <p class="text-gray-300 mb-4">Trouvez l'intrus</p>
        <div class="grid grid-cols-3 gap-4 max-w-sm mx-auto">
            <!-- Les éléments seront affichés ici -->
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
// État du jeu
const collectedFragments = [];
const fakeFragments = [];
let currentCave = null;
let currentMinigame = null;

// Symboles pour les mini-jeux
const symbols = ['♠', '♥', '♦', '♣', '★', '☾', '☀', '⚡', '❄', '🔥'];

// Gestion des caves
document.querySelectorAll('.cave-card').forEach(card => {
    card.addEventListener('click', () => {
        const caveNumber = parseInt(card.dataset.cave);
        
        // Vérifier si déjà explorée
        if (collectedFragments.includes(caveNumber) || fakeFragments.includes(caveNumber)) {
            showNotification('Cette cave a déjà été explorée', 'warning');
            return;
        }
        
        openCave(caveNumber);
    });
});

// Ouvrir une cave
function openCave(caveNumber) {
    currentCave = caveNumber;
    const container = document.getElementById('minigame-container');
    const title = document.getElementById('current-cave-title');
    const content = document.getElementById('minigame-content');
    
    container.classList.remove('hidden');
    title.textContent = `Cave ${caveNumber}`;
    
    // Déterminer le type de mini-jeu
    let gameType;
    if (caveNumber <= 3) {
        gameType = 'memory';
    } else if (caveNumber <= 6) {
        gameType = 'rotation';
    } else if (caveNumber <= 9) {
        gameType = 'intruder';
    } else {
        // Caves 10 et 11 : faux fragments avec un défi plus difficile
        gameType = 'intruder';
    }
    
    loadMinigame(gameType);
}

// Charger un mini-jeu
function loadMinigame(type) {
    const content = document.getElementById('minigame-content');
    currentMinigame = type;
    
    switch(type) {
        case 'memory':
            loadMemoryGame();
            break;
        case 'rotation':
            loadRotationGame();
            break;
        case 'intruder':
            loadIntruderGame();
            break;
    }
}

// Mini-jeu Memory
function loadMemoryGame() {
    const template = document.getElementById('memory-game');
    const content = document.getElementById('minigame-content');
    content.innerHTML = template.innerHTML;
    
    // Générer une séquence aléatoire
    const sequence = [];
    for (let i = 0; i < 3; i++) {
        sequence.push(symbols[Math.floor(Math.random() * symbols.length)]);
    }
    
    // Afficher les symboles
    const symbolsDiv = document.getElementById('memory-symbols');
    symbolsDiv.innerHTML = sequence.map(s => `<span>${s}</span>`).join('');
    
    // Après 3 secondes, cacher et demander la séquence
    setTimeout(() => {
        symbolsDiv.classList.add('hidden');
        const inputDiv = document.getElementById('memory-input');
        inputDiv.classList.remove('hidden');
        
        // Créer les boutons
        const buttonsDiv = inputDiv.querySelector('div');
        buttonsDiv.innerHTML = '';
        
        // Mélanger les symboles
        const shuffled = [...symbols].sort(() => Math.random() - 0.5).slice(0, 6);
        shuffled.forEach(symbol => {
            const btn = document.createElement('button');
            btn.className = 'bg-gray-700 hover:bg-gray-600 w-12 h-12 rounded-lg text-2xl';
            btn.textContent = symbol;
            btn.onclick = () => checkMemoryInput(symbol, sequence);
            buttonsDiv.appendChild(btn);
        });
    }, 3000);
}

let memoryInput = [];
function checkMemoryInput(symbol, correctSequence) {
    memoryInput.push(symbol);
    
    if (memoryInput.length === correctSequence.length) {
        const correct = memoryInput.every((s, i) => s === correctSequence[i]);
        completeMinigame(correct);
        memoryInput = [];
    }
}

// Mini-jeu Rotation
function loadRotationGame() {
    const template = document.getElementById('rotation-game');
    const content = document.getElementById('minigame-content');
    content.innerHTML = template.innerHTML;
    
    let rotation = 0;
    const targetRotation = [0, 90, 180, 270][Math.floor(Math.random() * 4)];
    const piece = document.getElementById('rotation-piece');
    
    // Rotation initiale aléatoire
    rotation = [90, 180, 270][Math.floor(Math.random() * 3)];
    piece.style.transform = `rotate(${rotation}deg)`;
    
    document.getElementById('rotate-left').onclick = () => {
        rotation = (rotation - 90 + 360) % 360;
        piece.style.transform = `rotate(${rotation}deg)`;
    };
    
    document.getElementById('rotate-right').onclick = () => {
        rotation = (rotation + 90) % 360;
        piece.style.transform = `rotate(${rotation}deg)`;
    };
    
    document.getElementById('validate-rotation').onclick = () => {
        completeMinigame(rotation === targetRotation);
    };
}

// Mini-jeu Intrus
function loadIntruderGame() {
    const template = document.getElementById('intruder-game');
    const content = document.getElementById('minigame-content');
    content.innerHTML = template.innerHTML;
    
    const grid = content.querySelector('.grid');
    grid.innerHTML = '';
    
    // Choisir un symbole principal et un intrus
    const mainSymbol = symbols[Math.floor(Math.random() * symbols.length)];
    let intruderSymbol;
    do {
        intruderSymbol = symbols[Math.floor(Math.random() * symbols.length)];
    } while (intruderSymbol === mainSymbol);
    
    // Position aléatoire pour l'intrus
    const intruderPos = Math.floor(Math.random() * 9);
    
    for (let i = 0; i < 9; i++) {
        const btn = document.createElement('button');
        btn.className = 'bg-gray-700 hover:bg-gray-600 w-16 h-16 rounded-lg text-2xl transition-all';
        btn.textContent = i === intruderPos ? intruderSymbol : mainSymbol;
        btn.onclick = () => {
            if (i === intruderPos) {
                btn.classList.add('bg-green-600');
                completeMinigame(true);
            } else {
                btn.classList.add('bg-red-600');
                setTimeout(() => completeMinigame(false), 1000);
            }
        };
        grid.appendChild(btn);
    }
}

// Compléter un mini-jeu
function completeMinigame(success) {
    if (success) {
        // Collecter le fragment
        if (currentCave <= 9) {
            collectedFragments.push(currentCave);
            updateCaveStatus(currentCave, 'collected');
            updateMapGrid(currentCave);
        } else {
            // Faux fragment
            fakeFragments.push(currentCave);
            updateCaveStatus(currentCave, 'fake');
            showNotification('Fragment piégé ! Ce n\'est pas un vrai morceau de carte.', 'error');
        }
        
        updateFragmentCount();
        
        // Vérifier si tous les vrais fragments sont collectés
        if (collectedFragments.length === 9) {
            revealCode();
        }
    } else {
        showNotification('Échec du défi. Réessayez !', 'error');
    }
    
    // Fermer après un délai
    setTimeout(() => {
        document.getElementById('minigame-container').classList.add('hidden');
    }, success ? 500 : 1500);
}

// Mettre à jour le statut d'une cave
function updateCaveStatus(caveNumber, status) {
    const card = document.querySelector(`[data-cave="${caveNumber}"]`);
    const statusEl = card.querySelector('.cave-status');
    const indicator = card.querySelector('.fragment-indicator');
    const cardDiv = card.querySelector('div');
    
    if (status === 'collected') {
        statusEl.textContent = 'Fragment collecté';
        indicator.classList.remove('hidden');
        indicator.querySelector('span').className = 'inline-block w-6 h-6 rounded-full bg-green-500';
        cardDiv.classList.add('border-green-500', 'bg-green-900', 'bg-opacity-20');
    } else if (status === 'fake') {
        statusEl.textContent = 'Fragment piégé';
        indicator.classList.remove('hidden');
        indicator.querySelector('span').className = 'inline-block w-6 h-6 rounded-full bg-red-500';
        cardDiv.classList.add('border-red-500', 'bg-red-900', 'bg-opacity-20');
    }
}

// Mettre à jour la grille de la carte
function updateMapGrid(fragmentNumber) {
    const slot = document.querySelector(`[data-slot="${fragmentNumber}"]`);
    const slotDiv = slot.querySelector('div');
    
    // Simuler un morceau de carte
    slotDiv.innerHTML = `
        <div class="w-full h-full bg-indigo-600 rounded-lg flex items-center justify-center">
            <span class="text-white text-lg font-bold">${fragmentNumber}</span>
        </div>
    `;
    slotDiv.classList.remove('border-dashed', 'border-gray-600');
    slotDiv.classList.add('border-solid', 'border-indigo-500');
}

// Mettre à jour le compteur
function updateFragmentCount() {
    document.getElementById('fragment-count').textContent = `${collectedFragments.length} / 9`;
    document.getElementById('fake-count').textContent = fakeFragments.length;
}

// Révéler le code
async function revealCode() {
    document.getElementById('hidden-message').classList.remove('hidden');
    showNotification('Carte complète ! Le chiffre est révélé !', 'success');
    
    // Envoyer au serveur
    await completeChallenge();
}

// Fermer le mini-jeu
document.getElementById('close-minigame').addEventListener('click', () => {
    document.getElementById('minigame-container').classList.add('hidden');
});

// Afficher une notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const colors = {
        'info': 'bg-blue-900 text-blue-300',
        'success': 'bg-green-900 text-green-300',
        'warning': 'bg-yellow-900 text-yellow-300',
        'error': 'bg-red-900 text-red-300'
    };
    
    notification.className = `fixed bottom-20 right-4 ${colors[type]} rounded-lg p-4 max-w-sm transform transition-all duration-300 translate-x-full z-50`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Envoyer le résultat au serveur
async function completeChallenge() {
    const playerId = localStorage.getItem('player_id');
    const deviceId = localStorage.getItem('device_id');
    
    try {
        await fetch('/api/game/complete-challenge', {
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
                    collected_fragments: collectedFragments,
                    fake_fragments: fakeFragments
                }
            })
        });
        
        setTimeout(() => {
            window.location.href = '/game';
        }, 3000);
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// API pour le contrôleur
async function collectFragment(caveNumber) {
    const playerId = localStorage.getItem('player_id');
    const deviceId = localStorage.getItem('device_id');
    
    try {
        const response = await fetch('/api/game/rooms/{{ $room->id }}/action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Player-ID': playerId,
                'X-Device-ID': deviceId
            },
            body: JSON.stringify({
                action: 'collect_fragment',
                data: { cave_number: caveNumber }
            })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur:', error);
        return { success: false };
    }
}
</script>
@endpush