Ext.define('Cetera.users.Window', {
    extend: 'Ext.Window',

    initComponent : function(){
     
        this.grid = Ext.create('Cetera.users.Panel', {
            nocheckboxes: true
        });
        
        this.grid.reload();
   
        if (!this.width) this.width = 600;
        if (!this.height) this.height = 450;
        this.title = Config.Lang.users;
        this.closeAction = 'hide';
        this.layout = 'fit';
        this.modal = true;
        this.items = [this.grid];
        this.buttons = [
            {
                text: Config.Lang.ok,
                scope: this,
                handler: function() { 
                    this.processSelect(); 
                }
            },{
                text: Config.Lang.cancel,
                scope: this,
                handler: function() { this.hide(); }
            }
        ];
   
        this.callParent();
        
    },
    
    processSelect: function() {
    
        var sel = this.grid.getSelectionModel().getSelection()[0];
        if (sel) {
            this.fireEvent('select', {
                id:   sel.getId(),
                name: sel.get('login')
            });
        }
        this.hide(); 
    }
});