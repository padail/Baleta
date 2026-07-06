const CACHE_VERSION = 'nelayan-pwa-v1.0.0';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;

const APP_SHELL = [
  '/offline.html',
  '/manifest.webmanifest',
  '/icons/pwa-192.png',
  '/icons/pwa-512.png',
  '/icons/maskable-192.png',
  '/icons/maskable-512.png',
  '/pwa-register.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => cache.addAll(APP_SHELL))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys
        .filter(key => key.startsWith('nelayan-pwa-') && !key.startsWith(CACHE_VERSION))
        .map(key => caches.delete(key))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);

  if (request.method !== 'GET') return;
  if (url.origin !== self.location.origin) return;

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request, { cache: 'no-store' }).catch(() => caches.match('/offline.html'))
    );
    return;
  }

  const isStaticAsset =
    url.pathname.startsWith('/build/') ||
    url.pathname.startsWith('/icons/') ||
    url.pathname === '/manifest.webmanifest' ||
    url.pathname === '/pwa-register.js' ||
    url.pathname.match(/\.(css|js|png|jpg|jpeg|svg|webp|ico|woff2?)$/);

  if (isStaticAsset) {
    event.respondWith(
      caches.match(request).then(cached => {
        if (cached) return cached;
        return fetch(request).then(response => {
          const copy = response.clone();
          caches.open(RUNTIME_CACHE).then(cache => cache.put(request, copy));
          return response;
        }).catch(() => caches.match('/icons/pwa-192.png'));
      })
    );
  }
});
