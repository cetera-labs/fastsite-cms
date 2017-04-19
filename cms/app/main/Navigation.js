Ext.define('Cetera.main.Navigation', {

    extend:'Ext.tree.TreePanel',

    id:'main_navigation',
    title: Config.Lang.navigation,   
    rootVisible:false,
    lines:false,
    autoScroll:true,
    cls: 'navigation-tree',
       
    initComponent : function() {
    
        var children = [];
        
        Ext.Object.each(Config.menu, function(key, value) {
            if (value.items && value.items.length)
                children.push({
                    text: value.name, 
                    name: value.name, 
                    expanded:true,
                    children: this.buildMenu(value.items)             
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
            
            if (value.submenu && value.submenu.length) {
                Ext.Object.each(value.submenu, function(k, v) {
                    item.children.push({
                        text    : v.name,
                        iconCls : 'tab-'+value.id + '_' + k,
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