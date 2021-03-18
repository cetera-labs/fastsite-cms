Ext.define('Cetera.view.main.Tree', {

    xtype: 'maintree',

    extend: 'Ext.tree.TreePanel',
    requires: 'Cetera.model.SiteTree',
    
	title: _('Структура'),
	border: false,
	anchor:'100% 100%',	
	
    rootVisible:false,
    lines:false,
    autoScroll:true,
	
	store: 'structureMain',
    
    initComponent : function() {
		     
        var create = {
            xtype: 'splitbutton',
            iconCls: 'x-fa fa-folder',
            tooltip: _('Создать...'),
            menu: []
        };
        if (Config.user.permissions.adminRootCat)
            Ext.Array.push(create.menu, {
                id:'tb_new_s',
                iconCls:'icon-server',
                text: _('Создать сервер'),
                handler: this.create_new_server,
                scope: this
            });
            
        Ext.Array.push(create.menu, 
            {
                id:'tb_new_f',
                iconCls:'icon-new_folder',
                text: _('Создать раздел'),
                handler: this.create_new,
                scope: this
            },
            {
                id:'tb_new_l',
                iconCls:'icon-new_folder_linked',
                text: _('Создать ссылку на раздел'),
                handler: this.create_link,
                scope: this
            },
            {
                id:'tb_new_hard_link',
                iconCls:'icon-new_folder_hardlinked',
                text: _('Создать жесткую ссылку'),
                handler: this.create_hard_link,
                scope: this
            }
        );  
             
        this.tbar = [
            {
                iconCls:'x-fa fa-sync',
                tooltip:_('Обновить'),
                handler: function () { this.reload(this.getSelectedPath()); },
                scope: this
            },
            '-',
            create
        ];
        
        Ext.Array.push(this.tbar,           
            {
                id:'tb_prop',
                iconCls:'x-fa fa-edit',
                tooltip: Config.Lang.catProps,
                handler: this.edit,
                scope: this
            },{
                id:'tb_up',
                iconCls:'x-fa fa-arrow-up',
                tooltip: Config.Lang.upper,
                handler: this.move_up,
                scope: this
            },{
                id:'tb_down',
                iconCls:'x-fa fa-arrow-down',
                tooltip: Config.Lang.downer,
                handler: this.move_down,
                scope: this
            },{
                id:'tb_copy',
                iconCls:'x-fa fa-copy',
                tooltip: Config.Lang.copy,
                handler: this.copy,
                scope: this
            },{
                id:'tb_delete',
                iconCls:'x-fa fa-trash',
                tooltip: Config.Lang.remove,
                handler: this.delete_cat,
                scope: this
            }
        );
        
        this.callParent();  
        
        this.getSelectionModel().on({
            'selectionchange' : function(sm, node){
                if(!this.menu) this.createContextMenu();
                
                node = node[0];
                           
                if (node && node.get('link')) {
                    Ext.getCmp('tb_new_f').disable();
                    Ext.getCmp('tb_new_l').disable();  
                    if (this.menu) {
                        Ext.getCmp('m_new_f').disable();
                        Ext.getCmp('m_new_l').disable();       
                    }   
                } else {
                    Ext.getCmp('tb_new_f').enable();
                    Ext.getCmp('tb_new_l').enable();  
                    if (this.menu) {
                        Ext.getCmp('m_new_f').enable();
                        Ext.getCmp('m_new_l').enable();       
                    }        
                }
                
                if(node && node.getId() != 'item-0-1'){
                    Ext.getCmp('tb_up').enable();
                    Ext.getCmp('tb_down').enable();
                    Ext.getCmp('tb_copy').enable();
                    Ext.getCmp('tb_delete').enable();
                    Ext.getCmp('tb_prop').enable();
                    if (this.menu) {
                        Ext.getCmp('m_up').enable();
                        Ext.getCmp('m_down').enable();
                        Ext.getCmp('m_copy').enable();
                        Ext.getCmp('m_delete').enable();
                        Ext.getCmp('m_prop').enable();           
                    }
                } else {
                    Ext.getCmp('tb_up').disable();
                    Ext.getCmp('tb_down').disable();
                    Ext.getCmp('tb_copy').disable();
                    Ext.getCmp('tb_delete').disable();

                    if(Config.user.permissions.admin && node && node.getId() == 'item-0')
                        Ext.getCmp('tb_prop').enable();
                        else Ext.getCmp('tb_prop').disable();
                        
                    if (this.menu) {
                        Ext.getCmp('m_up').disable();
                        Ext.getCmp('m_down').disable();
                        Ext.getCmp('m_copy').disable();
                        Ext.getCmp('m_delete').disable(); 
                        if(Config.user.permissions.admin && node && node.getId() == 'item-0')
                            Ext.getCmp('m_prop').enable();
                            else Ext.getCmp('m_prop').disable();                 
                    }
                }
                
                if (Config.user.permissions.adminRootCat) {
                
                    if (node && node.getDepth() == 1) {
                        Ext.getCmp('tb_new_s').enable();
                        if (this.menu) Ext.getCmp('m_new_s').enable();
                    } else {
                        Ext.getCmp('tb_new_s').disable();
                        if (this.menu) Ext.getCmp('m_new_s').disable();
                    }
                    
                }
    
                Cetera.getApplication().buildBoLink();
            },
            scope:this
        });
        
        this.on('dblclick', this.edit, this);		
		
        this.getView().on('itemcontextmenu', function(view, rec, node, index, e){
            e.stopEvent();
            if(!this.menu) this.createContextMenu();
            this.menu.showAt(e.getXY());
            return false;
        }, this);

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
        return sn.get('item_id');
    },
    
    edit: function(btn) {
        if (this.getSelectedId() < 0) return;
        
        Ext.create('Cetera.window.CatalogEdit',{
			catalog_id: this.getSelectedId()
		}); 
		

    },
    
    create_new_server: function() {
        if (this.getSelectedId() < 0) return;
        
        Ext.create('Cetera.catalog.ServerCreate', {
            win: this.getPropertyWindow()
        }).show();
    },
    
    create_new: function() {
        if (this.getSelectedId() < 0) return;
        
        Ext.create('Cetera.catalog.Create', {
            win: this.getPropertyWindow(),
            materialsType: this.getSelectionModel().getLastSelected().get('mtype')
        }).show();
    },
    
    create_link: function() {
        if (this.getSelectedId() < 0) return;
        
        Ext.create('Cetera.catalog.LinkCreate', {
            win: this.getPropertyWindow()
        }).show();        
    },
    
    create_hard_link: function() {
        if (this.getSelectedId() < 0) return;
        Ext.create('Cetera.catalog.HardLinkCreate', {
            win: this.getPropertyWindow()
        }).show();         
    },
    
    move_up: function() {
        var id = this.getSelectedId();
        if (id < 0) return;
        var tree = this;
        var path = tree.getSelectedPath();
        Ext.Ajax.request({
            url: '/cms/include/action_catalog.php',
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
            url: '/cms/include/action_catalog.php',
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
                    url:     '/cms/include/action_catalog.php', 
                    params:  {action: 'cat_copy', id: id, dest: dest_tree.getSelectedId() },
                    waitMsg: Config.Lang.wait,
                    scope:   this,
                    success: function(form, action) {
                        tree.reload(action.result.path);
                        wnd.close();
                    },
					failure: function(form, action) {
						var obj = Ext.decode(action.response.responseText);	
						if (obj.message) {
							var win = Ext.create('Cetera.window.Error', {
								msg: obj.message,
								ext_msg: obj.ext_message
							});
							win.show();	
						}
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
                   url: '/cms/include/action_catalog.php',
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
            this.propertyWindow = Ext.create('Ext.Window',{
                closable:true,
                width:500,
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
		this.propertyWindow.center();
        return this.propertyWindow;
    },
    
    createContextMenu: function() {
    
        var items = [
                {
                    id:'m_prop',
                    disabled: true,
                    iconCls:'icon-props',
                    text: Config.Lang.catProps,
                    handler: this.edit,
                    scope: this
                }, '-'        
        ];
        
        if (Config.user.permissions.adminRootCat)
            Ext.Array.push(items, {
                    id:'m_new_s',
                    disabled: true,
                    iconCls:'icon-server',
                    text: Config.Lang.createServer,
                    handler: this.create_new_server,
                    scope: this            
            }); 
            
        Ext.Array.push(items, 
                {
                    id:'m_new_f',
                    iconCls:'icon-new_folder',
                    text: Config.Lang.newCatalog,
                    handler: this.create_new,
                    scope: this
                }
                ,{
                    id:'m_new_l',
                    iconCls:'icon-new_folder_linked',
                    text: Config.Lang.newLink,
                    handler: this.create_link,
                    scope: this
                }
                ,{
                    id:'m_up',
                    iconCls:'icon-up',
                    text: Config.Lang.upper,
                    handler: this.move_up,
                    scope: this
                }
                ,{
                    id:'m_down',
                    iconCls:'icon-down',
                    text: Config.Lang.downer,
                    handler: this.move_down,
                    scope: this
                }
                ,{
                    id:'m_copy',
                    iconCls:'icon-copy',
                    text: Config.Lang.copy,
                    handler: this.copy,
                    scope: this
                }
                ,{
                    id:'m_delete',
                    iconCls:'icon-delete',
                    text: Config.Lang.remove,
                    handler: this.delete_cat,
                    scope: this
                }        
        );                   
    
        this.menu = Ext.create('Ext.menu.Menu', {
            id:'feeds-ctx',
            items: items
        });

    }
});
