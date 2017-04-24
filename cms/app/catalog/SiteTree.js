Ext.require('Cetera.model.SiteTree');

Ext.define('Cetera.catalog.SiteTree', {

    extend:'Ext.tree.TreePanel',
    
    rootVisible : false,
    line        : false,
    autoScroll  : true,
    
    loadMask: true,
	
	setOnly : function(value) {
		this.only = value;
		this.setStoreUrl();
		this.reload();
	},		
	
	setStoreUrl: function() {
		
        if (this.url)
            var url = this.url;
            else var url = '/cms/include/data_tree.php?1=1';
        if (this.exclude) url += '&exclude='+this.exclude;
        if (this.rule) url += '&rule='+this.rule;
        if (this.nolink) url += '&nolink='+this.nolink;
        if (this.only) url += '&only='+this.only;
        if (this.materials) url += '&materials='+this.materials;
        if (this.exclude_mat) url += '&exclude_mat='+this.exclude_mat;
        if (this.matsort) url += '&matsort='+this.matsort;
        if (this.nocatselect) url += '&nocatselect='+this.nocatselect;
        if (this.norootselect) url += '&norootselect='+this.norootselect;		
		
		this.store.setProxy({
            type: 'ajax',
            url:url
        });
		
	},
	
	reload: function(){
		var sn = this.getSelectionModel().getLastSelected();
		this.getSelectionModel().deselectAll();
		if (sn) var path = sn.getPath();

		var store = this.getStore();
		store.load({
			node: store.getNodeById('root'),
			callback: function() {
				if (path) this.selectPath(path, 'id', '/'); 
			},
			scope: this
		});		
	},
        
    initComponent : function(){

        this.store = new Ext.data.TreeStore({
            model: Cetera.model.SiteTree,
            root: {
                text: 'root',
                id: this.from?'node-'+this.from:'root',
                iconCls: 'tree-folder-visible'
            }
        });
		
		this.setStoreUrl();
        
        this.tbar = [
            {
                iconCls: 'icon-reload',
                tooltip: Config.Lang.refresh,
                handler: function () { 
									
					this.reload();
                
                },
                scope: this
            },{
                iconCls: 'icon-expandall',
                tooltip: Config.Lang.expandAll,
                handler: function () { 
                    this.expandAll();
                },
                scope: this
            }
        ];
        
        if (this.materials) {
        
            this.tbar[1] = Ext.create('Cetera.field.Search', {
                store: this.store,
                paramName: 'query',
                width:150
            });
        
        }
        
        this.callParent();
        
        this.getSelectionModel().on({
            'beforeselect' : function (sm, node) {
                if (node.get('disabled')) return false; 
            }
        });
        
        this.listeners = {
            'load': {
                fn: function (store, records, success) {
                    this.setLoading(false);
                },
                scope: this
            },
            'beforeload': {
                fn: function (store, records, success) {
                    this.setLoading(true);
                },
                scope: this
            }
        };
    },
    
    afterRender: function() {
        this.callParent();  
        this.expandPath( '/root/item-0' );   
    },
    
    getSelectedId: function() {
        var sn = this.getSelectionModel().getLastSelected();
        if (!sn) return false;
        var a = sn.getId().split('-');
        return a[1];
    }
});