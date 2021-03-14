Ext.require('Cetera.model.SiteTree');

Ext.define('Cetera.catalog.SiteTree', {

    extend:'Ext.tree.TreePanel',
    
    rootVisible : false,
    line        : false,
    autoScroll  : true,
    
    loadMask: true,
    
    nodesToExpand: [],
	
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
    
    checkExpanded: function(node){
        if (node.isExpanded()) {
            var res = 0;
            for (var i = 0; i < node.childNodes.length; i++) {
                res += this.checkExpanded(node.childNodes[i]);
            }
            if (res == 0) {
                this.nodesToExpand[this.nodesToExpand.length] = node.getPath();
            }
            return 1;
        }
        return 0;
    },
	
	reload: function(){
        this.nodesToExpand = [];
        this.checkExpanded(this.getRootNode());

		var store = this.getStore();
		store.load({
			node: store.getNodeById('root'),
			callback: function() {
                for (var i = 0; i < this.nodesToExpand.length; i++) {
                    this.expandPath(this.nodesToExpand[i], 'id', '/'); 
                }   
                
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
                iconCls:'x-fa fa-sync',
                tooltip: Config.Lang.refresh,
                handler: function () { 
									
					this.reload();
                
                },
                scope: this
            },{
                iconCls:'x-fa fa-angle-double-down',
                tooltip: Config.Lang.expandAll,
                handler: function () { 
                    this.expandAll();
                },
                scope: this
            }
        ];
        
        if (this.materials) {
        
            var search = Ext.create('Cetera.field.Search', {
                store: this.store,
                paramName: 'query',
                reloadStore: false,
                width:250
            });        
            
            search.on('search', function() {
                this.reload();
            },this); 

            this.tbar[this.tbar.length] = search;            
        
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
        this.expandPath( '/root/item-0-1' );   
    },
    
    getSelectedId: function() {
        var sn = this.getSelectionModel().getLastSelected();
        if (!sn) return false;
        return sn.get('item_id');
    }
});