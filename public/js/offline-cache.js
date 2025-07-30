// public/js/offline-cache.js

class OfflineCache {
    constructor() {
        this.dbName = 'EscapeGameOffline';
        this.version = 1;
        this.db = null;
        this.syncQueue = [];
        this.isOnline = navigator.onLine;
        
        this.init();
        this.setupEventListeners();
    }

    async init() {
        // Initialiser IndexedDB
        this.db = await this.openDB();
        
        // Pré-charger les données critiques
        if (this.isOnline) {
            await this.cacheEssentialData();
        }
        
        // Synchroniser la queue si en ligne
        if (this.isOnline && this.syncQueue.length > 0) {
            await this.processSyncQueue();
        }
    }

    openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.version);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                // Store pour les données de jeu
                if (!db.objectStoreNames.contains('gameData')) {
                    const gameStore = db.createObjectStore('gameData', { keyPath: 'id' });
                    gameStore.createIndex('type', 'type', { unique: false });
                }
                
                // Store pour les actions en attente
                if (!db.objectStoreNames.contains('syncQueue')) {
                    db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
                }
                
                // Store pour les progressions
                if (!db.objectStoreNames.contains('progressions')) {
                    const progStore = db.createObjectStore('progressions', { keyPath: 'id' });
                    progStore.createIndex('roomId', 'roomId', { unique: false });
                    progStore.createIndex('teamId', 'teamId', { unique: false });
                }
            };
        });
    }

    setupEventListeners() {
        // Détection de connexion/déconnexion
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.showNotification('Connexion rétablie', 'success');
            this.processSyncQueue();
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.showNotification('Mode hors-ligne activé', 'warning');
        });
        
        // Intercepter les requêtes API
        this.interceptFetch();
    }

    async cacheEssentialData() {
        try {
            // Cacher les données de l'équipe
            const teamId = localStorage.getItem('team_id');
            if (teamId) {
                const teamData = await this.fetchWithCache(`/api/team/${teamId}`);
                await this.saveToCache('team', teamData);
            }
            
            // Cacher les salles
            const rooms = await this.fetchWithCache('/api/rooms');
            await this.saveToCache('rooms', rooms);
            
            // Cacher la progression actuelle
            const progress = await this.fetchWithCache('/api/game/progress');
            await this.saveToCache('progress', progress);
            
            console.log('✅ Données essentielles mises en cache');
        } catch (error) {
            console.error('Erreur cache:', error);
        }
    }

    async fetchWithCache(url) {
        const response = await fetch(url, {
            headers: {
                'X-Player-ID': localStorage.getItem('player_id'),
                'X-Device-ID': localStorage.getItem('device_id')
            }
        });
        return response.json();
    }

    async saveToCache(type, data) {
        const transaction = this.db.transaction(['gameData'], 'readwrite');
        const store = transaction.objectStore('gameData');
        
        await store.put({
            id: type,
            type: type,
            data: data,
            timestamp: Date.now()
        });
    }

    async getFromCache(type) {
        const transaction = this.db.transaction(['gameData'], 'readonly');
        const store = transaction.objectStore('gameData');
        const request = store.get(type);
        
        return new Promise((resolve, reject) => {
            request.onsuccess = () => resolve(request.result?.data);
            request.onerror = () => reject(request.error);
        });
    }

    interceptFetch() {
        const originalFetch = window.fetch;
        const cache = this;
        
        window.fetch = async function(url, options = {}) {
            // Si en ligne, utiliser fetch normal
            if (cache.isOnline) {
                try {
                    const response = await originalFetch(url, options);
                    
                    // Mettre en cache les réponses GET importantes
                    if (options.method === 'GET' || !options.method) {
                        if (url.includes('/api/team/') || url.includes('/api/rooms')) {
                            const data = await response.clone().json();
                            const cacheKey = url.split('/').pop();
                            await cache.saveToCache(cacheKey, data);
                        }
                    }
                    
                    return response;
                } catch (error) {
                    // Si erreur réseau, essayer le cache
                    if (!navigator.onLine) {
                        return cache.handleOfflineRequest(url, options);
                    }
                    throw error;
                }
            } else {
                // Mode hors-ligne
                return cache.handleOfflineRequest(url, options);
            }
        };
    }

    async handleOfflineRequest(url, options = {}) {
        const method = options.method || 'GET';
        
        // Requêtes GET - servir depuis le cache
        if (method === 'GET') {
            const cacheKey = this.getCacheKey(url);
            const cachedData = await this.getFromCache(cacheKey);
            
            if (cachedData) {
                return new Response(JSON.stringify(cachedData), {
                    status: 200,
                    headers: { 'Content-Type': 'application/json' }
                });
            } else {
                return new Response(JSON.stringify({ 
                    error: 'Données non disponibles hors-ligne' 
                }), { 
                    status: 503 
                });
            }
        }
        
        // Requêtes POST/PUT - ajouter à la queue de sync
        if (method === 'POST' || method === 'PUT') {
            await this.addToSyncQueue(url, options);
            
            // Retourner une réponse optimiste
            return new Response(JSON.stringify({ 
                success: true, 
                offline: true,
                message: 'Action enregistrée, sera synchronisée à la reconnexion' 
            }), {
                status: 202
            });
        }
        
        return new Response('Method not allowed offline', { status: 405 });
    }

    getCacheKey(url) {
        // Extraire la clé du cache depuis l'URL
        const parts = url.split('/');
        if (url.includes('/api/team/')) return 'team';
        if (url.includes('/api/rooms')) return 'rooms';
        if (url.includes('/api/game/progress')) return 'progress';
        return parts[parts.length - 1];
    }

    async addToSyncQueue(url, options) {
        const transaction = this.db.transaction(['syncQueue'], 'readwrite');
        const store = transaction.objectStore('syncQueue');
        
        await store.add({
            url: url,
            options: options,
            timestamp: Date.now(),
            body: options.body ? JSON.parse(options.body) : null
        });
        
        this.showNotification('Action enregistrée pour synchronisation', 'info');
    }

    async processSyncQueue() {
        const transaction = this.db.transaction(['syncQueue'], 'readwrite');
        const store = transaction.objectStore('syncQueue');
        const request = store.getAll();
        
        request.onsuccess = async () => {
            const queue = request.result;
            
            for (const item of queue) {
                try {
                    // Rejouer la requête
                    const response = await fetch(item.url, item.options);
                    
                    if (response.ok) {
                        // Supprimer de la queue si succès
                        await store.delete(item.id);
                        this.showNotification('Action synchronisée', 'success');
                    }
                } catch (error) {
                    console.error('Erreur sync:', error);
                }
            }
        };
    }

    // Méthodes spécifiques pour la cave
    async cacheRoomData(roomId) {
        const transaction = this.db.transaction(['progressions'], 'readwrite');
        const store = transaction.objectStore('progressions');
        
        // Sauvegarder l'état local de la progression
        await store.put({
            id: `room_${roomId}_${Date.now()}`,
            roomId: roomId,
            teamId: localStorage.getItem('team_id'),
            status: 'in_progress',
            timestamp: Date.now(),
            synced: false
        });
    }

    async completeRoomOffline(roomId, data) {
        // Sauvegarder la complétion en local
        await this.cacheRoomData(roomId);
        
        // Ajouter à la queue de sync
        await this.addToSyncQueue('/api/game/complete-challenge', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Player-ID': localStorage.getItem('player_id'),
                'X-Device-ID': localStorage.getItem('device_id')
            },
            body: JSON.stringify({
                room_id: roomId,
                success: true,
                data: { ...data, offline_completion: true }
            })
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        const colors = {
            'info': 'bg-blue-600',
            'success': 'bg-green-600',
            'warning': 'bg-yellow-600',
            'error': 'bg-red-600'
        };
        
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
        notification.innerHTML = `
            <div class="flex items-center">
                ${!this.isOnline ? '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path></svg>' : ''}
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Initialiser le cache
window.offlineCache = new OfflineCache();

// Exporter pour utilisation dans d'autres scripts
window.OfflineCache = OfflineCache;