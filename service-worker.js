// A name for our cache
const CACHE_NAME = 'youtube-music-player-v1';

// The assets we want to cache (our app shell)
const assetsToCache = [
  '/', 
  'index.html'
];

// Install Event: Cache the app shell
self.addEventListener('install', event => {
  console.log('Service Worker: Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Caching app shell');
        return cache.addAll(assetsToCache);
      })
  );
});

// Activate Event: Clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker: Activating...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== CACHE_NAME) {
            console.log('Service Worker: Clearing old cache');
            return caches.delete(cache);
          }
        })
      );
    })
  );
});

// Fetch Event: Serve from cache first, then network
self.addEventListener('fetch', event => {
  // We only want to handle GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // If we have a cached response, return it.
        // Otherwise, fetch from the network.
        return response || fetch(event.request);
      })
  );
});