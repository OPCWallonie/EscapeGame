<!-- resources/views/game/rooms/vieux_bar.blade.php -->
@extends('layouts.app')

@section('title', 'Le Vieux Bar')
@section('header', $room->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <!-- Description -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <p class="text-gray-300 text-lg">{{ $room->description }}</p>
        <div class="mt-4 p-3 bg-gray-700 rounded">
            <p class="text-sm text-indigo-300">
                🍸 Explorez la cuisine pour trouver les indices, puis préparez le cocktail mystère
            </p>
        </div>
    </div>

    <!-- Zone principale divisée -->
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Cuisine (gauche) -->
        <div class="bg-gray-900 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-300 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Cuisine
            </h3>
            
            <!-- Placards à explorer -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                @php
                    $cupboards = [
                        ['name' => 'Placard du haut', 'icon' => '🚪', 'id' => 'cupboard-1'],
                        ['name' => 'Tiroir à couverts', 'icon' => '🍴', 'id' => 'drawer-1'],
                        ['name' => 'Réfrigérateur', 'icon' => '❄️', 'id' => 'fridge'],
                        ['name' => 'Étagère à épices', 'icon' => '🌶️', 'id' => 'spice-rack'],
                        ['name' => 'Placard du bas', 'icon' => '📦', 'id' => 'cupboard-2'],
                        ['name' => 'Tiroir secret', 'icon' => '🔐', 'id' => 'secret-drawer']
                    ];
                @endphp
                
                @foreach($cupboards as $cupboard)
                <button class="cupboard-btn" data-cupboard="{{ $cupboard['id'] }}">
                    <div class="bg-gray-800 hover:bg-gray-700 rounded-lg p-4 text-center transition-all duration-200 transform hover:scale-105">
                        <div class="text-3xl mb-2">{{ $cupboard['icon'] }}</div>
                        <p class="text-sm">{{ $cupboard['name'] }}</p>
                    </div>
                </button>
                @endforeach
            </div>
            
            <!-- Zone de découverte -->
            <div id="discovery-zone" class="bg-gray-800 rounded-lg p-4 min-h-[200px]">
                <p class="text-gray-500 text-center">Cliquez sur un élément pour l'explorer</p>
            </div>
            
            <!-- Indices trouvés -->
            <div class="mt-4">
                <h4 class="text-sm font-semibold text-gray-400 mb-2">Indices trouvés :</h4>
                <div id="clues-list" class="space-y-2">
                    <!-- Les indices apparaîtront ici -->
                </div>
            </div>
        </div>

        <!-- Bar (droite) -->
        <div class="bg-gray-900 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-300 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
                Bar
            </h3>
            
            <!-- Shaker virtuel -->
            <div class="text-center mb-6">
                <div id="shaker" class="relative inline-block">
                    <div class="w-32 h-48 bg-gradient-to-t from-gray-700 to-gray-600 rounded-t-lg rounded-b-3xl mx-auto relative overflow-hidden">
                        <!-- Liquide dans le shaker -->
                        <div id="shaker-liquid" class="absolute bottom-0 left-0 right-0 transition-all duration-500" style="height: 0%">
                            <div class="h-full bg-gradient-to-t from-indigo-600 to-indigo-400 opacity-80"></div>
                        </div>
                        <!-- Reflet -->
                        <div class="absolute top-4 left-4 w-8 h-16 bg-white opacity-20 rounded-full transform -rotate-12"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">Shaker</div>
                </div>
            </div>
            
            <!-- Ingrédients disponibles -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-400 mb-3">Ingrédients disponibles :</h4>
                <div class="grid grid-cols-3 gap-3">
                    @php
                        $ingredients = [
                            ['name' => 'Vodka', 'color' => 'bg-gray-300'],
                            ['name' => 'Gin', 'color' => 'bg-gray-200'],
                            ['name' => 'Rhum', 'color' => 'bg-yellow-700'],
                            ['name' => 'Citron', 'color' => 'bg-yellow-400'],
                            ['name' => 'Menthe', 'color' => 'bg-green-500'],
                            ['name' => 'Olive', 'color' => 'bg-green-700'],
                            ['name' => 'Cerise', 'color' => 'bg-red-600'],
                            ['name' => 'Orange', 'color' => 'bg-orange-500'],
                            ['name' => 'Sucre', 'color' => 'bg-white']
                        ];
                    @endphp
                    
                    @foreach($ingredients as $ingredient)
                    <button class="ingredient-btn" data-ingredient="{{ $ingredient['name'] }}">
                        <div class="bg-gray-800 hover:bg-gray-700 rounded-lg p-3 text-center transition-all duration-200">
                            <div class="w-8 h-8 {{ $ingredient['color'] }} rounded-full mx-auto mb-1"></div>
                            <p class="text-xs">{{ $ingredient['name'] }}</p>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
            
            <!-- Recette en cours -->
            <div class="bg-gray-800 rounded-lg p-4 mb-4">
                <h4 class="text-sm font-semibold text-gray-400 mb-2">Recette en cours :</h4>
                <div id="current-recipe" class="flex flex-wrap gap-2">
                    <!-- Les ingrédients sélectionnés apparaîtront ici -->
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button id="reset-cocktail" class="flex-1 bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Vider
                </button>
                <button id="shake-cocktail" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Mélanger !
                </button>
            </div>
        </div>
    </div>

    <!-- Message d'indice trouvé dans la cuisine -->
    <div id="recipe-clue" class="hidden bg-gray-800 rounded-lg p-6 mt-6">
        <h3 class="text-lg font-semibold text-yellow-400 mb-3">📜 Message du barman de 1933</h3>
        <p class="text-gray-300 italic">
            "Le barman de 1933 préparait toujours son cocktail signature :<br>
            - L'alcool des tsars<br>
            - Le fruit du marin<br>
            - L'herbe du thé marocain"
        </p>
    </div>

    <!-- Résultat -->
    <div id="result-message" class="hidden mt-6"></div>
</div>
@endsection

@push('scripts')
<script>
// État du jeu
let foundClues = [];
let currentRecipe = [];
let recipeFound = false;

// Indices cachés dans les placards
const cupboardContents = {
    'cupboard-1': { item: 'Bouteilles poussiéreuses', clue: null },
    'drawer-1': { item: 'Vieux tire-bouchon', clue: null },
    'fridge': { item: 'Citrons frais', clue: 'Des citrons bien conservés' },
    'spice-rack': { item: 'Feuilles de menthe séchées', clue: 'De la menthe ancienne mais encore parfumée' },
    'cupboard-2': { item: 'Verres à cocktail', clue: null },
    'secret-drawer': { item: 'Carnet de recettes', clue: 'recipe' }
};

// Solution : Vodka + Citron + Menthe
const correctRecipe = ['Vodka', 'Citron', 'Menthe'];

// Explorer les placards
document.querySelectorAll('.cupboard-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const cupboardId = btn.dataset.cupboard;
        exploreCupboard(cupboardId);
        
        // Feedback visuel
        btn.querySelector('div').classList.add('ring-2', 'ring-indigo-500');
        
        // Vibration
        if (navigator.vibrate) navigator.vibrate(50);
    });
});

