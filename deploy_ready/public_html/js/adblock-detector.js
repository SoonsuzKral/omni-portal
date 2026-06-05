(function() {
    const testScripts = [
        '/js/ads.js',
        '/ads/ad.js',
        '/advertisement.js',
        '/adserver.js'
    ];

    let adBlockDetected = false;

    function checkAdBlock() {
        if (window.adBlockDetected) return;

        const testElements = document.querySelectorAll('.ad-default-hidden, [id*="google_ads"], ins.adsbygoogle');
        
        testElements.forEach(function(el) {
            const styles = window.getComputedStyle(el);
            if (styles.display === 'none' || styles.visibility === 'hidden' || el.offsetParent === null) {
                adBlockDetected = true;
                window.adBlockDetected = true;
            }
        });

        const testAd = document.createElement('div');
        testAd.style.position = 'absolute';
        testAd.style.left = '-9999px';
        testAd.className = 'pub_300x250m pub_300x250m_2';
        document.body.appendChild(testAd);
        
        if (testAd.offsetHeight === 0 || testAd.offsetParent === null) {
            adBlockDetected = true;
            window.adBlockDetected = true;
        }
        
        testAd.remove();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkAdBlock);
    } else {
        checkAdBlock();
    }
})();