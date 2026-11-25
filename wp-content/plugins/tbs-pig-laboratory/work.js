/* Shared video recovery. Bundled before app.js and work.js; no extra request. */
(() => {
  if (window.TBSMediaRecovery) return;
  const nativeStyle = document.createElement('style');
  nativeStyle.textContent = `
video.tbs-native-play-only::-webkit-media-controls-timeline,
video.tbs-native-play-only::-webkit-media-controls-current-time-display,
video.tbs-native-play-only::-webkit-media-controls-time-remaining-display,
video.tbs-native-play-only::-webkit-media-controls-mute-button,
video.tbs-native-play-only::-webkit-media-controls-volume-slider,
video.tbs-native-play-only::-webkit-media-controls-fullscreen-button,
video.tbs-native-play-only::-webkit-media-controls-overflow-button {display:none!important}
video.tbs-native-play-only::-webkit-media-controls-enclosure{background:transparent;height:100%;max-height:none;width:56px;left:calc(50% - 28px);overflow:hidden}
video.tbs-native-play-only::-webkit-media-controls-panel{background:transparent;justify-content:flex-start;padding:0;width:56px;overflow:hidden}
`;
  nativeStyle.textContent += 'video.tbs-native-play-only::-webkit-media-controls-play-button{flex:0 0 56px;margin:0}';
  document.head.appendChild(nativeStyle);
  const states = new WeakMap();
  const motion = matchMedia('(prefers-reduced-motion: reduce)');
  function install(video, hydrate) {
    if (states.has(video)) return;
    const state = { hydrate, pending: false, blocked: false, button: null,
      nativeControls: video.controls, nativeControlsList: video.getAttribute("controlslist"), nativePiP: video.disablePictureInPicture, fallbackUsed: false };
    states.set(video, state);
    // Reuse the browser's own play control, including Safari's native overlay.
    const prepareNativePlay = () => {
      if (!video.controls) return;
      video.dataset.tbsUserPlay = '1';
      if (state.hydrate) state.hydrate(video);
    };
    video.addEventListener('pointerdown', prepareNativePlay);
    video.addEventListener('keydown', prepareNativePlay);
    video.addEventListener('play', () => {
      if (video.controls) video.dataset.tbsUserPlay = '1';
    });
    video.addEventListener('playing', () => {
      state.blocked = false;
      video.classList.remove('tbs-native-play-only');
      if (state.nativeControlsList === null) video.removeAttribute('controlslist');
      else video.setAttribute('controlslist', state.nativeControlsList);
      video.disablePictureInPicture = state.nativePiP;
      video.controls = state.nativeControls || motion.matches;
      video.controls = state.nativeControls || (motion.matches && video.dataset.tbsUserPlay === '1');
    });
    video.addEventListener('error', () => {
      // A declared WebM can fail decoding even when the browser accepts its MIME.
      const mp4 = [...video.querySelectorAll('source')].find(s => s.type === 'video/mp4');
      const url = mp4 && (mp4.getAttribute('src') || mp4.dataset.tbsSrc);
      if (!state.fallbackUsed && url && video.currentSrc !== new URL(url, document.baseURI).href
          && [3,4].includes(video.error?.code)) {
        state.fallbackUsed = true;
        video.src = url;
        video.load();
      }
      show(video);
    });
    const updateMotion = () => {
      if (motion.matches) { delete video.dataset.tbsUserPlay; video.pause(); show(video); }
    };
    motion.addEventListener('change', updateMotion);
    updateMotion();
  }
  function show(video) {
    const state = states.get(video);
    if (!state) return;
    state.blocked = true;
    video.classList.add('tbs-native-play-only');
    video.setAttribute('controlslist', 'nodownload nofullscreen noremoteplayback noplaybackrate');
    video.disablePictureInPicture = true;
    video.controls = true;
  }
  function request(video, manual = false) {
    const state = states.get(video);
    if (!state || document.hidden || state.pending ||
        (!manual && (state.blocked || (motion.matches && video.dataset.tbsUserPlay !== '1')))) return;
    video.muted = video.defaultMuted = true;
    video.playsInline = true;
    state.pending = true;
    let result;
    try { result = video.play(); }
    catch (error) { state.pending = false; show(video); return; }
    Promise.resolve(result).then(() => {
      state.pending = false;
      video.controls = state.nativeControls || motion.matches;
      if (motion.matches && video.dataset.tbsUserPlay === '1') video.controls = true;
    }, error => {
      state.pending = false;
      // Scrolling away or changing source legitimately cancels an in-flight play.
      if (error?.name !== 'AbortError') show(video);
    });
  }
  window.TBSMediaRecovery = { install, request };
})();

