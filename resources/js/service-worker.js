var CACHE_VERSION = 202307120021;
var CURRENT_CACHES = {
    prefetch: 'sumberdaya-cache-v' + CACHE_VERSION
};
var offline = [
    "tentang-aplikasi",
    "perlu-javascript",
    "offline",
    "tampilan.css",
    "interaksi.js",
    "slimselect-es.js",
    "siapkan-foto-es.js",
    "pwa-manifest.json",
    "ikon.svg",
    "images/Logo Perusahaan.webp",
    "images/Lambang Perusahaan.webp",
    "images/Ikon Aplikasi 192.png",
    "images/Ikon Aplikasi 512.png",
    "images/blank.webp",
    "images/Background Badan.webp",
    "images/Background Kepala.webp",
    "images/Brand Utama Putih.webp",
    "images/Brand Utama Warna.webp",
    "images/Budaya Perusahaan.webp",
    "images/Logo Brand.webp",
    "images/Logo ISO.webp",
    "fonts/Roboto400.woff2",
    "fonts/Roboto500.woff2"
];
var expectedCacheNames = Object.keys(CURRENT_CACHES).map(function (key) {
    return CURRENT_CACHES[key];
});

async function onInstall(event) {
    caches.open(CURRENT_CACHES.prefetch).then(function (cache) {
        // aset.forEach(function (aset) {
        //     cache.add(new Request(aset), { cache: 'reload', credentials: 'include' });
        // });
        offline.forEach(function (aset) {
            cache.add(new Request(aset), { cache: 'reload', credentials: 'include' });
        });
        console.log('All resources have been fetched and cached.');
        // skipWaiting() allows this service worker to become active
        // immediately, bypassing the waiting state, even if there's a previous
        // version of the service worker already installed.
    }).catch(function (error) {
        // This catch() will handle any exceptions from the caches.open()/cache.addAll() steps.
        console.error('Pre-fetching failed:', error);
    });
    // self.skipWaiting();
};

async function onActivate(event) {
    caches.keys().then(function (cacheNames) {
        return Promise.all(
            cacheNames.map(function (cacheName) {
                if (expectedCacheNames.indexOf(cacheName) === -1) {
                    // If this cache name isn't present in the array of "expected" cache names,
                    // then delete it.
                    console.log('Deleting out of date cache:', cacheName);
                    return caches.delete(cacheName);
                }
            })
        );
    });
};

self.addEventListener('install', function (event) {
    event.waitUntil(onInstall(event));
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    // Delete all caches that aren't named in CURRENT_CACHES.
    // While there is only one cache in this example, the same logic will handle the case where
    // there are multiple versioned caches.
    event.waitUntil(onActivate(event));
    // self.clients.claim();
    // clients.claim() tells the active service worker to take immediate
    // control of all of the clients under its scope.
});

self.addEventListener('fetch', function (event) {
    // var rek = new URL(event.request.url);
    // rek = location.origin + rek.pathname;
    // if (event.request.method === 'GET') {
    event.respondWith(
        (async () => {
            try {

                const cachedResponse = await caches.match(event.request, { ignoreSearch: true, ignoreVary: true });
                if (cachedResponse) return cachedResponse;
                // First, try to use the navigation preload response if it's supported.

                // Always try the network first.
                const networkResponse = await fetch(event.request);
                return networkResponse;
            } catch (error) {
                // catch is only triggered if an exception is thrown, which is likely
                // due to a network error.
                // If fetch() returns a valid HTTP response with a response code in
                // the 4xx or 5xx range, the catch() will NOT be called.
                console.log('Fetch failed; returning offline page instead.', error);

                const cache = await caches.open(CURRENT_CACHES.prefetch);
                const cachedResponse = await cache.match(self.location.origin + '/offline');
                return cachedResponse;
            }
        })()
    )
    // }
});