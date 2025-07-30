<!-- resources/views/game/rooms/bureaux_pluie.blade.php -->
@extends('layouts.app')

@section('title', 'Bureaux sous la Pluie')
@section('header', $room->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Description -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <p class="text-gray-300 text-lg">{{ $room->description }}</p>
        <div class="mt-4 p-3 bg-gray-700 rounded">
            <p class="text-sm text-indigo-300">
                🌧️ Le plafond effondré laisse passer la pluie... Observez attentivement
            </p>
        </div>
    </div>

    <!-- Zone de jeu -->
    <div class="bg-gray-900 rounded-lg p-6 mb-6 relative overflow-hidden" style="min-height: 500px;">
        <!-- Bureau abandonné -->
        <div class="absolute inset-0 opacity-20">
            <div class="grid grid-cols-3 gap-4 p-8">
                @for($i = 0; $i < 9; $i++)
                <div class="bg-gray-800 rounded-lg h-24"></div>
                @endfor
            </div>
        </div>
        
        <!-- Zone de pluie -->
        <div id="rain-container" class="absolute inset-0">
            <!-- Les gouttes seront générées ici -->
        </div>
        
        <!-- Pattern caché dans la pluie -->
        <div id="hidden-pattern" class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-3000">
            <div class="text-center">
                <p class="text-6xl font-bold text-blue-300 drop-shadow-lg mb-4">{{ $room->digit_reward }}</p>
                <p class="text-lg text-blue-200">Le 4ème chiffre du code</p>
            </div>
        </div>
        
        <!-- Indicateur de mouvement -->
        <div class="absolute bottom-4 left-4 right-4">
            <div class="bg-gray-800 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-400">Niveau de mouvement</span>
                    <span id="movement-status" class="text-sm font-semibold text-yellow-400">En mouvement</span>
                </div>
                <div class="bg-gray-700 rounded-full h-4 overflow-hidden">
                    <div id="movement-bar" class="h-full bg-gradient-to-r from-green-500 to-red-500 transition-all duration-200" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <!-- Timer d'immobilité -->
        <div class="absolute top-4 right-4 bg-gray-800 rounded-lg px-4 py-2">
            <p class="text-sm text-gray-400">Immobilité</p>
            <p id="stillness-timer" class="text-2xl font-bold text-white">0s</p>
        </div>
    </div>

    <!-- Instructions -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-indigo-400 mb-3">Comment jouer ?</h3>
        <div class="space-y-2 text-gray-300">
            <p>1. Tenez votre téléphone devant vous</p>
            <p>2. Restez parfaitement immobile pendant 10 secondes</p>
            <p>3. Le pattern apparaîtra dans la pluie si vous ne bougez pas</p>
            <p>4. Le moindre mouvement réinitialisera le compteur !</p>
        </div>
        
        <button id="start-detection" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
            Commencer la détection
        </button>
    </div>

    <!-- Indice poétique (affiché après 30 secondes) -->
    <div id="poetic-hint" class="hidden bg-yellow-900 bg-opacity-50 border border-yellow-600 rounded-lg p-6 text-center">
        <p class="text-yellow-300 italic text-lg">
            "Tel l'eau qui ne se trouble que par le mouvement,<br>
            certains mystères ne se dévoilent qu'aux âmes immobiles"
        </p>
    </div>

    <!-- Résultat -->
    <div id="result-message" class="hidden"></div>
</div>

<!-- Styles pour la pluie -->
<style>
@keyframes fall {
    to {
        transform: translateY(520px);
    }
}

.raindrop {
    position: absolute;
    width: 2px;
    height: 15px;
    background: linear-gradient(to bottom, transparent, rgba(147, 197, 253, 0.6), transparent);
    animation: fall linear infinite;
}

.hidden-pattern {
    text-shadow: 0 0 20px rgba(147, 197, 253, 0.8);
}

#hidden-pattern.revealed {
    opacity: 1 !important;
}

