(() => {
 const boot=()=>{
  if(!document.body.classList.contains('tbs-benchmarks-case-page')) return;
  const selector='.benchmarks-case-video video[data-bm-video]';
  const reduced=matchMedia('(prefers-reduced-motion: reduce)');
  const states=new WeakMap(), videos=new Set();
  const blocked=v=>!!document.querySelector('.tbs-case-media-dialog[open]')&&!v.closest('.tbs-case-media-dialog[open]');
  const hydrate=v=>{if(!v.getAttribute('src')&&v.dataset.bmSrc){v.src=v.dataset.bmSrc;v.preload='metadata';v.load();}};
  const stop=v=>{const s=states.get(v);if(s&&!v.paused){s.systemPause=true;v.pause();}};
  const allowed=(v,s)=>s.visible&&!document.hidden&&!blocked(v)&&(s.userPlaying||(!reduced.matches&&!s.userPaused));
  const sync=v=>{const s=states.get(v);if(!s)return;const next=allowed(v,s),enter=next&&!s.eligible;s.eligible=next;if(!next)stop(v);else if(enter&&v.paused){hydrate(v);v.play().catch(()=>{});}};
  const refresh=()=>videos.forEach(v=>{if(!v.isConnected){io?.unobserve(v);videos.delete(v);}else sync(v);});
  const io='IntersectionObserver' in window?new IntersectionObserver(entries=>entries.forEach(e=>{const s=states.get(e.target);if(s){s.visible=e.isIntersecting&&e.intersectionRatio>=.2;sync(e.target);}}),{threshold:[0,.2]}):null;
  const register=v=>{
   if(states.has(v))return;
   const s={visible:false,eligible:false,intentUntil:0,userPlaying:false,userPaused:false,systemPause:false};states.set(v,s);videos.add(v);
   v.controls=true;v.muted=true;v.playsInline=true;v.autoplay=false;
   const intent=()=>{s.intentUntil=performance.now()+750;hydrate(v);};
   v.addEventListener('pointerdown',intent);
   v.addEventListener('keydown',e=>{if(e.key===' '||e.key==='Enter')intent();});
   v.addEventListener('play',()=>{
    if(v.paused)return;
    if(s.intentUntil>performance.now()&&s.visible&&!document.hidden&&!blocked(v)){s.userPlaying=true;s.userPaused=false;}
    s.intentUntil=0;
    if(!allowed(v,s))stop(v);
   });
   v.addEventListener('pause',()=>{
    if(!v.paused){s.systemPause=false;return;}
    if(!s.systemPause&&!blocked(v)&&!document.hidden)s.userPaused=true;
    s.systemPause=false;s.userPlaying=false;s.intentUntil=0;
   });
   if(io)io.observe(v);else{hydrate(v);s.visible=true;s.userPaused=true;}
  };
  document.querySelectorAll(selector).forEach(register);
  new MutationObserver(records=>{
   records.forEach(r=>r.addedNodes.forEach(n=>{if(n.nodeType!==1)return;if(n.matches(selector))register(n);n.querySelectorAll(selector).forEach(register);}));
   refresh();
  }).observe(document.body,{childList:true,subtree:true});
  document.addEventListener('visibilitychange',refresh);
  document.addEventListener('close',e=>{if(e.target.matches('.tbs-case-media-dialog')){videos.forEach(v=>{states.get(v).intentUntil=0;});queueMicrotask(refresh);}},true);
  reduced.addEventListener('change',()=>{videos.forEach(v=>{const s=states.get(v);s.userPlaying=false;stop(v);});refresh();});
 };
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot,{once:true});else boot();
})();
