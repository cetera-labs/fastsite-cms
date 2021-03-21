Ext.define('Cetera.fileselect.Window', {

    extend: 'Ext.Window',

    width: '80%',
    height: '90%',
    layout: 'fit',
    modal: true,
    border: false,

    initComponent : function(){
        
        if (!this.title) this.title = Config.Lang.fileSelect;
               
        this.panel = Ext.create('Cetera.fileselect.Panel', {
            defaultExpand: this.defaultExpand || '',
            hideFolders: this.hideFolders,
            dontLoadFiles: this.dontLoadFiles,
            extension: this.extension,
            activePanel: this.activePanel
        });
                      
        this.items = this.panel;
        
        this.panel.on('select', function(url) {
            this.fireEvent('select', url);
            this.hide();
        } ,this);
		
        this.panel.on('cancel', function() {
            this.hide();
        } ,this);		
    
        this.callParent();
    },
    
  	onDestroy: function(){
  	}
});