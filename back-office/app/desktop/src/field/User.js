Ext.define('Cetera.field.User', {

    extend:'Cetera.field.Trigger',
	requires: ['Cetera.model.User'],
	alias : 'widget.userfield',
    
    initComponent : function(){
    
        this.setTriggers({
            del: {
                cls: 'x-fas fa-times-circle',
                handler: this.onTrigger1Click,
            },
            user: {
                cls: 'x-fa fa-user',
                handler: this.onTrigger2Click,
            }
        }); 
        
        this.callParent();
    },
    
    onTrigger1Click: function() { 
        this.setDisplayValue('');
        this.setValue(0); 
    },
    
    onTrigger2Click: function() {
        this.getWindow().show();
    },
    
    getWindow: function() {
        if (!this.window) {
            this.window = Ext.create('Cetera.users.Window');
            
            this.window.on('select', function(res) {
                this.setValue(Ext.JSON.encode(res));     
                this.fireEvent('select', res);
            }, this);
        }
        return this.window;
    },
    
    setValue : function(value) {
        var obj = Ext.JSON.decode(value, true);
        if (obj instanceof Object) {
            this.setDisplayValue(obj.name);
            this.callParent([obj.id]);
        } else {
            this.callParent([value]);
			if (value) {
                var user = new Cetera.model.User();
				user.load(value, {
					scope: this,
					success: function(user) {
						this.setDisplayValue(user.get('login'));
					}
				});				
			}
        }
    },
            
  	onDestroy: function(){
		if (this.window) this.window.destroy();
  		this.callParent();
  	}
});