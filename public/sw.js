/*
 * El trabajador de fondo de la aplicacion instalada (T046). Hace una sola cosa: si se pierde
 * la senal al abrir una pantalla, muestra una pagina en espanol que lo explica, en vez del
 * error del navegador.
 *
 * No guarda pantallas ni datos. Lo de la congregacion no se queda en el telefono (la
 * constitucion: los datos no salen del servidor acordado), y una version vieja no puede
 * quedarse pegada despues de un despliegue (RG-06). InstallableAppTest lo vigila.
 */
const CACHE = 'comites-ipuc-v1';
const SIN_SENAL = '/offline.html';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE)
            .then((cache) => cache.addAll([SIN_SENAL, '/img/logo-ipuc.webp']))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((nombres) => Promise.all(nombres.filter((nombre) => nombre !== CACHE).map((nombre) => caches.delete(nombre))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.mode !== 'navigate') {
        return;
    }

    event.respondWith(fetch(event.request).catch(() => caches.match(SIN_SENAL)));
});
