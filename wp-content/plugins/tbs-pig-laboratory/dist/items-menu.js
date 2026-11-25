(() => {
  'use strict';
  const arrowSVG='<svg width="23" height="16" viewBox="0 0 23 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.497 2.67793L8.81516 15.8289H0V12.6683H7.47251L19.2214 0.483398L21.497 2.67793Z" fill="#2B2B2B"/><path d="M22.0908 12.3125L18.9307 15.585V3.16113H6.49805L9.55078 0H22.0908V12.3125Z" fill="#2B2B2B"/></svg>';
  const decorateAction=link=>{
    link.classList.add('cta');
    if(link.querySelector('.cta-label')&&link.querySelector('.cta-arrow'))return;
    const label=document.createElement('span');
    label.className='cta-label';label.textContent=link.textContent.replace(/[↗×]/g,'').trim();
    const icon=document.createElement('span');
    icon.className='cta-arrow';icon.setAttribute('aria-hidden','true');icon.innerHTML=arrowSVG+arrowSVG;
    link.replaceChildren(label,icon);
  };
  const readProjects=header=>{
    let projects;
    try{projects=JSON.parse(header.dataset.tbsItems||'null');}catch{projects=null;}
    if(!Array.isArray(projects))projects=[...document.querySelectorAll('.footer-top nav a')];
    return projects.map((project,index)=>{
      try{
        const url=new URL(project.href,location.href);
        const title=String(project.textContent||'').replace(/[↗×]/g,'').trim();
        const value=Number(project.number),number=Number.isInteger(value)&&value>0?value:index+1;
        return /^https?:$/.test(url.protocol)&&title?{href:url.href,title,number:String(number).padStart(2,'0')}:null;
      }catch{return null;}
    }).filter(Boolean);
  };
  const boot=()=>{
    if(document.documentElement.dataset.tbsSiteNavigationReady)return;
    document.documentElement.dataset.tbsSiteNavigationReady='true';
    // Escape FSE wrappers so fixed positioning and blend use the same surface everywhere.
    document.querySelectorAll('.tbs-site-header,.tbs-site-corners,.tbs-edge-glass').forEach(node=>{
      if(node.parentElement!==document.body)document.body.append(node);
    });
    document.querySelectorAll('.tbs-site-corners a.tbs-corner-link,.tbs-site-footer .footer-top nav a,.tbs-site-footer .contact a,.tbs-site-footer .footer-base a').forEach(decorateAction);
    const headers=[...document.querySelectorAll('.tbs-site-header')],pickers=[];
    headers.forEach((header,index)=>{
      if(header.dataset.tbsNavigationReady)return;
      header.dataset.tbsNavigationReady='true';
      const host=header.querySelector('.items-menu-host');
      let trigger=host?.querySelector('.items-toggle,.header-link');
      if(!host||!trigger)return;
      const projects=readProjects(header),labelText=(header.dataset.tbsProjectLabel||'PROYECTOS').trim();
      const projectLabel=labelText+' ['+String(projects.length).padStart(2,'0')+']';
      if(!projects.length){trigger.textContent=projectLabel;return;}
      if(trigger.tagName!=='BUTTON'){
        const button=document.createElement('button');button.className=trigger.className;
        trigger.replaceWith(button);trigger=button;
      }
      trigger.classList.add('items-toggle');trigger.setAttribute('type','button');trigger.removeAttribute('href');
      const menuId='tbs-project-menu-'+(index+1);trigger.setAttribute('aria-controls',menuId);
      const label=document.createElement('span');label.className='items-label';label.textContent=projectLabel;
      const icon=document.createElement('span');icon.className='items-icon';icon.setAttribute('aria-hidden','true');icon.innerHTML='<i></i><i></i>';
      trigger.replaceChildren(label,icon);
      const portal=document.createElement('div');portal.className='tbs-items-portal';
      const menu=document.createElement('nav');menu.id=menuId;menu.className='items-menu';menu.setAttribute('aria-label','Proyectos');
      projects.forEach(project=>{
        const link=document.createElement('a');link.href=project.href;
        if(new URL(project.href).pathname===location.pathname&&location.pathname!=='/')link.setAttribute('aria-current','page');
        const number=document.createElement('span');number.className='items-menu-number';number.textContent=project.number;number.setAttribute('aria-hidden','true');
        const title=document.createElement('span');title.className='items-menu-title';title.textContent=project.title;
        const arrow=document.createElement('span');arrow.className='items-menu-arrow';arrow.setAttribute('aria-hidden','true');arrow.textContent='↗';
        link.append(number,title,arrow);menu.append(link);
      });
      portal.append(menu);document.body.append(portal);
      let open=false,hoverOpened=false,closeTimer;
      const positionMenu=()=>{
        if(!open)return;
        const rect=trigger.getBoundingClientRect(),top=rect.bottom+(innerWidth<=650?21:23);
        menu.style.top=top+'px';menu.style.right=Math.max(20,innerWidth-rect.right)+'px';
        portal.style.setProperty('--tbs-menu-top',top+'px');
      };
      const setOpen=value=>{
        clearTimeout(closeTimer);open=value;if(!open)hoverOpened=false;
        host.classList.toggle('is-open',open);portal.classList.toggle('is-open',open);
        trigger.setAttribute('aria-expanded',String(open));trigger.setAttribute('aria-label',open?'Cerrar proyectos':'Abrir proyectos');
        menu.setAttribute('aria-hidden',String(!open));menu.inert=!open;positionMenu();
      };
      const scheduleClose=()=>{
        clearTimeout(closeTimer);
        closeTimer=setTimeout(()=>{
          if(!host.matches(':hover')&&!menu.matches(':hover')&&!host.contains(document.activeElement)&&!menu.contains(document.activeElement))setOpen(false);
        },260);
      };
      trigger.addEventListener('click',()=>{const next=hoverOpened||!open;hoverOpened=false;setOpen(next);});
      trigger.addEventListener('keydown',event=>{
        if(event.key!=='ArrowDown'&&event.key!=='ArrowUp')return;
        event.preventDefault();setOpen(true);
        const links=[...menu.querySelectorAll('a')];links[event.key==='ArrowUp'?links.length-1:0]?.focus();
      });
      host.addEventListener('pointerenter',event=>{
        if(event.pointerType!=='touch'&&matchMedia('(hover:hover) and (pointer:fine)').matches&&!open){hoverOpened=true;setOpen(true);}
      });
      host.addEventListener('pointerleave',event=>{if(event.pointerType!=='touch')scheduleClose();});
      menu.addEventListener('pointerenter',()=>clearTimeout(closeTimer));menu.addEventListener('pointerleave',scheduleClose);
      host.addEventListener('focusout',scheduleClose);menu.addEventListener('focusout',scheduleClose);
      menu.addEventListener('focusin',()=>{clearTimeout(closeTimer);setOpen(true);});
      menu.addEventListener('click',event=>{if(event.target.closest('a'))setOpen(false);});
      menu.addEventListener('keydown',event=>{
        if(!['ArrowDown','ArrowUp','Home','End'].includes(event.key))return;
        const links=[...menu.querySelectorAll('a')],current=links.indexOf(document.activeElement);
        if(current<0)return;
        event.preventDefault();
        const next=event.key==='Home'?0:event.key==='End'?links.length-1:(current+(event.key==='ArrowDown'?1:-1)+links.length)%links.length;
        links[next].focus();
      });
      document.addEventListener('pointerdown',event=>{if(open&&!host.contains(event.target)&&!menu.contains(event.target))setOpen(false);});
      document.addEventListener('keydown',event=>{if(event.key==='Escape'&&open){setOpen(false);trigger.focus();}});
      setOpen(false);pickers.push({positionMenu,icon});
    });
    const corners=document.querySelector('.tbs-site-corners'),footer=document.getElementById('footer')||document.querySelector('footer');
    const bottomGlass=document.querySelector('.tbs-edge-glass--bottom');
    let footerTop=Infinity,restingBottom=innerHeight-12,scrollQueued=false,measureQueued=false;
    const positionMotion=matchMedia('(prefers-reduced-motion: reduce)');
    let displayedOffset=null,lastPositionTime=0;
    const updatePosition=now=>{
      scrollQueued=false;pickers.forEach(picker=>picker.positionMenu());
      if(!corners)return;
      const target=Math.min(0,footerTop-scrollY-restingBottom-8);
      const animate=corners.dataset.tbsContext==='home'&&!positionMotion.matches;
      if(displayedOffset===null||!animate||Math.abs(target-displayedOffset)>innerHeight*.75)displayedOffset=target;
      else {
        const dt=lastPositionTime?Math.min(50,Math.max(0,now-lastPositionTime)):1000/60;
        displayedOffset+=(target-displayedOffset)*(1-Math.exp(-dt/85));
        if(Math.abs(target-displayedOffset)<.1)displayedOffset=target;
      }
      const offset=displayedOffset,bottom=restingBottom+offset;
      if(offset!==target){lastPositionTime=now;scrollQueued=true;requestAnimationFrame(updatePosition);}
      else lastPositionTime=0;
      corners.style.transform='translateY('+offset+'px)';
      const strength=String(Math.max(0,Math.min(1,(bottom-100)/50)));
      corners.style.opacity=strength;
      corners.style.visibility=bottom<=100?'hidden':'visible';
      if(bottomGlass){
        // Share the exact docking position; fade individual layers, not their backdrop root.
        bottomGlass.style.transform=corners.style.transform;
        bottomGlass.style.setProperty('--tbs-glass-strength',strength);
        bottomGlass.style.visibility=corners.style.visibility;
      }
    };
    const queuePosition=()=>{if(!scrollQueued){scrollQueued=true;requestAnimationFrame(updatePosition);}};
    positionMotion.addEventListener('change',queuePosition);
    const measure=()=>{
      measureQueued=false;footerTop=footer?footer.getBoundingClientRect().top+scrollY:Infinity;
      restingBottom=innerHeight-(corners?parseFloat(getComputedStyle(corners).bottom)||12:12);
      const scale=devicePixelRatio||1;
      pickers.forEach(({icon})=>{
        icon.style.left='0px';icon.style.top='0px';
        const rect=icon.getBoundingClientRect();
        icon.style.left=Math.round(rect.left*scale)/scale-rect.left+'px';icon.style.top=Math.round(rect.top*scale)/scale-rect.top+'px';
      });
      queuePosition();
    };
    const queueMeasure=()=>{if(!measureQueued){measureQueued=true;requestAnimationFrame(measure);}};
    addEventListener('scroll',queuePosition,{passive:true});addEventListener('resize',queueMeasure,{passive:true});
    addEventListener('load',queueMeasure,{once:true});document.fonts?.ready.then(queueMeasure);
    if('ResizeObserver'in window){const observer=new ResizeObserver(queueMeasure);observer.observe(document.body);if(footer)observer.observe(footer);}
    measure();
    const motion=matchMedia('(prefers-reduced-motion: reduce)');
    const letterGroups=headers.map(header=>[...header.querySelectorAll('.brand .brand-letter')]).filter(letters=>letters.length);
    const widths=[175,285,210];
    let logoFrame=null,previousScroll=scrollY,previousTime=performance.now(),compression=0;
    const animateLogo=now=>{
      logoFrame=null;if(motion.matches||document.hidden||!letterGroups.length)return;
      const dt=Math.max(.001,Math.min(.05,(now-previousTime)/1000));previousTime=now;
      const speed=Math.abs(scrollY-previousScroll)/dt;previousScroll=scrollY;
      const target=Math.max(0,Math.min(1,speed/760));compression+=(target-compression)*(1-Math.exp(-dt*(target>compression?14:5)));
      letterGroups.forEach(letters=>letters.forEach((letter,index)=>{
        letter.style.fontVariationSettings="'slnt' 1000,'wdth' "+Math.round(365+((widths[index]||365)-365)*compression)+",'wght' "+Math.round(369-269*compression);
      }));
      logoFrame=requestAnimationFrame(animateLogo);
    };
    const syncMotion=()=>{
      if(logoFrame!==null)cancelAnimationFrame(logoFrame);logoFrame=null;previousScroll=scrollY;previousTime=performance.now();
      if(motion.matches){compression=0;letterGroups.forEach(letters=>letters.forEach(letter=>letter.style.removeProperty('font-variation-settings')));}
      else if(!document.hidden&&letterGroups.length)logoFrame=requestAnimationFrame(animateLogo);
    };
    motion.addEventListener('change',syncMotion);document.addEventListener('visibilitychange',syncMotion);syncMotion();
  };
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot,{once:true});else boot();
})();
