<!-- resources/views/admin/test-mode.blade.php -->
@extends('layouts.app')

@section('title', 'Mode Test Admin')
@section('header', '🧪 Mode Test')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Statut du test -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">Mode Test Administrateur</h2>
                <p class="text-gray-400 mt-1">Testez le parcours sans avoir à scanner les QR codes</p>
            </div>
            
            <div class="text-right">
                @if($testTeam)
                <p class="text-sm text-gray-400">Équipe active :</p>
                <p class="text-lg font-semibold text-indigo-400">{{ $testTeam->name }}</p>
                <p class="text-xs text-gray-500">Code : {{ $testTeam->code }}</p>
                @else
                <button id="create-test-team" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                    Créer équipe de test
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Vérifications système -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">État du système</h3>
        
        <div id="system-checks" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-700 rounded-lg p-4 text-center">
                <div class="check-status" data-check="database">
                    <div class="spinner w-8 h-8 mx-auto mb-2"></div>
                    <p class="text-sm text-gray-400">Base de données</p>
                </div>
            </div>
            
            <div class="bg-gray-700 rounded-lg p-4 text-center">
                <div class="check-status" data-check="websockets">
                    <div class="spinner w-8 h-8 mx-auto mb-2"></div>
                    <p class="text-sm text-gray-400">WebSockets</p>
                </div>
            </div>
            
            <div class="bg-gray-700 rounded-lg p-4 text-center">
                <div class="check-status" data-check="cache">
                    <div class="spinner w-8 h-8 mx-auto mb-2"></div>
                    <p class="text-sm text-gray-400">Cache</p>
                </div>
            </div>
            
            <div class="bg-gray-700 rounded-lg p-4 text-center">
                <div class="check-status" data-check="storage">
                    <div class="spinner w-8 h-8 mx-auto mb-2"></div>
                    <p class="text-sm text-gray-400">Storage</p>
                </div>
            </div>
        </div>
        
        <button id="refresh-checks" class="mt-4 bg-gray-600 hover:bg-gray-500 text-white py-2 px-4 rounded-lg text-sm">
            Rafraîchir les vérifications
        </button>
    </div>

    <!-- Actions rapides -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Actions rapides</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <button id="simulate-fast" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg" {{ !$testTeam ? 'disabled' : '' }}>
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Parcours rapide (15 min)
            </button>
            
            <button id="simulate-normal" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg" {{ !$testTeam ? 'disabled' : '' }}>
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                </svg>
                Parcours normal (1h)
            </button>
            
            <button id="simulate-slow" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded-lg" {{ !$testTeam ? 'disabled' : '' }}>
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Parcours lent (1h30)
            </button>
            
            <button id="test-offline" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path>
                </svg>
                Tester mode hors-ligne
            </button>
            
            <button id="reset-test" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg" {{ !$testTeam ? 'disabled' : '' }}>
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Réinitialiser
            </button>
            
            <button id="export-logs" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-3 px-4 rounded-lg">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Exporter logs
            </button>
        </div>
    </div>

    <!-- Test individuel des salles -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Test individuel des salles</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-400 border-b border-gray-700">
                        <th class="pb-3">#</th>
                        <th class="pb-3">Salle</th>
                        <th class="pb-3">Type</th>
                        <th class="pb-3">Chiffre</th>
                        <th class="pb-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rooms as $room)
                    <tr class="border-b border-gray-700 hover:bg-gray-700">
                        <td class="py-3 text-gray-400">{{ $room->order }}</td>
                        <td class="py-3">
                            <span class="font-medium text-white">{{ $room->name }}</span>
                            <span class="text-xs text-gray-500 block">{{ $room->slug }}</span>
                        </td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $room->type === 'main' ? 'bg-blue-900 text-blue-300' : 'bg-yellow-900 text-yellow-300' }}">
                                {{ $room->type === 'main' ? 'Principale' : 'Embranchement' }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($room->digit_reward)
                            <span class="text-green-400 font-bold">{{ $room->digit_reward }}</span>
                            @else
                            <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <div class="flex space-x-2">
                                <button class="test-room bg-indigo-600 hover:bg-indigo-700 text-white text-sm py-1 px-3 rounded" 
                                        data-room-id="{{ $room->id }}"
                                        {{ !$testTeam ? 'disabled' : '' }}>
                                    Tester
                                </button>
                                <button class="complete-room bg-green-600 hover:bg-green-700 text-white text-sm py-1 px-3 rounded" 
                                        data-room-id="{{ $room->id }}"
                                        {{ !$testTeam ? 'disabled' : '' }}>
                                    Compléter
                                </button>
                                <a href="/game/room/{{ $room->slug }}?test=true" 
                                   target="_blank"
                                   class="bg-gray-600 hover:bg-gray-500 text-white text-sm py-1 px-3 rounded inline-block">
                                    Voir
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Console de logs -->
    <div class="mt-6 bg-gray-900 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-300 mb-4">Console de test</h3>
        <div id="test-console" class="bg-black rounded p-4 h-64 overflow-y-auto font-mono text-sm text-green-400">
            <div>[{{ now()->format('H:i:s') }}] Mode test initialisé</div>
        </div>
    </div>
</div>

<!-- Style pour le spinner -->
<style>
.spinner {
    border: 3px solid rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    border-top: 3px solid #6366f1;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.check-ok {
    color: #10b981;
}

.check-error {
    color: #ef4444;
}
</style>
@endsection

@push('scripts')
<script>
// Logging dans la console
function log(message, type = 'info') {
    const console = document.getElementById('test-console');
    const timestamp = new Date().toLocaleTimeString();
    const colors = {
        'info': 'text-green-400',
        'error': 'text-red-400',
        'warning': 'text-yellow-400',
        'success': 'text-blue-400'
    };
    
    const div = document.createElement('div');
    div.className = colors[type] || 'text-green-400';
    div.textContent = `[${timestamp}] ${message}`;
    console.appendChild(div);
    console.scrollTop = console.scrollHeight;
}

// Vérifications système
async function checkSystem() {
    log('Vérification du système...');
    
    try {
        const response = await fetch('/api/admin/test/connectivity', {
            headers: {
                'X-Admin-Key': '{{ env("ADMIN_KEY") }}'
            }
        });
        
        const checks = await response.json();
        
        Object.entries(checks).forEach(([key, result]) => {
            const element = document.querySelector(`[data-check="${key}"]`);
            if (element) {
                const spinner = element.querySelector('.spinner');
                spinner.className = result.status === 'ok' ? 'check-ok' : 'check-error';
                spinner.innerHTML = result.status === 'ok' 
                    ? '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                    : '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
                
                log(`${key}: ${result.message}`, result.status === 'ok' ? 'success' : 'error');
            }
        });
    } catch (error) {
        log('Erreur vérification système: ' + error.message, 'error');
    }
}

// Créer équipe de test
document.getElementById('create-test-team')?.addEventListener('click', async () => {
    log('Création équipe de test...');
    
    try {
        const response = await fetch('/api/admin/test/create-team', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Admin-Key': '{{ env("ADMIN_KEY") }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            log(`Équipe créée: ${data.team.name} (Code: ${data.team.code})`, 'success');
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (error) {
        log('Erreur création équipe: ' + error.message, 'error');
    }
});

// Simulations
['fast', 'normal', 'slow'].forEach(speed => {
    document.getElementById(`simulate-${speed}`)?.addEventListener('click', async () => {
        log(`Lancement simulation ${speed}...`);
        
        try {
            const response = await fetch('/api/admin/test/simulate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Admin-Key': '{{ env("ADMIN_KEY") }}'
                },
                body: JSON.stringify({ speed })
            });
            
            const data = await response.json();
            
            if (data.success) {
                log(`Simulation terminée en ${data.total_time}`, 'success');
                data.results.forEach(result => {
                    log(`- ${result.room}: ${result.time} ${result.digit ? '(Chiffre ' + result.digit + ')' : ''}`);
                });
            }
        } catch (error) {
            log('Erreur simulation: ' + error.message, 'error');
        }
    });
});

// Test hors-ligne
document.getElementById('test-offline')?.addEventListener('click', () => {
    log('Activation mode hors-ligne...');
    
    // Forcer le mode offline
    window.dispatchEvent(new Event('offline'));
    
    setTimeout(() => {
        log('Mode hors-ligne activé - Testez les fonctionnalités', 'warning');
    }, 500);
    
    // Réactiver après 30 secondes
    setTimeout(() => {
        window.dispatchEvent(new Event('online'));
        log('Retour en ligne', 'success');
    }, 30000);
});

// Test individuel des salles
document.querySelectorAll('.test-room').forEach(btn => {
    btn.addEventListener('click', async () => {
        const roomId = btn.dataset.roomId;
        log(`Test salle ${roomId}...`);
        
        // Entrer dans la salle
        await fetch(`/api/admin/test/room/${roomId}/enter`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Admin-Key': '{{ env("ADMIN_KEY") }}'
            }
        });
        
        // Ouvrir dans un nouvel onglet
        window.open(`/api/admin/test/room/${roomId}/test`, '_blank');
    });
});

