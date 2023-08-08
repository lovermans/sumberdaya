var CACHE_VERSION = 202308090112;
var CURRENT_CACHES = {
    prefetch: "{{ $confRangka->get('app.name', 'Laravel') }}-cache-v" + CACHE_VERSION
};
var offline = [
    "{{ $rekRangka->getBasePath() . '/' }}",
    "tentang-aplikasi",
    "perlu-javascript",
    "offline",
    "{{ $urlRangka->asset($mixRangka('/tampilan.css')) }}",
    "{{ $urlRangka->asset($mixRangka('/interaksi.js')) }}",
    "{{ $urlRangka->asset($mixRangka('/slimselect-es.js')) }}",
    "{{ $urlRangka->asset($mixRangka('/siapkan-foto-es.js')) }}",
    "pwa-manifest.json",
    "{{ $urlRangka->asset($mixRangka('/ikon.svg')) }}",
    "{{ $urlRangka->asset($mixRangka('/images/Logo Perusahaan.webp')) }}",
    "{{ $urlRangka->asset($mixRangka('/images/Lambang Perusahaan.webp')) }}",
    "{{ $urlRangka->asset($mixRangka('/images/Ikon Aplikasi 192.png')) }}",
    "{{ $urlRangka->asset($mixRangka('/images/Ikon Aplikasi 512.png')) }}",
    "{{ $urlRangka->asset($mixRangka('/images/blank.webp')) }}",
    "{{ $urlRangka->asset($mixRangka('/images/Background Badan.webp')) }}",
    "{{ $urlRangka->asset($mixRangka('/images/Background Kepala.webp')) }}",
    "{{ $urlRangka->asset($mixRangka('/images/Logo Brand.webp')) }}",
    "{{ $urlRangka->asset($mixRangka('/images/Logo ISO.webp')) }}",
    "{{ $urlRangka->asset($mixRangka('/fonts/Roboto400.woff2')) }}",
    "{{ $urlRangka->asset($mixRangka('/fonts/Roboto500.woff2')) }}"
];
var expectedCacheNames = Object.keys(CURRENT_CACHES).map(function (key) {
    return CURRENT_CACHES[key];
});

async function onInstall(event) {
    caches.open(CURRENT_CACHES.prefetch).then(function (cache) {
        offline.forEach(function (aset) {
            cache.add(new Request(aset), { cache: 'reload', credentials: 'include' });
        });
    }).catch(function (error) {
        console.error('Gagal Mengambil Aset', error);
    });
};

async function onActivate(event) {
    caches.keys().then(function (cacheNames) {
        return Promise.all(
            cacheNames.map(function (cacheName) {
                if (expectedCacheNames.indexOf(cacheName) === -1) {
                    return caches.delete(cacheName);
                }
            })
        );
    });
};

self.addEventListener('install', function (event) {
    self.skipWaiting();
    event.waitUntil(onInstall(event));
});

self.addEventListener('activate', function (event) {
    event.waitUntil(onActivate(event));
});

self.addEventListener('fetch', function (event) {
    event.respondWith(
        (async () => {
            try {
                const cachedResponse = await caches.match(event.request, { /* ignoreSearch: true, */ ignoreVary: true });
                if (cachedResponse) return cachedResponse;

                const networkResponse = await fetch(event.request);
                return networkResponse;
            } catch (error) {
                const cache = await caches.open(CURRENT_CACHES.prefetch);
                const cachedResponse = await cache.match("offline");
                return cachedResponse;
            }
        })()
    )
});