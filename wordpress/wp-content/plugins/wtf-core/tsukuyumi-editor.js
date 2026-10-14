(() => {
  const {createElement:h, Fragment} = wp.element;
  const {useBlockProps, InnerBlocks, RichText, InspectorControls, MediaUpload, MediaUploadCheck} = wp.blockEditor;
  const {PanelBody, TextControl, Button} = wp.components;
  const base={apiVersion:3,category:'wtf',icon:'layout',supports:{html:false}};
  const object={type:'object',default:{}};
  const string=defaultValue=>({type:'string',default:defaultValue});
  function linkPanel(a,set){return a.attributes.href!==undefined?h(InspectorControls,{},h(PanelBody,{title:'Link'},h(TextControl,{label:'Adres',value:a.attributes.href,onChange:href=>set({attributes:{...a.attributes,href}})}))):null;}
  wp.blocks.registerBlockType('wtf/tsu-container',{...base,title:'Układ Tsukuyumi',attributes:{tag:string('div'),attributes:object,label:string('Układ')},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps({className:a.attributes.class}),linkPanel(a,setAttributes),h('div',{style:{fontSize:11,opacity:.65}},a.label),h(InnerBlocks)),
    save:()=>h(InnerBlocks.Content),
  });
  wp.blocks.registerBlockType('wtf/tsu-text',{...base,title:'Tekst Tsukuyumi',icon:'editor-paragraph',attributes:{tag:string('p'),content:string(''),attributes:object},
    edit:({attributes:a,setAttributes})=>h(Fragment,{},linkPanel(a,setAttributes),h(RichText,{...useBlockProps({className:a.attributes.class}),tagName:a.tag==='a'?'p':a.tag,value:a.content,onChange:content=>setAttributes({content}),placeholder:'Wpisz tekst…'})),save:()=>null,
  });
  function mediaField(a,set,mobile=false){const id=mobile?'mobileId':'imageId';return h(MediaUploadCheck,{},h(MediaUpload,{allowedTypes:['image'],value:a[id],onSelect:m=>set(mobile?{mobileId:m.id}:{imageId:m.id,image:m.url}),render:({open})=>h(Button,{variant:'secondary',onClick:open},mobile?'Wybierz kadr mobilny':'Wybierz grafikę')}));}
  wp.blocks.registerBlockType('wtf/tsu-image',{...base,title:'Grafika Tsukuyumi',icon:'format-image',attributes:{image:string(''),imageId:{type:'number',default:0},mobileId:{type:'number',default:0},alt:string(''),attributes:object},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps(),
      h('p',{},a.mobileId?'Panorama: zalecane 1920 × 1080 px na desktop i 1080 × 1920 px na telefon. Logo jest częścią ilustracji; zachowaj zapas na kadrowanie.':'Zalecana szerokość 1200 px. Zachowaj proporcje źródła i zapas na kadrowanie; dla pudełek oraz komponentów ważna jest pełna zawartość obrazu, bez dodatkowych napisów.'),
      a.image?h('img',{src:a.image,alt:a.alt,style:{maxHeight:320,maxWidth:'100%',objectFit:'contain'}}):null,
      mediaField(a,setAttributes),a.mobileId?mediaField(a,setAttributes,true):null,
      h(TextControl,{label:'Opis alternatywny',value:a.alt,onChange:alt=>setAttributes({alt})})),save:()=>null,
  });
  wp.blocks.registerBlockType('wtf/tsu-video',{...base,title:'Film Tsukuyumi',icon:'video-alt3',attributes:{url:string(''),title:string('Film Tsukuyumi')},
    edit:({attributes:a,setAttributes})=>h('div',useBlockProps(),h(TextControl,{label:'Tytuł filmu',value:a.title,onChange:title=>setAttributes({title})}),h(TextControl,{label:'Adres osadzenia YouTube',help:'https://www.youtube-nocookie.com/embed/IDENTYFIKATOR',value:a.url,onChange:url=>setAttributes({url})})),save:()=>null,
  });
})();
