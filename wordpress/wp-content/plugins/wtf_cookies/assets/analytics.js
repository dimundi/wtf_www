(() => {
  'use strict';
  const config=window.wtfAnalyticsConfig;
  if(!config?.enabled || !/^G-[A-Z0-9]{6,20}$/.test(config.id) || !window.wtfCookies)return;
  const id=config.id, disabled=`ga-disable-${id}`;
  let active=false,script,configured=false,viewSent=false;
  window[disabled]=true;
  const group=()=>{
    const path=location.pathname;
    if(path==='/quiz-klanarchia/' || path.startsWith('/klanarchia/'))return 'klanarchia';
    for(const name of ['kurzolaki','elekt','tsukuyumi','echo-swiatow'])if(path.startsWith(`/${name}/`) || path.startsWith(`/produkt/${name}/`))return name;
    return path.startsWith('/sklep/') || path.startsWith('/produkt/')?'sklep':'ogolne';
  };
  function pageLocation(){
    const url=new URL(location.origin+location.pathname);
    const source=new URL(location.href);
    for(const key of ['utm_source','utm_medium','utm_campaign','utm_content','utm_term']){
      const value=source.searchParams.get(key);
      if(value && /^[a-zA-Z0-9_. -]{1,100}$/.test(value))url.searchParams.set(key,value);
    }
    return url.href;
  }
  function referrer(){try{return document.referrer?new URL(document.referrer).origin+'/':'';}catch{return '';}}
  function tag(){window.dataLayer.push(arguments);}
  function event(name,params={}){
    if(!active || !window.wtfCookies.hasConsent('analytics'))return;
    tag('event',name,{send_to:id,content_group:group(),page_location:pageLocation(),page_referrer:referrer(),transport_type:'beacon',...params});
  }
  function eraseCookies(){
    const domains=location.hostname.split('.');
    const scopes=['',...domains.map((_,i)=>'; Domain='+domains.slice(i).join('.'))];
    const paths=['/',location.pathname,...location.pathname.split('/').slice(1).map((_,i)=>'/'+location.pathname.split('/').slice(1,i+2).join('/'))];
    document.cookie.split(';').forEach(item=>{
      const name=item.trim().split('=')[0];if(name!=='_ga' && name!==`_ga_${id.slice(2)}`)return;
      for(const domain of scopes)for(const path of paths)document.cookie=`${name}=; Max-Age=0; Path=${path}${domain}; SameSite=Lax${location.protocol==='https:'?'; Secure':''}`;
    });
  }
  window.wtfCookies.registerService('ga4',{
    category:'analytics',
    start(){
      active=true;window[disabled]=false;
      window.dataLayer=window.dataLayer||[];
      if(!configured){
        tag('consent','default',{analytics_storage:'denied',ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied'});
        tag('consent','update',{analytics_storage:'granted',ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied'});
        tag('js',new Date());
        tag('config',id,{send_page_view:false,allow_google_signals:false,allow_ad_personalization_signals:false,cookie_expires:15552000,cookie_update:false,page_location:pageLocation(),page_referrer:referrer(),content_group:group()});
        configured=true;
      }else tag('consent','update',{analytics_storage:'granted'});
      if(!script){script=document.createElement('script');script.async=true;script.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(id);document.head.append(script);}
      if(!viewSent){event('page_view');viewSent=true;}
    },
    stop(){
      active=false;window[disabled]=true;
      // Discard unprocessed events, including a consent/loading race.
      if(window.dataLayer)window.dataLayer.splice(0);
      eraseCookies();
      // A loaded SDK has its own listeners/timers. Reload without consent to remove it fully.
      location.reload();
    },
  });
  document.addEventListener('wtf:quiz-start',e=>event('quiz_start',{quiz_id:String(e.detail.quizId)}));
  document.addEventListener('wtf:quiz-complete',e=>event('quiz_complete',{quiz_id:String(e.detail.quizId)}));
  document.addEventListener('click',e=>{
    const link=e.target instanceof Element?e.target.closest('a'):null;
    if(!link)return;
    try{if(new URL(link.href).hostname==='gamefound.com')event('gamefound_click',{link_position:link.closest('.wtfq-campaign')?'quiz':group()});}catch{}
  });
})();
