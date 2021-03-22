Ext.define('Cetera.main.Navigation', {

    extend:'Ext.tree.TreePanel',

    id:'main_navigation',  
    rootVisible:false,
    lines:false,
    autoScroll:true,
    cls: 'navigation-tree',
	
	title: _('Навигация'),          
	border:true,
       
    initComponent : function() {
    
        var children = [];
        
        Ext.Object.each(Config.menu, function(key, value) {
                children.push({
                    text: value.name,  
                    iconCls: value.iconCls, 
                    expanded: value.items && value.items.length > 0,
                    children: (value.items && value.items.length > 0)?this.buildMenu(value.items):[]
                });
        }, this);
    
        this.store = Ext.create('Ext.data.TreeStore', {
            proxy: {
                type: 'ajax'
            },
            root: {
                text: 'root',
                expanded: true,
                children: children
            }
        });  

        this.on({
            'itemclick' : function( t, record, item, index, e, eOpts ) {
                Cetera.getApplication().activateModule(record.getId());
            },
            scope:this
        });
        
        this.callParent();
    },
    
    buildMenu: function(items) {
        
        var res = [];
        Ext.Object.each(items, function(key, value) {
            var item = {
                text    : value.name,
                iconCls : 'tab-'+value.id,
                id      : value.id,
                children: []            
            }     
            
            if (value.items && value.items.length) {
                Ext.Object.each(value.items, function(k, v) {
                    item.children.push({
                        text    : v.name,
                        iconCls : v.iconCls?v.iconCls:'tab-'+value.id + '_' + k,
                        id      : value.id + '_' + k,
                        children: []            
                    });     
                }, this);              
            }
               
            res.push(item);
        }, this);  
        
        return res;  
    }

});