/* Pokaz zdjęć korzysta z galerii i powiększania WooCommerce. */
(($) => {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  const selector = '.shop-single .woocommerce-product-gallery';
  $(document).on('wc-product-gallery-before-init', selector, (_event, _element, params) => {
    if (reduced.matches) params.flexslider.animationSpeed = 0;
  });
  function init(gallery) {
    if (gallery.dataset.wtfSlideshow) return;
    const slider = $(gallery).data('flexslider');
    const images = [...gallery.querySelectorAll('.woocommerce-product-gallery__image:not(.clone)')];
    if (!slider || images.length < 2) return;
    gallery.dataset.wtfSlideshow = '1';
    const controls = document.createElement('div');
    controls.className = 'wtf-gallery-controls';
    controls.setAttribute('role', 'group');
    controls.setAttribute('aria-label', 'Wybierz zdjęcie produktu');
    const track = document.createElement('div');
    track.className = 'wtf-gallery-thumbnails';
    const arrow = (label, direction) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'wtf-gallery-arrow';
      button.setAttribute('aria-label', label);
      button.textContent = direction < 0 ? '‹' : '›';
      button.hidden = true;
      button.addEventListener('click', () => {
        stop();
        track.scrollBy({left: direction * Math.max(72, track.clientWidth - 72), behavior: reduced.matches ? 'instant' : 'smooth'});
      });
      return button;
    };
    const previous = arrow('Przewiń miniatury w lewo', -1);
    const next = arrow('Przewiń miniatury w prawo', 1);
    controls.append(previous, track, next);
    let manual = false, hovered = false, timer;
    const stop = () => { clearTimeout(timer); manual = true; };
    const schedule = () => {
      clearTimeout(timer);
      if (manual || hovered || reduced.matches || document.hidden) return;
      timer = setTimeout(() => {
        if (!slider.animating) slider.flexAnimate((slider.currentSlide + 1) % images.length, false);
        schedule();
      }, 3000);
    };
    const buttons = images.map((image, index) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'wtf-gallery-control';
      button.setAttribute('aria-label', `Pokaż zdjęcie ${index + 1} z ${images.length}`);
      const thumbnail = document.createElement('img');
      thumbnail.src = image.dataset.thumb || image.querySelector('img').src;
      thumbnail.alt = '';
      thumbnail.width = 64;
      thumbnail.height = 64;
      thumbnail.draggable = false;
      button.append(thumbnail);
      button.addEventListener('click', () => {
        stop();
        slider.flexAnimate(index, true);
      });
      track.append(button);
      return button;
    });
    const updateArrows = () => {
      previous.disabled = track.scrollLeft <= 1;
      next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
    };
    const layout = () => {
      const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      const width = buttons.reduce((total, button) => total + button.offsetWidth, 0) + gap * (buttons.length - 1);
      const overflow = width > controls.clientWidth + 1;
      controls.classList.toggle('is-overflowing', overflow);
      previous.hidden = next.hidden = !overflow;
      updateArrows();
    };
    const sync = () => {
      buttons.forEach((button, index) => button.setAttribute('aria-pressed', String(index === slider.currentSlide)));
      const selected = buttons[slider.currentSlide];
      const left = selected.offsetLeft - track.offsetLeft;
      if (left < track.scrollLeft) track.scrollTo({left, behavior: reduced.matches ? 'instant' : 'smooth'});
      else if (left + selected.offsetWidth > track.scrollLeft + track.clientWidth) track.scrollTo({left: left + selected.offsetWidth - track.clientWidth, behavior: reduced.matches ? 'instant' : 'smooth'});
      updateArrows();
    };
    const after = slider.vars.after;
    slider.vars.after = instance => { if (typeof after === 'function') after(instance); sync(); };
    gallery.append(controls);
    track.addEventListener('scroll', updateArrows, {passive: true});
    if (window.ResizeObserver) new ResizeObserver(layout).observe(controls);
    else window.addEventListener('resize', layout);
    gallery.addEventListener('pointerdown', stop, {passive: true});
    gallery.addEventListener('keydown', stop);
    gallery.addEventListener('focusin', stop);
    gallery.addEventListener('pointerenter', () => { hovered = true; clearTimeout(timer); });
    gallery.addEventListener('pointerleave', () => { hovered = false; schedule(); });
    document.addEventListener('visibilitychange', schedule);
    reduced.addEventListener('change', () => {
      slider.vars.animationSpeed = reduced.matches ? 0 : 450;
      schedule();
    });
    layout();
    sync();
    schedule();
  }
  $(document).on('wc-product-gallery-after-init', selector, function () { init(this); });
  $(() => document.querySelectorAll(selector).forEach(init));
  // Ilustracje w opisie korzystają z tego samego powiększania co galeria produktu.
  $(document).on('click', '.wtf-product-image-zoom', function (event) {
    const root = document.querySelector('.pswp');
    if (!root || !window.PhotoSwipe || !window.PhotoSwipeUI_Default) return;
    event.preventDefault();
    const image = this.querySelector('img');
    const viewer = new window.PhotoSwipe(root, window.PhotoSwipeUI_Default, [{
      src: this.href, w: Number(this.dataset.width), h: Number(this.dataset.height),
      title: image?.alt || ''
    }], {history: false, shareEl: false, closeOnScroll: false});
    viewer.init();
  });
})(jQuery);
