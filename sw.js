const CACHE_NAME = 'minipos-cache-v1';
const STATIC_ASSETS = [
  './assets/css/local-font.css',
  './plugins/fontawesome-free/css/all.min.css',
  './dist/css/adminlte.min.css',
  './assets/css/global-custom.css',
  './plugins/jquery/jquery.min.js',
  './plugins/bootstrap/js/bootstrap.bundle.min.js',
  './plugins/sweetalert2/sweetalert2.all.min.js',
  './assets/img/logo/logo.png',
  './assets/img/image.jpg'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('PWA SW: Some static assets failed to pre-cache', err);
      });
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  const url = new URL(event.request.url);

  // Skip dynamic PHP API endpoints from SW caching so data stays live
  if (url.pathname.includes('/api/') || url.search.includes('action=')) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200) {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        return networkResponse;
      })
      .catch(() => {
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) return cachedResponse;
          if (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html')) {
            return caches.match('./pages/pos/pos.php');
          }
        });
      })
  );
});
