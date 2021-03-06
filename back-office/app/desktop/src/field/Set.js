Ext.define('Cetera.field.Set', {

    extend: 'Cetera.field.Panel',  
   
    prepareValue: function() {
        var val = [];
        this.store.each(function(rec) {
            val.push( rec.get('id') );
        }, this);
        this.setValue(Ext.JSON.encode(val), true);
    },
    
    setValue : function(value, internal) {
    
        this.callParent(arguments);
        
        if (internal) return;
    
        var obj = Ext.JSON.decode(value, true);
        if (!obj) return;
        
        this.store.removeAll();
        Ext.Array.each(obj, function(value) {    
            if (value === Object(value)) {
                this.store.add(value);
            }
            else {
                this.store.add(this.getObjectByValue(value));
            }
        }, this); 
        
        this.prepareValue();
        
    },
    
    getObjectByValue: function(value) {
        return {
            'id': value,
            'name': '-'
        };
    },
    
    moveSelectedRow: function(direction) {
    	var record = this.list.getSelectionModel().getSelection()[0];
    	if (!record) {
    		return;
    	}
    	var index = this.list.getStore().indexOf(record);
    	if (direction < 0) {
    		index--;
    		if (index < 0) {
    			return;
    		}
    	} else {
    		index++;
    		if (index >= this.list.getStore().getCount()) {
    			return;
    		}
    	}
    	this.list.getStore().remove(record);
    	this.list.getStore().insert(index, record);
    	this.list.getSelectionModel().select(index);
		this.prepareValue(); 
    },
    
    moveUp: function() {
        this.moveSelectedRow(-1); 
    },
    
    moveDown: function() {
        this.moveSelectedRow(1); 
    },
    
    addItem: function(item) {
        if (this.store.find('id', item.id) >= 0) return;
        //var r = new this.store.recordType(item);
        this.store.add(item);
        this.prepareValue(); 
    },
    
    removeItem: function() {
        this.store.remove(this.list.getSelectionModel().getSelection());
        this.prepareValue();
    },
    
    initListView : function() {
    
        this.list = new Ext.grid.GridPanel({
            region: 'center',
            store: this.store,
            multiSelect: true,
            hideHeaders: true,     
            columns: [
                {tpl: this.tpl, dataIndex: 'name', flex: 1}
            ]
        });
    
    },
	
	getButtons: function() {
		return [];
	},
    
    getPanel : function() {
    
        if (!this.tpl) this.tpl = '<div class="list-item-material"><tpl if="name == &quot;&quot;">-без имени-</tpl>{name}</div>';
    
        this.initListView();
        		
		this.buttons = this.getButtons();
        
        this.buttons[this.buttons.length] = {
            xtype:'button',
            margins:'8 0 0 0',
            iconCls:'icon-up',
            tooltip: _('Выше'),
            handler: this.moveUp,
            scope: this
        };
        
        this.buttons[this.buttons.length] = {
            xtype:'button',
            iconCls:'icon-down',
            tooltip: _('Ниже'),
            handler: this.moveDown,
            scope: this
        };
        
        return Ext.create('Ext.Panel',{
            layout: 'border',
            border: true,
            bodyStyle:'background: none',
            height: this.height,
            width: 500,
            items: [
                {
                    region: 'east',
                    border: false, 
                    width: 24,
                    bodyStyle:'background: none',
                    layout: {
                        type:'vbox',
                        align:'center'
                    },
                    defaults:{margins:'0 0 1 0'},
                    items: this.buttons
                },
                this.list
            ]
        });        
    
    }, 
    
    initComponent : function(){
        
        if (!this.store) {
            this.store = Ext.create('Ext.data.ArrayStore', {
                autoDestroy: true,
                fields: ['id','name'],
                data: []
            });
        }        
        
        this.callParent(arguments);
        this.prepareValue();
    },       
    
    renderIcon: function (value, metaData){
        metaData.css = 'icon-user';
    }
    
});
