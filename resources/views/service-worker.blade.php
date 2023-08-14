var CACHE_VERSION = "{{ filemtime($app->publicPath('mix-manifest.json')) }}";
var CURRENT_CACHES = {
prefetch: "{{ $app->config->get('app.name', 'Laravel') }}-cache-v" + CACHE_VERSION
};
var offline = [
"{{ $app->request->getBasePath() . '/' }}",
"tentang-aplikasi",
"perlu-javascript",
"offline",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/tampilan.css')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/interaksi.js')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/slimselect-es.js')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/siapkan-foto-es.js')) }}",
"pwa-manifest.json",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/ikon.svg')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Logo Perusahaan.webp')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Lambang Perusahaan.webp')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 192.png')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Ikon Aplikasi 512.png')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/blank.webp')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Background Badan.webp')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Background Kepala.webp')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Logo Brand.webp')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/images/Logo ISO.webp')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/fonts/Roboto400.woff2')) }}",
"{{ $app->url->asset($app->make('Illuminate\Foundation\Mix')('/fonts/Roboto500.woff2')) }}"
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