(() => {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const mediaVideos = [...document.querySelectorAll('main video[autoplay]')];
  const mediaVisibility = new WeakMap();
  mediaVideos.forEach(video => window.TBSMediaRecovery.install(video));
  const playMediaVideo = video => {
    if ((reduced && video.dataset.tbsUserPlay !== '1') || !mediaVisibility.get(video) || document.hidden) return;
    video.muted = true;
    video.defaultMuted = true;
    video.playsInline = true;
    window.TBSMediaRecovery.request(video);
  };
  if (reduced) {
    mediaVideos.forEach(video => {
      video.autoplay = false;
      video.removeAttribute('autoplay');
      video.pause();
    });
  }
  if ('IntersectionObserver' in window) {
    const mediaObserver = new IntersectionObserver(entries => entries.forEach(({target, isIntersecting}) => {
      mediaVisibility.set(target, isIntersecting);
      if (isIntersecting) playMediaVideo(target);
      else target.pause();
    }), {threshold: .08, rootMargin: '180px 0px'});
    mediaVideos.forEach(video => {
      video.muted = true;
      video.defaultMuted = true;
      video.playsInline = true;
      mediaVisibility.set(video, false);
      video.addEventListener('loadeddata', () => playMediaVideo(video));
      video.addEventListener('canplay', () => playMediaVideo(video));
      mediaObserver.observe(video);
    });
  } else {
    mediaVideos.forEach(video => {
      video.muted = true;
      video.defaultMuted = true;
      video.playsInline = true;
      mediaVisibility.set(video, true);
      playMediaVideo(video);
    });
  }
  const retryMediaVideos = () => mediaVideos.forEach(playMediaVideo);
  document.addEventListener('visibilitychange', retryMediaVideos);
  window.addEventListener('pageshow', retryMediaVideos);
  window.addEventListener('online', retryMediaVideos);
  const brand = document.querySelector('.brand');
  const wordmark = document.querySelector('.wordmark');
  if (!brand || !wordmark || reduced) return;
  const top = [...brand.querySelectorAll('.brand-letter')];
  const bottom = [...wordmark.querySelectorAll('.wordmark-letter')];
  const base = "'slnt' 1000,'wdth' 365,'wght' 369";
  // Let each glyph's intrinsic width follow the variable font axis. Fixed
  // pixel cells made the B overflow its container at some viewport sizes.
  const widths = [175, 285, 210];
  let active = -1, activePosition = null, motionStrength = 0, inputSource = 'none';
  let compression = 0, previousScroll = scrollY, previousTime = performance.now();
  const response = [0, 0, 0];
  let orientationOrigin = null;
  const onOrientation = event => {
    if (!Number.isFinite(event.gamma)) return;
    if (orientationOrigin === null) orientationOrigin = event.gamma;
    const delta = event.gamma - orientationOrigin;
    activePosition = Math.max(0, Math.min(2, 1 + delta / 20));
    motionStrength = Math.min(1, Math.abs(delta) / 20);
    active = -1;
    inputSource = 'motion';
  };
  const listenForMotion = () => addEventListener('deviceorientation', onOrientation, { passive: true });
  if (matchMedia('(pointer: coarse)').matches && 'DeviceOrientationEvent' in window) {
    if (typeof DeviceOrientationEvent.requestPermission === 'function') {
      const requestMotionPermission = async () => {
        try {
          const permission = await DeviceOrientationEvent.requestPermission();
          if (permission === 'granted') listenForMotion();
        } catch { /* Keep the original static proportions when motion is unavailable. */ }
      };
      addEventListener('pointerdown', requestMotionPermission, { once: true, capture: true, passive: true });
    } else {
      listenForMotion();
    }
  }
  wordmark.addEventListener('pointermove', event => {
    if (event.pointerType === 'touch' && inputSource === 'motion') return;
    let distance = Infinity;
    bottom.forEach((letter, index) => {
      const bounds = letter.getBoundingClientRect();
      const current = Math.abs(event.clientX - (bounds.left + bounds.right) / 2);
      if (current < distance) { distance = current; active = index; }
    });
    activePosition = null;
    motionStrength = 0;
    inputSource = 'pointer';
  });
  wordmark.addEventListener('pointerleave', () => {
    if (inputSource !== 'pointer') return;
    active = -1;
    activePosition = null;
    motionStrength = 0;
    inputSource = 'none';
  });
  const animate = now => {
    const dt = Math.min(.05, Math.max(.001, (now - previousTime) / 1000));
    previousTime = now;
    const speed = Math.abs(scrollY - previousScroll) / dt;
    previousScroll = scrollY;
    const target = Math.min(1, speed / 760);
    compression += (target - compression) * (1 - Math.exp(-dt * (target > compression ? 14 : 5)));
    bottom.forEach((letter, index) => {
      let target = activePosition === null ? (active === index ? 1 : 0) : Math.max(0, 1 - Math.abs(activePosition - index));
      if (inputSource === 'motion') target *= motionStrength;
      response[index] += (target - response[index]) * (1 - Math.exp(-dt * 10));
    });
    const total = response.reduce((sum, value) => sum + value, 0);
    bottom.forEach((letter, index) => {
      const p = response[index];
      letter.style.fontVariationSettings = `'slnt' 1000,'wdth' ${Math.round(365 - 265 * p + 170 * Math.max(0, total - p))},'wght' ${Math.round(369 - 269 * p)}`;
    });
    requestAnimationFrame(animate);
  };
  requestAnimationFrame(animate);
})();
