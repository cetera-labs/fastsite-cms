Ext.define('Cetera.field.Aliases', {

    extend: 'Cetera.field.Set',
	alias : 'widget.aliasesfield',
    
    prepareValue: function() {
        var val = [];
        this.store.each(function(rec) {
            val.push(rec.get('name'));
        }, this);
        this.setValue(Ext.JSON.encode(val), true);
    },        
                 
    addItem: function() {
        Ext.MessageBox.prompt('', Config.Lang.server, function(btn, text) {
            if (btn=='ok') {
                this.store.add({name: text});
                this.prepareValue(); 
            }
        },this);
    },
    
	getButtons: function() {
		return [{
            xtype:'button',
            iconCls:'x-fa fa-plus',
            tooltip:Config.Lang.add,
            handler: this.addItem,
            scope: this
        },{
            xtype:'button',
            iconCls:'x-fa fa-minus',
            tooltip:Config.Lang.remove,
            handler: this.removeItem,
            scope: this
        }];
	},	
	
    initComponent : function(){
       
        this.height = 130;
		
		this.store = Ext.create('Ext.data.Store', {
			fields: ['id','name'],
			proxy: {
				type: 'memory',
				reader: {
					type: 'json',
					rootProperty: 'rows'
				}
			},			
			data: {
				rows: this.value
			}
		});
            
        this.callParent();
    }
           
});