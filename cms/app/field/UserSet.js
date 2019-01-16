Ext.define('Cetera.field.UserSet', {

    extend: 'Cetera.field.Set',

    onAddItem: function() {
    
        if (!this.usersList) {
            this.usersList = Ext.create('Cetera.users.Window');
            this.usersList.on('select', function(res) {
                this.addItem({id: res.id, name: res.name});
            },this);
        }
        this.usersList.show(); 
    
    },
      
    initComponent : function(){
    
        if (!this.tpl) this.tpl = '<div class="list-item-user">{name}</div>';
        
        this.callParent();  
    },
	
	getButtons: function() {
        return [{
            xtype  : 'button',
            iconCls: 'icon-new',
            tooltip: Config.Lang.add,
            handler: this.onAddItem,
            scope  : this
        },{
            xtype  : 'button',
            iconCls: 'icon-delete',
            tooltip: Config.Lang.remove,
            handler: this.removeItem,
            scope  : this
        }];		
	}
});