(() => {
  const pageData = document.body ? document.body.dataset : {};
  const copy = {
    play: pageData.copyPlay || 'Play',
    pause: pageData.copyPause || 'Pause',
    thanks: pageData.copyThanks || 'Thanks.'
  };
  const analytics = { gaId: pageData.gaId || '' };
  const consentStorageKey = 'acta_analytics_consent';

  const cookieName = `${consentStorageKey}=`;
  const readConsent = () => {
    try {
      const fromStorage = localStorage.getItem(consentStorageKey);
      if (fromStorage) {
        return fromStorage;
      }
    } catch (_error) {
      // Ignore storage access errors and fallback to cookies.
    }

    const cookies = document.cookie.split(';').map((item) => item.trim());
    for (const cookie of cookies) {
      if (cookie.startsWith(cookieName)) {
        return decodeURIComponent(cookie.slice(cookieName.length));
      }
    }
    return '';
  };

  const writeConsent = (value) => {
    let persisted = false;
    try {
      localStorage.setItem(consentStorageKey, value);
      persisted = true;
    } catch (_error) {
      // Ignore and fallback to cookies.
    }

    try {
      document.cookie = `${consentStorageKey}=${encodeURIComponent(value)}; Max-Age=31536000; Path=/; SameSite=Lax`;
      persisted = true;
    } catch (_error) {
      // Ignore.
    }

    return persisted;
  };

  const loadGaIfConfigured = () => {
    if (!analytics.gaId || window.gtag) {
      return;
    }

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(analytics.gaId)}`;
    document.head.appendChild(script);

    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag() {
      window.dataLayer.push(arguments);
    };
    window.gtag('js', new Date());
    window.gtag('config', analytics.gaId);
  };

  const syncConsentWithServer = async (value) => {
    try {
      await fetch('/api/public/v1/cookie-consent', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ consent: value }),
        keepalive: true
      });
    } catch (_error) {
      // Ignore; link fallback still works without JS.
    }
  };

  const consentBanner = document.getElementById('consent-banner');
  const consentValue = readConsent();
  if (consentValue === 'granted') {
    loadGaIfConfigured();
  }

  if (consentBanner && consentValue !== 'granted' && consentValue !== 'denied') {
    consentBanner.hidden = false;

    const applyConsent = (value) => {
      writeConsent(value);
      syncConsentWithServer(value);
      consentBanner.hidden = true;
      if (value === 'granted') {
        loadGaIfConfigured();
      }
    };

    document.addEventListener('click', (event) => {
      const origin = event.target instanceof Element ? event.target : null;
      if (!origin) {
        return;
      }

      const target = origin.closest('[data-consent]');
      if (!target || !consentBanner.contains(target)) {
        return;
      }

      const mode = target.getAttribute('data-consent');
      if (mode !== 'accept' && mode !== 'reject') {
        return;
      }

      event.preventDefault();
      applyConsent(mode === 'accept' ? 'granted' : 'denied');
    });
  }

  const carouselRoot = document.querySelector('[data-carousel]');
  if (carouselRoot) {
    const track = carouselRoot.querySelector('[data-carousel-track]');
    const cards = Array.from(track ? track.children : []);
    const prevBtn = carouselRoot.querySelector('[data-carousel-prev]');
    const nextBtn = carouselRoot.querySelector('[data-carousel-next]');
    const toggleBtn = carouselRoot.querySelector('[data-carousel-toggle]');
    const toggleIcon = toggleBtn ? toggleBtn.querySelector('[data-carousel-toggle-icon]') : null;

    let index = 0;
    let timer = null;
    const autoplayEnabled = carouselRoot.getAttribute('data-autoplay') !== 'false';
    const configuredInterval = Number(carouselRoot.getAttribute('data-interval') || '');
    const intervalSeconds = Number.isFinite(configuredInterval) && configuredInterval > 0
      ? Math.max(2, Math.min(configuredInterval, 4))
      : 4;
    let paused = !autoplayEnabled;

    if (cards.length <= 1) {
      paused = true;
    }

    const normalizeIndex = (value) => {
      if (cards.length === 0) {
        return 0;
      }
      return ((value % cards.length) + cards.length) % cards.length;
    };

    const relativeDistance = (cardIndex) => {
      let distance = cardIndex - index;
      const half = cards.length / 2;
      if (distance > half) {
        distance -= cards.length;
      } else if (distance < -half) {
        distance += cards.length;
      }
      return distance;
    };

    const updateToggleControl = () => {
      if (!toggleBtn) {
        return;
      }

      const label = paused ? copy.play : copy.pause;
      toggleBtn.setAttribute('aria-label', label);
      toggleBtn.setAttribute('title', label);
      toggleBtn.setAttribute('aria-pressed', paused ? 'true' : 'false');
      if (toggleIcon) {
        toggleIcon.textContent = paused ? '▶' : '❚❚';
      }
    };

    const update = () => {
      if (!track || cards.length === 0) {
        updateToggleControl();
        return;
      }

      cards.forEach((card, cardIndex) => {
        const distance = relativeDistance(cardIndex);
        card.classList.remove('is-active', 'is-prev', 'is-next', 'is-hidden');
        card.setAttribute('aria-hidden', 'true');

        if (distance === 0) {
          card.classList.add('is-active');
          card.style.zIndex = '30';
          card.setAttribute('aria-hidden', 'false');
          return;
        }

        if (Math.abs(distance) === 1) {
          card.classList.add(distance < 0 ? 'is-prev' : 'is-next');
          card.style.zIndex = '20';
          return;
        }

        card.classList.add('is-hidden');
        card.style.zIndex = '10';
      });

      updateToggleControl();
    };

    const next = () => {
      if (cards.length <= 1) {
        return;
      }
      index = normalizeIndex(index + 1);
      update();
    };

    const prev = () => {
      if (cards.length <= 1) {
        return;
      }
      index = normalizeIndex(index - 1);
      update();
    };

    const stop = () => {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    };

    const start = () => {
      stop();
      if (paused || cards.length <= 1) {
        return;
      }
      timer = setInterval(next, intervalSeconds * 1000);
    };

    prevBtn?.addEventListener('click', () => {
      prev();
    });

    nextBtn?.addEventListener('click', () => {
      next();
    });

    toggleBtn?.addEventListener('click', () => {
      paused = !paused;
      update();
      if (paused) {
        stop();
      } else {
        start();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (!carouselRoot) {
        return;
      }

      if (event.target instanceof Element) {
        const tag = event.target.tagName.toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select') {
          return;
        }
      }

      if (event.key === 'ArrowRight') {
        next();
      }
      if (event.key === 'ArrowLeft') {
        prev();
      }
    });

    update();
    start();
  }

  const leadForm = document.getElementById('lead-form');
  if (leadForm) {
    const result = document.getElementById('lead-result');

    leadForm.addEventListener('submit', async (event) => {
      event.preventDefault();

      const formData = new FormData(leadForm);
      const payload = Object.fromEntries(formData.entries());
      payload.locale = leadForm.dataset.locale || 'da';
      payload.consent = payload.consent === '1' || payload.consent === 'on';

      try {
        const response = await fetch('/api/public/v1/leads', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!response.ok) {
          throw new Error(data.error || 'Failed to submit form');
        }

        if (result) {
          result.textContent = copy.thanks;
          result.className = 'lead-note full status-chip ok';
        }
        leadForm.reset();
      } catch (error) {
        if (result) {
          result.textContent = error.message || 'Submission failed';
          result.className = 'lead-note full status-chip error';
        }
      }
    });
  }
})();
