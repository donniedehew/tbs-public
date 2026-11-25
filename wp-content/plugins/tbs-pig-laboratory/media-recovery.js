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
