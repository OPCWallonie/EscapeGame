<!-- resources/views/game/rooms/aile_nord.blade.php -->
@extends('layouts.app')

@section('title', 'Aile Nord')
@section('header', $room->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Description -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <p class="text-gray-300 text-lg">{{ $room->description }}</p>
        <div class="mt-4 p-3 bg-gray-700 rounded">
            <p class="text-sm text-indigo-300">
                🔐 Entrez le code à 4 chiffres dans la boîte physique pour accéder au toit
            </p>
        </div>
    </div>

    <!-- Récapitulatif des chiffres trouvés -->
    <div class="bg-gray-900 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Chiffres collectés durant l'aventure</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $digits = [
                    1 => ['room' => 'Salon de coiffure', 'found' => false],
                    2 => ['room' => 'Les 11 caves', 'found' => false],
                    3 => ['room' => 'Centre de contrôle', 'found' => false],
                    4 => ['room' => 'Bureaux sous la pluie', 'found' => false]
                ];
                
                // Récupérer les chiffres trouvés par l'équipe
                $foundDigits = $team->progressions()
                    ->join('rooms', 'progressions.room_id', '=', 'rooms.id')
                    ->where('progressions.digit_found', true)
                    ->whereNotNull('rooms.digit_reward')
                    ->pluck('rooms.digit_reward')
                    ->toArray();
                
                foreach ($foundDigits as $digit) {
                    if (isset($digits[$digit])) {
                        $digits[$digit]['found'] = true;
                    }
                }
            @endphp
            
            @foreach($digits as $position => $info)
            <div class="bg-gray-800 rounded-lg p-4 text-center {{ $info['found'] ? 'border-2 border-green-500' : 'border-2 border-gray-700' }}">
                <p class="text-sm text-gray-400 mb-2">Position {{ $position }}</p>
                <div class="text-4xl font-bold mb-2 {{ $info['found'] ? 'text-green-400' : 'text-gray-600' }}">
                    {{ $info['found'] ? $position : '?' }}
                </div>
                <p class="text-xs text-gray-500">{{ $info['room'] }}</p>
                @if($info['found'])
                <svg class="w-5 h-5 text-green-400 mx-auto mt-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                @endif
            </div>
            @endforeach
        </div>
        
        @if(count($foundDigits) === 4)
        <div class="mt-6 bg-green-900 bg-opacity-50 border border-green-600 rounded-lg p-4 text-center">
            <p class="text-green-300 font-semibold">✅ Tous les chiffres ont été trouvés !</p>
            <p class="text-green-400 text-sm mt-1">Le code complet peut maintenant être entré dans la boîte</p>
        </div>
        @else
        <div class="mt-6 bg-yellow-900 bg-opacity-50 border border-yellow-600 rounded-lg p-4 text-center">
            <p class="text-yellow-300 font-semibold">⚠️ Il manque {{ 4 - count($foundDigits) }} chiffre(s)</p>
            <p class="text-yellow-400 text-sm mt-1">Retournez explorer les salles manquantes</p>
        </div>
        @endif
    </div>

    <!-- Instructions pour la boîte physique -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-indigo-400 mb-4">Instructions</h3>
        
        <ol class="space-y-3 text-gray-300">
            <li class="flex items-start">
                <span class="text-indigo-400 font-bold mr-3">1.</span>
                <span>Localisez la boîte à clé physique à côté de la porte</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 font-bold mr-3">2.</span>
                <span>Entrez le code à 4 chiffres dans l'ordre (Position 1, 2, 3, 4)</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 font-bold mr-3">3.</span>
                <span>Tournez la molette ou appuyez sur le bouton de validation</span>
            </li>
            <li class="flex items-start">
                <span class="text-indigo-400 font-bold mr-3">4.</span>
                <span>Récupérez la clé et ouvrez la porte vers les escaliers du toit</span>
            </li>
        </ol>
    </div>

    <!-- Validation virtuelle (pour confirmer l'ouverture) -->
    <div class="bg-gray-900 rounded-lg p-6 text-center">
        <p class="text-gray-300 mb-4">Une fois la boîte ouverte et la clé récupérée :</p>
        
        <button id="confirm-opened" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
            </svg>
            J'ai ouvert la boîte !
        </button>
        
        <p class="text-xs text-gray-500 mt-3">
            Cliquez uniquement après avoir physiquement ouvert la boîte
        </p>
    </div>

    <!-- Timer final -->
    <div class="mt-6 bg-gray-800 rounded-lg p-6 text-center">
        <h3 class="text-lg font-semibold text-gray-300 mb-2">Temps écoulé</h3>
        <p id="total-time" class="text-3xl font-mono text-indigo-400">--:--</p>
        <p class="text-sm text-gray-500 mt-1">depuis le début de l'aventure</p>
    </div>

    <!-- Message de confirmation -->
    <div id="success-message" class="hidden"></div>
</div>
@endsection

@push('scripts')
<script>
// Timer global
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
    
    document.getElementById('total-time').textContent = display;
}

