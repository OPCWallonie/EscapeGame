<!-- resources/views/game/rooms/cave_salon.blade.php -->
@extends('layouts.app')

@section('title', 'Cave du Salon')
@section('header', $room->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Description -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <p class="text-gray-300 text-lg">{{ $room->description }}</p>
        <div class="mt-4 p-3 bg-gray-700 rounded">
            <p class="text-sm text-indigo-300">
                🔦 Naviguez dans l'obscurité pour trouver la sortie vers la cour intérieure
            </p>
        </div>
    </div>

    <!-- Zone de jeu -->
    <div class="bg-gray-900 rounded-lg p-6 mb-6">
        <!-- Labyrinthe -->
        <div class="relative mx-auto" style="max-width: 500px;">
            <!-- Canvas du labyrinthe -->
            <div id="maze-container" class="relative bg-black rounded-lg overflow-hidden" style="height: 500px;">
                <!-- Zone de vision (suit le joueur) -->
                <div id="vision-circle" class="absolute transition-all duration-100" style="width: 120px; height: 120px; transform: translate(-50%, -50%);">
                    <div class="relative w-full h-full">
                        <!-- Gradient pour l'effet de lumière -->
                        <div class="absolute inset-0 rounded-full bg-gradient-radial from-transparent via-black/50 to-black"></div>
                    </div>
                </div>
                
                <!-- Grille du labyrinthe -->
                <div id="maze-grid" class="absolute inset-0">
                    <!-- Les murs seront générés ici -->
                </div>
                
                <!-- Joueur -->
                <div id="player" class="absolute w-6 h-6 bg-indigo-400 rounded-full transition-all duration-100 z-10" style="left: 20px; top: 20px;">
                    <div class="absolute inset-0 rounded-full bg-indigo-400 animate-pulse"></div>
                </div>
                
                <!-- Sortie -->
                <div id="exit" class="absolute w-8 h-8 bg-green-500 rounded transition-all duration-300" style="right: 20px; bottom: 20px;">
                    <div class="absolute inset-0 bg-green-400 rounded animate-pulse"></div>
                </div>
                
                <!-- Overlay d'obscurité -->
                <div id="darkness-overlay" class="absolute inset-0 bg-black pointer-events-none" style="opacity: 0.85;"></div>
            </div>
            
            <!-- Contrôles -->
            <div class="mt-6 grid grid-cols-3 gap-2 max-w-xs mx-auto">
                <div></div>
                <button id="move-up" class="control-button bg-gray-700 hover:bg-gray-600 rounded-lg p-3 transition">
                    <svg class="w-6 h-6 mx-auto text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                </button>
                <div></div>
                
                <button id="move-left" class="control-button bg-gray-700 hover:bg-gray-600 rounded-lg p-3 transition">
                    <svg class="w-6 h-6 mx-auto text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button id="toggle-light" class="bg-indigo-600 hover:bg-indigo-700 rounded-lg p-3 transition">
                    <svg class="w-6 h-6 mx-auto text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </button>
                <button id="move-right" class="control-button bg-gray-700 hover:bg-gray-600 rounded-lg p-3 transition">
                    <svg class="w-6 h-6 mx-auto text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                
                <div></div>
                <button id="move-down" class="control-button bg-gray-700 hover:bg-gray-600 rounded-lg p-3 transition">
                    <svg class="w-6 h-6 mx-auto text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div></div>
            </div>
            
            <!-- Instructions -->
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-400">Utilisez les flèches ou les touches du clavier</p>
                <p class="text-xs text-gray-500 mt-1">Lampe centrale = Lumière temporaire (batterie limitée)</p>
            </div>
        </div>
        
        <!-- Indicateurs -->
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="bg-gray-800 rounded-lg p-3 text-center">
                <p class="text-sm text-gray-400">Pas effectués</p>
                <p id="step-counter" class="text-2xl font-bold text-white">0</p>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 text-center">
                <p class="text-sm text-gray-400">Batterie lampe</p>
                <div class="bg-gray-700 rounded-full h-4 mt-2 overflow-hidden">
                    <div id="battery-bar" class="h-full bg-gradient-to-r from-green-500 to-yellow-500 transition-all duration-300" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message de résultat -->
    <div id="result-message" class="hidden"></div>
</div>

<!-- Style pour le gradient radial -->
<style>
.bg-gradient-radial {
    background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.5) 40%, rgba(0,0,0,1) 70%);
}

#vision-circle {
    pointer-events: none;
    z-index: 20;
}

.wall {
    background-color: #374151;
    position: absolute;
}

#darkness-overlay {
    mix-blend-mode: multiply;
}

.light-on #darkness-overlay {
    opacity: 0.3 !important;
}

.light-on #vision-circle {
    width: 300px !important;
    height: 300px !important;
}
</style>
@endsection

