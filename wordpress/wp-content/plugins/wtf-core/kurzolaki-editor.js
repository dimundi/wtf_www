(() => {
  const {createElement:h, Fragment} = wp.element;
  const {useBlockProps, InnerBlocks, RichText, InspectorControls, MediaUpload, MediaUploadCheck} = wp.blockEditor;
  const {PanelBody, TextControl, Button} = wp.components;
  const base={apiVersion:3,category:'wtf',icon:'layout',supports:{html:false}};
  const object={type:'object',default:{}};
  const string=defaultValue=>({type:'string',default:defaultValue});
  function linkPanel(a,set){return a.attributes.href!==undefined?h(InspectorControls,{},h(PanelBody,{title:'Link'},h(TextControl,{label:'Adres',value:a.attributes.href,onChange:href=>set({attributes:{...a.attributes,href}})}))):null;}
  wp.blocks.registerBlockType('wtf/kurz-container',{...base,title:'Układ Kurzołaków',attributes:{tag:string('div'),attributes:object,label:string('Układ')},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps({className:a.attributes.class}),linkPanel(a,setAttributes),h('div',{style:{fontSize:11,opacity:.65}},a.label),h(InnerBlocks)),
    save:()=>h(InnerBlocks.Content),
  });
  wp.blocks.registerBlockType('wtf/kurz-text',{...base,title:'Tekst Kurzołaków',icon:'editor-paragraph',attributes:{tag:string('p'),content:string(''),attributes:object},
    edit:({attributes:a,setAttributes})=>h(Fragment,{},linkPanel(a,setAttributes),h(RichText,{...useBlockProps({className:a.attributes.class}),tagName:a.tag==='a'?'p':a.tag,value:a.content,onChange:content=>setAttributes({content}),placeholder:'Wpisz tekst…'})),save:()=>null,
  });
  function mediaField(a,set,mobile=false){const id=mobile?'mobileId':'imageId';return h(MediaUploadCheck,{},h(MediaUpload,{allowedTypes:['image'],value:a[id],onSelect:m=>set(mobile?{mobileId:m.id}:{imageId:m.id,image:m.url}),render:({open})=>h(Button,{variant:'secondary',onClick:open},mobile?'Wybierz kadr mobilny':'Wybierz grafikę')}));}
  wp.blocks.registerBlockType('wtf/kurz-image',{...base,title:'Grafika Kurzołaków',icon:'format-image',attributes:{image:string(''),imageId:{type:'number',default:0},mobileId:{type:'number',default:0},alt:string(''),attributes:object},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps(),
      h('p',{},a.attributes.class==='story-card-art'?'Grafika karty: przezroczyste tło i kompletna sylwetka. Zalecana wysokość 1500 px. Okno karty ma osobne ustawienie kadru dla postaci: odcina zbędne podłoże, bez zmiany grafiki w opisie.':a.attributes.class==='story-intro-background'?'Tło wstępu: bez wbudowanych napisów. Zalecana wysokość 1400 px; grafika wypełnia sekcję i jest kadrowana od dołu. Całość rozjaśnia jednolita warstwa pod tekstem.':(a.attributes.class||'').includes('faction-logo')?'Znak frakcji: wymagane przezroczyste tło i oryginalne proporcje, bez szarej obwódki. Bez kadrowania ani zmiany barw; zalecane 320 px na dłuższym boku.':(a.attributes.class||'').startsWith('story-')?'Historyjki: wymagane przezroczyste tło i pełna sylwetka bez wbudowanych napisów. Oryginalne proporcje, bez kadrowania postaci; zalecana wysokość 1500 px.':a.attributes.class==='dust-logo'?'Logotyp: zachowaj oryginalne proporcje i przezroczyste tło; zalecana szerokość 800 px.':'Zalecana szerokość 1200 px, oryginalne proporcje. Pudełko, dodatek i plansza wyświetlane w pełnym kadrze; bez dodatkowych napisów.'),
      a.image?h('img',{src:a.image,alt:a.alt,style:{maxHeight:320,maxWidth:'100%',objectFit:'contain'}}):null,
      mediaField(a,setAttributes),a.mobileId?mediaField(a,setAttributes,true):null,
      h(TextControl,{label:'Opis alternatywny',value:a.alt,onChange:alt=>setAttributes({alt})})),save:()=>null,
  });
  wp.blocks.registerBlockType('wtf/kurz-video',{...base,title:'Film Kurzołaków',icon:'video-alt3',attributes:{url:string(''),title:string('Film Kurzołaków'),fileId:{type:'number',default:0},poster:string('')},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps(),
      h(TextControl,{label:'Tytuł filmu',value:a.title,onChange:title=>setAttributes({title})}),
      h('p',{},'Film MP4: zalecane proporcje 16:9 i rozdzielczość 1920 × 1080 px. Odtwarzanie po kliknięciu, bez automatycznego pobierania.'),
      h(MediaUploadCheck,{},h(MediaUpload,{allowedTypes:['video'],value:a.fileId,onSelect:m=>setAttributes({fileId:m.id}),render:({open})=>h(Button,{variant:'secondary',onClick:open},a.fileId?'Zmień film MP4':'Wybierz film MP4')})),
      a.fileId?h(Button,{variant:'tertiary',onClick:()=>setAttributes({fileId:0})},'Usuń MP4 i użyj YouTube'):null,
      h(MediaUploadCheck,{},h(MediaUpload,{allowedTypes:['image'],onSelect:m=>setAttributes({poster:m.url}),render:({open})=>h(Button,{variant:'secondary',onClick:open},'Miniatura filmu (16:9)')})),
      a.poster?h('img',{src:a.poster,alt:'Miniatura filmu',style:{maxWidth:'100%',maxHeight:220}}):null,
      h(TextControl,{label:'Adres osadzenia YouTube (gdy nie wybrano MP4)',help:'https://www.youtube-nocookie.com/embed/IDENTYFIKATOR',value:a.url,onChange:url=>setAttributes({url})})),save:()=>null,
  });
})();
