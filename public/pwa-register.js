(function () {
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
  const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
  const isSafari = /^((?!chrome|android).)*safari/i.test(window.navigator.userAgent);

  if ('serviceWorker' in navigator && isSecure) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js').catch(function (error) {
        console.warn('PWA service worker gagal didaftarkan:', error);
      });
    });
  }

  let deferredPrompt = null;
  const card = document.querySelector('[data-pwa-install-card]');
  const installButton = document.querySelector('[data-pwa-install-button]');
  const iosGuide = document.querySelector('[data-pwa-ios-guide]');
  const dismissButton = document.querySelector('[data-pwa-dismiss]');
  const dismissedAt = Number(localStorage.getItem('nelayan_pwa_dismissed_at') || 0);
  const dismissedRecently = Date.now() - dismissedAt < 1000 * 60 * 60 * 24 * 7;

  function showCard() {
    if (!card || isStandalone || dismissedRecently) return;
    card.removeAttribute('hidden');
  }

  function hideCard() {
    if (!card) return;
    card.setAttribute('hidden', 'hidden');
  }

  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredPrompt = event;
    showCard();
  });

  if (installButton) {
    installButton.addEventListener('click', async function () {
      if (!deferredPrompt) return;
      installButton.disabled = true;
      installButton.textContent = 'Membuka...';
      deferredPrompt.prompt();
      await deferredPrompt.userChoice.catch(() => null);
      deferredPrompt = null;
      hideCard();
    });
  }

  if (dismissButton) {
    dismissButton.addEventListener('click', function () {
      localStorage.setItem('nelayan_pwa_dismissed_at', String(Date.now()));
      hideCard();
    });
  }

  if (iosGuide && isIos && isSafari && !isStandalone && !dismissedRecently) {
    iosGuide.removeAttribute('hidden');
    setTimeout(showCard, 1200);
  }
})();
