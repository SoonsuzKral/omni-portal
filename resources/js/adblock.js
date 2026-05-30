(function() {
    'use strict';

    const AdBlockDetector = {
        debugLog: [],
        score: 0,
        signals: [],
        scriptLoaded: false,

        log(type, message) {
            const entry = `[AdBlock] ${type}: ${message}`;
            console.log(entry);
            this.debugLog.push({ type, message, timestamp: Date.now() });
        },

        addSignal(signal, points = 1) {
            if (!this.signals.includes(signal)) {
                this.signals.push(signal);
                this.score += points;
                this.log('SIGNAL', `+${points} point: ${signal}`);
            }
        },

        resetScoreOnSuccess() {
            if (this.scriptLoaded) {
                this.score = Math.max(0, this.score - 1);
                this.log('RESET', `Script loaded successfully, score reset to: ${this.score}`);
            }
        },

        async detectAdBlock() {
            this.log('START', 'Detection started');
            this.debugLog = [];
            this.score = 0;
            this.signals = [];
            this.scriptLoaded = false;

            this.log('STEP', '=== CHECK 1: Google Script Load ===');
            const scriptResult = await this.checkScriptLoad();
            this.log('RESULT', `Script load: ${scriptResult ? 'SUCCESS' : 'FAILED'}`);

            if (scriptResult) {
                this.scriptLoaded = true;
                this.resetScoreOnSuccess();
            }

            this.log('STEP', '=== CHECK 2: adsbygoogle Global ===');
            const adsenseResult = await this.checkAdsenseExists();
            this.log('RESULT', `adsbygoogle: ${adsenseResult ? 'EXISTS' : 'UNDEFINED'}`);

            if (adsenseResult) {
                this.scriptLoaded = true;
                this.resetScoreOnSuccess();
            }

            this.log('STEP', '=== CHECK 3: Bait Elements Visibility ===');
            const baitResult = await this.checkBaitVisible();
            this.log('RESULT', `Bait elements: ${baitResult ? 'VISIBLE' : 'HIDDEN'}`);

            this.log('FINAL', `Score: ${this.score}, Signals: [${this.signals.join(', ')}]`);

            const isBlocked = this.score >= 2;
            this.log('FINAL', `Result: ${isBlocked ? 'BLOCKED' : 'CLEAN'}`);

            await this.sendDebugToBackend({
                scriptLoaded: scriptResult,
                adsenseExists: adsenseResult,
                baitVisible: baitResult
            }, isBlocked);

            return isBlocked;
        },

        checkScriptLoad() {
            return new Promise((resolve) => {
                if (window.adsbygoogle && window.adsbygoogle.loaded === true) {
                    this.log('INFO', 'adsbygoogle.loaded === true (already loaded)');
                    resolve(true);
                    return;
                }

                if (document.querySelector('script[src*="pagead2.googlesyndication.com/pagead/js/adsbygoogle"]')) {
                    this.log('INFO', 'adsbygoogle script already in DOM');
                    this.scriptLoaded = true;
                    resolve(true);
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
                script.async = true;
                script.crossOrigin = 'anonymous';

                let resolved = false;

                const timeout = setTimeout(() => {
                    if (!resolved) {
                        resolved = true;
                        this.log('IGNORE', 'Timeout after 3s - not a blocker');
                        resolve(true);
                    }
                }, 3000);

                script.onload = () => {
                    clearTimeout(timeout);
                    if (!resolved) {
                        resolved = true;
                        this.log('SUCCESS', 'Google script loaded successfully');
                        resolve(true);
                    }
                };

                script.onerror = (event) => {
                    clearTimeout(timeout);
                    if (!resolved) {
                        resolved = true;

                        if (event && event.isTrusted) {
                            const errorText = event.message || '';

                            if (errorText.includes('ERR_BLOCKED_BY_CLIENT') || 
                                event.type === 'error' && !errorText.includes('CORS')) {
                                this.addSignal('ERR_BLOCKED_BY_CLIENT', 1);
                                this.log('BLOCKER', 'ERR_BLOCKED_BY_CLIENT detected');
                                resolve(false);
                                return;
                            }

                            if (errorText.includes('CORS') || 
                                errorText.includes('net::ERR_FAILED') ||
                                errorText.includes('no-cors')) {
                                this.log('IGNORE', `CORS/Network error - not counting: ${errorText}`);
                                resolve(true);
                                return;
                            }
                        }

                        this.log('IGNORE', 'Script error but not ERR_BLOCKED_BY_CLIENT');
                        resolve(true);
                    }
                };

                document.head.appendChild(script);
            });
        },

        checkAdsenseExists() {
            return new Promise((resolve) => {
                const hasAdsense = typeof window.adsbygoogle !== 'undefined';

                if (!hasAdsense) {
                    this.addSignal('adsbygoogle_undefined', 1);
                    this.log('BLOCKER', 'adsbygoogle is undefined');
                    resolve(false);
                    return;
                }

                if (window.adsbygoogle && window.adsbygoogle.pauseAdRequests === 1) {
                    this.addSignal('pauseAdRequests_active', 1);
                    this.log('BLOCKER', 'pauseAdRequests === 1 (AdBlock active)');
                    resolve(false);
                    return;
                }

                this.log('INFO', 'adsbygoogle exists and ready');
                resolve(true);
            });
        },

        checkBaitVisible() {
            return new Promise((resolve) => {
                const container = document.createElement('div');
                container.id = 'adblock-bait-container';
                container.style.cssText = 'position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;';
                container.innerHTML = `
                    <div class="adsbygoogle" style="width:1px;height:1px;display:block;position:absolute;top:0;left:0;"></div>
                    <ins class="adsbygoogle" style="display:block;width:1px;height:1px;position:absolute;top:0;left:0;"></ins>
                    <div class="ad-banner" style="height:1px;display:block;position:absolute;top:0;left:0;"></div>
                    <div class="ad-container" style="height:1px;display:block;position:absolute;top:0;left:0;"></div>
                    <div class="advertisement" style="height:1px;display:block;position:absolute;top:0;left:0;"></div>
                `;
                document.body.appendChild(container);

                setTimeout(() => {
                    let hiddenCount = 0;
                    let totalCount = 0;

                    const elements = container.querySelectorAll('.adsbygoogle, .ad-banner, .ad-container, .advertisement');

                    elements.forEach(el => {
                        totalCount++;
                        const rect = el.getBoundingClientRect();
                        const style = window.getComputedStyle(el);
                        const isHidden = style.display === 'none' ||
                                         style.visibility === 'hidden' ||
                                         rect.width === 0 ||
                                         rect.height === 0;

                        if (isHidden) {
                            hiddenCount++;
                            this.log('CHECK', `Hidden: ${el.className || el.tagName}`);
                        }
                    });

                    container.remove();

                    if (hiddenCount >= 3) {
                        this.addSignal('bait_hidden', 1);
                        this.log('BLOCKER', `${hiddenCount}/${totalCount} bait elements hidden`);
                        resolve(false);
                    } else {
                        this.log('INFO', `${totalCount - hiddenCount}/${totalCount} bait elements visible`);
                        resolve(true);
                    }
                }, 100);
            });
        },

        async sendDebugToBackend(checks, isBlocked) {
            try {
                const payload = {
                    score: this.score,
                    signals: this.signals,
                    result: isBlocked ? 'BLOCKED' : 'CLEAN',
                    checks: checks,
                    logs: this.debugLog,
                    user_agent: navigator.userAgent,
                    url: window.location.href,
                    adBlockDetected: isBlocked
                };

                const response = await fetch('/adblock/debug', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                this.log('BACKEND', `Sent to Laravel: score=${this.score}, result=${isBlocked ? 'BLOCKED' : 'CLEAN'}`);
            } catch (e) {
                this.log('ERROR', `Backend send failed: ${e.message}`);
            }
        },

        async init() {
            const isDetected = await this.detectAdBlock();

            window.adBlockDetected = isDetected;
            window.adBlockScore = this.score;
            window.detectAdBlock = () => this.detectAdBlock();

            if (isDetected) {
                this.showModal();
            }
        },

        showModal() {
            if (document.getElementById('adblock-modal-overlay')) return;

            const overlay = document.createElement('div');
            overlay.id = 'adblock-modal-overlay';
            overlay.innerHTML = `
                <div class="adblock-modal-wrapper">
                    <div class="adblock-modal">
                        <div class="adblock-modal-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="adblock-modal-title">AdBlock tespit edildi</h2>
                        <p class="adblock-modal-desc">Web sitemiz reklamlarla ücretsiz içerik sunmaktadır. Reklamları görebilmek için lütfen AdBlock'u kapatın ve sayfayı yenileyin.</p>
                        <div class="adblock-modal-actions">
                            <button id="adblock-recheck" class="adblock-btn adblock-btn-secondary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Tekrar kontrol et
                            </button>
                            <button id="adblock-refresh" class="adblock-btn adblock-btn-primary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Sayfayı yenile
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';

            document.getElementById('adblock-recheck').addEventListener('click', async () => {
                const btn = document.getElementById('adblock-recheck');
                btn.disabled = true;
                btn.innerHTML = '<span class="adblock-spinner"></span> Kontrol ediliyor...';

                const result = await this.detectAdBlock();

                if (!result) {
                    document.getElementById('adblock-modal-overlay').remove();
                    window.adBlockDetected = false;
                    window.location.reload();
                } else {
                    btn.disabled = false;
                    btn.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Tekrar kontrol et
                    `;
                }
            });

            document.getElementById('adblock-refresh').addEventListener('click', () => {
                window.location.reload();
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => AdBlockDetector.init());
    } else {
        AdBlockDetector.init();
    }

    window.AdBlockDetector = AdBlockDetector;
})();