// Explorer un placard
function exploreCupboard(cupboardId) {
    const content = cupboardContents[cupboardId];
    const discoveryZone = document.getElementById('discovery-zone');
    
    if (content.clue === 'recipe') {
        // Recette trouvée !
        discoveryZone.innerHTML = `
            <div class="text-center">
                <p class="text-yellow-400 font-semibold mb-2">🎉 Carnet de recettes trouvé !</p>
                <p class="text-gray-300">${content.item}</p>
                <button onclick="revealRecipe()" class="mt-3 bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded-lg text-white">
                    Lire la recette
                </button>
            </div>
        `;
        
        if (!foundClues.includes('recipe')) {
            foundClues.push('recipe');
            addClueToList('📜 Carnet de recettes du barman');
        }
    } else {
        discoveryZone.innerHTML = `
            <div class="text-center">
                <p class="text-gray-300 mb-2">Vous trouvez :</p>
                <p class="text-white font-semibold">${content.item}</p>
                ${content.clue ? `<p class="text-sm text-indigo-300 mt-2">${content.clue}</p>` : ''}
            </div>
        `;
        
        if (content.clue && !foundClues.includes(cupboardId)) {
            foundClues.push(cupboardId);
            addClueToList(content.clue);
        }
    }
}

// Révéler la recette
function revealRecipe() {
    document.getElementById('recipe-clue').classList.remove('hidden');
    recipeFound = true;
    
    // Animation de scroll
    document.getElementById('recipe-clue').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Ajouter un indice à la liste
function addClueToList(clue) {
    const cluesList = document.getElementById('clues-list');
    const clueDiv = document.createElement('div');
    clueDiv.className = 'bg-gray-700 rounded px-3 py-2 text-sm text-gray-300';
    clueDiv.textContent = clue;
    cluesList.appendChild(clueDiv);
}

// Gérer les ingrédients
document.querySelectorAll('.ingredient-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const ingredient = btn.dataset.ingredient;
        
        if (currentRecipe.length < 3 && !currentRecipe.includes(ingredient)) {
            addIngredient(ingredient);
            btn.querySelector('div').classList.add('ring-2', 'ring-indigo-500', 'opacity-50');
            btn.disabled = true;
        }
    });
});

