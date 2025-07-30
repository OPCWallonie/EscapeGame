<!-- resources/views/game/scanner.blade.php -->
@extends('layouts.app')

@section('title', 'Scanner QR Code')
@section('header', 'Scanner')

@section('content')
<div class="max-w-md mx-auto">
    <!-- Zone de scan -->
    <div class="bg-gray-800 rounded-lg overflow-hidden shadow-xl mb-6">
        <div id="qr-reader" class="relative" style="min-height: 300px;">
            <!-- Le scanner sera injecté ici -->
        </div>
        
        <!-- Overlay avec guide de scan -->
        <div id="scan-overlay" class="absolute inset-0 pointer-events-none hidden">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-64 h-64 border-2 border-white rounded-lg relative">
                    <div class="absolute -top-1 -left-1 w-8 h-8 border-t-4 border-l-4 border-indigo-400"></div>
                    <div class="absolute -top-1 -right-1 w-8 h-8 border-t-4 border-r-4 border-indigo-400"></div>
                    <div class="absolute -bottom-1 -left-1 w-8 h-8 border-b-4 border-l-4 border-indigo-400"></div>
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 border-b-4 border-r-4 border-indigo-400"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- État du scanner -->
    <div id="scanner-status" class="bg-gray-800 rounded-lg p-4 mb-6">
        <div class="flex items-center space-x-3">
            <div id="status-icon" class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
            <div class="flex-1">
                <p id="status-text" class="text-gray-300">Initialisation du scanner...</p>
                <p id="status-detail" class="text-sm text-gray-500">Autorisez l'accès à la caméra</p>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="space-y-3">
        <button id="start-scan" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            Démarrer le scan
        </button>
        
        <button id="manual-input" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
            Entrer le code manuellement
        </button>
    </div>

    <!-- Historique des scans -->
    <div id="scan-history" class="mt-6 bg-gray-800 rounded-lg p-4 hidden">
        <h3 class="font-semibold text-indigo-400 mb-3">Derniers scans</h3>
        <div id="history-list" class="space-y-2">
            <!-- Les scans seront ajoutés ici -->
        </div>
    </div>
</div>

<!-- Modal saisie manuelle -->
<div id="manual-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 px-4">
    <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full">
        <h3 class="text-xl font-bold mb-4">Entrer le code QR manuellement</h3>
        
        <form id="manual-form">
            <input 
                type="text" 
                id="manual-code" 
                class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white uppercase focus:ring-2 focus:ring-indigo-500 mb-4"
                placeholder="Ex: QR_GALERIE_001"
                maxlength="20"
            >
            
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg">
                    Valider
                </button>
                <button type="button" id="cancel-manual" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Styles spécifiques pour le scanner */
#qr-reader {
    position: relative;
    padding: 0 !important;
}

#qr-reader__scan_region {
    background: transparent !important;
}

#qr-reader video {
    width: 100% !important;
    height: auto !important;
}
</style>
@endpush

@push('scripts')
<!-- Bibliothèque QR Code Scanner -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
let html5QrCode = null;
let isScanning = false;
const playerId = localStorage.getItem('player_id');
const deviceId = localStorage.getItem('device_id');

// Configuration du scanner
const config = {
    fps: 10,
    qrbox: { width: 250, height: 250 },
    aspectRatio: 1.0
};

// Initialiser le scanner
function initScanner() {
    html5QrCode = new Html5Qrcode("qr-reader");
    
    // Vérifier les caméras disponibles
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            updateStatus('ready', 'Scanner prêt', 'Appuyez sur "Démarrer le scan"');
            document.getElementById('start-scan').disabled = false;
        }
    }).catch(err => {
        console.error('Erreur caméra:', err);
        updateStatus('error', 'Caméra non disponible', 'Utilisez la saisie manuelle');
    });
}

// Démarrer le scan
function startScanning() {
    if (isScanning) return;
    
    updateStatus('scanning', 'Scan en cours...', 'Placez le QR code dans le cadre');
    document.getElementById('scan-overlay').classList.remove('hidden');
    
    html5QrCode.start(
        { facingMode: "environment" }, // Caméra arrière
        config,
        onScanSuccess,
        onScanFailure
    ).then(() => {
        isScanning = true;
        document.getElementById('start-scan').textContent = 'Arrêter le scan';
    }).catch(err => {
        console.error('Erreur démarrage scan:', err);
        updateStatus('error', 'Erreur de scan', err);
    });
}

