<!-- resources/views/game/rooms/toit.blade.php -->
@extends('layouts.app')

@section('title', 'Le Toit - Finale')
@section('header', $room->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Cinématique d'introduction -->
    <div id="intro-sequence" class="fixed inset-0 bg-black z-50 flex items-center justify-center">
        <div class="text-center text-white">
            <h1 class="text-4xl font-bold mb-4 animate-pulse">FINALE</h1>
            <p class="text-xl text-gray-300">Vous avez atteint le toit...</p>
            <button id="start-finale" class="mt-8 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                Commencer la séquence finale
            </button>
        </div>
    </div>

    <!-- Zone principale (cachée initialement) -->
    <div id="main-content" class="hidden">
        <!-- Vue du toit -->
        <div class="bg-gradient-to-b from-gray-900 to-blue-900 rounded-lg p-6 mb-6 relative overflow-hidden" style="min-height: 500px;">
            <!-- Ciel étoilé -->
            <div class="absolute inset-0">
                @for($i = 0; $i < 50; $i++)
                <div class="absolute animate-pulse" 
                     style="left: {{ rand(0, 100) }}%; top: {{ rand(0, 40) }}%; animation-delay: {{ rand(0, 3000) }}ms;">
                    <div class="w-1 h-1 bg-white rounded-full"></div>
                </div>
                @endfor
            </div>
            
            <!-- Zone d'atterrissage -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="relative">
                    <!-- Héliport -->
                    <div class="w-64 h-64 border-4 border-yellow-400 rounded-full flex items-center justify-center">
                        <span class="text-yellow-400 text-6xl font-bold">H</span>
                    </div>
                    
                    <!-- Lumières clignotantes -->
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-2">
                        <div class="w-4 h-4 bg-red-500 rounded-full animate-pulse"></div>
                    </div>
                    <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-2">
                        <div class="w-4 h-4 bg-red-500 rounded-full animate-pulse" style="animation-delay: 500ms;"></div>
                    </div>
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-2">
                        <div class="w-4 h-4 bg-red-500 rounded-full animate-pulse" style="animation-delay: 1000ms;"></div>
                    </div>
                    <div class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-2">
                        <div class="w-4 h-4 bg-red-500 rounded-full animate-pulse" style="animation-delay: 1500ms;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Zone AR pour l'hélicoptère -->
            <div id="ar-container" class="absolute inset-0 pointer-events-none">
                <!-- L'hélicoptère AR sera affiché ici -->
            </div>
            
            <!-- Interface de communication -->
            <div class="absolute bottom-4 left-4 right-4 bg-black bg-opacity-75 rounded-lg p-4">
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-green-400 font-mono text-sm">COMMUNICATION ÉTABLIE</span>
                </div>
                <p id="radio-message" class="text-gray-300 font-mono text-sm"></p>
            </div>
        </div>

        <!-- Panneau de contrôle -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-indigo-400 mb-4">Panneau de contrôle d'extraction</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <!-- Signal de détresse -->
                <button id="distress-signal" class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200 disabled:opacity-50">
                    <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Signal de détresse
                </button>
                
                <!-- Lumières d'atterrissage -->
                <button id="landing-lights" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200">
                    <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    Lumières d'atterrissage
                </button>
                
                <!-- Appel hélico -->
                <button id="call-helicopter" class="bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200 col-span-2 disabled:opacity-50" disabled>
                    <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Appeler l'hélicoptère d'extraction
                </button>
            </div>
            
            <!-- Statut -->
            <div class="mt-4 text-center">
                <p id="status-message" class="text-gray-400 text-sm">Activez le signal et les lumières avant d'appeler l'hélicoptère</p>
            </div>
        </div>

        <!-- Photo de groupe AR -->
        <div id="photo-section" class="hidden bg-gray-800 rounded-lg p-6 text-center">
            <h3 class="text-lg font-semibold text-green-400 mb-4">🎉 Mission accomplie !</h3>
            
            <div id="camera-view" class="relative bg-black rounded-lg overflow-hidden mb-4" style="min-height: 400px;">
                <!-- Vue caméra pour la photo -->
                <video id="camera-stream" class="w-full h-full object-cover"></video>
                
                <!-- Overlay AR hélicoptère -->
                <div id="helicopter-overlay" class="absolute inset-0 pointer-events-none">
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDIwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0xMDAgNTBDMTAwIDUwIDcwIDQ1IDYwIDQ1QzUwIDQ1IDQwIDUwIDQwIDYwQzQwIDcwIDUwIDc1IDYwIDc1QzcwIDc1IDEwMCA3MCAxMDAgNzBWNTBaIiBmaWxsPSIjNEI1NTYzIi8+CjxyZWN0IHg9IjgwIiB5PSIzMCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iNSIgZmlsbD0iIzM3NDE1MSIvPgo8cmVjdCB4PSI5NSIgeT0iMTAiIHdpZHRoPSIxMCIgaGVpZ2h0PSIyMCIgZmlsbD0iIzZCNzI4MCIvPgo8cmVjdCB4PSI1MCIgeT0iMTUiIHdpZHRoPSIxMDAiIGhlaWdodD0iNSIgZmlsbD0iIzZCNzI4MCIgY2xhc3M9ImFuaW1hdGUtc3BpbiIgc3R5bGU9InRyYW5zZm9ybS1vcmlnaW46IDEwMHB4IDIwcHgiLz4KPC9zdmc+" 
                         alt="Helicopter" 
                         class="absolute top-10 right-10 w-48 animate-bounce" />
                </div>
                
                <!-- Compte à rebours photo -->
                <div id="photo-countdown" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                    <span class="text-8xl font-bold text-white"></span>
                </div>
            </div>
            
            <button id="take-photo" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg">
                📸 Prendre la photo souvenir
            </button>
            
            <!-- Photo finale -->
            <div id="final-photo" class="hidden mt-4">
                <img id="captured-photo" class="rounded-lg shadow-xl mx-auto" />
                <button id="download-photo" class="mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                    Télécharger la photo
                </button>
            </div>
        </div>

        <!-- Résultats finaux -->
        <div id="final-results" class="hidden bg-gradient-to-r from-green-900 to-blue-900 rounded-lg p-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-6">🏆 Félicitations !</h2>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="bg-black bg-opacity-30 rounded-lg p-4">
                    <p class="text-gray-400 text-sm">Temps total</p>
                    <p id="final-time" class="text-2xl font-bold text-white">--:--</p>
                </div>
                <div class="bg-black bg-opacity-30 rounded-lg p-4">
                    <p class="text-gray-400 text-sm">Pénalités</p>
                    <p id="final-penalties" class="text-2xl font-bold text-white">0</p>
                </div>
            </div>
            
            <p class="text-gray-300 mb-6">
                Vous avez brillamment résolu tous les défis et atteint l'extraction !
            </p>
            
            <button onclick="window.location.href='/'" class="bg-white text-gray-900 font-bold py-3 px-8 rounded-lg hover:bg-gray-100 transition">
                Retour à l'accueil
            </button>
        </div>
    </div>
</div>

<!-- Sons d'ambiance -->
<audio id="helicopter-sound" preload="auto">
    <source src="data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEARKwAABCxAgAEABAAZGF0YQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==" type="audio/wav">
</audio>
@endsection

@push('scripts')
<script>
let distressActive = false;
let lightsActive = false;
let helicopterCalled = false;
let cameraStream = null;

// Démarrer la finale
document.getElementById('start-finale').addEventListener('click', () => {
    document.getElementById('intro-sequence').classList.add('hidden');
    document.getElementById('main-content').classList.remove('hidden');
    
    // Démarrer les messages radio
    startRadioSequence();
});

// Séquence de messages radio
function startRadioSequence() {
    const messages = [
        { delay: 1000, text: "Tour de contrôle à équipe d'extraction, nous recevons votre position..." },
        { delay: 4000, text: "Activez le signal de détresse et les lumières d'atterrissage." },
        { delay: 7000, text: "L'hélicoptère est en attente de votre signal." }
    ];
    
    messages.forEach(msg => {
        setTimeout(() => {
            typeRadioMessage(msg.text);
        }, msg.delay);
    });
}

// Effet machine à écrire pour les messages
function typeRadioMessage(text) {
    const element = document.getElementById('radio-message');
    element.textContent = '';
    let index = 0;
    
    const interval = setInterval(() => {
        element.textContent += text[index];
        index++;
        
        if (index >= text.length) {
            clearInterval(interval);
        }
    }, 50);
}

// Signal de détresse
document.getElementById('distress-signal').addEventListener('click', function() {
    distressActive = !distressActive;
    
    if (distressActive) {
        this.classList.remove('bg-red-600', 'hover:bg-red-700');
        this.classList.add('bg-red-800', 'animate-pulse');
        typeRadioMessage("Signal de détresse activé. Fréquence 121.5 MHz.");
    } else {
        this.classList.add('bg-red-600', 'hover:bg-red-700');
        this.classList.remove('bg-red-800', 'animate-pulse');
    }
    
    checkHelicopterReady();
});

// Lumières d'atterrissage
document.getElementById('landing-lights').addEventListener('click', function() {
    lightsActive = !lightsActive;
    
    if (lightsActive) {
        this.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
        this.classList.add('bg-yellow-400');
        
        // Animer les lumières de l'héliport
        document.querySelectorAll('.bg-red-500').forEach(light => {
            light.classList.add('animate-spin');
        });
        
        typeRadioMessage("Lumières d'atterrissage allumées. Zone sécurisée.");
    } else {
        this.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
        this.classList.remove('bg-yellow-400');
        
        document.querySelectorAll('.bg-red-500').forEach(light => {
            light.classList.remove('animate-spin');
        });
    }
    
    checkHelicopterReady();
});

// Vérifier si on peut appeler l'hélico
function checkHelicopterReady() {
    const button = document.getElementById('call-helicopter');
    
    if (distressActive && lightsActive) {
        button.disabled = false;
        document.getElementById('status-message').textContent = "Systèmes prêts. Vous pouvez appeler l'hélicoptère !";
        document.getElementById('status-message').classList.add('text-green-400');
    } else {
        button.disabled = true;
        document.getElementById('status-message').textContent = "Activez le signal et les lumières avant d'appeler l'hélicoptère";
        document.getElementById('status-message').classList.remove('text-green-400');
    }
}

// Appeler l'hélicoptère
document.getElementById('call-helicopter').addEventListener('click', async function() {
    if (helicopterCalled) return;
    
    helicopterCalled = true;
    this.disabled = true;
    this.textContent = "Hélicoptère en approche...";
    
    // Messages radio
    typeRadioMessage("Hélicoptère Alpha-Charlie-7 en route. ETA 30 secondes.");
    
    // Animation d'approche
    setTimeout(() => {
        startHelicopterApproach();
    }, 3000);
    
    // Compléter le défi
    await completeChallenge();
});

// Animation d'approche de l'hélicoptère
function startHelicopterApproach() {
    // Effet sonore (via le maître)
    if (window.Echo) {
        window.Echo.channel('master').whisper('play-sound', {
            sound: 'helicopter',
            team_id: {{ $team->id }}
        });
    }
    
    // Animation visuelle
    const arContainer = document.getElementById('ar-container');
    arContainer.innerHTML = `
        <div class="helicopter-approach">
            <div class="absolute top-0 right-0 animate-helicopter-fly">
                <svg width="100" height="50" viewBox="0 0 100 50" fill="white" opacity="0.8">
                    <path d="M50 25 L30 20 L30 30 Z M40 25 L60 25 M50 15 L50 35 M45 10 L55 10 M25 15 L75 15" stroke="white" stroke-width="2"/>
                </svg>
            </div>
        </div>
    `;
    
    // Message de fin
    setTimeout(() => {
        typeRadioMessage("Hélicoptère en position. Préparez-vous pour l'embarquement !");
        showPhotoSection();
    }, 5000);
}

// Afficher la section photo
function showPhotoSection() {
    document.getElementById('photo-section').classList.remove('hidden');
    initCamera();
}

// Initialiser la caméra
async function initCamera() {
    try {
        const video = document.getElementById('camera-stream');
        cameraStream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'user' }, 
            audio: false 
        });
        video.srcObject = cameraStream;
    } catch (error) {
        console.error('Erreur caméra:', error);
        // Fallback sans caméra
        document.getElementById('camera-view').innerHTML = `
            <div class="flex items-center justify-center h-full bg-gray-900">
                <p class="text-gray-400">Caméra non disponible</p>
            </div>
        `;
    }
}

