/* Service worker Rentra: cache aset statis, fallback offline. */
const NAMA = 'rentra-v2';
const ASET = ['/', '/favicon.svg', '/apple-touch-icon.png', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(NAMA).then((cache) => cache.addAll(ASET)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== NAMA).map((k) => caches.delete(k))))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        caches.match(event.request).then((cache) => {
            return cache || fetch(event.request).then((response) => {
                if (response.ok && response.type === 'same-origin') {
                    const salinan = response.clone();
                    caches.open(NAMA).then((c) => c.put(event.request, salinan));
                }
                return response;
            }).catch(() => caches.match('/'));
        })
    );
});
