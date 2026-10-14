(() => {
 const config=window.wtfFaqConfig;if(!config)return;
 const key=config.storageKey;
 let visitor,votes={};
 try{visitor=localStorage.getItem(key+':visitor');votes=JSON.parse(localStorage.getItem(key+':votes')||'{}');}catch{}
 if(!visitor||!/^[a-zA-Z0-9-]{32,64}$/.test(visitor)){visitor=crypto.randomUUID?crypto.randomUUID():Array.from(crypto.getRandomValues(new Uint8Array(16)),v=>v.toString(16).padStart(2,'0')).join('');try{localStorage.setItem(key+':visitor',visitor);}catch{}}
 if(!votes||typeof votes!=='object')votes={};
 function sync(id,direction,counts){document.querySelectorAll('[data-wtf-faq] [data-faq-id="'+id+'"]').forEach(item=>{
  item.querySelectorAll('[data-vote]').forEach(button=>{button.disabled=!!direction;button.setAttribute('aria-pressed',String(button.dataset.vote===direction));if(counts)button.querySelector('[data-vote-count]').textContent=counts[button.dataset.vote];});
  if(direction)item.querySelector('.wtf-faq__status').textContent='Dziękujemy za głos.';
 });}
 document.querySelectorAll('[data-wtf-faq] [data-faq-id]').forEach(item=>{const id=item.dataset.faqId;if(votes[id])sync(id,votes[id]);});
 document.addEventListener('click',async event=>{
  const button=event.target.closest('[data-wtf-faq] [data-vote]');if(!button||button.disabled)return;
  const item=button.closest('[data-faq-id]'),id=item.dataset.faqId,status=item.querySelector('.wtf-faq__status');
  if(votes[id]){sync(id,votes[id]);return;}
  document.querySelectorAll('[data-wtf-faq] [data-faq-id="'+id+'"] [data-vote]').forEach(b=>b.disabled=true);
  status.textContent='Zapisywanie…';
  try{
   const headers={'Content-Type':'application/json'};if(config.restNonce)headers['X-WP-Nonce']=config.restNonce;
   const response=await fetch(config.endpoint,{method:'POST',headers,credentials:'same-origin',body:JSON.stringify({id:Number(id),direction:button.dataset.vote,visitor})});
   const result=await response.json();if(!response.ok)throw Error(result.message||'Nie udało się zapisać głosu.');
   votes[id]=result.direction;try{localStorage.setItem(key+':votes',JSON.stringify(votes));}catch{}
   sync(id,result.direction,result.counts);
  }catch(error){sync(id,null);status.textContent=error.message;}
 });
})();
