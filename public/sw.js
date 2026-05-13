const CACHE_VERSION = 'lapl-pwa-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const PAGE_CACHE = `${CACHE_VERSION}-pages`;

const PRECACHE_URLS = [
    '/',
    '/offline',
    '/site.webmanifest',
    '/favicon.ico',
    '/favicon-16x16.png',
    '/favicon-32x32.png',
    '/apple-touch-icon.png',
    '/android-chrome-192x192.png',
    '/android-chrome-512x512.png',
];

const PRIVATE_PATH_PREFIXES = [
    '/api',
    '/dashboard',
    '/login',
    '/logout',
    '/register',
    '/password',
    '/email',
    '/sanctum',
];

const STATIC_EXTENSIONS = [
    '.css',
    '.js',
    '.mjs',
    '.png',
    '.jpg',
    '.jpeg',
    '.gif',
    '.svg',
    '.webp',
    '.ico',
    '.woff',
    '.woff2',
    '.ttf',
    '.eot',
];

const isPrivatePath = (pathname) => PRIVATE_PATH_PREFIXES.some((prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`));

const isStaticRequest = (url) => {
    if (url.origin !== self.location.origin) {
        return false;
    }

    return STATIC_EXTENSIONS.some((extension) => url.pathname.toLowerCase().endsWith(extension))
        || url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/public-assets/')
        || url.pathname.startsWith('/kester-assets/');
};

const cacheResponse = async (cacheName, request, response) => {
    if (!response || !response.ok || response.type !== 'basic') {
        return;
    }

    const cache = await caches.open(cacheName);
    await cache.put(request, response.clone());
};

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('lapl-pwa-') && !key.startsWith(CACHE_VERSION))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin || isPrivatePath(url.pathname)) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const response = await fetch(request);
                await cacheResponse(PAGE_CACHE, request, response);
                return response;
            } catch (error) {
                return await caches.match(request)
                    || await caches.match('/offline')
                    || Response.error();
            }
        })());

        return;
    }

    if (isStaticRequest(url)) {
        event.respondWith((async () => {
            const cached = await caches.match(request);

            if (cached) {
                return cached;
            }

            const response = await fetch(request);
            await cacheResponse(STATIC_CACHE, request, response);
            return response;
        })());
    }
});
