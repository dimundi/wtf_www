(() => {
  const header = document.querySelector('.header');
  const toggle = header?.querySelector('.mobile-menu-toggle');
  const compact = matchMedia('(max-width:700px)');
  const en = document.documentElement.lang.startsWith('en');
  if (toggle) {
    const tools = header.querySelector('.tools');
    const nav = header.querySelector('#main-navigation');
    const login = header.querySelector('.header-login');
    const cart = header.querySelector('.header-cart');
    const games = header.querySelector('.games-navigation');
    let inertElements = [];
    const close = () => {
      header.classList.remove('menu-open');
      document.body.classList.remove('mobile-menu-open');
      toggle.setAttribute('aria-expanded','false');
      toggle.querySelector('span').textContent='Menu';
      games.open=false;
      inertElements.forEach(([element,previous])=>element.inert=previous);
      inertElements=[];
    };
    const sync = () => {
      close();
      if(compact.matches){nav.append(login);header.insertBefore(cart,toggle);}
      else tools.prepend(login,cart);
    };
    header.classList.add('has-mobile-menu');toggle.hidden=false;sync();
    compact.addEventListener('change',sync);
    toggle.addEventListener('click',()=>{
      if(header.classList.contains('menu-open')){close();return;}
      header.classList.add('menu-open');toggle.setAttribute('aria-expanded','true');
      toggle.querySelector('span').textContent=en?'Close menu':'Zamknij menu';
      games.open=true;
      if(compact.matches){
        document.body.classList.add('mobile-menu-open');
        const region=header.closest('.wp-block-template-part')||header;
        [...document.querySelectorAll('main,footer')].filter(element=>!region.contains(element)).forEach(element=>{inertElements.push([element,element.inert]);element.inert=true;});
      }
    });
    nav.addEventListener('click',event=>{if(event.target.closest('a'))close();});
    document.addEventListener('click',event=>{if(!header.contains(event.target))close();});
    document.addEventListener('keydown',event=>{
      if(event.key==='Escape'&&(games.open||header.classList.contains('menu-open'))){close();toggle.focus();}
      if(event.key==='Tab'&&document.body.classList.contains('mobile-menu-open')){
        const nodes=[...header.querySelectorAll('a,button,summary')].filter(node=>node.getClientRects().length&&!node.hidden);
        if(event.shiftKey&&document.activeElement===nodes[0]){event.preventDefault();nodes.at(-1).focus();}
        else if(!event.shiftKey&&document.activeElement===nodes.at(-1)){event.preventDefault();nodes[0].focus();}
      }
    });
  }
  const themeButton=document.getElementById('theme-toggle');
  const syncTheme=()=>{
    const dark=document.documentElement.dataset.theme!=='light';
    themeButton?.setAttribute('aria-pressed',String(dark));
    if(themeButton)themeButton.textContent=dark?'☀':'☾';
  };
  syncTheme();
  window.addEventListener('wtf:themechange',syncTheme);
  themeButton?.addEventListener('click',()=>{
    if(document.documentElement.dataset.themeDev!=='1')return;
    const theme=document.documentElement.dataset.theme==='light'?'dark':'light';
    document.documentElement.dataset.theme=theme;
    document.documentElement.dataset.themeSource='manual';
    try{localStorage.setItem('wtf-theme',theme);}catch{}
    syncTheme();window.dispatchEvent(new CustomEvent('wtf:themechange',{detail:{theme}}));
  });
  const hero=document.querySelector('.hero[data-slides]');
  if(!hero)return;
  let slides;try{slides=JSON.parse(hero.dataset.slides);}catch{return;}
  if(slides.length<2)return;
  const reduce=matchMedia('(prefers-reduced-motion:reduce)');
  const images=slides.map((slide,index)=>{
    const img=index===0?hero.querySelector('.hero-art'):document.createElement('img');
    if(slide.srcset){img.sizes=slide.sizes;img.srcset=slide.srcset;}
    if(index>0)img.loading='lazy';
    img.src=slide.image;img.alt=slide.title;img.className='hero-art hero-slide';
    img.classList.toggle('is-active',index===0);
    if(index>0){img.loading='lazy';hero.prepend(img);}return img;
  });
  const controls=document.createElement('div');controls.className='hero-slider-controls';
  let current=0,paused=reduce.matches,timer;
  const show=index=>{
    current=(index+slides.length)%slides.length;
    images.forEach((img,i)=>{img.classList.toggle('is-active',i===current);img.setAttribute('aria-hidden',String(i!==current));});
    dots.forEach((dot,i)=>dot.setAttribute('aria-pressed',String(i===current)));
    const link=hero.querySelector('.hero-bottom>a');link.href=slides[current].url;link.textContent=slides[current].title+' ↗';
  };
  const restart=()=>{clearInterval(timer);if(!paused&&!document.hidden&&!document.body.classList.contains('mobile-menu-open'))timer=setInterval(()=>show(current+1),6500);};
  const dots=slides.map((slide,index)=>{const b=document.createElement('button');b.type='button';b.textContent=String(index+1);b.setAttribute('aria-label',slide.title);b.addEventListener('click',()=>{show(index);restart();});controls.append(b);return b;});
  hero.append(controls);show(0);restart();
  document.addEventListener('visibilitychange',restart);
  reduce.addEventListener('change',event=>{paused=event.matches;restart();});
})();
