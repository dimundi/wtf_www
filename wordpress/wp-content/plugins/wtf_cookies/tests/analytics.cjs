const {chromium}=require(process.env.WTF_PLAYWRIGHT_MODULE || 'playwright-core');
const assert=require('node:assert/strict');
const prod=process.argv.includes('--prod');
const origin=prod?'https://whatthefrog.pl':'http://localhost:8081',id='G-ZLESXQL7WK';
(async()=>{
 const browser=await chromium.launch({channel:'chrome',headless:true});
 try{
  // Native DEV must not send even after consent.
  if(!prod){const dev=await browser.newContext();let devGoogle=0;
  await dev.route('**/*',r=>{if(new URL(r.request().url()).origin===origin)return r.continue();if(/google-analytics|googletagmanager/.test(r.request().url()))devGoogle++;return r.abort();});
  const dp=await dev.newPage();await dp.goto(origin+'/quiz-klanarchia/',{waitUntil:'networkidle'});await dp.locator('[data-wtfc-accept]').click();assert.equal(devGoogle,0);assert.equal(await dp.locator('script[src*="googletagmanager"]').count(),0);await dev.close();}

  // Emulate production configuration in the browser only. Never enable real collection on DEV.
  const context=await browser.newContext();const requests=[],errors=[];
  if(!prod)await context.addInitScript(()=>{let config;Object.defineProperty(window,'wtfAnalyticsConfig',{configurable:true,get:()=>config,set:value=>{config={...value,enabled:true};}});});
  await context.route('**/*',async route=>{
   const request=route.request(),url=request.url();
   if(new URL(url).origin===origin)return route.continue();
   requests.push({url,body:request.postData()});
   if(url.startsWith('https://www.googletagmanager.com/gtag/js?id='+id)){
    if(process.argv.includes('--real-sdk'))return route.fulfill({response:await route.fetch()});
    return route.fulfill({contentType:'application/javascript',body:'window.analyticsSDKLoaded=true;'});
   }
   // No collection request or other third-party traffic leaves this browser.
   return route.fulfill({status:204,body:''});
  });
  await context.addCookies([{name:'wtf_cookies_consent',value:encodeURIComponent(JSON.stringify({revision:1,at:Date.now(),categories:{media:true}})),url:origin}]);
  const page=await context.newPage();page.on('pageerror',e=>errors.push(e.message));
  await page.goto(origin+'/quiz-klanarchia/?email=private-test@example.test&key=secret-test&utm_source=messenger&utm_medium=social',{waitUntil:'networkidle'});
  assert.equal(await page.evaluate(()=>window.wtfAnalyticsConfig.enabled),true);
  assert.equal(await page.locator('meta[name="google-site-verification"]').getAttribute('content'),'eLec4cfCaTeNAysFFDIwObuq6zhcJx8xlWe1XloeX2A');
  assert.equal(await page.locator('#wtfc-banner').isVisible(),true);assert.equal(requests.length,0);
  await page.locator('#wtfc-banner [data-wtfc-reject]').click();await page.reload({waitUntil:'networkidle'});assert.equal(requests.length,0);
  await page.locator('.wtfc-footer-link').click();await page.locator('[data-wtfc-category="analytics"]').check();await page.locator('[data-wtfc-save]').click();
  await page.waitForFunction(()=>window.dataLayer?.some(x=>x[0]==='event'&&x[1]==='page_view'));
  assert.equal(requests.filter(r=>r.url.includes('/gtag/js?')).length,1);
  assert.equal(await page.evaluate(()=>window.wtfCookies.hasConsent('media')),false);
  let calls=await page.evaluate(()=>window.dataLayer.map(x=>Array.from(x)));
  assert.equal(calls.filter(x=>x[0]==='event'&&x[1]==='page_view').length,1);
  assert.ok(!JSON.stringify(calls).includes('private-test'));assert.ok(!JSON.stringify(calls).includes('secret-test'));
  const config=calls.find(x=>x[0]==='config')[2];assert.equal(config.send_page_view,false);assert.equal(config.allow_google_signals,false);assert.equal(config.allow_ad_personalization_signals,false);
  for(let i=0;i<10;i++){
   await page.locator('.wtfq-question:visible input').first().check();
   if(i===0){await page.locator('.wtfq-question:visible input').nth(1).check();await page.locator('.wtfq-question:visible input').first().check();}
   if(i<9)await page.locator('[data-next]').click();
  }
  assert.equal(await page.evaluate(()=>window.dataLayer.filter(x=>x[0]==='event'&&x[1]==='quiz_start').length),1);
  // Capture completion before the normal result navigation; avoid generating a second test pageview.
  await page.route('**/klanarchia/archetyp-*/**',r=>r.fulfill({contentType:'text/html',body:'<!doctype html><title>Test result</title>'}));
  // Observe the event synchronously and persist it only in the test browser.
  await page.evaluate(()=>document.addEventListener('wtf:quiz-complete',()=>sessionStorage.setItem('test-complete',JSON.stringify(window.dataLayer.map(x=>Array.from(x))))));
  await Promise.all([page.waitForURL('**/klanarchia/archetyp-*/**'),page.locator('[data-next]').click()]);
  const complete=await page.evaluate(()=>JSON.parse(sessionStorage.getItem('test-complete'))).catch(()=>null);
  assert.ok(complete?.some(x=>x[0]==='event'&&x[1]==='quiz_complete'&&x[2].quiz_id==='3144'));
  await page.goto(origin+'/kurzolaki/',{waitUntil:'networkidle'});
  assert.ok(await page.evaluate(()=>window.dataLayer.some(x=>x[0]==='event'&&x[1]==='page_view'&&x[2].content_group==='kurzolaki')));
  await page.evaluate(()=>{const a=document.createElement('a');a.href='https://gamefound.com/en/projects/whatthefrog/klanarchia';a.textContent='test';a.addEventListener('click',e=>e.preventDefault());document.body.append(a);a.click();a.remove();});
  assert.equal(await page.evaluate(()=>window.dataLayer.filter(x=>x[0]==='event'&&x[1]==='gamefound_click').length),1);
  await context.addCookies([{name:'_ga',value:'test',url:origin},{name:'_ga_ZLESXQL7WK',value:'test',domain:new URL(origin).hostname,path:'/'}]);
  const beforeRevoke=requests.filter(r=>r.url.includes('/gtag/js?')).length;
  await page.locator('.wtfc-footer-link').click();await page.locator('[data-wtfc-category="analytics"]').uncheck();
  await Promise.all([page.waitForNavigation({waitUntil:'networkidle'}),page.locator('[data-wtfc-save]').click()]);
  assert.equal(await page.evaluate(()=>window.wtfCookies.hasConsent('analytics')),false);
  assert.equal(requests.filter(r=>r.url.includes('/gtag/js?')).length,beforeRevoke);
  assert.ok(!(await context.cookies()).some(c=>c.name==='_ga'||c.name==='_ga_ZLESXQL7WK'));
  assert.deepEqual(errors,[]);
  if(process.argv.includes('--real-sdk')){
   const hits=requests.filter(r=>/google-analytics\.com\/g\/collect/.test(r.url));
   assert.ok(hits.length>0,'Real SDK must attempt collection (intercepted)');
   assert.ok(!JSON.stringify(hits).includes('private-test'));assert.ok(!JSON.stringify(hits).includes('secret-test'));
   console.log('Real Google SDK loaded; collection intercepted, sanitized URLs and withdrawal verified');
  }
  await context.close();
  console.log('Analytics: DEV disabled, old consent invalidated, opt-in/reject, one pageview/start, quiz completion, content groups, CTA, withdrawal and cookie deletion OK');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
