/* Answers and scoring stay in the browser; the winner opens its configured page. */
(() => {
  const scoreQuiz = (data, answers, random = Math.random) => {
    const scores=Object.fromEntries(data.outcomes.map(id=>[id,0]));
    data.questions.forEach((question,index)=>{
      const targets=question[answers[index]];
      if(!Array.isArray(targets))throw new Error('Brak odpowiedzi');
      targets.forEach(id=>{if(Object.hasOwn(scores,id))scores[id]++;});
    });
    const highest=Math.max(...Object.values(scores));
    const tied=data.outcomes.filter(id=>scores[id]===highest);
    return tied[Math.floor(random()*tied.length)];
  };
  if(typeof module!=='undefined' && module.exports)module.exports={scoreQuiz};
  if(typeof document==='undefined')return;
  document.querySelectorAll('[data-wtfq-outcome]').forEach(root=>{
    const earned=new URLSearchParams(location.search).get('wynik')==='quiz';
    root.querySelector('[data-outcome-direct]').hidden=earned;
    root.querySelector('[data-outcome-earned]').hidden=!earned;
    root.querySelector('[data-outcome-controls]').hidden=!earned;
  });
  document.querySelectorAll('[data-wtfq]').forEach(root=>{
    const data=JSON.parse(root.querySelector('.wtfq-data').textContent);
    const app=root.querySelector('.wtfq-app');const form=root.querySelector('form');
    const fields=[...form.querySelectorAll('fieldset')];const progress=root.querySelector('.wtfq-progress');
    const error=root.querySelector('.wtfq-error');const previous=root.querySelector('[data-prev]');const next=root.querySelector('[data-next]');
    const result=root.querySelector('.wtfq-result');let index=0;
    const show=(focus=true)=>{
      fields.forEach((field,n)=>field.hidden=n!==index);
      progress.textContent=`Pytanie ${index+1} z ${fields.length}`;
      previous.hidden=index===0;next.textContent=index===fields.length-1?'Pokaż mój archetyp':'Dalej';error.textContent='';
      if(focus)fields[index].querySelector('legend').focus();
    };
    previous.addEventListener('click',()=>{index=Math.max(0,index-1);show();});
    form.addEventListener('change',()=>error.textContent='');
    form.addEventListener('submit',event=>{
      event.preventDefault();
      const selected=fields[index].querySelector('input:checked');
      if(!selected){error.textContent='Wybierz jedną odpowiedź, aby przejść dalej.';fields[index].querySelector('input').focus();return;}
      if(index<fields.length-1){index++;show();return;}
      const answers=fields.map(field=>Number(field.querySelector('input:checked')?.value));
      const outcome=scoreQuiz(data,answers);
      if(data.urls?.[outcome]){
        const destination=new URL(data.urls[outcome],location.href);
        if(destination.origin===location.origin){destination.searchParams.set('wynik','quiz');location.assign(destination.href);return;}
      }
      app.hidden=true;result.hidden=false;root.querySelector('.wtfq-heading').hidden=true;root.classList.add('wtfq--complete');
      const article=[...result.querySelectorAll('[data-outcome]')].find(el=>el.dataset.outcome===outcome);
      result.querySelectorAll('[data-outcome]').forEach(el=>el.hidden=el!==article);
      article.querySelector('h2').focus();
    });
    root.querySelector('[data-restart]').addEventListener('click',()=>{form.reset();result.hidden=true;app.hidden=false;root.querySelector('.wtfq-heading').hidden=false;root.classList.remove('wtfq--complete');index=0;show();});
    app.hidden=false;show(false);
  });
})();
