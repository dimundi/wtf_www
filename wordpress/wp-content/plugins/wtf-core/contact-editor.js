(() => {
  const {createElement:h, Fragment} = wp.element;
  const {useBlockProps, InnerBlocks, RichText, InspectorControls, MediaUpload, MediaUploadCheck} = wp.blockEditor;
  const {PanelBody, TextControl, Button} = wp.components;
  const base={apiVersion:3,category:'wtf',icon:'layout',supports:{html:false}};
  const object={type:'object',default:{}};
  const string=defaultValue=>({type:'string',default:defaultValue});
  function linkPanel(a,set){return a.attributes.href!==undefined?h(InspectorControls,{},h(PanelBody,{title:'Link'},h(TextControl,{label:'Adres',value:a.attributes.href,onChange:href=>set({attributes:{...a.attributes,href}})}))):null;}
  wp.blocks.registerBlockType('wtf/contact-container',{...base,title:'Układ kontaktu',attributes:{tag:string('div'),attributes:object,label:string('Układ')},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps({className:a.attributes.class}),linkPanel(a,setAttributes),h('div',{style:{fontSize:11,opacity:.65}},a.label),h(InnerBlocks)),
    save:()=>h(InnerBlocks.Content),
  });
  wp.blocks.registerBlockType('wtf/contact-text',{...base,title:'Tekst kontaktu',icon:'editor-paragraph',attributes:{tag:string('p'),content:string(''),attributes:object},
    edit:({attributes:a,setAttributes})=>h(Fragment,{},linkPanel(a,setAttributes),h(RichText,{...useBlockProps({className:a.attributes.class}),tagName:a.tag==='a'?'p':a.tag,value:a.content,onChange:content=>setAttributes({content}),placeholder:'Wpisz tekst…'})),save:()=>null,
  });
  const fields={nameLabel:'Etykieta imienia',emailLabel:'Etykieta e-maila',subjectLabel:'Etykieta tematu',messageLabel:'Etykieta wiadomości',buttonLabel:'Napis przycisku'};
  wp.blocks.registerBlockType('wtf/contact-form',{...base,title:'Formularz kontaktowy',icon:'email',attributes:Object.fromEntries(Object.keys(fields).map(k=>[k,string('')])),
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps(),h('p',{},'Formularz wysyła wiadomości na adres ustawiony w Ustawienia → Ogólne → Odbiorca formularza kontaktowego.'),...Object.entries(fields).map(([key,label])=>h(TextControl,{key,label,value:a[key],onChange:value=>setAttributes({[key]:value})}))),save:()=>null});
})();
