(() => {
  'use strict';
  const config=window.wtfMetaPixelConfig;
  if(!config || !/^[0-9]{5,25}$/.test(config.id) || !window.wtfCookies)return;
  const id=config.id;
  let active=false,configured=false,sent=false,script;
  function safeReferrer(){
    if(!document.referrer)return true;
    try{
      const url=new URL(document.referrer);
      if(/^\/(?:wp-admin|wp-login\.php|moje-konto|koszyk|zamowienie|checkout)(?:\/|$)/.test(url.pathname))return false;
      const allowed=['utm_source','utm_medium','utm_campaign','utm_content','utm_term','fbclid','game','world','sort'];
      return [...url.searchParams].every(([key,value])=>allowed.includes(key) && (key==='fbclid'?/^[A-Za-z0-9_.-]{1,500}$/:/^[A-Za-z0-9_. -]{0,100}$/).test(value));
    }catch{return false;}
  }

  function eraseCookies(){
    const parts=location.hostname.split('.');
    const domains=['',...parts.map((_,i)=>'; Domain='+parts.slice(i).join('.'))];
    const paths=new Set(['/',location.pathname]);
    const segments=location.pathname.split('/').filter(Boolean);
    for(let i=1;i<=segments.length;i++){
      const path='/'+segments.slice(0,i).join('/');paths.add(path);paths.add(path+'/');
    }
    for(const name of ['_fbp','_fbc'])for(const domain of domains)for(const path of paths){
      document.cookie=`${name}=; Max-Age=0; Path=${path}${domain}; SameSite=Lax${location.protocol==='https:'?'; Secure':''}`;
    }
  }

  function flush(){
    if(!active || sent || !window.wtfCookies.hasConsent('marketing'))return;
    window.fbq('trackSingle',id,'PageView');
    if(config.product)window.fbq('trackSingle',id,'ViewContent',config.product);
    sent=true;
  }

  window.wtfCookies.registerService('meta-pixel',{
    category:'marketing',
    start(){
      if(!config.enabled || !config.publicRequest || !safeReferrer())return;
      // Refuse a competing installation instead of initializing a second pixel.
      if(!configured && (window.fbq || window._fbq)){
        console.error('WTF Meta Pixel: wykryto inną instalację; sprawdź integracje.');return;
      }
      active=true;
      if(!configured){
        const queue=function(){queue.callMethod?queue.callMethod.apply(queue,arguments):queue.queue.push(arguments);};
        queue.push=queue;queue.loaded=true;queue.version='2.0';queue.queue=[];
        window.fbq=window._fbq=queue;
        queue('consent','grant');
        queue('set','autoConfig',false,id);
        queue('init',id);
        configured=true;
        script=document.createElement('script');script.async=true;
        script.src='https://connect.facebook.net/en_US/fbevents.js';
        script.onload=()=>{if(active && window.wtfCookies.hasConsent('marketing'))flush();};
        script.onerror=()=>{script.remove();script=null;};
        document.head.append(script);
      }else{
        window.fbq('consent','grant');flush();
      }
    },
    stop(){
      const wasActive=active;active=false;
      if(configured){
        window.fbq('consent','revoke');
        if(Array.isArray(window.fbq.queue))window.fbq.queue.splice(0);
      }
      if(script){script.onload=null;script.remove();script=null;}
      eraseCookies();
      // Unload SDK listeners and timers as well; consent was already saved.
      if(wasActive)location.reload();
    },
  });
})();
