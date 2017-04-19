Ext.define('Cetera.field.MatSet', {

    extend:'Cetera.field.Set',

    onAddItem: function() {
        this.openWindow();
    },
    
    onEditItem: function() {
        var sel = this.list.getSelectionModel().getSelection();
        if (sel.length < 1) return;
        this.openWindow(sel[0].get('id'));
    },
    
    onRemoveItem: function() {
        var sel = this.list.getSelectionModel().getSelection();
        if (!sel.length) return;
        var s = [];
        for (var i=0; i<sel.length; i++) s[i] = sel[i].get('id');
        Ext.Ajax.request({
           url: 'include/action_materials.php',
           params: { action: 'mark_del', 'sel[]': s, mat_type: this.mat_type }
        });
        this.removeItem();
    }, 
    
    openWindow: function(id) {
        if (!id) id = 0;
        this.edit_id = id;
        if (this.window) this.window.destroy();
        this.window = Ext.create('Cetera.window.MaterialEdit', { 
            listeners: {
                close: {
                    fn: function(win){
                        if (win.returnValue) {
                            if (!this.edit_id) {
                                this.addItem(win.returnValue);
                            } else {                               
                              	var sel = this.list.getSelectionModel().getSelection();
                                if (sel.length) sel[0].set('name', win.returnValue.name);
                            }
                        }
                    },
                    scope: this
                }
            }
        });
        
        var win = this.window;
        var mat_type = this.mat_type;
        
        Ext.Loader.loadScript({
            url: 'include/ui_material_edit.php?type='+this.mat_type+'&idcat=-1&id='+id,
            onLoad: function() { 
                var cc = Ext.create('MaterialEditor'+mat_type, {win: win});
                if (cc) cc.show();
            }
        });
        
    },
    
	onDestroy: function(){
		if (this.window) this.window.close();
		this.callParent();
	},
    
    initComponent : function(){
       
        this.buttons = [{
            xtype  : 'button',
            iconCls: 'icon-new',
            tooltip: Config.Lang.add,
            handler: this.onAddItem,
            scope  : this
        },{
            xtype  :'button',
            iconCls: 'icon-edit',
            tooltip: Config.Lang.edit,
            handler: this.onEditItem,
            scope  : this
        },{
            xtype  : 'button',
            iconCls: 'icon-delete',
            tooltip: Config.Lang.remove,
            handler: this.onRemoveItem,
            scope  : this
        }];
    
        this.callParent();  
    }
});