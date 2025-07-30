<!-- resources/views/game/rooms/salon_coiffure.blade.php -->
@extends('layouts.app')

@section('title', 'Salon de Coiffure')
@section('header', $room->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Description -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <p class="text-gray-300 text-lg">{{ $room->description }}</p>
        <div class="mt-4 p-3 bg-gray-700 rounded">
            <p class="text-sm text-indigo-300">
                💇 Coiffez le client selon ses instructions mystérieuses pour révéler le premier chiffre du code
            </p>
        </div>
    </div>

    <!-- Zone de jeu -->
    <div class="bg-gray-900 rounded-lg p-6 mb-6">
        <!-- Client virtuel -->
        <div class="text-center mb-6">
            <div class="relative inline-block">
                <!-- Tête du client -->
                <div class="w-48 h-48 mx-auto bg-gray-700 rounded-full relative overflow-hidden">
                    <!-- Visage -->
                    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
                        <div class="w-32 h-32 bg-pink-200 rounded-full"></div>
                        <!-- Yeux -->
                        <div class="absolute top-8 left-6 w-3 h-3 bg-gray-800 rounded-full"></div>
                        <div class="absolute top-8 right-6 w-3 h-3 bg-gray-800 rounded-full"></div>
                        <!-- Bouche -->
                        <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 w-8 h-1 bg-gray-800 rounded-full"></div>
                    </div>
                    
                    <!-- Cheveux (dynamiques) -->
                    <div id="hair-container" class="absolute inset-0">
                        <div id="hair-back" class="absolute inset-0 transition-all duration-500"></div>
                        <div id="hair-front" class="absolute inset-0 transition-all duration-500"></div>
                    </div>
                </div>
                
                <!-- État de satisfaction -->
                <div id="satisfaction-meter" class="mt-4 bg-gray-700 rounded-full h-4 w-48 overflow-hidden">
                    <div id="satisfaction-bar" class="h-full bg-gradient-to-r from-red-500 to-green-500 transition-all duration-500" style="width: 0%"></div>
                </div>
                <p class="text-sm text-gray-400 mt-1">Satisfaction du client</p>
            </div>
        </div>

        <!-- Instructions cryptées -->
        <div class="bg-gray-800 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-400 mb-2">Instructions du client :</h3>
            <p id="client-instructions" class="text-indigo-300 italic text-center">
                "Je veux quelque chose de court mais pas trop, 
                avec du mouvement vers la droite, 
                et surtout pas de rouge !"
            </p>
        </div>

        <!-- Outils de coiffure -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <!-- Peigne -->
            <button class="tool-button" data-tool="comb" data-action="style">
                <div class="bg-gray-800 hover:bg-gray-700 rounded-lg p-4 text-center transition-all duration-200 transform hover:scale-105">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 4v16h2V4H3zm4 0v16h2V4H7zm4 0v16h2V4h-2zm4 0v16h2V4h-2zm4 0v16h2V4h-2z"/>
                    </svg>
                    <p class="text-sm">Peigne</p>
                </div>
            </button>

            <!-- Ciseaux -->
            <button class="tool-button" data-tool="scissors" data-action="cut">
                <div class="bg-gray-800 hover:bg-gray-700 rounded-lg p-4 text-center transition-all duration-200 transform hover:scale-105">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>
                    </svg>
                    <p class="text-sm">Ciseaux</p>
                </div>
            </button>

            <!-- Sèche-cheveux -->
            <button class="tool-button" data-tool="dryer" data-action="dry">
                <div class="bg-gray-800 hover:bg-gray-700 rounded-lg p-4 text-center transition-all duration-200 transform hover:scale-105">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22 9a4.32 4.32 0 0 1-2.22-.55A3.4 3.4 0 0 0 18 8V7a4.32 4.32 0 0 1 2.22.55A3.4 3.4 0 0 0 22 8m0-2a3.4 3.4 0 0 1-1.78-.45A4.32 4.32 0 0 0 18 5v1a3.4 3.4 0 0 1 1.78.45A4.32 4.32 0 0 0 22 7m0 3v1a3.4 3.4 0 0 1-1.78-.45A4.32 4.32 0 0 0 18 10v1a3.4 3.4 0 0 1 1.78.45A4.32 4.32 0 0 0 22 12m-4 1.73A2 2 0 0 0 16 11h-4.2a1 1 0 0 0-.98.8L10 16.33V22h2v-4h1v4h2v-9.27z"/>
                    </svg>
                    <p class="text-sm">Sèche-cheveux</p>
                </div>
            </button>
        </div>

        <!-- Options de style -->
        <div class="space-y-4">
            <!-- Direction -->
            <div>
                <p class="text-sm text-gray-400 mb-2">Direction du coiffage :</p>
                <div class="grid grid-cols-3 gap-2">
                    <button class="style-option" data-style="direction" data-value="left">
                        <div class="bg-gray-700 hover:bg-gray-600 rounded px-3 py-2 text-sm transition">← Gauche</div>
                    </button>
                    <button class="style-option" data-style="direction" data-value="center">
                        <div class="bg-gray-700 hover:bg-gray-600 rounded px-3 py-2 text-sm transition">↑ Centre</div>
                    </button>
                    <button class="style-option" data-style="direction" data-value="right">
                        <div class="bg-gray-700 hover:bg-gray-600 rounded px-3 py-2 text-sm transition">→ Droite</div>
                    </button>
                </div>
            </div>

            <!-- Longueur -->
            <div>
                <p class="text-sm text-gray-400 mb-2">Longueur :</p>
                <div class="grid grid-cols-3 gap-2">
                    <button class="style-option" data-style="length" data-value="short">
                        <div class="bg-gray-700 hover:bg-gray-600 rounded px-3 py-2 text-sm transition">Court</div>
                    </button>
                    <button class="style-option" data-style="length" data-value="medium">
                        <div class="bg-gray-700 hover:bg-gray-600 rounded px-3 py-2 text-sm transition">Moyen</div>
                    </button>
                    <button class="style-option" data-style="length" data-value="long">
                        <div class="bg-gray-700 hover:bg-gray-600 rounded px-3 py-2 text-sm transition">Long</div>
                    </button>
                </div>
            </div>

            <!-- Couleur -->
            <div>
                <p class="text-sm text-gray-400 mb-2">Couleur :</p>
                <div class="grid grid-cols-4 gap-2">
                    <button class="style-option" data-style="color" data-value="black">
                        <div class="bg-gray-900 hover:opacity-80 rounded h-10 transition"></div>
                    </button>
                    <button class="style-option" data-style="color" data-value="brown">
                        <div class="bg-yellow-900 hover:opacity-80 rounded h-10 transition"></div>
                    </button>
                    <button class="style-option" data-style="color" data-value="blonde">
                        <div class="bg-yellow-600 hover:opacity-80 rounded h-10 transition"></div>
                    </button>
                    <button class="style-option" data-style="color" data-value="red">
                        <div class="bg-red-600 hover:opacity-80 rounded h-10 transition"></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex space-x-3">
            <button id="reset-hair" class="flex-1 bg-gray-600 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                Recommencer
            </button>
            <button id="validate-style" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                Valider la coupe
            </button>
        </div>
    </div>

    <!-- Résultat -->
    <div id="result-container" class="hidden"></div>
</div>
@endsection

@push('scripts')
<script>
// État du style
let currentStyle = {
    tool: null,
    direction: null,
    length: null,
    color: 'brown',
    sequence: []
};

// Solution correcte : Peigne → Ciseaux (moyen) → Droite → Brun → Sèche-cheveux
const correctSequence = ['comb', 'scissors-medium', 'direction-right', 'color-brown', 'dryer'];
let playerSequence = [];

// Sélection des outils
document.querySelectorAll('.tool-button').forEach(button => {
    button.addEventListener('click', () => {
        const tool = button.dataset.tool;
        currentStyle.tool = tool;
        playerSequence.push(tool);
        
        // Feedback visuel
        document.querySelectorAll('.tool-button div').forEach(div => {
            div.classList.remove('ring-4', 'ring-indigo-500', 'bg-indigo-900');
        });
        button.querySelector('div').classList.add('ring-4', 'ring-indigo-500', 'bg-indigo-900');
        
        // Activer les options selon l'outil
        if (tool === 'scissors') {
            enableOptions('length');
        } else if (tool === 'comb') {
            enableOptions('direction');
        } else if (tool === 'dryer') {
            applyHairStyle();
        }
        
        // Vibration
        if (navigator.vibrate) navigator.vibrate(50);
    });
});

// Options de style
document.querySelectorAll('.style-option').forEach(button => {
    button.addEventListener('click', () => {
        const style = button.dataset.style;
        const value = button.dataset.value;
        
        currentStyle[style] = value;
        
        // Si c'est une option de ciseaux
        if (style === 'length' && currentStyle.tool === 'scissors') {
            playerSequence.push(`scissors-${value}`);
        } else {
            playerSequence.push(`${style}-${value}`);
        }
        
        // Feedback visuel
        document.querySelectorAll(`.style-option[data-style="${style}"] div`).forEach(div => {
            div.classList.remove('ring-2', 'ring-indigo-500');
        });
        button.querySelector('div').classList.add('ring-2', 'ring-indigo-500');
        
        // Appliquer le style visuellement
        applyVisualStyle(style, value);
    });
});

// Appliquer le style visuellement
function applyVisualStyle(style, value) {
    const hairBack = document.getElementById('hair-back');
    const hairFront = document.getElementById('hair-front');
    
    if (style === 'direction') {
        const transforms = {
            'left': 'skewX(-15deg)',
            'center': 'skewX(0deg)',
            'right': 'skewX(15deg)'
        };
        hairFront.style.transform = transforms[value];
    } else if (style === 'length') {
        const heights = {
            'short': '60%',
            'medium': '80%',
            'long': '100%'
        };
        hairBack.style.height = heights[value];
        hairFront.style.height = heights[value];
    } else if (style === 'color') {
        const colors = {
            'black': '#1f2937',
            'brown': '#92400e',
            'blonde': '#fbbf24',
            'red': '#dc2626'
        };
        hairBack.style.backgroundColor = colors[value];
        hairFront.style.backgroundColor = colors[value];
    }
}

// Appliquer le style final
function applyHairStyle() {
    // Animation de séchage
    const hairContainer = document.getElementById('hair-container');
    hairContainer.classList.add('animate-pulse');
    
    setTimeout(() => {
        hairContainer.classList.remove('animate-pulse');
        updateSatisfaction();
    }, 1000);
}

// Activer les options
function enableOptions(type) {
    document.querySelectorAll('.style-option').forEach(btn => {
        btn.disabled = btn.dataset.style !== type;
        btn.querySelector('div').classList.toggle('opacity-50', btn.dataset.style !== type);
    });
}

// Mettre à jour la satisfaction
function updateSatisfaction() {
    // Comparer avec la séquence correcte
    let matches = 0;
    for (let i = 0; i < Math.min(playerSequence.length, correctSequence.length); i++) {
        if (playerSequence[i] === correctSequence[i]) {
            matches++;
        }
    }
    
    const satisfaction = (matches / correctSequence.length) * 100;
    document.getElementById('satisfaction-bar').style.width = satisfaction + '%';
    
    return satisfaction;
}

// Réinitialiser
document.getElementById('reset-hair').addEventListener('click', () => {
    currentStyle = {
        tool: null,
        direction: null,
        length: null,
        color: 'brown',
        sequence: []
    };
    playerSequence = [];
    
    // Réinitialiser visuellement
    document.getElementById('hair-back').style = '';
    document.getElementById('hair-front').style = '';
    document.getElementById('satisfaction-bar').style.width = '0%';
    
    // Réinitialiser les sélections
    document.querySelectorAll('.ring-4, .ring-2').forEach(el => {
        el.classList.remove('ring-4', 'ring-2', 'ring-indigo-500', 'bg-indigo-900');
    });
    
    document.querySelectorAll('.style-option').forEach(btn => {
        btn.disabled = false;
        btn.querySelector('div').classList.remove('opacity-50');
    });
});

// Valider la coupe
document.getElementById('validate-style').addEventListener('click', async () => {
    const satisfaction = updateSatisfaction();
    const isCorrect = satisfaction === 100;
    
    // Afficher le résultat
    const resultContainer = document.getElementById('result-container');
    resultContainer.classList.remove('hidden');
    
    if (isCorrect) {
        resultContainer.className = 'bg-green-900 bg-opacity-50 border border-green-600 rounded-lg p-6 text-center';
        resultContainer.innerHTML = `
            <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-semibold text-green-300 mb-2">Parfait !</p>
            <p class="text-green-400">Le client est ravi ! Il vous révèle le premier chiffre du code :</p>
            <p class="text-6xl font-bold text-green-300 mt-4">{{ $room->digit_reward }}</p>
        `;
        
        // Envoyer au serveur
        await completeChallenge(true);
    } else {
        resultContainer.className = 'bg-red-900 bg-opacity-50 border border-red-600 rounded-lg p-6 text-center';
        resultContainer.innerHTML = `
            <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <p class="text-lg font-semibold text-red-300 mb-2">Le client n'est pas satisfait...</p>
            <p class="text-red-400">Satisfaction : ${Math.round(satisfaction)}%</p>
            <p class="text-sm text-yellow-400 mt-2">Indice : Suivez exactement les instructions dans l'ordre</p>
        `;
        
        setTimeout(() => {
            resultContainer.classList.add('hidden');
        }, 4000);
    }
});

// Envoyer le résultat au serveur
async function completeChallenge(success) {
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
                success: success,
                data: {
                    sequence: playerSequence,
                    style: currentStyle
                }
            })
        });
        
        if (success) {
            setTimeout(() => {
                window.location.href = '/game';
            }, 3000);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Initialiser avec des cheveux de base
window.addEventListener('load', () => {
    const hairBack = document.getElementById('hair-back');
    const hairFront = document.getElementById('hair-front');
    
    hairBack.style.backgroundColor = '#92400e';
    hairBack.style.height = '100%';
    hairBack.style.borderRadius = '50%';
    
    hairFront.style.backgroundColor = '#92400e';
    hairFront.style.height = '100%';
    hairFront.style.borderRadius = '50%';
});
</script>
@endpush