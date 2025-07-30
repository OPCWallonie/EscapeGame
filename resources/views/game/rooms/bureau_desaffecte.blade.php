<!-- resources/views/game/rooms/bureau_desaffecte.blade.php -->
@extends('layouts.app')

@section('title', 'Bureau Désaffecté')
@section('header', $room->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Description -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <p class="text-gray-300 text-lg">{{ $room->description }}</p>
        <div class="mt-4 p-3 bg-gray-700 rounded">
            <p class="text-sm text-indigo-300">
                🔍 Trouvez le bon bureau parmi les 5. Les mauvais QR codes vous pénalisent !
            </p>
        </div>
    </div>

    <!-- Plan des bureaux -->
    <div class="bg-gray-900 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Plan du dédale de bureaux</h3>
        
        <!-- Grille des bureaux -->
        <div class="relative max-w-lg mx-auto">
            <svg viewBox="0 0 400 400" class="w-full h-full">
                <!-- Murs -->
                <rect x="50" y="50" width="300" height="300" fill="none" stroke="#4B5563" stroke-width="2"/>
                
                <!-- Bureau 1 (haut gauche) -->
                <rect x="60" y="60" width="130" height="130" fill="#1F2937" stroke="#6B7280" stroke-width="2" 
                      class="bureau-rect cursor-pointer hover:fill-gray-700 transition-colors" data-bureau="1"/>
                <text x="125" y="130" text-anchor="middle" fill="#9CA3AF" font-size="20">Bureau 1</text>
                
                <!-- Bureau 2 (haut droite) -->
                <rect x="210" y="60" width="130" height="130" fill="#1F2937" stroke="#6B7280" stroke-width="2"
                      class="bureau-rect cursor-pointer hover:fill-gray-700 transition-colors" data-bureau="2"/>
                <text x="275" y="130" text-anchor="middle" fill="#9CA3AF" font-size="20">Bureau 2</text>
                
                <!-- Bureau 3 (centre) -->
                <rect x="135" y="135" width="130" height="130" fill="#1F2937" stroke="#6B7280" stroke-width="2"
                      class="bureau-rect cursor-pointer hover:fill-gray-700 transition-colors" data-bureau="3"/>
                <text x="200" y="205" text-anchor="middle" fill="#9CA3AF" font-size="20">Bureau 3</text>
                
                <!-- Bureau 4 (bas gauche) -->
                <rect x="60" y="210" width="130" height="130" fill="#1F2937" stroke="#6B7280" stroke-width="2"
                      class="bureau-rect cursor-pointer hover:fill-gray-700 transition-colors" data-bureau="4"/>
                <text x="125" y="280" text-anchor="middle" fill="#9CA3AF" font-size="20">Bureau 4</text>
                
                <!-- Bureau 5 (bas droite) - Le bon ! -->
                <rect x="210" y="210" width="130" height="130" fill="#1F2937" stroke="#6B7280" stroke-width="2"
                      class="bureau-rect cursor-pointer hover:fill-gray-700 transition-colors" data-bureau="5"/>
                <text x="275" y="280" text-anchor="middle" fill="#9CA3AF" font-size="20">Bureau 5</text>
                
                <!-- Entrée -->
                <rect x="195" y="340" width="40" height="10" fill="#6366F1"/>
                <text x="215" y="370" text-anchor="middle" fill="#9CA3AF" font-size="14">Entrée</text>
                
                <!-- Position du joueur -->
                <circle id="player-position" cx="215" cy="345" r="8" fill="#10B981" class="transition-all duration-500">
                    <animate attributeName="r" values="8;12;8" dur="2s" repeatCount="indefinite"/>
                </circle>
            </svg>
        </div>
        
        <!-- Indicateur de température -->
        <div class="mt-6 bg-gray-800 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-400">Proximité du bon bureau</span>
                <span id="temperature-text" class="text-sm font-semibold">-</span>
            </div>
            <div class="bg-gray-700 rounded-full h-6 overflow-hidden">
                <div id="temperature-bar" class="h-full bg-gradient-to-r from-blue-500 via-yellow-500 to-red-500 transition-all duration-500" style="width: 0%"></div>
            </div>
            
            <!-- Indicateurs sonores -->
            <div class="mt-3 flex justify-around text-center">
                <div>
                    <div class="w-2 h-2 bg-blue-500 rounded-full mx-auto mb-1"></div>
                    <span class="text-xs text-gray-500">Glacial</span>
                </div>
                <div>
                    <div class="w-2 h-2 bg-blue-400 rounded-full mx-auto mb-1"></div>
                    <span class="text-xs text-gray-500">Froid</span>
                </div>
                <div>
                    <div class="w-2 h-2 bg-yellow-500 rounded-full mx-auto mb-1"></div>
                    <span class="text-xs text-gray-500">Tiède</span>
                </div>
                <div>
                    <div class="w-2 h-2 bg-orange-500 rounded-full mx-auto mb-1"></div>
                    <span class="text-xs text-gray-500">Chaud</span>
                </div>
                <div>
                    <div class="w-2 h-2 bg-red-500 rounded-full mx-auto mb-1"></div>
                    <span class="text-xs text-gray-500">Brûlant!</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Actions disponibles</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <button id="scan-current" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
                Scanner le QR du bureau
            </button>
            
            <button id="use-detector" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Utiliser le détecteur
            </button>
        </div>
        
        <!-- Compteur de tentatives -->
        <div class="mt-4 flex justify-between items-center text-sm">
            <div>
                <span class="text-gray-400">Bureaux scannés :</span>
                <span id="scan-count" class="text-white font-semibold ml-2">0 / 5</span>
            </div>
            <div>
                <span class="text-gray-400">Pénalités :</span>
                <span id="penalty-count" class="text-red-400 font-semibold ml-2">0</span>
            </div>
        </div>
    </div>

    <!-- Message d'avertissement -->
    <div id="penalty-warning" class="hidden bg-red-900 bg-opacity-50 border border-red-600 rounded-lg p-4 mb-6">
        <p class="text-red-300 font-semibold">⏱️ Pénalité de 30 secondes !</p>
        <p class="text-red-200 text-sm">Ce n'était pas le bon bureau...</p>
        <div class="mt-2">
            <div class="bg-red-800 rounded-full h-2 overflow-hidden">
                <div id="penalty-progress" class="h-full bg-red-600 transition-all duration-1000" style="width: 100%"></div>
            </div>
        </div>
    </div>

    <!-- Résultat -->
    <div id="result-message" class="hidden"></div>
</div>
@endsection

@push('scripts')
<script>
// Configuration
const CORRECT_BUREAU = 5;
const PENALTY_DURATION = 30; // secondes
let currentBureau = null;
let scannedBureaus = [];
let penalties = 0;
let isInPenalty = false;
let detectorActive = false;
let beepInterval = null;

// Solution : Bureau 5 est le bon
const distances = {
    1: 3, // Froid
    2: 2, // Tiède
    3: 2, // Tiède
    4: 1, // Chaud
    5: 0  // Brûlant!
};

// Gérer les clics sur les bureaux
document.querySelectorAll('.bureau-rect').forEach(rect => {
    rect.addEventListener('click', () => {
        if (isInPenalty) {
            showNotification('Attendez la fin de la pénalité', 'warning');
            return;
        }
        
        const bureau = parseInt(rect.dataset.bureau);
        selectBureau(bureau);
    });
});

// Sélectionner un bureau
function selectBureau(bureau) {
    currentBureau = bureau;
    
    // Mettre à jour la position du joueur
    const positions = {
        1: { x: 125, y: 125 },
        2: { x: 275, y: 125 },
        3: { x: 200, y: 200 },
        4: { x: 125, y: 275 },
        5: { x: 275, y: 275 }
    };
    
    const player = document.getElementById('player-position');
    player.setAttribute('cx', positions[bureau].x);
    player.setAttribute('cy', positions[bureau].y);
    
    // Mettre à jour la température si le détecteur est actif
    if (detectorActive) {
        updateTemperature(bureau);
    }
    
    // Activer le bouton scanner
    document.getElementById('scan-current').disabled = false;
    
    // Feedback visuel
    document.querySelectorAll('.bureau-rect').forEach(rect => {
        rect.setAttribute('fill', '#1F2937');
    });
    document.querySelector(`[data-bureau="${bureau}"]`).setAttribute('fill', '#374151');
}

// Scanner le bureau actuel
document.getElementById('scan-current').addEventListener('click', async () => {
    if (!currentBureau || isInPenalty) return;
    
    if (scannedBureaus.includes(currentBureau)) {
        showNotification('Bureau déjà scanné !', 'warning');
        return;
    }
    
    scannedBureaus.push(currentBureau);
    updateScanCount();
    
    if (currentBureau === CORRECT_BUREAU) {
        // Succès !
        showSuccess();
    } else {
        // Mauvais bureau - pénalité
        applyPenalty();
    }
});

// Utiliser le détecteur
document.getElementById('use-detector').addEventListener('click', () => {
    detectorActive = !detectorActive;
    const button = document.getElementById('use-detector');
    
    if (detectorActive) {
        button.classList.remove('bg-green-600', 'hover:bg-green-700');
        button.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
        button.textContent = 'Arrêter le détecteur';
        
        if (currentBureau) {
            updateTemperature(currentBureau);
        }
        
        showNotification('Détecteur activé - Déplacez-vous pour tester', 'info');
    } else {
        button.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
        button.classList.add('bg-green-600', 'hover:bg-green-700');
        button.innerHTML = `
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Utiliser le détecteur
        `;
        
        stopBeeping();
        resetTemperature();
    }
});

// Mettre à jour la température
function updateTemperature(bureau) {
    const distance = distances[bureau];
    const temps = ['Brûlant!', 'Chaud', 'Tiède', 'Froid', 'Glacial'];
    const colors = ['text-red-500', 'text-orange-500', 'text-yellow-500', 'text-blue-400', 'text-blue-500'];
    const percentages = [100, 75, 50, 25, 10];
    
    const tempText = document.getElementById('temperature-text');
    const tempBar = document.getElementById('temperature-bar');
    
    tempText.textContent = temps[distance];
    tempText.className = `text-sm font-semibold ${colors[distance]}`;
    tempBar.style.width = percentages[distance] + '%';
    
    // Beep sonore
    startBeeping(distance);
}

// Démarrer les bips
function startBeeping(distance) {
    stopBeeping();
    
    const delays = [200, 500, 1000, 2000, 3000]; // Plus c'est chaud, plus c'est rapide
    
    const beep = () => {
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
        
        // Effet visuel de pulsation
        const bar = document.getElementById('temperature-bar');
        bar.style.opacity = '0.5';
        setTimeout(() => {
            bar.style.opacity = '1';
        }, 100);
    };
    
    beep(); // Premier bip immédiat
    beepInterval = setInterval(beep, delays[distance]);
}

// Arrêter les bips
function stopBeeping() {
    if (beepInterval) {
        clearInterval(beepInterval);
        beepInterval = null;
    }
}

// Réinitialiser la température
function resetTemperature() {
    document.getElementById('temperature-text').textContent = '-';
    document.getElementById('temperature-text').className = 'text-sm font-semibold';
    document.getElementById('temperature-bar').style.width = '0%';
}

// Appliquer une pénalité
function applyPenalty() {
    penalties++;
    isInPenalty = true;
    updatePenaltyCount();
    
    // Afficher l'avertissement
    const warning = document.getElementById('penalty-warning');
    warning.classList.remove('hidden');
    
    // Désactiver les actions
    document.getElementById('scan-current').disabled = true;
    
    // Animation de la barre de progression
    const progress = document.getElementById('penalty-progress');
    progress.style.width = '100%';
    
    let timeLeft = PENALTY_DURATION;
    const interval = setInterval(() => {
        timeLeft--;
        progress.style.width = (timeLeft / PENALTY_DURATION * 100) + '%';
        
        if (timeLeft <= 0) {
            clearInterval(interval);
            warning.classList.add('hidden');
            isInPenalty = false;
            document.getElementById('scan-current').disabled = false;
        }
    }, 1000);
    
    // Vibration longue
    if (navigator.vibrate) {
        navigator.vibrate([200, 100, 200, 100, 200]);
    }
}

// Mettre à jour les compteurs
function updateScanCount() {
    document.getElementById('scan-count').textContent = `${scannedBureaus.length} / 5`;
}

function updatePenaltyCount() {
    document.getElementById('penalty-count').textContent = penalties;
}

// Afficher le succès
async function showSuccess() {
    stopBeeping();
    
    const resultDiv = document.getElementById('result-message');
    resultDiv.classList.remove('hidden');
    resultDiv.className = 'bg-green-900 bg-opacity-50 border border-green-600 rounded-lg p-6 text-center';
    resultDiv.innerHTML = `
        <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-lg font-semibold text-green-300">Bureau trouvé !</p>
        <p class="text-green-400 mt-2">C'était bien le bureau ${CORRECT_BUREAU} !</p>
        <p class="text-sm text-gray-400 mt-2">Tentatives : ${scannedBureaus.length} | Pénalités : ${penalties}</p>
    `;
    
    // Marquer le bureau correct
    document.querySelector(`[data-bureau="${CORRECT_BUREAU}"]`).setAttribute('fill', '#10B981');
    
    // Envoyer au serveur
    await completeChallenge();
}

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
                    bureaus_scanned: scannedBureaus,
                    penalties: penalties,
                    detector_used: detectorActive
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

// Raccourcis clavier
document.addEventListener('keydown', (e) => {
    if (isInPenalty) return;
    
    const keyMap = {
        '1': 1, '2': 2, '3': 3, '4': 4, '5': 5,
        'NumPad1': 1, 'NumPad2': 2, 'NumPad3': 3, 'NumPad4': 4, 'NumPad5': 5
    };
    
    if (keyMap[e.code]) {
        selectBureau(keyMap[e.code]);
    } else if (e.code === 'Space' && currentBureau) {
        e.preventDefault();
        document.getElementById('scan-current').click();
    } else if (e.code === 'KeyD') {
        document.getElementById('use-detector').click();
    }
});
</script>
@endpush