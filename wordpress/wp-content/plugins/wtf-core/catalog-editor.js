(() => {
  const {createElement:h, Fragment} = wp.element;
  const {useBlockProps, InnerBlocks, RichText, InspectorControls, MediaUpload, MediaUploadCheck} = wp.blockEditor;
  const {PanelBody, TextControl, Button} = wp.components;
  const base={apiVersion:3,category:'wtf',icon:'layout',supports:{html:false}};
  const object={type:'object',default:{}};
  const string=defaultValue=>({type:'string',default:defaultValue});
  function linkPanel(a,set){return a.attributes.href!==undefined?h(InspectorControls,{},h(PanelBody,{title:'Link'},h(TextControl,{label:'Adres',value:a.attributes.href,onChange:href=>set({attributes:{...a.attributes,href}})}))):null;}
  wp.blocks.registerBlockType('wtf/catalog-container',{...base,title:'Układ katalogu',attributes:{tag:string('div'),attributes:object,label:string('Układ')},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps({className:a.attributes.class}),linkPanel(a,setAttributes),h('div',{style:{fontSize:11,opacity:.65}},a.label),h(InnerBlocks)),
    save:()=>h(InnerBlocks.Content),
  });
  wp.blocks.registerBlockType('wtf/catalog-text',{...base,title:'Tekst katalogu',icon:'editor-paragraph',attributes:{tag:string('p'),content:string(''),attributes:object},
    edit:({attributes:a,setAttributes})=>h(Fragment,{},linkPanel(a,setAttributes),h(RichText,{...useBlockProps({className:a.attributes.class}),tagName:a.tag==='a'?'p':a.tag,value:a.content,onChange:content=>setAttributes({content}),placeholder:'Wpisz tekst…'})),save:()=>null,
  });
  function mediaField(a,set,mobile=false){const id=mobile?'mobileId':'imageId';return h(MediaUploadCheck,{},h(MediaUpload,{allowedTypes:['image'],value:a[id],onSelect:m=>set(mobile?{mobileId:m.id}:{imageId:m.id,image:m.url}),render:({open})=>h(Button,{variant:'secondary',onClick:open},mobile?'Wybierz kadr mobilny':'Wybierz grafikę')}));}
  wp.blocks.registerBlockType('wtf/catalog-image',{...base,title:'Grafika katalogu',icon:'format-image',attributes:{image:string(''),imageId:{type:'number',default:0},mobileId:{type:'number',default:0},alt:string(''),attributes:object},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps(),
      h('p',{},a.attributes.class==='catalog-feature-art'?'Grafika gry: zalecane 1600 × 1200 px. Ilustracje są kadrowane także na telefonie: ważny motyw w centrum lub u góry, spokojne tło pod tekst na dole. Pudełko Tsukuyumi pokazujemy w pełnym kadrze. Bez wbudowanych napisów.':'Zalecana szerokość 1200 px, zachowaj oryginalne proporcje. Dodatki, pudełka i publikacje pokazujemy w pełnym kadrze.'),
      a.image?h('img',{src:a.image,alt:a.alt,style:{maxHeight:320,maxWidth:'100%',objectFit:'contain'}}):null,
      mediaField(a,setAttributes),a.mobileId?mediaField(a,setAttributes,true):null,
      h(TextControl,{label:'Opis alternatywny',value:a.alt,onChange:alt=>setAttributes({alt})})),save:()=>null,
  });
  wp.blocks.registerBlockType('wtf/catalog-video',{...base,title:'Film katalogu',icon:'video-alt3',attributes:{url:string(''),title:string('Film katalogu'),attributes:object},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps(),h(TextControl,{label:'Tytuł filmu',value:a.title,onChange:title=>setAttributes({title})}),h(TextControl,{label:'Adres osadzenia YouTube',help:'https://www.youtube-nocookie.com/embed/IDENTYFIKATOR',value:a.url,onChange:url=>setAttributes({url})})),save:()=>null,
  });
  wp.blocks.registerBlockType('wtf/catalog-audio',{...base,title:'Nagranie katalogu',icon:'format-audio',attributes:{url:string(''),fileId:{type:'number',default:0},label:string(''),mime:string('audio/mpeg'),attributes:object},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps(),
      h('p',{},'Wybierz nagranie z Mediów. Obsługiwane MP3 i WAV; MP3 zalecane dla mniejszego pliku. Odtwarzacz nie pobiera nagrania przed uruchomieniem.'),
      h(MediaUploadCheck,{},h(MediaUpload,{allowedTypes:['audio'],value:a.fileId,onSelect:m=>setAttributes({fileId:m.id,url:m.url,mime:m.mime||'audio/mpeg'}),render:({open})=>h(Button,{variant:'secondary',onClick:open},'Wybierz nagranie')})),
      a.url?h('audio',{controls:true,preload:'none',src:a.url,style:{maxWidth:'100%'}}):null,
      h(TextControl,{label:'Opis nagrania',value:a.label,onChange:label=>setAttributes({label})})),save:()=>null,
  });
})();