// Prendre la photo
document.getElementById('take-photo').addEventListener('click', () => {
    const countdown = document.getElementById('photo-countdown');
    const countdownText = countdown.querySelector('span');
    countdown.classList.remove('hidden');
    
    let count = 3;
    countdownText.textContent = count;
    
    const interval = setInterval(() => {
        count--;
        if (count > 0) {
            countdownText.textContent = count;
        } else {
            clearInterval(interval);
            countdownText.textContent = '📸';
            
            // Capturer la photo
            setTimeout(() => {
                capturePhoto();
                countdown.classList.add('hidden');
            }, 500);
        }
    }, 1000);
});

// Capturer la photo
function capturePhoto() {
    const video = document.getElementById('camera-stream');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    // Dessiner la vidéo
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Ajouter l'overlay hélicoptère
    ctx.font = '30px Arial';
    ctx.fillStyle = 'white';
    ctx.fillText('🚁 Mission Accomplie!', 20, 50);
    
    // Ajouter la date
    ctx.font = '20px Arial';
    ctx.fillText(new Date().toLocaleDateString(), 20, canvas.height - 20);
    
    // Afficher la photo
    const img = document.getElementById('captured-photo');
    img.src = canvas.toDataURL('image/jpeg');
    document.getElementById('final-photo').classList.remove('hidden');
    document.getElementById('take-photo').classList.add('hidden');
    
    // Stopper la caméra
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
    
    // Afficher les résultats finaux
    setTimeout(() => {
        showFinalResults();
    }, 2000);
}

