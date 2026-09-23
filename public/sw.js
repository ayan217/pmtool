const CACHE_NAME = 'pmtool-static-v1';
const PRECACHE = [
    '/offline.html',
    '/css/app.css',
    '/js/app.js',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/manifest.webmanifest',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    if (url.origin !== self.location.origin) {
        return;
    }

    if (!['/css/', '/js/', '/icons/', '/manifest.webmanifest'].some((prefix) => (
        url.pathname === prefix || url.pathname.startsWith(prefix)
    ))) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => {
            const fetched = fetch(request).then((response) => {
                if (response && response.ok) {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                }

                return response;
            }).catch(() => cached);

            return cached || fetched;
        })
    );
});