// Mettre à jour le timer toutes les secondes
setInterval(updateTimer, 1000);
updateTimer();

// Confirmation d'ouverture
document.getElementById('confirm-opened').addEventListener('click', async () => {
    if (!confirm('Confirmez-vous avoir ouvert la boîte physique avec le bon code ?')) {
        return;
    }
    
    const button = document.getElementById('confirm-opened');
    button.disabled = true;
    button.textContent = 'Validation en cours...';
    
    // Animation de succès
    showSuccessAnimation();
    
    // Envoyer au serveur
    await completeChallenge();
});

// Animation de succès
function showSuccessAnimation() {
    const successDiv = document.getElementById('success-message');
    successDiv.classList.remove('hidden');
    successDiv.className = 'bg-green-900 bg-opacity-50 border border-green-600 rounded-lg p-8 text-center mt-6';
    successDiv.innerHTML = `
        <svg class="w-20 h-20 text-green-400 mx-auto mb-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h2 class="text-2xl font-bold text-green-300 mb-2">Félicitations !</h2>
        <p class="text-green-400 text-lg mb-4">La boîte est ouverte !</p>
        <p class="text-gray-300">Prenez les escaliers de secours et montez jusqu'au toit.</p>
        <p class="text-gray-400 text-sm mt-2">Un QR code vous attend sur la porte d'accès au toit.</p>
        
        <div class="mt-6 flex items-center justify-center space-x-2 text-yellow-400">
            <svg class="w-5 h-5 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
            </svg>
            <span class="font-semibold">Direction : Le Toit !</span>
        </div>
    `;
    
    // Confettis virtuels
    createConfetti();
}

// Créer des confettis
function createConfetti() {
    const colors = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444'];
    const confettiCount = 50;
    
    for (let i = 0; i < confettiCount; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'fixed pointer-events-none z-50';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.top = '-10px';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
            confetti.style.transition = 'all 3s ease-out';
            
            document.body.appendChild(confetti);
            
            // Animation
            setTimeout(() => {
                confetti.style.top = '100vh';
                confetti.style.transform = `rotate(${Math.random() * 720}deg)`;
                confetti.style.opacity = '0';
            }, 10);
            
            // Nettoyer
            setTimeout(() => confetti.remove(), 3000);
        }, i * 50);
    }
}

// Envoyer le résultat au serveur
async function completeChallenge() {
    const playerId = localStorage.getItem('player_id');
    const deviceId = localStorage.getItem('device_id');
    const totalTime = Math.floor((new Date() - startTime) / 1000);
    
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
                    total_time: totalTime,
                    digits_found: {{ json_encode($foundDigits) }}
                }
            })
        });
        
        // Redirection après un délai
        setTimeout(() => {
            window.location.href = '/game';
        }, 5000);
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Easter egg : Konami code
let konamiCode = [];
const konamiSequence = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];

document.addEventListener('keydown', (e) => {
    konamiCode.push(e.key);
    konamiCode = konamiCode.slice(-10);
    
    if (konamiCode.join(',') === konamiSequence.join(',')) {
        // Easter egg activé !
        document.body.style.transform = 'rotate(180deg)';
        setTimeout(() => {
            document.body.style.transform = '';
        }, 2000);
    }
});
</script>
@endpush