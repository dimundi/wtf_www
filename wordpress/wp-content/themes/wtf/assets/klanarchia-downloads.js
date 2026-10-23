(() => {
  const root=document.querySelector('.klan-downloads');if(!root)return;
  root.querySelectorAll('a[href]').forEach(link=>{
    const url=new URL(link.href,location.href);
    if(url.origin===location.origin && url.pathname.startsWith('/download/Klanarchia/') && /\.(pdf|jpe?g|png|webp)$/i.test(url.pathname))link.setAttribute('download','');
  });
  const button=root.querySelector('[data-copy-coupon]');
  button?.addEventListener('click',async()=>{
    const code=root.querySelector('.klan-coupon-code').textContent.trim();const status=root.querySelector('[data-copy-status]');
    try{await navigator.clipboard.writeText(code);status.textContent='Skopiowano kod '+code+'.';}catch{status.textContent='Zaznacz kod i skopiuj go ręcznie.';}
  });
  if(button)button.hidden=false;
})();
