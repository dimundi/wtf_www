const {chromium}=require(process.env.WTF_PLAYWRIGHT_MODULE||'playwright-core');
const assert=require('node:assert/strict');
const origin='http://localhost:8081',id='2354007262067045';
const sdk=`window.metaCalls=[];const f=window.fbq,pending=f.queue.splice(0);f.callMethod=function(){const a=[...arguments];window.metaCalls.push(a);if(a[0]==='init')document.cookie='_fbp=test-pixel; Path=/';if(a[0]==='trackSingle'){const i=new Image();i.src='https://www.facebook.com/tr/?id='+a[1]+'&ev='+a[2];}};pending.forEach(a=>f.callMethod.apply(f,a));`;
(async()=>{
 const browser=await chromium.launch({channel:'chrome',headless:true});const errors=[];
 async function context(emulate=true){const c=await browser.newContext();const requests=[];
  if(emulate)await c.addInitScript(()=>{let value;Object.defineProperty(window,'wtfMetaPixelConfig',{configurable:true,get:()=>value,set:x=>value={...x,enabled:x.publicRequest}});});
  await c.route('**/*',r=>{const url=r.request().url();if(new URL(url).origin===origin)return r.continue();requests.push(url);if(url==='https://connect.facebook.net/en_US/fbevents.js')return r.fulfill({contentType:'application/javascript',body:sdk});return r.fulfill({status:204,body:''});});
  const p=await c.newPage();p.on('pageerror',e=>errors.push(e.message));return{c,p,requests};
 }
 async function select(p,categories){await p.locator('.wtfc-footer-link').click();for(const key of ['media','analytics','marketing'])await p.locator(`[data-wtfc-category="${key}"]`).setChecked(categories.includes(key));await p.locator('[data-wtfc-save]').click();}
 const meta=urls=>urls.filter(x=>/facebook\.com|facebook\.net/.test(x));
 try{
  const native=await context(false);await native.p.goto(origin+'/kurzolaki/',{waitUntil:'networkidle'});await native.p.locator('[data-wtfc-accept]').click();await native.p.waitForTimeout(150);assert.equal(meta(native.requests).length,0);assert.equal(await native.p.evaluate(()=>window.fbq),undefined);await native.c.close();
  const {c,p,requests}=await context();
  await c.addCookies([{name:'wtf_cookies_consent',url:origin,value:encodeURIComponent(JSON.stringify({revision:2,at:Date.now(),categories:{media:true,analytics:true}}))}]);
  await p.goto(origin+'/produkt/kurzolaki/podstawka/',{waitUntil:'networkidle'});assert.ok(await p.locator('#wtfc-banner').isVisible());assert.equal(meta(requests).length,0);
  await p.locator('#wtfc-banner [data-wtfc-reject]').click();await p.reload({waitUntil:'networkidle'});assert.equal(meta(requests).length,0);
  await select(p,['analytics']);await p.waitForTimeout(100);assert.equal(meta(requests).length,0);
  await select(p,['analytics','marketing']);await p.waitForFunction(()=>window.metaCalls?.some(x=>x[2]==='ViewContent'));
  let calls=await p.evaluate(()=>window.metaCalls);assert.equal(calls.filter(x=>x[0]==='init'&&x[1]===id).length,1);assert.ok(calls.some(x=>x[0]==='set'&&x[1]==='autoConfig'&&x[2]===false));assert.equal(calls.filter(x=>x[2]==='PageView').length,1);assert.equal(calls.filter(x=>x[2]==='ViewContent').length,1);assert.equal(calls.find(x=>x[2]==='ViewContent')[3].content_ids[0],'806');assert.equal(await p.evaluate(()=>window.wtfCookies.hasConsent('media')),false);
  await select(p,['analytics','marketing']);calls=await p.evaluate(()=>window.metaCalls);assert.equal(calls.filter(x=>x[2]==='PageView').length,1);
  await c.addCookies([{name:'_fbc',url:origin,value:'test-click'}]);
  await p.locator('.wtfc-footer-link').click();await p.locator('[data-wtfc-category="marketing"]').uncheck();const before=meta(requests).length;await Promise.all([p.waitForNavigation({waitUntil:'networkidle'}),p.locator('[data-wtfc-save]').click()]);assert.equal(meta(requests).length,before);assert.equal(await p.evaluate(()=>window.wtfCookies.hasConsent('marketing')),false);assert.ok(!(await c.cookies()).some(x=>['_fbp','_fbc'].includes(x.name)));await p.reload({waitUntil:'networkidle'});assert.equal(meta(requests).length,before);
  // Even existing marketing consent must not start the SDK on private URLs.
  const revision=await p.evaluate(()=>window.wtfCookiesConfig.revision);await c.addCookies([{name:'wtf_cookies_consent',url:origin,value:encodeURIComponent(JSON.stringify({revision,at:Date.now(),categories:{media:false,analytics:false,marketing:true}}))}]);
  for(const path of ['/kurzolaki/?email=private-test@example.test&key=secret','/koszyk/','/moje-konto/']){await p.goto(origin+path,{waitUntil:'networkidle'});assert.equal(await p.evaluate(()=>window.wtfMetaPixelConfig?.enabled),false);assert.equal(meta(requests).length,before);}
  // Withdrawal on an excluded page still removes first-party Meta cookies.
  await c.addCookies([{name:'_fbp',url:origin,value:'test'},{name:'_fbc',url:origin,value:'test'}]);await select(p,[]);assert.ok(!(await c.cookies()).some(x=>['_fbp','_fbc'].includes(x.name)));await c.close();
  const nojs=await browser.newContext({javaScriptEnabled:false});let hits=0;await nojs.route('**/*',r=>{if(new URL(r.request().url()).origin===origin)return r.continue();hits++;return r.abort();});const np=await nojs.newPage();await np.goto(origin+'/kurzolaki/',{waitUntil:'networkidle'});assert.equal(hits,0);await nojs.close();assert.deepEqual(errors,[]);
  console.log('PASS: DEV disabled, old consent, reject, separate opt-in, PageView/ViewContent once, withdrawal and cookie cleanup, sensitive URLs, no-JS. Meta SDK simulated and all external requests intercepted.');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1);});