// Ajouter un ingrédient
function addIngredient(ingredient) {
    currentRecipe.push(ingredient);
    updateRecipeDisplay();
    updateShakerLevel();
}

// Mettre à jour l'affichage de la recette
function updateRecipeDisplay() {
    const recipeDiv = document.getElementById('current-recipe');
    recipeDiv.innerHTML = currentRecipe.map((ing, index) => `
        <span class="bg-indigo-600 px-3 py-1 rounded-full text-sm">
            ${index + 1}. ${ing}
        </span>
    `).join('');
}

// Mettre à jour le niveau du shaker
function updateShakerLevel() {
    const liquid = document.getElementById('shaker-liquid');
    const level = (currentRecipe.length / 3) * 100;
    liquid.style.height = level + '%';
}

// Réinitialiser le cocktail
document.getElementById('reset-cocktail').addEventListener('click', () => {
    currentRecipe = [];
    updateRecipeDisplay();
    updateShakerLevel();
    
    // Réactiver tous les boutons
    document.querySelectorAll('.ingredient-btn').forEach(btn => {
        btn.disabled = false;
        btn.querySelector('div').classList.remove('ring-2', 'ring-indigo-500', 'opacity-50');
    });
});

// Mélanger le cocktail
document.getElementById('shake-cocktail').addEventListener('click', async () => {
    if (currentRecipe.length !== 3) {
        showResult(false, 'Il faut exactement 3 ingrédients !');
        return;
    }
    
    // Animation de shake
    const shaker = document.getElementById('shaker');
    shaker.classList.add('animate-bounce');
    
    // Vibration
    if (navigator.vibrate) {
        navigator.vibrate([100, 50, 100, 50, 100]);
    }
    
    setTimeout(() => {
        shaker.classList.remove('animate-bounce');
        
        // Vérifier la recette
        const isCorrect = correctRecipe.every(ing => currentRecipe.includes(ing)) && 
                         currentRecipe.length === correctRecipe.length;
        
        if (isCorrect) {
            showResult(true, 'Parfait ! Le barman fantôme approuve !');
        } else {
            if (!recipeFound) {
                showResult(false, 'Ce n\'est pas le bon cocktail... Avez-vous trouvé la recette ?');
            } else {
                showResult(false, 'Relisez bien les indices... L\'alcool des tsars, c\'est quoi ?');
            }
        }
    }, 1000);
});

// Afficher le résultat
async function showResult(success, message) {
    const resultDiv = document.getElementById('result-message');
    resultDiv.classList.remove('hidden');
    
    if (success) {
        resultDiv.className = 'bg-green-900 bg-opacity-50 border border-green-600 rounded-lg p-6 text-center mt-6';
        resultDiv.innerHTML = `
            <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-semibold text-green-300">${message}</p>
            <p class="text-green-400 mt-2">Le fantôme du barman vous sourit et la porte s'ouvre...</p>
        `;
        
        // Envoyer au serveur
        await completeChallenge();
    } else {
        resultDiv.className = 'bg-red-900 bg-opacity-50 border border-red-600 rounded-lg p-6 text-center mt-6';
        resultDiv.innerHTML = `
            <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <p class="text-lg font-semibold text-red-300">${message}</p>
        `;
        
        setTimeout(() => {
            resultDiv.classList.add('hidden');
        }, 3000);
    }
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
                    recipe: currentRecipe,
                    clues_found: foundClues
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
</script>
@endpush