(() => {
  if(!window.wp?.media?.view?.AttachmentsBrowser||!window.wtfMediaGroups)return;
  const GroupFilter=wp.media.view.AttachmentFilters.extend({
    id:'wtf-media-group-filter',
    createFilters(){
      this.filters={all:{text:'Wszystkie grupy',props:{wtf_media_group:0},priority:0},none:{text:'Bez grupy',props:{wtf_media_group:-1},priority:1}};
      wtfMediaGroups.terms.forEach((term,index)=>{this.filters['group-'+term.id]={text:term.name,props:{wtf_media_group:term.id},priority:index+2};});
    }
  });
  const original=wp.media.view.AttachmentsBrowser.prototype.createToolbar;
  wp.media.view.AttachmentsBrowser.prototype.createToolbar=function(){
    original.apply(this,arguments);
    this.toolbar.set('wtfGroupLabel',new wp.media.view.Label({value:'Grupa mediów',attributes:{for:'wtf-media-group-filter'},priority:-74}).render());
    this.toolbar.set('wtfGroupFilter',new GroupFilter({controller:this.controller,model:this.collection.props,priority:-74}).render());
  };
})();