// Télécharger la photo
document.getElementById('download-photo').addEventListener('click', () => {
    const img = document.getElementById('captured-photo');
    const link = document.createElement('a');
    link.download = `escape-game-${Date.now()}.jpg`;
    link.href = img.src;
    link.click();
});

// Afficher les résultats finaux
function showFinalResults() {
    document.getElementById('final-results').classList.remove('hidden');
    
    // Récupérer les stats finales
    const totalTime = document.querySelector('#total-time')?.textContent || '--:--';
    document.getElementById('final-time').textContent = totalTime;
    document.getElementById('final-penalties').textContent = '{{ $team->penalties }}';
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
                    finale_completed: true
                }
            })
        });
        
        // Marquer l'équipe comme terminée
        await fetch('/api/master/finish/{{ $team->id }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Player-ID': playerId,
                'X-Device-ID': deviceId
            }
        });
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Style pour l'animation de l'hélicoptère
const style = document.createElement('style');
style.textContent = `
@keyframes helicopter-fly {
    from {
        transform: translate(100px, -100px) scale(0.5);
        opacity: 0;
    }
    to {
        transform: translate(-50px, 50px) scale(1.5);
        opacity: 1;
    }
}
.animate-helicopter-fly {
    animation: helicopter-fly 5s ease-in-out;
}
`;
document.head.appendChild(style);

// Timer global (si présent)
@if($team->started_at)
const startTime = new Date('{{ $team->started_at }}');
function updateTimer() {
    const now = new Date();
    const elapsed = Math.floor((now - startTime) / 1000);
    
    const hours = Math.floor(elapsed / 3600);
    const minutes = Math.floor((elapsed % 3600) / 60);
    const seconds = elapsed % 60;
    
    const display = hours > 0 
        ? `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
        : `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    const timerEl = document.getElementById('total-time');
    if (timerEl) timerEl.textContent = display;
}
setInterval(updateTimer, 1000);
updateTimer();
@endif
</script>
@endpush