(() => {
 const taxonomy=new URLSearchParams(location.search).get('taxonomy');
 if(['wtf_faq_group','wtf_faq_game'].includes(taxonomy)){
  const panel=document.getElementById('col-left'),container=document.getElementById('col-container'),title=document.querySelector('.wrap>h1');
  if(panel&&container&&title){
   const button=document.createElement('button');button.type='button';button.className='page-title-action';button.id='wtf-faq-add-term';button.textContent=taxonomy==='wtf_faq_group'?'Dodaj grupę':'Dodaj grę';
   button.setAttribute('aria-controls',panel.id);button.setAttribute('aria-expanded','false');
   title.after(button);container.before(panel);panel.hidden=true;document.body.classList.add('wtf-faq-terms-ready');
   const formTitle=panel.querySelector('.form-wrap>h2');if(formTitle)formTitle.textContent=button.textContent;
   button.addEventListener('click',()=>{panel.hidden=!panel.hidden;button.setAttribute('aria-expanded',String(!panel.hidden));if(!panel.hidden)panel.querySelector('#tag-name')?.focus();});
  }
 }
 const game=document.getElementById('wtf-faq-game'),groups=document.getElementById('wtf-faq-groups'),output=document.getElementById('wtf-faq-shortcode');
 if(!game||!groups||!output)return;
 function update(){const selected=[...groups.querySelectorAll('input:checked')].map(i=>i.value);output.value=selected.length?'[wtf_faq'+(game.value?' game="'+game.value+'"':'')+' groups="'+selected.join(',')+'"]':'';}
 game.addEventListener('change',update);groups.addEventListener('change',update);output.addEventListener('click',()=>output.select());update();
})();
