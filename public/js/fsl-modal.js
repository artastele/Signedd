/**
 * SignED — FSL (Filipino Sign Language) Vocabulary Pop-up Engine
 * Wide Kid & SPED-Friendly Theater Modal with Slow-Mo & Speed Controls
 */

(function () {
    'use strict';

    let currentSpeed = 1.0;

    // Inject / Ensure Wider FSL Modal is present in DOM
    function ensureFSLModal() {
        let existing = document.getElementById('fslSignModal');
        if (existing) {
            // If old version without speed controls, replace it
            if (!document.getElementById('fslSpeedControls')) {
                existing.remove();
            } else {
                return;
            }
        }

        const modalHtml = `
        <div class="modal fade" id="fslSignModal" tabindex="-1" aria-labelledby="fslModalTitle" aria-hidden="true" style="z-index: 1090;">
            <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 840px; width: 94%;">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    <!-- Header -->
                    <div class="modal-header px-4 py-3 text-white" style="background: linear-gradient(135deg, #1e4072 0%, #a01422 100%);">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 0.85rem;">
                                <i class="ph-bold ph-hand-waving me-1" aria-hidden="true"></i> Tunay na Senyas (FSL)
                            </span>
                            <h4 class="modal-title fw-semibold mb-0 text-capitalize text-white" id="fslModalTitle" style="font-size: 1.25rem;">
                                Salita
                            </h4>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Isara"></button>
                    </div>

                    <!-- Body (Wide Theater Style) -->
                    <div class="modal-body p-4 text-center bg-light">
                        
                        <!-- Video / Media Canvas -->
                        <div id="fslMediaContainer" class="p-2 bg-dark rounded-4 shadow-sm mb-3 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="min-height: 380px; max-height: 520px; background: #0f172a;">
                            <div class="spinner-border text-primary" role="status" id="fslLoader">
                                <span class="visually-hidden">Kinukuha ang video...</span>
                            </div>
                            <img id="fslImage" src="" alt="FSL Sign" class="img-fluid d-none rounded-3" style="max-height: 440px; object-fit: contain;">
                            <video id="fslVideo" src="" class="img-fluid d-none rounded-3 w-100 shadow-sm" controls autoplay loop muted playsinline style="max-height: 480px; height: 100%; object-fit: contain; background: #0f172a;"></video>
                        </div>
                        
                        <!-- Slow-Mo & Playback Speed Controls (Clean, Functional, Tactile) -->
                        <div id="fslSpeedControls" class="d-flex align-items-center justify-content-center gap-2 mb-3 flex-wrap p-2 bg-white rounded-3 border shadow-sm">
                            <span class="text-muted me-2" style="font-size: 0.875rem; font-weight: 500;">
                                <i class="ph-bold ph-gauge text-primary me-1" aria-hidden="true"></i> Bilis ng Senyas:
                            </span>
                            <button type="button" class="btn btn-outline-primary px-3 py-1 fsl-speed-btn" style="border-radius: 8px; font-size: 0.875rem; font-weight: 500; height: 34px;" data-speed="0.5" onclick="window.setFSLSpeed(0.5, this)">
                                0.5x Dahan-dahan
                            </button>
                            <button type="button" class="btn btn-outline-primary px-3 py-1 fsl-speed-btn" style="border-radius: 8px; font-size: 0.875rem; font-weight: 500; height: 34px;" data-speed="0.75" onclick="window.setFSLSpeed(0.75, this)">
                                0.75x Katamtaman
                            </button>
                            <button type="button" class="btn btn-primary px-3 py-1 fsl-speed-btn active" style="border-radius: 8px; font-size: 0.875rem; font-weight: 600; height: 34px;" data-speed="1.0" onclick="window.setFSLSpeed(1.0, this)">
                                1.0x Normal
                            </button>
                            <button type="button" class="btn btn-light border px-3 py-1 text-dark ms-md-2 d-flex align-items-center gap-1" style="border-radius: 8px; font-size: 0.875rem; font-weight: 500; height: 34px;" title="Ulitin ang video mula simula" onclick="window.replayFSLVideo()">
                                <i class="ph-bold ph-arrow-counter-clockwise text-primary" aria-hidden="true"></i>
                                <span>Ulitin</span>
                            </button>
                        </div>

                        <!-- Category & Description -->
                        <div class="text-start p-3 bg-white rounded-3 border shadow-sm">
                            <span class="badge bg-primary-subtle text-primary text-uppercase mb-1" id="fslCategory" style="font-size: 0.8rem; font-weight: 600;">
                                KATEGORYA
                            </span>
                            <p class="mb-0 text-dark" id="fslDescription" style="font-size: 1.05rem; font-weight: 600; line-height: 1.4;">
                                Kahulugan ng senyas.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer bg-light px-4 py-3 justify-content-between">
                        <span class="text-muted small">
                            <i class="ph-bold ph-video-camera text-primary me-1" aria-hidden="true"></i>Tunay na Filipino Sign Language (FSL) Video
                        </span>
                        <button type="button" class="btn btn-secondary px-4 py-1.5" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 0.875rem; font-weight: 500; height: 36px;">
                            Isara
                        </button>
                    </div>

                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modalEl = document.getElementById('fslSignModal');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                const videoEl = document.getElementById('fslVideo');
                if (videoEl) {
                    videoEl.pause();
                    videoEl.removeAttribute('src');
                    while (videoEl.firstChild) {
                        videoEl.removeChild(videoEl.firstChild);
                    }
                }
            });
        }
    }

    // Set video playback rate
    window.setFSLSpeed = function (speed, btnElement) {
        currentSpeed = speed;
        const videoEl = document.getElementById('fslVideo');
        if (videoEl) {
            videoEl.playbackRate = speed;
        }

        // Update button active styles
        const speedBtns = document.querySelectorAll('.fsl-speed-btn');
        speedBtns.forEach(btn => {
            if (btn === btnElement || parseFloat(btn.dataset.speed) === speed) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary', 'active');
            } else {
                btn.classList.remove('btn-primary', 'active');
                btn.classList.add('btn-outline-primary');
            }
        });
    };

    // Replay video from start
    window.replayFSLVideo = function () {
        const videoEl = document.getElementById('fslVideo');
        if (videoEl) {
            videoEl.currentTime = 0;
            videoEl.playbackRate = currentSpeed;
            videoEl.play().catch(e => console.log('Replay error:', e));
        }
    };

    // Open FSL Modal for word
    window.openFSLModal = function (word, customData = {}) {
        ensureFSLModal();
        const modalEl = document.getElementById('fslSignModal');
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);

        const titleEl = document.getElementById('fslModalTitle');
        const descEl = document.getElementById('fslDescription');
        const catEl = document.getElementById('fslCategory');
        const loader = document.getElementById('fslLoader');
        const imgEl = document.getElementById('fslImage');
        const videoEl = document.getElementById('fslVideo');

        if (customData.displayText && customData.displayText.toLowerCase() !== word.toLowerCase()) {
            titleEl.innerHTML = `${escapeModalHtml(customData.displayText)} <span class="badge bg-warning text-dark ms-2 fw-normal" style="font-size: 0.8rem; vertical-align: middle;">FSL: ${escapeModalHtml(word)}</span>`;
        } else {
            titleEl.textContent = word;
        }

        loader.classList.remove('d-none');
        imgEl.classList.add('d-none');
        videoEl.classList.add('d-none');
        bsModal.show();

        if (customData.gif || customData.video || customData.desc) {
            catEl.textContent = customData.category || 'General';
            descEl.textContent = customData.desc || ('FSL Sign senyas para sa ' + word);
            displayMedia(customData.gif, customData.video, word);
            return;
        }

        // Fetch from API
        const basePath = window.BASE_PATH || '';
        fetch(`${basePath}/api/fsl/lookup?word=${encodeURIComponent(word)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    catEl.textContent = data.category || 'General';
                    descEl.textContent = data.description || 'FSL Sign demonstration for ' + word;
                    displayMedia(data.gif_path, data.video_path, word);
                } else {
                    loader.classList.add('d-none');
                    descEl.textContent = `Sign media for "${word}" is currently being prepared by SPED educators.`;
                    catEl.textContent = 'Learning Aid';
                    imgEl.src = `${basePath}/images/fsl/default_sign.svg`;
                    imgEl.classList.remove('d-none');
                }
            })
            .catch(err => {
                console.error('Error fetching FSL sign:', err);
                loader.classList.add('d-none');
                descEl.textContent = 'Unable to load FSL sign media at this time.';
            });
    };

    function escapeModalHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function resolveMediaUrl(path) {
        if (!path) return '';
        const trimmed = String(path).trim();
        if (trimmed.startsWith('http://') || trimmed.startsWith('https://') || trimmed.startsWith('data:')) {
            return trimmed;
        }

        const bp = (window.BASE_PATH || '').replace(/\/+$/, '');

        // If path already starts with bp + '/' or is exactly bp
        if (bp && (trimmed.startsWith(bp + '/') || trimmed === bp)) {
            return trimmed;
        }

        // Clean leading slashes
        const clean = trimmed.replace(/^\/+/, '');

        // Check if clean starts with bp without slash (e.g. 'Signedd/public/...')
        const bpNoSlash = bp.replace(/^\/+/, '');
        if (bpNoSlash && (clean.startsWith(bpNoSlash + '/') || clean === bpNoSlash)) {
            return '/' + clean;
        }

        return bp ? (bp + '/' + clean) : ('/' + clean);
    }

    function displayMedia(gifPath, videoPath, word) {
        const loader = document.getElementById('fslLoader');
        const imgEl = document.getElementById('fslImage');
        const videoEl = document.getElementById('fslVideo');
        const speedControls = document.getElementById('fslSpeedControls');

        loader.classList.add('d-none');

        if (videoPath) {
            const fullVideoUrl = resolveMediaUrl(videoPath);

            imgEl.classList.add('d-none');

            // Reset any previous video state
            videoEl.pause();
            videoEl.removeAttribute('src');
            while (videoEl.firstChild) {
                videoEl.removeChild(videoEl.firstChild);
            }

            // Create video sources for robust compatibility across browsers
            const sourceMp4 = document.createElement('source');
            sourceMp4.src = fullVideoUrl;
            sourceMp4.type = 'video/mp4';
            videoEl.appendChild(sourceMp4);

            const sourceMov = document.createElement('source');
            sourceMov.src = fullVideoUrl;
            sourceMov.type = 'video/quicktime';
            videoEl.appendChild(sourceMov);

            // Also set src directly on video element
            videoEl.src = fullVideoUrl;
            videoEl.muted = true; // Required by modern browsers for autoplay
            videoEl.classList.remove('d-none');
            if (speedControls) speedControls.classList.remove('d-none');

            videoEl.onloadeddata = function () {
                loader.classList.add('d-none');
            };

            videoEl.onerror = function () {
                console.warn('FSL video failed to load at:', fullVideoUrl);
                videoEl.classList.add('d-none');
                if (speedControls) speedControls.classList.add('d-none');
                imgEl.src = resolveMediaUrl('images/fsl/default_sign.svg');
                imgEl.classList.remove('d-none');
            };

            videoEl.load();
            videoEl.playbackRate = currentSpeed;
            const playPromise = videoEl.play();
            if (playPromise !== undefined) {
                playPromise.catch(e => {
                    console.log('Video autoplay deferred by browser, student can use controls:', e);
                });
            }
        } else if (gifPath) {
            const fullGifUrl = resolveMediaUrl(gifPath);
            videoEl.classList.add('d-none');
            imgEl.src = fullGifUrl;
            imgEl.classList.remove('d-none');
            if (speedControls) speedControls.classList.add('d-none');
        } else {
            // Placeholder animated illustration
            videoEl.classList.add('d-none');
            imgEl.src = resolveMediaUrl('images/fsl/default_sign.svg');
            imgEl.classList.remove('d-none');
            if (speedControls) speedControls.classList.add('d-none');
        }
    }

    // Global Click Delegation
    document.addEventListener('DOMContentLoaded', function () {
        ensureFSLModal();

        document.body.addEventListener('click', function (e) {
            const wordEl = e.target.closest('.fsl-word');
            if (wordEl) {
                e.preventDefault();
                const word = wordEl.dataset.word || wordEl.textContent.trim();
                const displayText = wordEl.textContent.trim();
                window.openFSLModal(word, {
                    video: wordEl.dataset.video,
                    category: wordEl.dataset.category,
                    desc: wordEl.dataset.desc,
                    displayText: displayText
                });
                return;
            }

            const btnEl = e.target.closest('.fsl-trigger-btn');
            if (btnEl) {
                e.preventDefault();
                const word = btnEl.dataset.word || btnEl.textContent.trim();
                window.openFSLModal(word, {
                    category: btnEl.dataset.category,
                    desc: btnEl.dataset.desc,
                    gif: btnEl.dataset.gif,
                    video: btnEl.dataset.video
                });
            }
        });
    });
})();
