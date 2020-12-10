Ext.define('Cetera.panel.StructureTree', {
	
	extend: 'Ext.tree.Panel',
	alias : 'widget.structuretree',
	requires: 'Cetera.field.File',
	
	rootVisible:false,
	useArrows: true,
	
	store: 'structureMain',
	
	initComponent : function() {
		
        this.reloadAction = Ext.create('Ext.Action', {
            iconCls: 'icon-reload', 
            tooltip: _('Обновить'),
            scope: this,
            handler: function () { this.reload(this.getSelectedPath()); },
        });		
		
		
        var tbar = [
            this.reloadAction,
            '-'
        ];	

        if (Config.user.permissions.adminRootCat) {
			
			this.newServerAction = Ext.create('Ext.Action', {
                iconCls:'icon-server',
                text: Config.Lang.createServer,
                handler: this.create_new_server,
                scope: this
			});			
		
            Ext.Array.push(tbar, this.newServerAction);
		}
		
		this.newFolderAction = Ext.create('Ext.Action', {
                iconCls:'icon-new_folder',
                text: Config.Lang.newCatalog,
                handler: this.create_new,
                scope: this
		});	
		this.newLinkAction = Ext.create('Ext.Action', {
                iconCls:'icon-new_folder_linked',
                text: Config.Lang.newLink,
                handler: this.create_link,
                scope: this
		});	
		this.propAction = Ext.create('Ext.Action', {
                iconCls:'icon-props',
                text: Config.Lang.catProps,
                handler: this.edit,
                scope: this
		});	
		this.upAction = Ext.create('Ext.Action', {
                iconCls:'icon-up',
                text: Config.Lang.upper,
                handler: this.move_up,
                scope: this
		});	
		this.downAction = Ext.create('Ext.Action', {
                iconCls:'icon-down',
                text: Config.Lang.downer,
                handler: this.move_down,
                scope: this
		});	
		this.copyAction = Ext.create('Ext.Action', {
                iconCls:'icon-copy',
                text: Config.Lang.copy,
                handler: this.copy,
                scope: this
		});	
		this.deleteAction = Ext.create('Ext.Action', {
                iconCls:'icon-delete',
                text: Config.Lang.remove,
                handler: this.delete_cat,
                scope: this
		});	
        
        Ext.Array.push(tbar, 
            this.newFolderAction,
			this.newLinkAction,
			this.propAction,
			this.upAction,
			this.downAction,
			this.copyAction,
			this.deleteAction
        );	
		
        if (Config.user.permissions.adminRootCat) {
			
			this.exportAction = Ext.create('Ext.Action', {
                iconCls:'icon-export',
                text: _('Экспорт'),
                handler: this.doExport,
                scope: this
			});		

			this.importAction = Ext.create('Ext.Action', {
                iconCls:'icon-import',
                text: _('Импорт'),
                handler: this.doImport,
                scope: this
			});				
		
            Ext.Array.push(tbar, '-', this.exportAction, this.importAction);
		}		

		this.tbar = Ext.create('Ext.toolbar.Toolbar', {
			items: tbar
		});
		
		Ext.apply(this, {
			
			columns: [{
				header: "ID", 
				width: 50, 
				align: 'right',
				dataIndex: 'item_id'
			},{
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
		
        this.menu = Ext.create('Ext.menu.Menu', {
            items: [
				this.upAction,
				this.downAction,
				this.copyAction,
				this.deleteAction,	
				'-',
				this.propAction
			]
        });		
		
		this.callParent();
		
        this.getSelectionModel().on({
            'selectionchange' : function(sm, node){
                
                node = node[0];
                           
                if (node && node.get('link')) {
                    this.newFolderAction.disable();
                    this.newLinkAction.disable();  
                } else {
                    this.newFolderAction.enable();
                    this.newLinkAction.enable();         
                }
                
                if(node && node.getId() != 'item-0-1'){
                    this.upAction.enable();
                    this.downAction.enable();
                    this.copyAction.enable();
                    this.deleteAction.enable();
					this.propAction.enable();
                } else {
                    this.upAction.disable();
                    this.downAction.disable();
                    this.copyAction.disable();
                    this.deleteAction.disable();

                    if(Config.user.permissions.admin && node && node.getId() == 'item-0')
                        this.propAction.enable();
                        else this.propAction.disable();
                }
                
                if (Config.user.permissions.adminRootCat) {
					this.newServerAction.enable();
                }
				else {
					this.newServerAction.disable();
				}
    
                Cetera.getApplication().buildBoLink();
            },
            scope:this
        });

        this.getView().on('itemcontextmenu', function(view, rec, node, index, e){
            e.stopEvent();
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
                closeAction: 'hide',
                plain:true,
                layout: 'fit',
                resizable: true,
                modal: true,
				tree: this
            });
            this.propertyWindow.on({ hide: function(win){
                win.removeAll(true);
            }});
        }
		this.propertyWindow.center();
        return this.propertyWindow;
    },

	doExport: function() {
		var win = this.getPropertyWindow();
		win.setHeight(100);	
		win.setTitle(_('Экспорт'));			
		win.show();
		win.setLoading(true);
		Cetera.Ajax.request({
		   url: 'include/action_backup.php',
           timeout: 1000000,
		   params: { 
				action: 'backup', 
				section: this.getSelectedId()
		   },
		   scope: this,
		   failure: function(o, success, response) {
			   win.setLoading(false);
			   win.hide();
		   },
		   success: function(response) {
			   win.setLoading(false);
			   var obj = Ext.decode(response.responseText);
			   win.update('<p align="center">'+_('Экспорт завершен.')+'<br><a target="_blank" href="/cms/include/action_backup.php?action=download&file='+obj.file+'">'+_('Скачать файл.')+'</a></p>')
		   }
		});
	},
	
	doImport: function() {
		
		var frm = Ext.create('Ext.form.Panel', {
			layout: 'anchor',
			defaults: {
				anchor: '100%'
			},
			border: false,
			bodyStyle:'background: none',
			padding: 10,
			items: [
				{
					xtype: 'fileselectfield',
					name: 'file',
					fieldLabel: _('Файл'),
					allowBlank: false
				}
			],
			buttons: [
				{
					text: _('OK'),
					handler: function() {
						var win = this.up('window');
						var form = this.up('form').getForm();
						if (!form.isValid()) return;
						form.submit({
							url: 'include/action_backup.php',
							waitMsg: Config.Lang.wait,
						    params: { 
								action: 'restore',
								section: win.tree.getSelectedId()
						    },
							success: function(form, action) {
								win.hide();
							    Ext.Msg.alert('Success', action.result.message);								
								win.tree.reload(win.tree.getSelectedPath());
							},
							failure: function(form, action) {
								var obj = Ext.decode(action.response.responseText);			
								var win = Ext.create('Cetera.window.Error', {
									msg: obj.message,
									ext_msg: obj.ext_message
								});
								win.show();				
							}										
						});
						
					}
				},
				{
					text: _('Отмена'),
					handler: function() {
						this.up('window').hide();
					}
				}
			]
			
		});
		
		var win = this.getPropertyWindow();
		
		win.add(frm);
		win.setHeight(120);	
		win.setTitle(_('Импорт'));	
		win.show();		
	}
	
});