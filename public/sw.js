/**
 * SignED Service Worker — Offline Read-Only Lesson MVP
 * Provides scoped, user-isolated offline caching for assigned lessons and FSL media.
 */

const STATIC_CACHE = 'signed-static-v1';

self.addEventListener('install', (event) => {
    // Activate worker immediately
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    // Claim active clients immediately
    event.waitUntil(self.clients.claim());
});

// ── Message Event Handlers (Cache / Delete / Check) ──────────
self.addEventListener('message', (event) => {
    const data = event.data;
    if (!data || !data.type) return;

    if (data.type === 'CACHE_OFFLINE_LESSON') {
        const { learnerId, lessonId, urls } = data;
        const cacheName = `signed-offline-lesson-${learnerId}-${lessonId}-v1`;

        event.waitUntil(
            (async () => {
                try {
                    const cache = await caches.open(cacheName);
                    let completed = 0;
                    const total = urls.length;

                    for (const url of urls) {
                        try {
                            const response = await fetch(url, { cache: 'no-cache' });
                            if (response.ok || response.type === 'opaque') {
                                await cache.put(url, response);
                            }
                        } catch (err) {
                            console.warn('[SignED SW] Skipped asset:', url, err);
                        }
                        completed++;
                        if (event.source) {
                            event.source.postMessage({
                                type: 'CACHE_PROGRESS',
                                lessonId,
                                completed,
                                total
                            });
                        }
                    }

                    if (event.source) {
                        event.source.postMessage({
                            type: 'CACHE_COMPLETE',
                            lessonId,
                            success: true
                        });
                    }
                } catch (err) {
                    console.error('[SignED SW] Caching error:', err);
                    if (event.source) {
                        event.source.postMessage({
                            type: 'CACHE_COMPLETE',
                            lessonId,
                            success: false,
                            error: err.message
                        });
                    }
                }
            })()
        );
    } else if (data.type === 'DELETE_OFFLINE_LESSON') {
        const { learnerId, lessonId } = data;
        const cacheName = `signed-offline-lesson-${learnerId}-${lessonId}-v1`;
        event.waitUntil(
            (async () => {
                await caches.delete(cacheName);
                if (event.source) {
                    event.source.postMessage({
                        type: 'DELETE_COMPLETE',
                        lessonId,
                        success: true
                    });
                }
            })()
        );
    }
});

// ── Fetch Event Handler ──────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Never intercept non-GET requests (e.g. POST progress, POST submissions, etc.)
    if (req.method !== 'GET') {
        return;
    }

    const url = new URL(req.url);

    // 1. Navigation requests (Opening a lesson page while offline)
    if (req.mode === 'navigate' || req.destination === 'document') {
        // Match lesson viewer routes: /learning/lesson/{id}
        const lessonMatch = url.pathname.match(/\/learning\/lesson\/(\d+)/);
        if (lessonMatch) {
            const lessonId = lessonMatch[1];
            event.respondWith(
                (async () => {
                    try {
                        // Attempt online fetch first
                        const networkResp = await fetch(req);
                        return networkResp;
                    } catch (netError) {
                        // Offline fallback: find scoped offline cache
                        const cacheNames = await caches.keys();
                        const lessonCacheName = cacheNames.find(c => c.includes(`-lesson-`) && c.includes(`-${lessonId}-`));

                        if (lessonCacheName) {
                            const cache = await caches.open(lessonCacheName);
                            const allCached = await cache.keys();
                            for (const k of allCached) {
                                if (k.url.includes('offline-reader.html')) {
                                    return await cache.match(k);
                                }
                            }
                        }

                        // Generic offline fallback
                        return new Response(
                            `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Offline - SignED</title><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="font-family:sans-serif;text-align:center;padding:50px 20px;background:#f8fafc;"><div style="max-width:500px;margin:auto;background:white;padding:30px;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);"><h2 style="color:#1e4072;">Walang Koneksyon sa Internet</h2><p style="color:#64748b;">Hindi pa nai-download ang araling ito para magamit nang offline.</p><a href="javascript:history.back()" style="display:inline-block;margin-top:15px;padding:10px 20px;background:#1e4072;color:white;text-decoration:none;border-radius:8px;font-weight:600;">Bumalik</a></div></body></html>`,
                            { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                        );
                    }
                })()
            );
            return;
        }
        return;
    }

    // 2. Static and media assets (images, FSL videos, fonts, scripts, styles)
    event.respondWith(
        (async () => {
            // First check if asset is in any of our lesson caches
            const cacheNames = await caches.keys();
            for (const cName of cacheNames) {
                if (cName.startsWith('signed-offline-lesson-') || cName === STATIC_CACHE) {
                    const cache = await caches.open(cName);
                    const matched = await cache.match(req, { ignoreSearch: true });
                    if (matched) {
                        return matched;
                    }
                }
            }

            // Otherwise fetch from network
            try {
                return await fetch(req);
            } catch (err) {
                // Return fallback for missing images if offline
                if (req.destination === 'image') {
                    for (const cName of cacheNames) {
                        const cache = await caches.open(cName);
                        const allKeys = await cache.keys();
                        for (const k of allKeys) {
                            if (k.url.includes('default_sign.svg')) {
                                return await cache.match(k);
                            }
                        }
                    }
                }
                throw err;
            }
        })()
    );
});
