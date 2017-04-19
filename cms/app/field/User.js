Ext.define('Cetera.field.User', {

    extend:'Cetera.field.Trigger',
    
    initComponent : function(){
    
        this.trigger1Cls = 'icon-delete';
        this.trigger2Cls = 'icon-user';
    
        this.window = Ext.create('Cetera.users.Window');
        
        this.window.on('select', function(res) {
            this.setValue(Ext.JSON.encode(res));     
            this.fireEvent('select', res);
        }, this);
    
        this.callParent();
    },
    
    onTrigger1Click: function() { 
        this.setDisplayValue('');
        this.setValue(0); 
    },
    
    onTrigger2Click: function() {
        this.window.show();
    },
    
    setValue : function(value) {
        var obj = Ext.JSON.decode(value, true);
        if (obj instanceof Object) {
            this.setDisplayValue(obj.name);
            this.callParent([obj.id]);
        } else {
            this.callParent([value]);
        }
    },
            
  	onDestroy: function(){
  		  this.window.close();
  		  this.callParent();
  	}
});