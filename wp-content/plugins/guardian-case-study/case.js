(() => {
  const article = document.querySelector('.tbs-case-article');
  if (article && !article.dataset.mediaLightboxReady) {
    article.dataset.mediaLightboxReady = 'true';
    const mediaItems = [...article.querySelectorAll('.tbs-case-media')]
      // The hero is already the opening presentation; keep its video clean.
      .filter(item => !item.classList.contains('tbs-case-hero-video') && item.querySelector('img, video'));
    if (mediaItems.length) {
      const dialog = document.createElement('dialog');
      dialog.className = 'tbs-case-media-dialog';
      dialog.setAttribute('aria-label', 'Vista ampliada del proyecto');
      const close = document.createElement('button');
      close.className = 'tbs-case-media-dialog-close';
      close.type = 'button';
      close.setAttribute('aria-label', 'Cerrar ampliación');
      close.innerHTML = '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="m5 5 10 10M15 5 5 15"/></svg>';
      const stage = document.createElement('div');
      stage.className = 'tbs-case-media-dialog-stage';
      dialog.append(close, stage);
      document.body.append(dialog);

      let activeMedia = null;
      let pausedVideo = null;
      const fitMedia = () => {
        if (!activeMedia) return;
        const clone = stage.firstElementChild;
        if (!clone) return;
        const bounds = activeMedia.getBoundingClientRect();
        const ratio = bounds.width / Math.max(1, bounds.height);
        const maxWidth = Math.min(innerWidth * .92, 1480);
        const maxHeight = innerHeight * .86;
        const width = Math.min(maxWidth, maxHeight * ratio);
        clone.style.width = `${width}px`;
        clone.style.height = `${width / ratio}px`;
      };
      const closeDialog = () => dialog.close();
      close.addEventListener('click', closeDialog);
      // Ignore clicks dragged from the media onto the backdrop.
      const isBackdrop = target => target === stage || target === dialog;
      let startedOnBackdrop = false;
      dialog.addEventListener('pointerdown', event => {
        startedOnBackdrop = isBackdrop(event.target);
      });
      dialog.addEventListener('click', event => {
        if (startedOnBackdrop && isBackdrop(event.target)) closeDialog();
        startedOnBackdrop = false;
      });
      addEventListener('resize', fitMedia, { passive: true });
      dialog.addEventListener('close', () => {
        stage.querySelectorAll('video').forEach(video => video.pause());
        stage.replaceChildren();
        activeMedia = null;
        if (pausedVideo) pausedVideo.play().catch(() => {});
        pausedVideo = null;
      });

      mediaItems.forEach(media => {
        const source = media.querySelector('img, video');
        const description = source.getAttribute('alt') || source.getAttribute('aria-label') || 'medio del proyecto';
        const open = document.createElement('button');
        open.className = 'tbs-case-media-open';
        open.type = 'button';
        open.setAttribute('aria-haspopup', 'dialog');
        open.setAttribute('aria-label', `Abrir ampliación: ${description}`);
        open.innerHTML = '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M11.5 3.5h5v5M16.5 3.5 9 11M15.5 10.5v5.75H3.75V4.5H9.5"/></svg>';
        media.append(open);
        open.addEventListener('click', () => {
          activeMedia = media;
          const clone = media.cloneNode(true);
          clone.querySelectorAll('.tbs-case-media-open').forEach(button => button.remove());
          clone.classList.add('tbs-case-media-dialog-item');
          stage.replaceChildren(clone);
          fitMedia();
          pausedVideo = media.querySelector('video');
          if (pausedVideo && !pausedVideo.paused) pausedVideo.pause();
          dialog.showModal();
          close.focus();
          clone.querySelectorAll('video[autoplay]').forEach(video => video.play().catch(() => {}));
        });
      });
    }
  }

  const nav = document.querySelector('.tbs-case-nav');
  if (!nav || nav.dataset.ready) return;
  nav.dataset.ready = 'true';
  const chapters = [...nav.querySelectorAll('a[href^="#"]')].map(link => ({
    link,
    section: document.getElementById(decodeURIComponent(link.hash.slice(1)))
  })).filter(chapter => chapter.section);
  const closing = document.querySelector('.tbs-case-closing');
  const nextProject = document.querySelector('.tbs-case-next-link');
  if (!chapters.length) return;

  let pending = false;
  let current = null;
  const revealActive = () => {
    if (!current || nav.scrollWidth <= nav.clientWidth) return;
    const bounds = nav.getBoundingClientRect();
    const pill = current.link.getBoundingClientRect();
    const inset = parseFloat(getComputedStyle(nav).paddingInlineStart) || 8;
    const delta = pill.left < bounds.left + inset ? pill.left - bounds.left - inset
      : pill.right > bounds.right - inset ? pill.right - bounds.right + inset : 0;
    if (delta) nav.scrollLeft += delta;
  };
  const update = () => {
    pending = false;
    if (closing) {
      const fixedBottom = parseFloat(getComputedStyle(nav).bottom) || 18;
      const clearance = Math.max(36, Math.min(56, innerWidth * .035));
      const baseTop = innerHeight - fixedBottom - nav.offsetHeight;
      // Keep the rail anchored through the conclusion, then let it leave above
      // the next-project link so it never floats over that block or the footer.
      const exitEdge = nextProject
        ? nextProject.getBoundingClientRect().top
        : closing.getBoundingClientRect().bottom;
      const followTop = exitEdge - clearance - nav.offsetHeight;
      const lift = Math.max(0, baseTop - followTop);
      nav.style.setProperty('--tbs-nav-shift', `${-Math.ceil(lift)}px`);
      const navTop = baseTop - lift;
      // Fade it completely before the next-project block reaches the rail.
      const opacity = Math.max(0, Math.min(1, (navTop + nav.offsetHeight - 84) / 54));
      nav.style.setProperty('--tbs-nav-opacity', opacity.toFixed(3));
      nav.style.pointerEvents = opacity < .05 ? 'none' : '';
    }
    // A stable reading line avoids switching to the next chapter too early.
    const readingLine = Math.min(180, innerHeight * .25);
    const active = chapters.find(({section}) => {
      const bounds = section.getBoundingClientRect();
      return bounds.top <= readingLine && bounds.bottom > readingLine;
    }) || null;
    if (active === current) {
      revealActive();
      return;
    }
    current = active;
    chapters.forEach(chapter => {
      const selected = chapter === active;
      chapter.link.classList.toggle('is-active', selected);
      if (selected) chapter.link.setAttribute('aria-current', 'location');
      else chapter.link.removeAttribute('aria-current');
    });
    requestAnimationFrame(revealActive);
  };
  const schedule = () => {
    if (pending) return;
    pending = true;
    requestAnimationFrame(update);
  };
  addEventListener('scroll', schedule, {passive: true});
  addEventListener('resize', schedule, {passive: true});
  addEventListener('pageshow', schedule);
  addEventListener('hashchange', schedule);
  nav.addEventListener('transitionend', revealActive);
  // Media and fonts can shift the article even without scrolling.
  const resizeArticle = document.querySelector('.tbs-case-article');
  if ('ResizeObserver' in window && resizeArticle) {
    const observer = new ResizeObserver(schedule);
    observer.observe(resizeArticle);
  }
  document.fonts?.ready.then(schedule);
  const arrowSvg = '<svg width="23" height="16" viewBox="0 0 23 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.497 2.67793L8.81516 15.8289H0V12.6683H7.47251L19.2214 0.483398L21.497 2.67793Z" fill="currentColor"/><path d="M22.0908 12.3125L18.9307 15.585V3.16113H6.49805L9.55078 0H22.0908V12.3125Z" fill="currentColor"/></svg>';
  for (const link of document.querySelectorAll('#footer .footer-top nav a,#footer .contact a,#footer .footer-base a')) {
    if (link.querySelector('.tbs-footer-arrow,.cta-arrow')) continue;
    const label = document.createElement('span');
    label.className = 'tbs-footer-label';
    label.textContent = link.textContent.replace(/↗/g, '').trim();
    const icon = document.createElement('span');
    icon.className = 'tbs-footer-arrow';
    icon.setAttribute('aria-hidden', 'true');
    icon.innerHTML = arrowSvg + arrowSvg;
    link.textContent = '';
    link.classList.add('tbs-footer-cta');
    link.append(label, icon);
  }
  update();
})();
