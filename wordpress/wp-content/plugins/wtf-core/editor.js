/* Bloki bez etapu kompilacji: korzystają z React dostarczanego przez WordPress. */
(() => {
  const {createElement: h, Fragment} = wp.element;
  const {TextControl, TextareaControl, Button, PanelBody, ToggleControl} = wp.components;
  const {useBlockProps, MediaUpload, MediaUploadCheck, InspectorControls} = wp.blockEditor;
  const ServerSideRender = wp.serverSideRender.default || wp.serverSideRender;
  const schemas = window.wtfBlockSchemas;
  const imageGuidance = {
    shop: 'Baner Elekta: zalecane 1920 × 800 px, bez napisów. Ilustracja po prawej, spokojne tło pod tekst po lewej. Na telefonie obraz jest kadrowany do węższego banera; zostaw zapas wokół głównego motywu.',
    header: 'Zalecany rozmiar: 600 × 600 px (kwadrat 1:1). Grafika jest kadrowana do kwadratu; najważniejsze elementy umieść bliżej środka. Bez drobnych napisów — na telefonach ikonka ma 36 × 36 px, a nazwa gry wyświetla się obok.',
    cards: 'Zalecany rozmiar: 900 × 750 px, najlepiej bez napisów. Zostaw zapas przy krawędziach — grafika jest kadrowana zależnie od szerokości ekranu. Najważniejsze elementy umieść bliżej środka.',
    hero: 'Zalecany rozmiar: 1920 × 800 px. Bez napisów i przycisków — dodaje je strona. Główny motyw po prawej, spokojniejsze tło po lewej pod tekst. Zostaw zapas przy krawędziach: na telefonach ten sam obraz jest mocniej kadrowany.',
  };
  function field(key, label, value, update, id, imageHelp) {
    if (key === 'image') return h('div', {key},
      imageHelp ? h('p', {className:'components-base-control__help'}, imageHelp) : null,
      value ? h('img', {src:value, alt:''}) : null,
      h(MediaUploadCheck, {}, h(MediaUpload, {allowedTypes:['image'], value:id, onSelect:media=>update({image:media.url,imageId:media.id}), render:({open})=>h(Button, {variant:'secondary',onClick:open},'Wybierz grafikę')})),
      h(TextControl,{label:'Adres grafiki',value:value||'',onChange:text=>update({[key]:text})}));
    const Control=['title','description','secondary','note'].includes(key)?TextareaControl:TextControl;
    return h(Control,{key,label,value:value||'',onChange:text=>update({[key]:text})});
  }
  Object.entries(schemas).forEach(([name,schema])=>wp.blocks.registerBlockType('wtf/'+name,{
    apiVersion:3,title:schema.title,icon:'layout',category:'wtf',
    attributes:Object.fromEntries(Object.entries(schema.defaults).map(([key,value])=>[key,{type:Array.isArray(value)?'array':typeof value==='number'?'number':'string',default:value}])),
    supports:{html:false},
    edit:({attributes:a,setAttributes,isSelected})=>{
      const props=useBlockProps();
      function updateItem(index,changes){setAttributes({items:a.items.map((item,i)=>i===index?{...item,...changes}:item)});}
      const editFields=h('div',{className:'wtf-editor-fields'},h('h3',{},schema.title),
        ...Object.entries(schema.fields).map(([key,label])=>field(key,label,a[key],setAttributes,a.imageId,imageGuidance[name])),
        schema.itemFields?h(Fragment,{},...a.items.map((item,index)=>h('div',{className:'wtf-editor-item',key:index},h('h4',{},'Pozycja '+(index+1)),
          ...Object.entries(schema.itemFields).map(([key,label])=>field(key,label,item[key],changes=>updateItem(index,changes),item.imageId,imageGuidance[name])),
          h(Button,{variant:'secondary',disabled:index===0,onClick:()=>{const items=[...a.items];[items[index-1],items[index]]=[items[index],items[index-1]];setAttributes({items});}},'Przenieś wyżej'),
          h(Button,{variant:'secondary',disabled:index===a.items.length-1,onClick:()=>{const items=[...a.items];[items[index+1],items[index]]=[items[index],items[index+1]];setAttributes({items});}},'Przenieś niżej'),
          h(Button,{isDestructive:true,onClick:()=>setAttributes({items:a.items.filter((_,i)=>i!==index)})},'Usuń pozycję'))),
          h(Button,{variant:'primary',onClick:()=>setAttributes({items:[...a.items,Object.fromEntries(Object.keys(schema.itemFields).map(key=>[key,'']))]})},'Dodaj pozycję')):null);
      return h('div',props,
        h(InspectorControls,{},h(PanelBody,{title:'Edycja sekcji'},h('p',{},'Wybierz blok, aby zmieniać teksty, grafiki i linki. Bloki można przenosić lub usuwać w widoku listy.'))),
        isSelected?editFields:h(ServerSideRender,{block:'wtf/'+name,attributes:a}));
    },save:()=>null,
  }));
})();
