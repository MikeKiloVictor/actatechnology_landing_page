(() => {
  const data = window.DECK_DATA || { slides: [], labels: { slide: 'Slide', of: 'of' } };
  const slides = Array.isArray(data.slides) ? data.slides : [];

  const titleNode = document.getElementById('deck-title');
  const contentNode = document.getElementById('deck-content');
  const bulletsNode = document.getElementById('deck-bullets');
  const imageNode = document.getElementById('deck-image');
  const linkNode = document.getElementById('deck-link');
  const counterNode = document.getElementById('deck-counter');
  const progressNode = document.getElementById('deck-progress-bar');
  const prevBtn = document.getElementById('deck-prev');
  const nextBtn = document.getElementById('deck-next');

  if (!slides.length) {
    return;
  }

  let index = 0;

  const render = () => {
    const current = slides[index] || {};
    titleNode.textContent = current.title || '';
    contentNode.textContent = current.content || '';

    while (bulletsNode.firstChild) {
      bulletsNode.removeChild(bulletsNode.firstChild);
    }

    const bullets = Array.isArray(current.bullets) ? current.bullets : [];
    bullets.forEach((bullet) => {
      const li = document.createElement('li');
      li.textContent = bullet;
      bulletsNode.appendChild(li);
    });

    if (current.image_url) {
      imageNode.src = current.image_url;
      imageNode.alt = current.title || 'Slide image';
      imageNode.style.display = 'block';
    } else {
      imageNode.style.display = 'none';
      imageNode.removeAttribute('src');
      imageNode.alt = '';
    }

    if (current.link_url) {
      linkNode.href = current.link_url;
      linkNode.textContent = current.link_label || 'Open link';
      linkNode.style.display = 'inline-flex';
    } else {
      linkNode.style.display = 'none';
    }

    counterNode.textContent = `${data.labels.slide} ${index + 1} ${data.labels.of} ${slides.length}`;
    progressNode.style.width = `${((index + 1) / slides.length) * 100}%`;

    prevBtn.disabled = index === 0;
    nextBtn.disabled = index >= slides.length - 1;
  };

  prevBtn.addEventListener('click', () => {
    if (index > 0) {
      index -= 1;
      render();
    }
  });

  nextBtn.addEventListener('click', () => {
    if (index < slides.length - 1) {
      index += 1;
      render();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') {
      prevBtn.click();
    } else if (event.key === 'ArrowRight' || event.key === ' ') {
      nextBtn.click();
    }
  });

  render();
})();
