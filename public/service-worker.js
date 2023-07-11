var CACHE_VERSION = 20230711207;
var CURRENT_CACHES = {
    prefetch: 'sumberdaya-cache-v' + CACHE_VERSION
};
var offline = [
    self.location.origin + "/",
    self.location.origin + "/tentang-aplikasi",
    self.location.origin + "/perlu-javascript",
    self.location.origin + "/tampilan.css",
    self.location.origin + "/interaksi.js",
    self.location.origin + "/slimselect-es.js",
    self.location.origin + "/siapkan-foto-es.js",
    self.location.origin + "/pwa-manifest.json",
    self.location.origin + "/ikon.svg",
    self.location.origin + "/images/Logo Perusahaan.webp",
    self.location.origin + "/images/Lambang Perusahaan.webp",
    self.location.origin + "/images/Ikon Aplikasi 192.png",
    self.location.origin + "/images/Ikon Aplikasi 512.png",
    self.location.origin + "/images/blank.webp",
    self.location.origin + "/images/Background Badan.webp",
    self.location.origin + "/images/Background Kepala.webp",
    self.location.origin + "/images/Brand Utama Putih.webp",
    self.location.origin + "/images/Brand Utama Warna.webp",
    self.location.origin + "/images/Budaya Perusahaan.webp",
    self.location.origin + "/images/Logo Brand.webp",
    self.location.origin + "/images/Logo ISO.webp",
    self.location.origin + "/fonts/Roboto400.woff2",
    self.location.origin + "/fonts/Roboto500.woff2"
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
    self.skipWaiting();
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
    self.clients.claim();
};

self.addEventListener('install', function (event) {
    event.waitUntil(onInstall(event));
});

self.addEventListener('activate', function (event) {
    // Delete all caches that aren't named in CURRENT_CACHES.
    // While there is only one cache in this example, the same logic will handle the case where
    // there are multiple versioned caches.
    event.waitUntil(onActivate(event));

    // clients.claim() tells the active service worker to take immediate
    // control of all of the clients under its scope.
});

self.addEventListener('fetch', function (event) {
    var rek = new URL(event.request.url);
    rek = location.origin + rek.pathname;
    // if (event.request.method == 'GET') {
    event.respondWith(
        caches.match(rek, { ignoreSearch: true, ignoreVary: true }).then(
            function (cr) {
                return cr ? cr : fetch(event.request);
            }
        ).catch(
            function (er) {
                return caches.match('/');
            }
        )
    )
    // }
});