// Compléter une salle
document.querySelectorAll('.complete-room').forEach(btn => {
    btn.addEventListener('click', async () => {
        const roomId = btn.dataset.roomId;
        log(`Complétion salle ${roomId}...`);
        
        try {
            const response = await fetch(`/api/admin/test/room/${roomId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Admin-Key': '{{ env("ADMIN_KEY") }}'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                log(`Salle complétée ${data.digit ? '(Chiffre ' + data.digit + ')' : ''}`, 'success');
                btn.textContent = '✓';
                btn.disabled = true;
            }
        } catch (error) {
            log('Erreur complétion: ' + error.message, 'error');
        }
    });
});

// Réinitialiser
document.getElementById('reset-test')?.addEventListener('click', async () => {
    if (!confirm('Réinitialiser toutes les données de test ?')) return;
    
    log('Réinitialisation...');
    
    try {
        await fetch('/api/admin/test/reset', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Admin-Key': '{{ env("ADMIN_KEY") }}'
            }
        });
        
        log('Test réinitialisé', 'success');
        setTimeout(() => window.location.reload(), 1000);
    } catch (error) {
        log('Erreur réinitialisation: ' + error.message, 'error');
    }
});

// Rafraîchir les vérifications
document.getElementById('refresh-checks').addEventListener('click', checkSystem);

// Export logs
document.getElementById('export-logs').addEventListener('click', () => {
    const console = document.getElementById('test-console');
    const text = console.innerText;
    
    const blob = new Blob([text], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `test-logs-${new Date().toISOString()}.txt`;
    a.click();
    
    log('Logs exportés', 'success');
});

// Vérifications au chargement
checkSystem();
</script>
@endpush