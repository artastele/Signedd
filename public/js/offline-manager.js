/**
 * SignED — Offline Lesson Manager
 * Manages Service Worker registration, offline lesson manifest downloads,
 * storage management, and network state indicators.
 */

(function () {
    'use strict';

    const bp = (window.BASE_PATH || '').replace(/\/+$/, '');

    function getActiveLearnerId() {
        if (window.CURRENT_USER_ID && window.CURRENT_USER_ID > 0) {
            const uidStr = String(window.CURRENT_USER_ID);
            localStorage.setItem('signed_active_learner_id', uidStr);
            return uidStr;
        }
        return localStorage.getItem('signed_active_learner_id') || '';
    }

    // ── 1. Register Service Worker ──────────────────────────────
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            const swUrl = bp ? (bp + '/sw.js') : '/sw.js';
            navigator.serviceWorker.register(swUrl, { scope: bp ? (bp + '/') : '/' })
                .then(reg => {
                    console.log('[SignED Offline] Service Worker registered with scope:', reg.scope);
                })
                .catch(err => {
                    console.warn('[SignED Offline] Service Worker registration failed:', err);
                });
        });
    }

    // ── 2. Network State Monitor ────────────────────────────────
    function updateNetworkPill() {
        const isOnline = navigator.onLine;
        const pills = document.querySelectorAll('.signed-network-indicator');
        pills.forEach(pill => {
            if (isOnline) {
                pill.className = 'signed-network-indicator badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold';
                pill.innerHTML = '<i class="ph-bold ph-wifi-high me-1"></i> Online';
            } else {
                pill.className = 'signed-network-indicator badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill fw-semibold';
                pill.innerHTML = '<i class="ph-bold ph-wifi-slash me-1"></i> Offline Mode';
            }
        });
    }

    window.addEventListener('online', updateNetworkPill);
    window.addEventListener('offline', updateNetworkPill);

    // ── 3. Storage & Cache Utilities ────────────────────────────
    function getStorageKey(learnerId, lessonId) {
        return `signed_offline_lesson_${learnerId}_${lessonId}`;
    }

    function getCatalogKey(learnerId) {
        return `signed_offline_catalog_${learnerId}`;
    }

    function isLessonDownloaded(learnerId, lessonId) {
        return !!localStorage.getItem(getStorageKey(learnerId, lessonId));
    }

    function saveToCatalog(learnerId, lessonMeta) {
        const key = getCatalogKey(learnerId);
        let list = [];
        try {
            list = JSON.parse(localStorage.getItem(key)) || [];
        } catch (e) {}
        // Remove existing duplicate
        list = list.filter(item => item.id !== lessonMeta.id);
        list.push({
            id: lessonMeta.id,
            title: lessonMeta.title,
            domain: lessonMeta.domain,
            total_pages: lessonMeta.total_pages,
            downloaded_at: new Date().toISOString()
        });
        localStorage.setItem(key, JSON.stringify(list));
    }

    function removeFromCatalog(learnerId, lessonId) {
        const key = getCatalogKey(learnerId);
        let list = [];
        try {
            list = JSON.parse(localStorage.getItem(key)) || [];
        } catch (e) {}
        list = list.filter(item => item.id !== lessonId);
        localStorage.setItem(key, JSON.stringify(list));
    }

    // ── 4. Offline Package Download Handler ──────────────────────
    window.downloadOfflineLesson = async function (lessonId, btnElement) {
        if (!navigator.onLine) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Walang Internet',
                    text: 'Kailangan ng koneksyon sa internet upang mai-download ang aralin.',
                    confirmButtonColor: '#1e4072'
                });
            } else {
                alert('Kailangan ng koneksyon sa internet upang mai-download ang aralin.');
            }
            return;
        }

        const originalHtml = btnElement ? btnElement.innerHTML : '';
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Kinukuha ang listahan...';
        }

        try {
            const manifestUrl = (bp ? bp : '') + '/learning/lesson/' + lessonId + '/offline-manifest';
            const resp = await fetch(manifestUrl);

            if (!resp.ok) {
                if (resp.status === 403) {
                    throw new Error('Access denied. Hindi nakatalaga sa iyo ang araling ito.');
                }
                throw new Error('Hindi nakuha ang manifest ng aralin. (HTTP ' + resp.status + ')');
            }

            const data = await resp.json();
            if (!data.success || !data.lesson) {
                throw new Error(data.message || 'May error sa manifest.');
            }

            const learnerId = data.learner_id || getActiveLearnerId();
            const assets = data.assets || [];

            if (btnElement) {
                btnElement.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Dina-download (${data.estimated_size || '...'})`;
            }

            // Post to Service Worker to cache assets
            if (navigator.serviceWorker && navigator.serviceWorker.controller) {
                const messageChannel = new MessageChannel();

                messageChannel.port1.onmessage = function (event) {
                    const msg = event.data;
                    if (msg.type === 'CACHE_PROGRESS' && btnElement) {
                        const pct = Math.round((msg.completed / msg.total) * 100);
                        btnElement.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> ${pct}% (${msg.completed}/${msg.total})`;
                    } else if (msg.type === 'CACHE_COMPLETE') {
                        if (msg.success) {
                            // Persist lesson metadata to localStorage
                            localStorage.setItem(getStorageKey(learnerId, lessonId), JSON.stringify(data.lesson));
                            saveToCatalog(learnerId, data.lesson);

                            // Update UI
                            updateOfflineWidget(lessonId, true);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: '✓ Handa na Nang Offline!',
                                    html: `<p class="mb-1">Matagumpay na na-download ang <strong>${data.lesson.title}</strong>.</p><small class="text-muted">Maaari mo na itong basahin at panoorin ang mga FSL sign kahit walang internet.</small>`,
                                    confirmButtonColor: '#3b6d11'
                                });
                            }
                        } else {
                            throw new Error(msg.error || 'Nabigo ang pag-cache.');
                        }
                    }
                };

                navigator.serviceWorker.controller.postMessage({
                    type: 'CACHE_OFFLINE_LESSON',
                    learnerId: learnerId,
                    lessonId: lessonId,
                    urls: assets
                }, [messageChannel.port2]);

            } else {
                // Fallback direct cache storage API if controller not yet claimed
                const cacheName = `signed-offline-lesson-${learnerId}-${lessonId}-v1`;
                const cache = await caches.open(cacheName);
                for (const u of assets) {
                    try {
                        const res = await fetch(u, { cache: 'no-cache' });
                        if (res.ok || res.type === 'opaque') {
                            await cache.put(u, res);
                        }
                    } catch (e) {
                        console.warn('[SignED Offline] Direct cache skip:', u);
                    }
                }
                localStorage.setItem(getStorageKey(learnerId, lessonId), JSON.stringify(data.lesson));
                saveToCatalog(learnerId, data.lesson);
                updateOfflineWidget(lessonId, true);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '✓ Handa na Nang Offline!',
                        text: 'Maaari mo na itong basahin nang walang internet.',
                        confirmButtonColor: '#3b6d11'
                    });
                }
            }

        } catch (err) {
            console.error('[SignED Offline] Download failed:', err);
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalHtml;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Hindi Na-download',
                    text: err.message || 'Nagkaroon ng problema habang dina-download ang aralin.',
                    confirmButtonColor: '#a01422'
                });
            } else {
                alert('Hindi na-download: ' + err.message);
            }
        }
    };

    // ── 5. Remove Offline Package Handler ────────────────────────
    window.removeOfflineLesson = async function (lessonId) {
        const learnerId = getActiveLearnerId();

        const confirmRemove = async () => {
            try {
                if (navigator.serviceWorker && navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage({
                        type: 'DELETE_OFFLINE_LESSON',
                        learnerId: learnerId,
                        lessonId: lessonId
                    });
                } else {
                    const cacheName = `signed-offline-lesson-${learnerId}-${lessonId}-v1`;
                    await caches.delete(cacheName);
                }

                localStorage.removeItem(getStorageKey(learnerId, lessonId));
                removeFromCatalog(learnerId, lessonId);

                updateOfflineWidget(lessonId, false);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tinanggal na ang Kopya',
                        text: 'Tinanggal na ang offline na kopya ng araling ito mula sa iyong device.',
                        confirmButtonColor: '#1e4072'
                    });
                }
            } catch (err) {
                console.error('[SignED Offline] Delete failed:', err);
            }
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Tanggalin ang Offline na Kopya?',
                text: 'Kailangan mo muling mag-download kung nais mong basahin ito nang walang internet.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oo, Tanggalin',
                cancelButtonText: 'Kanselahin',
                confirmButtonColor: '#a01422',
                cancelButtonColor: '#64748b'
            }).then((res) => {
                if (res.isConfirmed) confirmRemove();
            });
        } else {
            if (confirm('Tanggalin ang offline na kopya?')) confirmRemove();
        }
    };

    // ── 6. UI Widget Updater ─────────────────────────────────────
    function updateOfflineWidget(lessonId, isDownloaded) {
        const container = document.getElementById('offlineLessonWidget');
        if (!container) return;

        if (isDownloaded) {
            container.innerHTML = `
                <div class="d-inline-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" style="border-radius: 8px; font-size: 0.85rem;">
                        <i class="ph-bold ph-check-circle"></i> Available Offline
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" onclick="window.removeOfflineLesson(${lessonId})" title="Tanggalin ang offline copy" style="border-radius: 8px; font-size: 0.8125rem; background: #ffffff;">
                        <i class="ph-bold ph-trash"></i> Remove Download
                    </button>
                </div>
            `;
        } else {
            container.innerHTML = `
                <button type="button" class="btn btn-sm btn-outline-primary px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnDownloadOffline" onclick="window.downloadOfflineLesson(${lessonId}, this)" style="background:#ffffff; border-radius: 8px; font-size: 0.875rem;">
                    <i class="ph-bold ph-download-simple"></i> Download for Offline
                </button>
            `;
        }
    }

    // ── 7. Initialize on Page Load ──────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        updateNetworkPill();

        const currentLearner = getActiveLearnerId();

        // Check if currently on a lesson page
        const pathMatch = window.location.pathname.match(/\/learning\/lesson\/(\d+)/);
        if (pathMatch) {
            const lessonId = parseInt(pathMatch[1], 10);
            const downloaded = isLessonDownloaded(currentLearner, lessonId);
            updateOfflineWidget(lessonId, downloaded);

            // If offline, disable activity tabs with explanation
            if (!navigator.onLine) {
                const actTab = document.getElementById('vleTabActivities');
                if (actTab) {
                    actTab.title = 'Kailangan ng internet para sa mga pagsasanay';
                }
            }
        }

        // On learner dashboard, populate offline badges on cards
        const cardSlots = document.querySelectorAll('.offline-badge-slot');
        cardSlots.forEach(slot => {
            const lid = parseInt(slot.dataset.lessonId, 10);
            if (lid && isLessonDownloaded(currentLearner, lid)) {
                slot.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="border-radius: 6px; font-size: 0.72rem; font-weight: 600;"><i class="ph-bold ph-download-simple me-0.5"></i> Available Offline</span>`;
            }
        });
    });

})();
