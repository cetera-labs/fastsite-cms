Ext.define('Cetera.field.LinkSetEditable', {

    extend: 'Cetera.field.LinkSet',
	
    onEditItem: function() {
        var sel = this.list.getSelectionModel().getSelection();
        if (sel.length < 1) return;
        this.openWindow(sel[0].get('id'));
    },

    openWindow: function(id) {

        if (this.window) this.window.destroy();
        this.window = Ext.create('Cetera.window.MaterialEdit', { 
            listeners: {
                close: {
                    fn: function(win){
                        if (win.returnValue) {                           
							var sel = this.list.getSelectionModel().getSelection();
							if (sel.length) sel[0].set('name', win.returnValue.name);
                        }
                    },
                    scope: this
                }
            }
        });
        
        var win = this.window;
        var mat_type = this.mat_type;
        
        Ext.Loader.loadScript({
            url: 'include/ui_material_edit.php?type='+this.mat_type+'&id='+id,
            onLoad: function() { 
                var cc = Ext.create('MaterialEditor'+mat_type, {win: win});
                if (cc) cc.show();
            }
        });
        
    },	
	
	getButtons: function() {
		var buttons = this.callParent();
		
        buttons[buttons.length] = {
            xtype:'button',
            margins:'8 0 0 0',
            iconCls:'icon-edit',
            tooltip: Config.Lang.edit,
			handler: this.onEditItem,
            scope: this
        };		
		
		return buttons;
	}	
   
});