.shake {
    animation: shake 0.5s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
</style>
@endsection

@push('scripts')
<script>
// Variables de détection de mouvement
let motionDetector = null;
let stillnessTimer = 0;
let stillnessInterval = null;
let isDetecting = false;
let movementLevel = 100;
let hintShown = false;
let challengeCompleted = false;

// Configuration
const STILLNESS_REQUIRED = 10; // secondes
const MOTION_THRESHOLD = 0.5; // sensibilité

// Générer la pluie
function generateRain() {
    const container = document.getElementById('rain-container');
    const dropCount = 100;
    
    for (let i = 0; i < dropCount; i++) {
        const drop = document.createElement('div');
        drop.className = 'raindrop';
        drop.style.left = Math.random() * 100 + '%';
        drop.style.animationDuration = (Math.random() * 1 + 0.5) + 's';
        drop.style.animationDelay = Math.random() * 2 + 's';
        container.appendChild(drop);
    }
}

// Démarrer la détection de mouvement
document.getElementById('start-detection').addEventListener('click', async () => {
    if (isDetecting) return;
    
    const button = document.getElementById('start-detection');
    button.disabled = true;
    button.textContent = 'Détection en cours...';
    
    // Demander l'accès aux capteurs
    if (typeof DeviceMotionEvent !== 'undefined' && typeof DeviceMotionEvent.requestPermission === 'function') {
        try {
            const permission = await DeviceMotionEvent.requestPermission();
            if (permission !== 'granted') {
                alert('Accès aux capteurs de mouvement refusé');
                resetDetection();
                return;
            }
        } catch (error) {
            console.error('Erreur permission:', error);
        }
    }
    
    startMotionDetection();
});

// Démarrer la détection
function startMotionDetection() {
    isDetecting = true;
    stillnessTimer = 0;
    movementLevel = 0;
    
    // Démarrer le timer d'immobilité
    stillnessInterval = setInterval(() => {
        if (movementLevel < 10) { // Seuil de mouvement acceptable
            stillnessTimer++;
            updateStillnessDisplay();
            
            // Vérifier si le temps requis est atteint
            if (stillnessTimer >= STILLNESS_REQUIRED) {
                revealPattern();
            }
            
            // Afficher l'indice après 30 secondes
            if (stillnessTimer === 30 && !hintShown) {
                showHint();
            }
        } else {
            // Mouvement détecté, réinitialiser
            if (stillnessTimer > 0) {
                resetStillness();
            }
        }
        
        // Diminuer progressivement le niveau de mouvement
        movementLevel = Math.max(0, movementLevel - 5);
        updateMovementBar();
    }, 1000);
    
    // Écouter les mouvements
    if (window.DeviceMotionEvent) {
        window.addEventListener('devicemotion', handleMotion);
    } else {
        // Fallback pour desktop : utiliser la souris
        window.addEventListener('mousemove', handleMouseMotion);
    }
}

// Gérer les mouvements du device
function handleMotion(event) {
    if (!isDetecting) return;
    
    const acc = event.accelerationIncludingGravity;
    if (!acc) return;
    
    // Calculer l'amplitude du mouvement
    const motion = Math.sqrt(
        Math.pow(acc.x || 0, 2) + 
        Math.pow(acc.y || 0, 2) + 
        Math.pow(acc.z || 0, 2)
    );
    
    // Normaliser (la gravité est ~9.8)
    const normalizedMotion = Math.abs(motion - 9.8);
    
    if (normalizedMotion > MOTION_THRESHOLD) {
        movementLevel = Math.min(100, movementLevel + normalizedMotion * 10);
    }
}

// Fallback pour souris (tests desktop)
let lastMouseX = null;
let lastMouseY = null;
function handleMouseMotion(event) {
    if (!isDetecting) return;
    
    if (lastMouseX !== null) {
        const deltaX = Math.abs(event.clientX - lastMouseX);
        const deltaY = Math.abs(event.clientY - lastMouseY);
        const motion = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
        
        if (motion > 2) {
            movementLevel = Math.min(100, movementLevel + motion);
        }
    }
    
    lastMouseX = event.clientX;
    lastMouseY = event.clientY;
}

// Réinitialiser l'immobilité
function resetStillness() {
    stillnessTimer = 0;
    updateStillnessDisplay();
    
    // Animation de secousse
    document.getElementById('rain-container').classList.add('shake');
    setTimeout(() => {
        document.getElementById('rain-container').classList.remove('shake');
    }, 500);
    
    // Vibration
    if (navigator.vibrate) {
        navigator.vibrate(200);
    }
}

// Mettre à jour l'affichage
function updateStillnessDisplay() {
    document.getElementById('stillness-timer').textContent = stillnessTimer + 's';
}

function updateMovementBar() {
    const bar = document.getElementById('movement-bar');
    const status = document.getElementById('movement-status');
    
    bar.style.width = movementLevel + '%';
    
    if (movementLevel < 10) {
        status.textContent = 'Immobile';
        status.className = 'text-sm font-semibold text-green-400';
    } else if (movementLevel < 50) {
        status.textContent = 'Léger mouvement';
        status.className = 'text-sm font-semibold text-yellow-400';
    } else {
        status.textContent = 'En mouvement';
        status.className = 'text-sm font-semibold text-red-400';
    }
}

// Afficher l'indice
function showHint() {
    document.getElementById('poetic-hint').classList.remove('hidden');
    hintShown = true;
}

// Révéler le pattern
function revealPattern() {
    if (challengeCompleted) return;
    
    challengeCompleted = true;
    isDetecting = false;
    
    // Arrêter la détection
    clearInterval(stillnessInterval);
    window.removeEventListener('devicemotion', handleMotion);
    window.removeEventListener('mousemove', handleMouseMotion);
    
    // Révéler le chiffre
    document.getElementById('hidden-pattern').classList.add('revealed');
    
    // Effet sonore de révélation
    playRevealSound();
    
    // Afficher le succès
    setTimeout(() => {
        showResult();
    }, 2000);
}

// Jouer un son de révélation (via le maître du jeu)
function playRevealSound() {
    // Envoyer un événement au téléphone maître pour jouer le son
    if (window.Echo) {
        window.Echo.channel('master').whisper('play-sound', {
            sound: 'reveal',
            team_id: {{ $team->id }}
        });
    }
}

// Afficher le résultat
async function showResult() {
    const resultDiv = document.getElementById('result-message');
    resultDiv.classList.remove('hidden');
    resultDiv.className = 'bg-green-900 bg-opacity-50 border border-green-600 rounded-lg p-6 text-center';
    resultDiv.innerHTML = `
        <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-lg font-semibold text-green-300">Magnifique !</p>
        <p class="text-green-400 mt-2">Votre patience a révélé le dernier chiffre du code.</p>
        <p class="text-sm text-gray-400 mt-2">Temps d'immobilité : ${stillnessTimer} secondes</p>
    `;
    
    // Envoyer au serveur
    await completeChallenge();
}

// Réinitialiser la détection
function resetDetection() {
    isDetecting = false;
    clearInterval(stillnessInterval);
    window.removeEventListener('devicemotion', handleMotion);
    window.removeEventListener('mousemove', handleMouseMotion);
    
    const button = document.getElementById('start-detection');
    button.disabled = false;
    button.textContent = 'Commencer la détection';
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
                    stillness_time: stillnessTimer,
                    hint_shown: hintShown
                }
            })
        });
        
        // Diffuser l'événement
        await fetch('/api/game/rooms/{{ $room->id }}/action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Player-ID': playerId,
                'X-Device-ID': deviceId
            },
            body: JSON.stringify({
                action: 'check_stillness',
                data: { duration: stillnessTimer }
            })
        });
        
        setTimeout(() => {
            window.location.href = '/game';
        }, 3000);
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Initialiser
generateRain();

// Test de compatibilité
if (!window.DeviceMotionEvent && !('ontouchstart' in window)) {
    const hint = document.createElement('div');
    hint.className = 'bg-blue-900 bg-opacity-50 border border-blue-600 rounded-lg p-4 mb-4';
    hint.innerHTML = '<p class="text-blue-300 text-sm">💡 Sur ordinateur, le mouvement de la souris sera détecté</p>';
    document.querySelector('.bg-gray-800.rounded-lg.p-6.mb-6').appendChild(hint);
}
</script>
@endpush