@push('scripts')
<script>
// Configuration du labyrinthe
const MAZE_SIZE = 15;
const CELL_SIZE = 30;
const maze = [];
let playerPos = { x: 0, y: 0 };
let exitPos = { x: MAZE_SIZE - 1, y: MAZE_SIZE - 1 };
let steps = 0;
let battery = 100;
let lightOn = false;
let gameCompleted = false;

// Générer le labyrinthe
function generateMaze() {
    // Initialiser la grille
    for (let y = 0; y < MAZE_SIZE; y++) {
        maze[y] = [];
        for (let x = 0; x < MAZE_SIZE; x++) {
            maze[y][x] = {
                top: true,
                right: true,
                bottom: true,
                left: true,
                visited: false
            };
        }
    }
    
    // Algorithme de génération (Recursive Backtracking simplifié)
    const stack = [];
    let current = { x: 0, y: 0 };
    maze[0][0].visited = true;
    
    function getUnvisitedNeighbors(x, y) {
        const neighbors = [];
        if (y > 0 && !maze[y-1][x].visited) neighbors.push({x, y: y-1, dir: 'top'});
        if (x < MAZE_SIZE-1 && !maze[y][x+1].visited) neighbors.push({x: x+1, y, dir: 'right'});
        if (y < MAZE_SIZE-1 && !maze[y+1][x].visited) neighbors.push({x, y: y+1, dir: 'bottom'});
        if (x > 0 && !maze[y][x-1].visited) neighbors.push({x: x-1, y, dir: 'left'});
        return neighbors;
    }
    
    while (stack.length > 0 || getUnvisitedNeighbors(current.x, current.y).length > 0) {
        const neighbors = getUnvisitedNeighbors(current.x, current.y);
        
        if (neighbors.length > 0) {
            const next = neighbors[Math.floor(Math.random() * neighbors.length)];
            stack.push(current);
            
            // Retirer les murs
            if (next.dir === 'top') {
                maze[current.y][current.x].top = false;
                maze[next.y][next.x].bottom = false;
            } else if (next.dir === 'right') {
                maze[current.y][current.x].right = false;
                maze[next.y][next.x].left = false;
            } else if (next.dir === 'bottom') {
                maze[current.y][current.x].bottom = false;
                maze[next.y][next.x].top = false;
            } else if (next.dir === 'left') {
                maze[current.y][current.x].left = false;
                maze[next.y][next.x].right = false;
            }
            
            maze[next.y][next.x].visited = true;
            current = next;
        } else if (stack.length > 0) {
            current = stack.pop();
        }
    }
}

// Dessiner le labyrinthe
function drawMaze() {
    const container = document.getElementById('maze-grid');
    container.innerHTML = '';
    
    for (let y = 0; y < MAZE_SIZE; y++) {
        for (let x = 0; x < MAZE_SIZE; x++) {
            const cell = maze[y][x];
            
            // Mur du haut
            if (cell.top) {
                const wall = document.createElement('div');
                wall.className = 'wall';
                wall.style.left = (x * CELL_SIZE) + 'px';
                wall.style.top = (y * CELL_SIZE) + 'px';
                wall.style.width = CELL_SIZE + 'px';
                wall.style.height = '2px';
                container.appendChild(wall);
            }
            
            // Mur de droite
            if (cell.right) {
                const wall = document.createElement('div');
                wall.className = 'wall';
                wall.style.left = ((x + 1) * CELL_SIZE - 2) + 'px';
                wall.style.top = (y * CELL_SIZE) + 'px';
                wall.style.width = '2px';
                wall.style.height = CELL_SIZE + 'px';
                container.appendChild(wall);
            }
            
            // Mur du bas (dernière ligne)
            if (y === MAZE_SIZE - 1 && cell.bottom) {
                const wall = document.createElement('div');
                wall.className = 'wall';
                wall.style.left = (x * CELL_SIZE) + 'px';
                wall.style.top = ((y + 1) * CELL_SIZE - 2) + 'px';
                wall.style.width = CELL_SIZE + 'px';
                wall.style.height = '2px';
                container.appendChild(wall);
            }
            
            // Mur de gauche (première colonne)
            if (x === 0 && cell.left) {
                const wall = document.createElement('div');
                wall.className = 'wall';
                wall.style.left = '0px';
                wall.style.top = (y * CELL_SIZE) + 'px';
                wall.style.width = '2px';
                wall.style.height = CELL_SIZE + 'px';
                container.appendChild(wall);
            }
        }
    }
}

