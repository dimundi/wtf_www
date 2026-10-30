(() => {
  document.querySelectorAll('[data-kurz-game]').forEach(root => {
    const $=s=>root.querySelector(s),arena=$('[data-arena]'),canvas=$('[data-canvas]'),ctx=canvas.getContext('2d'),overlay=$('[data-overlay]'),start=$('[data-start]'),pause=$('[data-pause]'),dash=$('[data-dash]'),scoreEl=$('[data-score]'),timeEl=$('[data-time]'),status=$('[data-status]'),message=$('[data-message]'),tag=$('[data-tag]');
    if(!ctx){status.textContent='Ta przeglądarka nie obsługuje planszy gry.';start.disabled=true;return;}
    const keys=new Set(),art={};let w=600,h=420,state='ready',score=0,left=45,last=0,elapsed=0,cooldown=0,burst=0,particles=[],candy=[],trail=[],touch=null;
    let player={x:90,y:200,dx:1,dy:0},enemy={x:500,y:200,vx:0,vy:0};
    const clamp=(v,a,b)=>Math.max(a,Math.min(b,v)),dist=(a,b)=>Math.hypot(a.x-b.x,a.y-b.y);
    function setState(s){state=s;root.dataset.gameState=s;pause.disabled=s!=='playing'&&s!=='paused';dash.disabled=s!=='playing';}
    function resize(){const oldW=w,oldH=h;w=arena.clientWidth;h=arena.clientHeight;const dpr=Math.min(devicePixelRatio||1,2);canvas.width=Math.round(w*dpr);canvas.height=Math.round(h*dpr);ctx.setTransform(dpr,0,0,dpr,0,0);player.x=clamp(player.x/oldW*w,22,w-22);player.y=clamp(player.y/oldH*h,28,h-24);enemy.x=clamp(enemy.x/oldW*w,22,w-22);enemy.y=clamp(enemy.y/oldH*h,28,h-24);candy.forEach(c=>{c.x=clamp(c.x/oldW*w,23,w-23);c.y=clamp(c.y/oldH*h,30,h-25);});trail=[];draw();}
    function spawn(){let c;for(let i=0;i<80;i++){c={x:28+Math.random()*(w-56),y:35+Math.random()*(h-70),color:['#cf3979','#df9934','#719c36'][Math.floor(Math.random()*3)],phase:Math.random()*6};if(dist(c,player)>65&&dist(c,enemy)>80&&candy.every(other=>dist(c,other)>55))break;}return c;}
    function reset(){score=0;left=45;elapsed=0;cooldown=0;burst=0;particles=[];trail=[];keys.clear();touch=null;player={x:w*.22,y:h*.5,dx:1,dy:0};enemy={x:w*.8,y:h*.45,vx:0,vy:0};candy=[];for(let i=0;i<3;i++)candy.push(spawn());scoreEl.textContent='0';timeEl.textContent='45';overlay.hidden=true;pause.textContent='Pauza';setState('playing');status.textContent='Puca cię widzi. Ruszaj!';canvas.focus({preventScroll:true});}
    function finish(caught){setState('over');keys.clear();touch=null;overlay.hidden=false;tag.textContent=caught?'PUCA: 1 · HIGIENA: 100%':'KONIEC SŁODKIEJ ZMIANY';message.textContent=caught?'Puca zrobiła z ciebie porządnego obywatela.':'45 sekund bez mycia. Szacunek!';$('[data-overlay] .kg-overlay-hint').textContent=`Zebrane landrynki: ${score}. ${score>0?'Dasz radę zgarnąć więcej?':'Następnym razem ruszaj od razu!'}`;start.textContent='Jeszcze raz!';status.textContent=`${caught?'Złapany!':'Czas minął!'} Twój wynik: ${score}.`;start.focus({preventScroll:true});}
    function togglePause(){if(state==='playing'){setState('paused');keys.clear();touch=null;pause.textContent='Wznów';status.textContent='Pauza. Puca też musi odsapnąć.';}else if(state==='paused'){setState('playing');pause.textContent='Pauza';status.textContent='Uciekamy dalej!';canvas.focus({preventScroll:true});}}
    function sprint(){if(state!=='playing'||cooldown>0)return;burst=.19;cooldown=2.5;}
    function update(dt){elapsed+=dt;left=Math.max(0,45-elapsed);timeEl.textContent=String(Math.ceil(left));if(left<=0){finish(false);return;}
      cooldown=Math.max(0,cooldown-dt);burst=Math.max(0,burst-dt);dash.disabled=cooldown>0;dash.innerHTML=cooldown>0?`Zryw za ${cooldown.toFixed(1)} s`:'Zryw! <small>SPACJA</small>';
      let dx=(keys.has('ArrowRight')||keys.has('d')?1:0)-(keys.has('ArrowLeft')||keys.has('a')?1:0),dy=(keys.has('ArrowDown')||keys.has('s')?1:0)-(keys.has('ArrowUp')||keys.has('w')?1:0);
      if(touch){dx=clamp((touch.x-touch.ox)/36,-1,1);dy=clamp((touch.y-touch.oy)/36,-1,1);}
      const length=Math.hypot(dx,dy);if(length>1){dx/=length;dy/=length;}if(length>.1){player.dx=dx/(Math.hypot(dx,dy)||1);player.dy=dy/(Math.hypot(dx,dy)||1);}
      const speed=burst>0?520:190;if(burst>0&&length<.1){dx=player.dx;dy=player.dy;}player.x=clamp(player.x+dx*speed*dt,20,w-20);player.y=clamp(player.y+dy*speed*dt,30,h-23);
      const distance=dist(player,enemy)||1,enemySpeed=Math.min(164,108+score*3+elapsed*.65),steer=1-Math.exp(-dt*3.2);enemy.vx+=(player.x-enemy.x)/distance*enemySpeed*steer-enemy.vx*steer;enemy.vy+=(player.y-enemy.y)/distance*enemySpeed*steer-enemy.vy*steer;enemy.x=clamp(enemy.x+enemy.vx*dt,22,w-22);enemy.y=clamp(enemy.y+enemy.vy*dt,30,h-23);
      if(!trail.length||dist(enemy,trail[trail.length-1])>12)trail.push({x:enemy.x,y:enemy.y,life:1});trail.forEach(p=>p.life-=dt*.45);trail=trail.filter(p=>p.life>0).slice(-45);
      for(let i=0;i<candy.length;i++)if(dist(player,candy[i])<29){const c=candy[i];scoreEl.textContent=String(++score);for(let j=0;j<12;j++){let a=Math.random()*Math.PI*2;particles.push({x:c.x,y:c.y,vx:Math.cos(a)*(35+Math.random()*80),vy:Math.sin(a)*(35+Math.random()*80),life:.6,color:c.color});}candy[i]=spawn();}
      particles.forEach(p=>{p.x+=p.vx*dt;p.y+=p.vy*dt;p.life-=dt;});particles=particles.filter(p=>p.life>0);if(dist(player,enemy)<30)finish(true);
    }
    function ellipse(x,y,rx,ry,color){ctx.fillStyle=color;ctx.beginPath();ctx.ellipse(x,y,rx,ry,0,0,Math.PI*2);ctx.fill();}
    function sprite(name,p,size){ellipse(p.x,p.y+18,size*.32,7,'#283d3928');const img=art[name];if(img){const ratio=img.width/img.height,hh=size/Math.max(1,ratio),ww=hh*ratio;ctx.drawImage(img,p.x-ww/2,p.y-hh+25,ww,hh);}else{ellipse(p.x,p.y,19,22,name==='player'?'#297d74':'#b7dad9');ellipse(p.x-6,p.y-5,4,5,'white');ellipse(p.x+6,p.y-5,4,5,'white');}}
    function sweet(c){ctx.save();ctx.translate(c.x,c.y+Math.sin(elapsed*3+c.phase)*2);ctx.rotate(-.25);ctx.fillStyle=c.color;ctx.beginPath();ctx.moveTo(-9,-4);ctx.lineTo(-18,-10);ctx.lineTo(-18,10);ctx.lineTo(-9,4);ctx.moveTo(9,-4);ctx.lineTo(18,-10);ctx.lineTo(18,10);ctx.lineTo(9,4);ctx.fill();ellipse(0,0,10,12,c.color);ellipse(-3,-3,3,6,'#ffffff80');ctx.restore();}
    function draw(){ctx.clearRect(0,0,w,h);ctx.save();ctx.strokeStyle='#ab8c6330';ctx.lineWidth=1;for(let y=35;y<h;y+=65){ctx.beginPath();ctx.moveTo(0,y);ctx.lineTo(w,y+5);ctx.stroke();}for(let i=0;i<16;i++){const x=(i*137+37)%w,y=(i*97+29)%h;ellipse(x,y,2+i%3,1.5,'#887a5933');}
      for(const p of trail)ellipse(p.x,p.y+12,19,9,`rgba(152,210,215,${p.life*.27})`);
      candy.forEach(sweet);if(burst>0)for(let i=1;i<4;i++)ellipse(player.x-player.dx*i*12,player.y-player.dy*i*12,15-i*3,10-i*2,'#a3345e33');
      const sprites=[['player',player,70],['enemy',enemy,82]].sort((a,b)=>a[1].y-b[1].y);sprites.forEach(s=>sprite(...s));
      if(state==='playing'){ctx.font='bold 10px Verdana';ctx.textAlign='center';ctx.fillStyle='#283d39';ctx.fillText('TY',player.x,player.y-49);ctx.fillStyle='#367781';ctx.fillText('PUCA',enemy.x,enemy.y-58);}
      particles.forEach(p=>ellipse(p.x,p.y,3,3,p.color));if(touch){ellipse(touch.ox,touch.oy,34,34,'#283d3920');ellipse(touch.ox+clamp(touch.x-touch.ox,-28,28),touch.oy+clamp(touch.y-touch.oy,-28,28),14,14,'#a3345e80');}
      if(state==='paused'){ctx.fillStyle='#f2ede3d9';ctx.fillRect(0,0,w,h);ctx.fillStyle='#a3345e';ctx.font='bold 32px Trebuchet MS';ctx.textAlign='center';ctx.fillText('Pauza',w/2,h/2);}ctx.restore();
    }
    function frame(now){const dt=Math.min((now-last)/1000||0,.04);last=now;if(state==='playing')update(dt);if(state==='playing'||state==='paused')draw();requestAnimationFrame(frame);}
    start.addEventListener('click',reset);dash.addEventListener('click',sprint);pause.addEventListener('click',togglePause);
    const controls=['ArrowUp','ArrowDown','ArrowLeft','ArrowRight','w','a','s','d',' '];
    window.addEventListener('keydown',e=>{const k=e.key.length===1?e.key.toLowerCase():e.key;if(state!=='playing')return;if(controls.includes(k)){e.preventDefault();keys.add(k);if(k===' '&&!e.repeat)sprint();}if(e.key==='Escape')togglePause();});window.addEventListener('keyup',e=>keys.delete(e.key.length===1?e.key.toLowerCase():e.key));
    canvas.addEventListener('pointerdown',e=>{if(state!=='playing'||touch)return;e.preventDefault();const r=canvas.getBoundingClientRect();touch={id:e.pointerId,ox:e.clientX-r.left,oy:e.clientY-r.top,x:e.clientX-r.left,y:e.clientY-r.top};canvas.setPointerCapture(e.pointerId);});
    canvas.addEventListener('pointermove',e=>{if(!touch||touch.id!==e.pointerId)return;const r=canvas.getBoundingClientRect();touch.x=e.clientX-r.left;touch.y=e.clientY-r.top;});for(const type of ['pointerup','pointercancel','lostpointercapture'])canvas.addEventListener(type,e=>{if(touch?.id===e.pointerId)touch=null;});
    window.addEventListener('blur',()=>{if(state==='playing')togglePause();});document.addEventListener('visibilitychange',()=>{if(document.hidden&&state==='playing')togglePause();});new ResizeObserver(resize).observe(arena);
    for(const name of ['player','enemy']){const img=new Image();img.onload=()=>{art[name]=img;draw();};img.src=$('[data-art]').dataset[name];}setState('ready');resize();requestAnimationFrame(frame);
  });
})();
