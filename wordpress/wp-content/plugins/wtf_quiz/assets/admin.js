(() => {
  const element = (tag, attrs = {}, text = '') => {
    const el = document.createElement(tag);
    Object.entries(attrs).forEach(([key, value]) => el.setAttribute(key, value));
    el.textContent = text;
    return el;
  };
  let sequence = Date.now();
  const tabs=[...document.querySelectorAll('[data-wtfq-tab]')];
  const selectTab=button=>tabs.forEach(tab=>{
    tab.classList.toggle('nav-tab-active',tab===button);
    const box=document.getElementById(tab.dataset.wtfqTab);if(box)box.hidden=tab!==button;
  });
  tabs.forEach(button=>button.addEventListener('click',()=>selectTab(button)));
  if(tabs.length)selectTab(tabs[0]);
  const key = () => String(++sequence);
  document.querySelector('[data-add-outcome]')?.addEventListener('click', () => {
    const id = key();
    const card = element('div', {class:'wtfq-outcome wtfq-card'});
    card.append(element('h3', {}, 'Wynik / archetyp'));
    card.append(element('input', {type:'hidden', name:`wtfq_outcomes[${id}][id]`,value:`outcome_${id}`}));
    [['title','Nazwa wyniku','text'],['url','Adres podstrony archetypu','url']].forEach(([field, title, type]) => {
      const p = element('p'); const label = element('label', {}, title);
      const input = element('input', {class:'widefat',type,name:`wtfq_outcomes[${id}][${field}]`});
      if (field === 'title') input.required = true;
      label.append(input);p.append(label);card.append(p);
    });
    const editor = `wtfq_result_${id}`;
    card.append(element('p', {}, 'Opis wyniku i ilustracja'),element('textarea',{id:editor,name:`wtfq_outcomes[${id}][content]`,rows:'7',class:'widefat'}));
    const remove = element('button',{type:'button',class:'button-link-delete','data-remove':''},'Usuń wynik');
    const p=element('p');p.append(remove);card.append(p);
    document.querySelector('[data-outcomes]').append(card);
    wp.editor.initialize(editor,{tinymce:{wpautop:true,toolbar1:'formatselect bold italic bullist numlist link unlink image'},quicktags:true,mediaButtons:true});
    card.querySelector('input[type=text]').focus();
  });
  document.addEventListener('click',event => {
    const remove=event.target.closest('[data-remove]');if(!remove)return;
    const card=remove.closest('.wtfq-card');
    if(card.classList.contains('wtfq-outcome') && !confirm('Usunąć wynik? Sprawdź później przypisania w pytaniach.'))return;
    card.querySelectorAll('textarea[id]').forEach(el=>wp.editor.remove(el.id));card.remove();
  });
  const answers=document.querySelector('[data-answers]');
  const quiz=document.querySelector('#wtfq-quiz');
  const fillTargets = (row, selected = []) => {
    const targets=row.querySelector('[data-targets]');targets.replaceChildren();
    const outcomes=wtfqAdmin.quizzes[quiz.value] || [];
    if(!outcomes.length)targets.append(element('p',{class:'description'},'Najpierw dodaj wyniki w ustawieniach wybranego quizu.'));
    outcomes.forEach(outcome=>{
      const label=element('label');const input=element('input',{type:'checkbox',name:`wtfq_answers[${row.dataset.key}][outcomes][]`,value:outcome.id});
      input.checked=selected.includes(outcome.id);label.append(input,document.createTextNode(` ${outcome.title} (+1)`));targets.append(label);
    });
  };
  const addAnswer = (answer={text:'',outcomes:[]}) => {
    const id=key();const row=element('div',{class:'wtfq-card','data-key':id});
    const label=element('label',{},'Treść odpowiedzi');label.append(element('input',{type:'text',class:'widefat',name:`wtfq_answers[${id}][text]`,value:answer.text}));
    row.append(label,element('div',{'data-targets':'',class:'wtfq-targets'}),element('button',{type:'button',class:'button-link-delete','data-remove':''},'Usuń odpowiedź'));
    answers.append(row);fillTargets(row,answer.outcomes);
    return row;
  };
  if(answers){
    JSON.parse(answers.dataset.initial).forEach(addAnswer);
    document.querySelector('[data-add-answer]').addEventListener('click',()=>addAnswer().querySelector('input').focus());
    quiz.addEventListener('change',()=>answers.querySelectorAll('.wtfq-card').forEach(row=>fillTargets(row,[])));
  }
  document.querySelector('[data-choose-image]')?.addEventListener('click',()=>{
    const frame=wp.media({title:'Ilustracja pytania',library:{type:'image'},multiple:false});
    frame.on('select',()=>{
      const item=frame.state().get('selection').first().toJSON();
      document.querySelector('[name=wtfq_image]').value=item.id;
      const image=element('img',{src:item.sizes?.medium?.url || item.url,alt:item.alt || ''});document.querySelector('[data-image-preview]').replaceChildren(image);
    });frame.open();
  });
  document.querySelector('[data-clear-image]')?.addEventListener('click',()=>{document.querySelector('[name=wtfq_image]').value='0';document.querySelector('[data-image-preview]').replaceChildren();});
})();
