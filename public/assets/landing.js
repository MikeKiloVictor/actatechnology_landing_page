(() => {
  const copy = window.ACTA_COPY || { play: 'Play', pause: 'Pause', thanks: 'Thanks.' };
  const analytics = window.ACTA_ANALYTICS || { gaId: '' };
  const consentStorageKey = 'acta_analytics_consent';

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

  const consentBanner = document.getElementById('consent-banner');
  const consentValue = localStorage.getItem(consentStorageKey);
  if (consentValue === 'granted') {
    loadGaIfConfigured();
  } else if (consentBanner) {
    consentBanner.hidden = false;
    consentBanner.querySelector('[data-consent=\"accept\"]')?.addEventListener('click', () => {
      localStorage.setItem(consentStorageKey, 'granted');
      consentBanner.hidden = true;
      loadGaIfConfigured();
    });

    consentBanner.querySelector('[data-consent=\"reject\"]')?.addEventListener('click', () => {
      localStorage.setItem(consentStorageKey, 'denied');
      consentBanner.hidden = true;
    });
  }

  const carouselRoot = document.querySelector('[data-carousel]');
  if (carouselRoot) {
    const track = carouselRoot.querySelector('[data-carousel-track]');
    const cards = Array.from(track ? track.children : []);
    const dotsRoot = carouselRoot.querySelector('[data-carousel-dots]');
    const prevBtn = carouselRoot.querySelector('[data-carousel-prev]');
    const nextBtn = carouselRoot.querySelector('[data-carousel-next]');
    const toggleBtn = carouselRoot.querySelector('[data-carousel-toggle]');

    let index = 0;
    let paused = false;
    let timer = null;
    const intervalSeconds = Math.max(3, Number(carouselRoot.getAttribute('data-interval') || 6));
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reducedMotion) {
      paused = true;
    }

    const update = () => {
      if (!track || cards.length === 0) {
        return;
      }
      const firstCard = cards[0];
      const gap = Number.parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '0') || 0;
      const step = firstCard.getBoundingClientRect().width + gap;
      track.style.transform = `translateX(${-index * step}px)`;

      if (dotsRoot) {
        Array.from(dotsRoot.children).forEach((node, dotIndex) => {
          node.setAttribute('aria-current', dotIndex === index ? 'true' : 'false');
        });
      }

      if (toggleBtn) {
        toggleBtn.textContent = paused ? copy.play : copy.pause;
      }
    };

    const next = () => {
      if (cards.length === 0) {
        return;
      }
      index = (index + 1) % cards.length;
      update();
    };

    const prev = () => {
      if (cards.length === 0) {
        return;
      }
      index = (index - 1 + cards.length) % cards.length;
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

    const pauseTemporarily = () => {
      paused = true;
      update();
      stop();
    };

    const resumeIfPossible = () => {
      if (!carouselRoot.matches(':hover') && !carouselRoot.matches(':focus-within')) {
        paused = false;
        update();
        start();
      }
    };

    if (dotsRoot) {
      cards.forEach((_, dotIndex) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'carousel-dot';
        dot.setAttribute('aria-label', `Slide ${dotIndex + 1}`);
        dot.addEventListener('click', () => {
          index = dotIndex;
          pauseTemporarily();
          update();
        });
        dotsRoot.appendChild(dot);
      });
    }

    prevBtn?.addEventListener('click', () => {
      prev();
      pauseTemporarily();
    });

    nextBtn?.addEventListener('click', () => {
      next();
      pauseTemporarily();
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

    carouselRoot.addEventListener('mouseenter', pauseTemporarily);
    carouselRoot.addEventListener('focusin', pauseTemporarily);
    carouselRoot.addEventListener('mouseleave', resumeIfPossible);
    carouselRoot.addEventListener('focusout', () => {
      setTimeout(resumeIfPossible, 10);
    });

    document.addEventListener('keydown', (event) => {
      if (!carouselRoot) {
        return;
      }
      if (event.key === 'ArrowRight') {
        next();
        pauseTemporarily();
      }
      if (event.key === 'ArrowLeft') {
        prev();
        pauseTemporarily();
      }
    });

    window.addEventListener('resize', update);
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
