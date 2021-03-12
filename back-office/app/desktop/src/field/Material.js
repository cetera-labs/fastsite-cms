Ext.define('Cetera.field.Material', {

    extend: 'Cetera.field.Trigger',
    
    alias: 'widget.materialfield',
    
    initComponent : function(){
    
        this.trigger1Cls = 'icon-delete';
        this.trigger2Cls = 'icon-edit';
        this.editable    = false;  
        
        this.callParent();
    },
        
    setValue : function(value, displayOK) {
	
        var obj = Ext.JSON.decode(value, true);
        if (obj instanceof Object) {
        
            this.setDisplayValue(obj.name);
            this.callParent([obj.id]);
            
        } else {

            if (!displayOK && value && !this.materials) {
                Ext.Ajax.request({
                    url: '/cms/include/action_catalog.php?action=get_path_info&id='+value,
                    success: function(response){
                        var obj = Ext.decode(response.responseText);
                        this.setDisplayValue(obj.displayPath);
                        this.path = obj.treePath;
                        this.window.path = obj.treePath;
                    },
                    scope: this
                });        
            }
            this.callParent([value]);

        }        
    
    },

    onTrigger2Click: function() {
        this.openWindow( this.value );
    },
    
    onTrigger1Click: function() { 
        this.setDisplayValue('');
        this.setValue(0, true); 
    },
     
	  onDestroy: function(){
		  if (this.window) this.window.close();
          this.callParent();
	  }, 
    
    validator: function(value) {
        if (!this.allowBlank && !parseInt(value)) return this.blankText;
        return true;
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
							
							this.setDisplayValue( win.returnValue.name );
							this.setValue( win.returnValue.id, true ); 							

                        }
                    },
                    scope: this
                }
            }
        });
        
        var win = this.window;
        var mat_type = this.mat_type;
        
        Ext.Loader.loadScript({
            url: '/cms/include/ui_material_edit.php?type='+this.mat_type+'&idcat=-1&id='+id,
            onLoad: function() { 
                var cc = Ext.create('MaterialEditor'+mat_type, {win: win});
                if (cc) cc.show();
            }
        });
        
    }	
});