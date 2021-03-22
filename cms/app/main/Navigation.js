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
        
        this.store = Ext.create('Ext.data.TreeStore', {
            proxy: {
                type: 'ajax'
            },
            root: {
                text: 'root',
                expanded: true,
                children: this.buildMenu(Config.menu)
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
                iconCls : value.iconCls?value.iconCls:'tab-'+value.id,
                id      : value.id,
                leaf    : !value.items || value.items.length == 0,
                children: []            
            }     
            
            if (value.items && value.items.length) {
                Ext.Object.each(value.items, function(k, v) {
                    var id = v.id;
                    if (value.id) {
                        id = value.id + '-' + id;
                    }
                    item.children.push({
                        text    : v.name,
                        iconCls : v.iconCls?v.iconCls:'tab-'+id,
                        id      : id,
                        leaf    : true,          
                    });     
                }, this);              
            }
               
            res.push(item);
        }, this);  
        
        return res;  
    }

});