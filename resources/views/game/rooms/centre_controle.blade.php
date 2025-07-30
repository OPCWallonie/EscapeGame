<!-- resources/views/game/rooms/centre_controle.blade.php -->
@extends('layouts.app')

@section('title', 'Centre de Contrôle')
@section('header', $room->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Description -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <p class="text-gray-300 text-lg">{{ $room->description }}</p>
        <div class="mt-4 p-3 bg-gray-700 rounded">
            <p class="text-sm text-indigo-300">
                💡 Observez le pattern lumineux dans la galerie et reproduisez-le
            </p>
        </div>
    </div>

    <!-- Simulation de la baie vitrée -->
    <div class="bg-gray-900 rounded-lg p-6 mb-6 relative overflow-hidden">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Vue sur la galerie commerçante</h3>
        
        <!-- Fenêtre avec vue -->
        <div class="bg-gradient-to-b from-gray-800 to-gray-900 rounded-lg p-8 border-4 border-gray-700">
            <div class="grid grid-cols-5 gap-4 mb-8">
                <!-- Lumières de la galerie -->
                @for($i = 0; $i < 5; $i++)
                <div class="light-window" data-index="{{ $i }}">
                    <div class="bg-gray-800 rounded-full w-20 h-20 mx-auto flex items-center justify-center shadow-inner">
                        <div class="light-bulb w-16 h-16 rounded-full bg-gray-700 transition-all duration-300"></div>
                    </div>
                    <p class="text-center text-xs text-gray-500 mt-2">Lumière {{ $i + 1 }}</p>
                </div>
                @endfor
            </div>
            
            <!-- Bouton pour démarrer la séquence -->
            <div class="text-center">
                <button id="start-sequence" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                    Observer le pattern
                </button>
                <p class="text-sm text-gray-400 mt-2">Mémorisez bien la séquence !</p>
            </div>
        </div>
    </div>

    <!-- Zone de reproduction du pattern -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Reproduire le pattern</h3>
        
        <div class="grid grid-cols-5 gap-4 mb-6">
            <!-- Boutons de couleur -->
            <button class="color-button" data-color="red">
                <div class="bg-red-600 hover:bg-red-500 rounded-lg p-6 transition-all duration-200 transform hover:scale-105">
                    <div class="w-12 h-12 rounded-full bg-red-400 mx-auto shadow-lg"></div>
                    <p class="text-white text-sm mt-2">Rouge</p>
                </div>
            </button>
            
            <button class="color-button" data-color="blue">
                <div class="bg-blue-600 hover:bg-blue-500 rounded-lg p-6 transition-all duration-200 transform hover:scale-105">
                    <div class="w-12 h-12 rounded-full bg-blue-400 mx-auto shadow-lg"></div>
                    <p class="text-white text-sm mt-2">Bleu</p>
                </div>
            </button>
            
            <button class="color-button" data-color="green">
                <div class="bg-green-600 hover:bg-green-500 rounded-lg p-6 transition-all duration-200 transform hover:scale-105">
                    <div class="w-12 h-12 rounded-full bg-green-400 mx-auto shadow-lg"></div>
                    <p class="text-white text-sm mt-2">Vert</p>
                </div>
            </button>
            
            <button class="color-button" data-color="yellow">
                <div class="bg-yellow-600 hover:bg-yellow-500 rounded-lg p-6 transition-all duration-200 transform hover:scale-105">
                    <div class="w-12 h-12 rounded-full bg-yellow-400 mx-auto shadow-lg"></div>
                    <p class="text-white text-sm mt-2">Jaune</p>
                </div>
            </button>
            
            <button class="color-button" data-color="purple">
                <div class="bg-purple-600 hover:bg-purple-500 rounded-lg p-6 transition-all duration-200 transform hover:scale-105">
                    <div class="w-12 h-12 rounded-full bg-purple-400 mx-auto shadow-lg"></div>
                    <p class="text-white text-sm mt-2">Violet</p>
                </div>
            </button>
        </div>
        
        <!-- Séquence entrée -->
        <div class="bg-gray-700 rounded-lg p-4">
            <p class="text-sm text-gray-400 mb-2">Votre séquence :</p>
            <div id="user-sequence" class="flex space-x-2 min-h-[3rem]">
                <!-- Les couleurs sélectionnées apparaîtront ici -->
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex space-x-3 mt-4">
            <button id="clear-sequence" class="flex-1 bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                Effacer
            </button>
            <button id="submit-sequence" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Valider
            </button>
        </div>
    </div>

    <!-- Tentatives restantes -->
    <div class="bg-gray-800 rounded-lg p-4 text-center">
        <p class="text-gray-400">
            Tentatives : <span id="attempts-count" class="text-white font-semibold">0</span> / 3
        </p>
    </div>

    <!-- Message de résultat -->
    <div id="result-message" class="hidden mt-6"></div>
</div>
@endsection

@push('scripts')
<script>
// Configuration
const correctPattern = ['red', 'blue', 'blue', 'green', 'red'];
const colorMap = {
    'red': '#ef4444',
    'blue': '#3b82f6',
    'green': '#10b981',
    'yellow': '#f59e0b',
    'purple': '#8b5cf6'
};

let userPattern = [];
let attempts = 0;
let isShowingPattern = false;
let canInteract = false;

// Démarrer la séquence
document.getElementById('start-sequence').addEventListener('click', () => {
    if (isShowingPattern) return;
    showLightPattern();
});

// Afficher le pattern lumineux
async function showLightPattern() {
    isShowingPattern = true;
    canInteract = false;
    const button = document.getElementById('start-sequence');
    button.disabled = true;
    button.textContent = 'Observation en cours...';
    
    // Réinitialiser les lumières
    document.querySelectorAll('.light-bulb').forEach(bulb => {
        bulb.style.backgroundColor = '#374151';
        bulb.style.boxShadow = 'none';
    });
    
    // Attendre un peu avant de commencer
    await sleep(1000);
    
    // Afficher chaque couleur de la séquence
    for (let i = 0; i < correctPattern.length; i++) {
        const color = correctPattern[i];
        const bulb = document.querySelectorAll('.light-bulb')[i];
        
        // Allumer la lumière
        bulb.style.backgroundColor = colorMap[color];
        bulb.style.boxShadow = `0 0 20px ${colorMap[color]}`;
        
        await sleep(800);
        
        // Éteindre la lumière
        bulb.style.backgroundColor = '#374151';
        bulb.style.boxShadow = 'none';
        
        await sleep(300);
    }
    
    // Fin de la séquence
    button.disabled = false;
    button.textContent = 'Observer le pattern';
    isShowingPattern = false;
    canInteract = true;
    
    // Message d'instruction
    showNotification('Maintenant, reproduisez la séquence avec les boutons de couleur', 'info');
}

// Boutons de couleur
document.querySelectorAll('.color-button').forEach(button => {
    button.addEventListener('click', () => {
        if (!canInteract || userPattern.length >= 5) return;
        
        const color = button.dataset.color;
        addColorToSequence(color);
        
        // Feedback visuel
        const colorDiv = button.querySelector('div');
        colorDiv.classList.add('scale-90');
        setTimeout(() => colorDiv.classList.remove('scale-90'), 200);
        
        // Vibration
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
    });
});

// Ajouter une couleur à la séquence
function addColorToSequence(color) {
    userPattern.push(color);
    updateSequenceDisplay();
    
    // Activer le bouton de validation si 5 couleurs
    document.getElementById('submit-sequence').disabled = userPattern.length !== 5;
}

// Mettre à jour l'affichage de la séquence
function updateSequenceDisplay() {
    const container = document.getElementById('user-sequence');
    container.innerHTML = '';
    
    userPattern.forEach((color, index) => {
        const colorDiv = document.createElement('div');
        colorDiv.className = 'w-12 h-12 rounded-full shadow-lg cursor-pointer transform transition-all duration-200 hover:scale-110';
        colorDiv.style.backgroundColor = colorMap[color];
        colorDiv.title = `Cliquer pour retirer`;
        
        // Permettre de retirer une couleur en cliquant dessus
        colorDiv.addEventListener('click', () => {
            userPattern.splice(index, 1);
            updateSequenceDisplay();
            document.getElementById('submit-sequence').disabled = userPattern.length !== 5;
        });
        
        container.appendChild(colorDiv);
    });
}

// Effacer la séquence
document.getElementById('clear-sequence').addEventListener('click', () => {
    userPattern = [];
    updateSequenceDisplay();
    document.getElementById('submit-sequence').disabled = true;
});

// Valider la séquence
document.getElementById('submit-sequence').addEventListener('click', async () => {
    if (userPattern.length !== 5) return;
    
    attempts++;
    document.getElementById('attempts-count').textContent = attempts;
    
    const isCorrect = JSON.stringify(userPattern) === JSON.stringify(correctPattern);
    
    // Afficher le résultat
    showResult(isCorrect);
    
    // Envoyer au serveur
    await submitPattern(isCorrect);
    
    if (!isCorrect && attempts < 3) {
        // Réinitialiser pour un nouvel essai
        setTimeout(() => {
            userPattern = [];
            updateSequenceDisplay();
            document.getElementById('submit-sequence').disabled = true;
        }, 2000);
    }
});

// Afficher le résultat
function showResult(success) {
    const resultDiv = document.getElementById('result-message');
    resultDiv.classList.remove('hidden');
    
    if (success) {
        resultDiv.className = 'bg-green-900 bg-opacity-50 border border-green-600 rounded-lg p-6 text-center mt-6';
        resultDiv.innerHTML = `
            <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-semibold text-green-300">Pattern correct !</p>
            <p class="text-green-400 mt-2">Le 3ème chiffre du code apparaît sur l'écran de contrôle...</p>
            <p class="text-4xl font-bold text-green-300 mt-4">{{ $room->digit_reward }}</p>
        `;
    } else {
        resultDiv.className = 'bg-red-900 bg-opacity-50 border border-red-600 rounded-lg p-6 text-center mt-6';
        const remaining = 3 - attempts;
        resultDiv.innerHTML = `
            <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <p class="text-lg font-semibold text-red-300">Pattern incorrect !</p>
            <p class="text-red-400 mt-2">${remaining > 0 ? `${remaining} tentative(s) restante(s)` : 'Plus de tentatives !'}</p>
        `;
        
        if (remaining === 0) {
            resultDiv.innerHTML += `<p class="text-yellow-400 mt-2">Une pénalité a été ajoutée à votre temps</p>`;
        }
    }
}

// Envoyer le pattern au serveur
async function submitPattern(success) {
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
                action: 'submit_pattern',
                data: {
                    pattern: userPattern,
                    attempts: attempts
                }
            })
        });
        
        const data = await response.json();
        
        if (data.success && data.digit_revealed) {
            // Compléter le défi
            await completeChallenge();
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Compléter le défi
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
                data: { attempts: attempts }
            })
        });
        
        // Rediriger après 3 secondes
        setTimeout(() => {
            window.location.href = '/game';
        }, 3000);
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Afficher une notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-20 right-4 ${
        type === 'info' ? 'bg-blue-900' : 'bg-yellow-900'
    } bg-opacity-90 rounded-lg p-4 max-w-sm transform transition-all duration-300 translate-x-full`;
    notification.innerHTML = `<p class="text-white">${message}</p>`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Fonction utilitaire sleep
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
</script>
@endpush