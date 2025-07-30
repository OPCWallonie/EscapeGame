// public/service-worker.js

const CACHE_NAME = 'escape-game-v2';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/js/offline-cache.js',
  '/images/logo.png',
  '/offline.html',
  // Ajouter les assets des mini-jeux
  '/game/rooms/cave_salon',
  '/game/rooms/onze_caves'
];

// Caches dynamiques par type
const cacheNames = {
  static: CACHE_NAME,
  dynamic: 'escape-game-dynamic-v1',
  api: 'escape-game-api-v1'
};

// Installation du Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(cacheNames.static)
      .then(cache => {
        console.log('Cache statique ouvert');
        return cache.addAll(urlsToCache);
      })
      .then(() => self.skipWaiting())
  );
});

// Activation du Service Worker
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (!Object.values(cacheNames).includes(key)) {
            console.log('Suppression ancien cache:', key);
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Stratégies de cache
const cacheStrategies = {
  // Network First - Pour l'API
  networkFirst: async (request) => {
    try {
      const networkResponse = await fetch(request);
      if (networkResponse.ok) {
        const cache = await caches.open(cacheNames.api);
        cache.put(request, networkResponse.clone());
      }
      return networkResponse;
    } catch (error) {
      const cachedResponse = await caches.match(request);
      if (cachedResponse) {
        return cachedResponse;
      }
      
      // Si c'est une requête API, retourner une réponse d'erreur
      if (request.url.includes('/api/')) {
        return new Response(JSON.stringify({
          error: 'Hors ligne',
          cached: false
        }), {
          status: 503,
          headers: { 'Content-Type': 'application/json' }
        });
      }
      
      throw error;
    }
  },
  
  // Cache First - Pour les assets statiques
  cacheFirst: async (request) => {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    try {
      const networkResponse = await fetch(request);
      const cache = await caches.open(cacheNames.static);
      cache.put(request, networkResponse.clone());
      return networkResponse;
    } catch (error) {
      return caches.match('/offline.html');
    }
  },
  
  // Stale While Revalidate - Pour les pages HTML
  staleWhileRevalidate: async (request) => {
    const cachedResponse = await caches.match(request);
    
    const fetchPromise = fetch(request).then(networkResponse => {
      if (networkResponse.ok) {
        const cache = caches.open(cacheNames.dynamic);
        cache.then(c => c.put(request, networkResponse.clone()));
      }
      return networkResponse;
    }).catch(() => cachedResponse);
    
    return cachedResponse || fetchPromise;
  }
};

// Interception des requêtes
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Ne pas cacher les WebSockets
  if (url.pathname.includes('/app/') || url.pathname.includes('/broadcasting/')) {
    return;
  }
  
  // Stratégie selon le type de requête
  if (request.method !== 'GET') {
    // Pour POST/PUT, utiliser le cache intelligent
    return;
  }
  
  // API calls
  if (url.pathname.includes('/api/')) {
    event.respondWith(cacheStrategies.networkFirst(request));
    return;
  }
  
  // Assets statiques (CSS, JS, images)
  if (request.destination === 'style' || 
      request.destination === 'script' || 
      request.destination === 'image') {
    event.respondWith(cacheStrategies.cacheFirst(request));
    return;
  }
  
  // Pages HTML
  if (request.destination === 'document') {
    event.respondWith(cacheStrategies.staleWhileRevalidate(request));
    return;
  }
  
  // Par défaut
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});

// Gestion des messages du client
self.addEventListener('message', event => {
  if (event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data.type === 'CACHE_URLS') {
    event.waitUntil(
      caches.open(cacheNames.dynamic).then(cache => {
        return cache.addAll(event.data.urls);
      })
    );
  }
});

// Synchronisation en arrière-plan
self.addEventListener('sync', event => {
  if (event.tag === 'sync-game-data') {
    event.waitUntil(syncGameData());
  }
});

async function syncGameData() {
  // Récupérer les données en attente depuis IndexedDB
  // et les synchroniser avec le serveur
  console.log('Synchronisation des données de jeu...');
}

// Notifications push (pour plus tard)
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'Nouvelle notification',
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/icon-72x72.png',
    vibrate: [200, 100, 200],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Voir',
        icon: '/images/icons/checkmark.png'
      },
      {
        action: 'close',
        title: 'Fermer',
        icon: '/images/icons/xmark.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('Escape Game', options)
  );
});