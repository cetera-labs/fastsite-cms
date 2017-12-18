Ext.define('Cetera.panel.StructureTree', {
	
	extend: 'Ext.tree.Panel',
	alias : 'widget.structuretree',
	
	rootVisible:false,
	useArrows: true,
	
	store: 'structureMain',
	
	initComponent : function() {
		
        var tbar = [
            {
                iconCls:'icon-reload',
                tooltip: _('Обновить'),
                handler: function () { this.reload(this.getSelectedPath()); },
                scope: this
            },
            '-'
        ];	

        if (Config.user.permissions.adminRootCat)
            Ext.Array.push(tbar, {
                itemId:'tb_new_s',
                iconCls:'icon-server',
                tooltip: Config.Lang.createServer,
                handler: this.create_new_server,
                scope: this
            });
        
        Ext.Array.push(tbar, 
            {
                itemId:'tb_new_f',
                iconCls:'icon-new_folder',
                tooltip: Config.Lang.newCatalog,
                handler: this.create_new,
                scope: this
            },{
                itemId:'tb_new_l',
                iconCls:'icon-new_folder_linked',
                tooltip: Config.Lang.newLink,
                handler: this.create_link,
                scope: this
            },{
                itemId:'tb_prop',
                iconCls:'icon-props',
                tooltip: Config.Lang.catProps,
                handler: this.edit,
                scope: this
            },{
                itemId:'tb_up',
                iconCls:'icon-up',
                tooltip: Config.Lang.upper,
                handler: this.move_up,
                scope: this
            },{
                itemId:'tb_down',
                iconCls:'icon-down',
                tooltip: Config.Lang.downer,
                handler: this.move_down,
                scope: this
            },{
                itemId:'tb_copy',
                iconCls:'icon-copy',
                tooltip: Config.Lang.copy,
                handler: this.copy,
                scope: this
            },{
                itemId:'tb_delete',
                iconCls:'icon-delete',
                tooltip: Config.Lang.remove,
                handler: this.delete_cat,
                scope: this
            }
        );	

		this.tbar = Ext.create('Ext.toolbar.Toolbar', {
			items: tbar
		});
		this.toolbar = this.tbar;
		
		Ext.apply(this, {
			
			columns: [{
                xtype: 'treecolumn', 
                text: _('Раздел'),
                flex: 2,
                sortable: true,
                dataIndex: 'name'
            },{
				header: "Alias", 
				flex: 1, 
				dataIndex: 'alias'
			},{
				header: _('Тип материалов'), 
				width: 200, 
				dataIndex: 'mtype_name'
			},{
				header: _('Дата создания'), 
				width: 150, 
				dataIndex: 'date',
				xtype: 'datecolumn',   
				format:'Y-m-d H:i:s' 
			}]
			
		});
		
		this.callParent();
		
        this.getSelectionModel().on({
            'selectionchange' : function(sm, node){
                
                node = node[0];
                           
                if (node && node.get('link')) {
                    this.toolbar.getComponent('tb_new_f').disable();
                    this.toolbar.getComponent('tb_new_l').disable();  
                } else {
                    this.toolbar.getComponent('tb_new_f').enable();
                    this.toolbar.getComponent('tb_new_l').enable();         
                }
                
                if(node && node.getId() != 'item-0'){
                    this.toolbar.getComponent('tb_up').enable();
                    this.toolbar.getComponent('tb_down').enable();
                    this.toolbar.getComponent('tb_copy').enable();
                    this.toolbar.getComponent('tb_delete').enable();
                    this.toolbar.getComponent('tb_prop').enable();
                } else {
                    this.toolbar.getComponent('tb_up').disable();
                    this.toolbar.getComponent('tb_down').disable();
                    this.toolbar.getComponent('tb_copy').disable();
                    this.toolbar.getComponent('tb_delete').disable();

                    if(Config.user.permissions.admin && node && node.getId() == 'item-0')
                        this.toolbar.getComponent('tb_prop').enable();
                        else this.toolbar.getComponent('tb_prop').disable();
                }
                
                if (Config.user.permissions.adminRootCat) {
                
                    if (node && node.getDepth() == 1) {
                        this.toolbar.getComponent('tb_new_s').enable();
                    } else {
                        this.toolbar.getComponent('tb_new_s').disable();
                    }
                    
                }
    
                Cetera.getApplication().buildBoLink();
            },
            scope:this
        });		
	},
	
    reload: function(path, callback) {
        this.getSelectionModel().deselectAll();
        if (!path) path = '/root/item-0';
        var tree = this;
		var store = this.getStore();
        store.load({
            node: store.getNodeById('root'),
            callback: function() {
                tree.selectPath(path, 'id', '/', function(bSuccess, oLastNode) {
                    if (callback) callback();
                });
            }
        });
    },
    
    reloadNode: function(node, callback) {
        var path = node.getPath();
        var tree = this;
        this.getStore().load({
            node: node,
            callback: function() {
                tree.selectPath(path, 'id', '/', function(bSuccess, oLastNode) {
                    if (bSuccess) oLastNode.expand();
                    if (callback) callback();
                });
            }
        });
    },
    
    getSelectedPath: function() {
        var sn = this.getSelectionModel().getLastSelected();
        if (!sn) return false;
        return sn.getPath();
    },
    
    getSelectedId: function() {
        var sn = this.getSelectionModel().getLastSelected();
        if (!sn) return -1;
        var a = sn.getId().split('-');
        return a[1];
    },
	
    edit: function(btn) {
        if (this.getSelectedId() < 0) return;
        
        Ext.create('Cetera.window.CatalogEdit',{
			catalog_id: this.getSelectedId()
		}); 
		

    },
    
    create_new_server: function() {
        if (this.getSelectedId() < 0) return;
        
        var cc = Ext.create('Cetera.catalog.ServerCreate', {
			tree: this,
            win: this.getPropertyWindow()
        });
        cc.show();
    },
    
    create_new: function() {
        if (this.getSelectedId() < 0) return;
        
        var cc = Ext.create('Cetera.catalog.Create', {
            win: this.getPropertyWindow(),
			tree: this,
            materialsType: this.getSelectionModel().getLastSelected().get('mtype')
        });
        cc.show();
    },
    
    create_link: function() {
        if (this.getSelectedId() < 0) return;
        
        var cc = Ext.create('Cetera.catalog.LinkCreate', {
			tree: this,
            win: this.getPropertyWindow()
        });
        cc.show();
        
    },
    
    move_up: function() {
        var id = this.getSelectedId();
        if (id < 0) return;
        var tree = this;
        var path = tree.getSelectedPath();
        Ext.Ajax.request({
            url: 'include/action_catalog.php',
            params: { action: 'up', id: id },
            success: function() {
                tree.reloadNode(tree.getSelectionModel().getLastSelected().parentNode, function() {
                    tree.selectPath(path, 'id');
                });
            }
        });
    },
    
    move_down: function() {
        var id = this.getSelectedId();
        if (id < 0) return;
        var tree = this;
        var path = tree.getSelectedPath();
        Ext.Ajax.request({
            url: 'include/action_catalog.php',
            params: { action: 'down', id: id },
            success: function() {
                tree.reloadNode(tree.getSelectionModel().getLastSelected().parentNode, function() {
                    tree.selectPath(path, 'id');
                });
            }
        });
    },
    
    copy: function() {
    
        var id = this.getSelectedId();
        if (id < 0) return;
        var tree = this;
        
        var dest_tree = Ext.create('Cetera.catalog.SiteTree', {
            exclude: id,
            rule: 7,
            nolink: 1,
            region: 'center'
        });
        
        var form = new Ext.FormPanel({
            defaultType: 'checkbox',
            waitMsgTarget: true,
            height: 70,
            region: 'south',
            fieldDefaults : { 
                labelWidth: 5
            },
            layoutConfig: {labelSeparator: ''},
            bodyStyle:'padding:5px 5px 0; background: none',
            border: false,
            margins: '5 0 0 0',
            timeout: 1000000,
            items: [
                { boxLabel: Config.Lang.copySub, name: 'subs', checked:1, inputValue: 1},
                { boxLabel: Config.Lang.copyMaterials, name: 'math', checked:1, inputValue: 1}
            ]
        });
         
        dest_tree.getSelectionModel().on({
            selectionchange: function() {
                okbut.setDisabled(false);
            }
        });
        
        var okbut = Ext.create('Ext.Button', {
            text: Config.Lang.ok,
            disabled: true,
            width: 100,
            handler: function() { 
                if (dest_tree.getSelectedId()<0) return;
                form.getForm().submit({
                    url:     'include/action_catalog.php', 
                    params:  {action: 'cat_copy', id: id, dest: dest_tree.getSelectedId() },
                    waitMsg: Config.Lang.wait,
                    scope:   this,
                    success: function(form, action) {
                        tree.reload(action.result.path);
                        wnd.close();
                    }
                });
            }
        });

        var wnd = new Ext.Window({
            title: Config.Lang.copyTo,
            width:500,
            height:500,
            bodyBorder: false,
            plain:true,
            layout:'border',
            modal: true,
            resizable: false,
            buttons: [
                okbut,
                {
                    xtype: 'button',
                    width: 100,
                    text: Config.Lang.cancel,
                    handler: function() { wnd.close(); }
                }],
            items: [
                dest_tree,
                form
            ]
        });
        
        wnd.show();
        
    },
    
    delete_cat: function() {
        var id = this.getSelectedId();
        if (id < 0) return;
        var tree = this;
                
        Ext.MessageBox.confirm(Config.Lang.confirmation, Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes') {
                Ext.Ajax.request({
                   url: 'include/action_catalog.php',
                   params: { action: 'cat_delete', id: id },
                   success: function() {
                        tree.reloadNode(tree.getSelectionModel().getLastSelected().parentNode);
                   }
                });
            }
        });

    },
     
    getPropertyWindow: function() {
        if (!this.propertyWindow) {
            this.propertyWindow = new Ext.Window({
                closable:true,
                width:500,
                height:350,
                closeAction: 'hide',
                plain:true,
                layout: 'fit',
                resizable: true,
                modal: true 
            });
            this.propertyWindow.on({ hide: function(win){
                win.removeAll(true);
            }});
        }
        return this.propertyWindow;
    }	
	
});