// Arrêter le scan
function stopScanning() {
    if (!isScanning) return;
    
    html5QrCode.stop().then(() => {
        isScanning = false;
        document.getElementById('start-scan').textContent = 'Démarrer le scan';
        document.getElementById('scan-overlay').classList.add('hidden');
        updateStatus('ready', 'Scanner arrêté', 'Prêt pour un nouveau scan');
    }).catch(err => {
        console.error('Erreur arrêt scan:', err);
    });
}

// Succès du scan
async function onScanSuccess(decodedText, decodedResult) {
    // Vibration de confirmation
    if (navigator.vibrate) {
        navigator.vibrate(200);
    }
    
    // Arrêter le scan
    stopScanning();
    
    // Envoyer au serveur
    await processQRCode(decodedText);
}

// Échec du scan (pas d'erreur, juste pas de QR détecté)
function onScanFailure(error) {
    // Ignorer, c'est normal quand il n'y a pas de QR dans le champ
}

// Traiter le QR code
async function processQRCode(code) {
    updateStatus('processing', 'Traitement...', 'Vérification du code');
    
    try {
        const response = await fetch('/api/game/scan-qr', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Player-ID': playerId,
                'X-Device-ID': deviceId
            },
            body: JSON.stringify({ qr_code: code })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            updateStatus('success', 'Scan réussi !', data.message);
            addToHistory(code, true, data.room_name);
            
            // Rediriger vers la salle si nécessaire
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            updateStatus('error', 'Code invalide', data.message);
            addToHistory(code, false, 'Invalide');
        }
    } catch (error) {
        console.error('Erreur:', error);
        updateStatus('error', 'Erreur réseau', 'Vérifiez votre connexion');
    }
}

// Mettre à jour le statut
function updateStatus(type, text, detail) {
    const statusIcon = document.getElementById('status-icon');
    const statusText = document.getElementById('status-text');
    const statusDetail = document.getElementById('status-detail');
    
    // Icônes selon le type
    const icons = {
        ready: '<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        scanning: '<svg class="w-6 h-6 text-indigo-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>',
        processing: '<svg class="w-6 h-6 text-yellow-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>',
        success: '<svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        error: '<svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
    };
    
    statusIcon.innerHTML = icons[type] || icons.ready;
    statusText.textContent = text;
    statusDetail.textContent = detail;
    
    // Couleurs de fond
    statusIcon.className = 'w-10 h-10 rounded-full flex items-center justify-center';
    statusIcon.classList.add(
        type === 'success' ? 'bg-green-900' :
        type === 'error' ? 'bg-red-900' :
        type === 'scanning' ? 'bg-indigo-900' :
        type === 'processing' ? 'bg-yellow-900' :
        'bg-gray-700'
    );
}

// Ajouter à l'historique
function addToHistory(code, success, roomName) {
    const historyContainer = document.getElementById('scan-history');
    const historyList = document.getElementById('history-list');
    
    historyContainer.classList.remove('hidden');
    
    const entry = document.createElement('div');
    entry.className = `flex items-center justify-between p-2 rounded ${success ? 'bg-green-900' : 'bg-red-900'} bg-opacity-20`;
    entry.innerHTML = `
        <div>
            <p class="text-sm font-medium">${roomName}</p>
            <p class="text-xs text-gray-400">${new Date().toLocaleTimeString()}</p>
        </div>
        <svg class="w-5 h-5 ${success ? 'text-green-400' : 'text-red-400'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${success ? 
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' :
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
            }
        </svg>
    `;
    
    historyList.insertBefore(entry, historyList.firstChild);
    
    // Limiter à 5 entrées
    while (historyList.children.length > 5) {
        historyList.removeChild(historyList.lastChild);
    }
}

// Événements
document.getElementById('start-scan').addEventListener('click', () => {
    if (isScanning) {
        stopScanning();
    } else {
        startScanning();
    }
});

// Modal saisie manuelle
const manualModal = document.getElementById('manual-modal');
document.getElementById('manual-input').addEventListener('click', () => {
    manualModal.classList.remove('hidden');
    document.getElementById('manual-code').focus();
});

document.getElementById('cancel-manual').addEventListener('click', () => {
    manualModal.classList.add('hidden');
    document.getElementById('manual-code').value = '';
});

document.getElementById('manual-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const code = document.getElementById('manual-code').value.toUpperCase();
    if (code) {
        manualModal.classList.add('hidden');
        await processQRCode(code);
        document.getElementById('manual-code').value = '';
    }
});

// Initialisation
document.addEventListener('DOMContentLoaded', initScanner);
</script>
@endpush