// Déplacer le joueur
function movePlayer(dx, dy) {
    if (gameCompleted) return;
    
    const newX = playerPos.x + dx;
    const newY = playerPos.y + dy;
    
    // Vérifier les limites
    if (newX < 0 || newX >= MAZE_SIZE || newY < 0 || newY >= MAZE_SIZE) return;
    
    // Vérifier les murs
    const currentCell = maze[playerPos.y][playerPos.x];
    if (dx === 1 && currentCell.right) return;
    if (dx === -1 && currentCell.left) return;
    if (dy === 1 && currentCell.bottom) return;
    if (dy === -1 && currentCell.top) return;
    
    // Déplacer
    playerPos.x = newX;
    playerPos.y = newY;
    steps++;
    
    updatePlayerPosition();
    updateStepCounter();
    checkWin();
}

// Mettre à jour la position du joueur
function updatePlayerPosition() {
    const player = document.getElementById('player');
    const vision = document.getElementById('vision-circle');
    
    const left = playerPos.x * CELL_SIZE + CELL_SIZE/2 - 3;
    const top = playerPos.y * CELL_SIZE + CELL_SIZE/2 - 3;
    
    player.style.left = left + 'px';
    player.style.top = top + 'px';
    
    vision.style.left = (left + 3) + 'px';
    vision.style.top = (top + 3) + 'px';
}

// Mettre à jour le compteur de pas
function updateStepCounter() {
    document.getElementById('step-counter').textContent = steps;
}

// Gérer la lampe
function toggleLight() {
    if (battery <= 0) return;
    
    lightOn = !lightOn;
    const container = document.getElementById('maze-container');
    
    if (lightOn) {
        container.classList.add('light-on');
        // Consommer la batterie
        const batteryInterval = setInterval(() => {
            if (!lightOn || battery <= 0) {
                clearInterval(batteryInterval);
                if (battery <= 0) {
                    lightOn = false;
                    container.classList.remove('light-on');
                }
                return;
            }
            battery -= 2;
            updateBattery();
        }, 100);
    } else {
        container.classList.remove('light-on');
    }
}

// Mettre à jour la batterie
function updateBattery() {
    document.getElementById('battery-bar').style.width = battery + '%';
    
    // Changer la couleur selon le niveau
    const bar = document.getElementById('battery-bar');
    if (battery > 50) {
        bar.className = 'h-full bg-gradient-to-r from-green-500 to-green-400 transition-all duration-300';
    } else if (battery > 20) {
        bar.className = 'h-full bg-gradient-to-r from-yellow-500 to-yellow-400 transition-all duration-300';
    } else {
        bar.className = 'h-full bg-gradient-to-r from-red-500 to-red-400 transition-all duration-300';
    }
}

// Vérifier la victoire
function checkWin() {
    if (playerPos.x === exitPos.x && playerPos.y === exitPos.y) {
        gameCompleted = true;
        showResult(true);
    }
}

// Afficher le résultat
async function showResult(success) {
    const resultDiv = document.getElementById('result-message');
    resultDiv.classList.remove('hidden');
    
    if (success) {
        resultDiv.className = 'bg-green-900 bg-opacity-50 border border-green-600 rounded-lg p-6 text-center';
        resultDiv.innerHTML = `
            <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-semibold text-green-300">Bravo !</p>
            <p class="text-green-400 mt-2">Vous avez trouvé la sortie en ${steps} pas !</p>
            <p class="text-sm text-gray-400 mt-2">La porte vers la cour intérieure s'ouvre...</p>
        `;
        
        // Envoyer au serveur
        await completeChallenge();
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
                    steps: steps,
                    battery_remaining: battery
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

// Contrôles
document.getElementById('move-up').addEventListener('click', () => movePlayer(0, -1));
document.getElementById('move-down').addEventListener('click', () => movePlayer(0, 1));
document.getElementById('move-left').addEventListener('click', () => movePlayer(-1, 0));
document.getElementById('move-right').addEventListener('click', () => movePlayer(1, 0));
document.getElementById('toggle-light').addEventListener('click', toggleLight);

// Contrôles clavier
document.addEventListener('keydown', (e) => {
    if (gameCompleted) return;
    
    switch(e.key) {
        case 'ArrowUp':
        case 'w':
        case 'W':
            e.preventDefault();
            movePlayer(0, -1);
            break;
        case 'ArrowDown':
        case 's':
        case 'S':
            e.preventDefault();
            movePlayer(0, 1);
            break;
        case 'ArrowLeft':
        case 'a':
        case 'A':
            e.preventDefault();
            movePlayer(-1, 0);
            break;
        case 'ArrowRight':
        case 'd':
        case 'D':
            e.preventDefault();
            movePlayer(1, 0);
            break;
        case ' ':
            e.preventDefault();
            toggleLight();
            break;
    }
});

// Vibration sur mobile
document.querySelectorAll('.control-button').forEach(button => {
    button.addEventListener('click', () => {
        if (navigator.vibrate) navigator.vibrate(20);
    });
});

// Initialiser
generateMaze();
drawMaze();
updatePlayerPosition();

// Positionner la sortie
const exit = document.getElementById('exit');
exit.style.right = '20px';
exit.style.bottom = '20px';
</script>
@endpush