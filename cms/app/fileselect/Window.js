Ext.define('Cetera.fileselect.Window', {

    extend: 'Ext.Window',

    initComponent : function(){
        
        if (!this.title) this.title = Config.Lang.fileSelect;
               
        this.panel = Ext.create('Cetera.fileselect.Panel', {
            defaultExpand: this.defaultExpand || '',
            hideFolders: this.hideFolders,
            dontLoadFiles: this.dontLoadFiles,
            extension: this.extension,
            activePanel: this.activePanel
        });
                      
        this.width = 830;
        this.height = 450;
        this.layout = 'fit';
        this.modal = true;
        this.items = this.panel;
        this.border = false; 

        this.btnOk = Ext.create('Ext.Button', {
                text: Ext.MessageBox.buttonText.ok,
                disabled: true,
                handler: function() { 
                    this.fireEvent('select', this.panel.url);
                    this.hide(); 
                },
                scope: this
        });
        
        this.buttons = [
            this.btnOk,
            {
                text: Ext.MessageBox.buttonText.cancel,
                handler: function() { 
                    this.hide(); 
                },
                scope: this
            }
        ];
        
        this.panel.on('select', function() {
            if (this.panel.file)
                this.btnOk.enable();
                else this.btnOk.disable();
        } ,this);
    
        this.callParent();
    },
    
  	onDestroy: function(){
  	}
});