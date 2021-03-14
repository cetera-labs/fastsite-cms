Ext.require('Cetera.model.Menu');

Ext.define('Cetera.panel.Menu', {

    extend: 'Ext.Panel',
    
    border: false,
    autoScroll : true,
    
    layout: 'border',

    initComponent : function(){
       
        this.tbar = [
            {
                iconCls:'x-fa fa-sync',
                toolTip: Config.Lang.reload,
                handler: function() { this.store.load(); },
                scope: this
            },{
                iconCls:'x-fa fa-plus-square',
                text: _('Добавить меню'),
                handler: this.addMenu,
                scope: this
            },{
                id: 'tb_menu_delete',
                iconCls:'x-fa fa-trash',
                disabled: true,
                text: _('Удалить меню'),
                handler: this.deleteMenu,
                scope: this
            },{
                id: 'tb_menu_rename',
                iconCls:'x-fa fa-edit',
                disabled: true,
                text: _('Переименовать'),
                handler: this.renameMenu,
                scope: this
            },'-',	
			{
                itemId: 'add_item',
                iconCls:'x-fa fa-plus',
                disabled: true,
                text: _('Добавить ссылку'),
                handler: this.addItem,
                scope: this
            },	
			{
                itemId: 'edit_item',
                iconCls:'x-far fa-edit',
                disabled: true,
                text: _('Изменить ссылку'),
                handler: this.editItem,
                scope: this
            },				
			{
                id: 'tb_menu_deleteitem',
                iconCls:'x-fa fa-minus',
                disabled: true,
                text: _('Удалить элемент'),
                handler: this.deleteItem,
                scope: this
            }
        ]; 
                
        this.store = Ext.create('Ext.data.TreeStore',{
            model: Cetera.model.Menu,
            proxy: {
                type: 'ajax',
                url: '/cms/include/data_menus.php'
            },
            root: {
                text: 'root',
                id: this.from?'node-'+this.from:'root',
                iconCls: 'tree-folder-visible',
                allowDrop: false
            }
        });
        
        
        this.menus = Ext.create('Ext.tree.TreePanel',{
            region: 'west',
            padding: 5,
            width: 300,
            
            store: this.store,
            animate: true,
            rootVisible: false,
            
            viewConfig: {
                plugins: {
                    ddGroup: 'organizerDD',
                    ptype  : 'treeviewdragdrop',
                    displayField: 'name'
                },
                listeners : {
                    drop : {
                        fn : function(node, data, overModel, dropPosition, eOpts ) {
                        
                            if (overModel.get('menu'))
                                var t = overModel;
                                else t = overModel.parentNode;                
                        
                            this.saveMenu(t);
                              
                        },
                        scope: this
                    },
                    beforedrop : {
                        fn : function(node, data, overModel, dropPosition, eOpts ) {
                    
                              var rec = data.records[0];
                              
                              if (rec.get('text')) {
                                  var c = Ext.create('Cetera.model.Menu', {
                                      children: [],
                                      leaf: true,
                                      data: rec.getId(),
                                      iconCls: rec.get('iconCls'),
                                      name: rec.get('text')    
                                  });
                                  data.records = [c];
                              }

                        },
                        scope: this
                    }
                }
            },
            
            displayField: 'name',
            
            listeners : {
                itemmove : {
                    fn : function(node, oldParent, newParent, index, eOpts) {
                          if (oldParent != newParent) this.saveMenu(oldParent);
                    },
                    scope: this
                }
            }
            
        });
        
        this.menus.getSelectionModel().on({
            'selectionchange' : function(sm, node){
                    
				var menu = false;
				var link = false;
				if (node.length) 
				{
					menu = node[0].get('menu');
					link = node[0].get('link');
				}
				else
				{
					node[0] = false;
				}
				
				var tb = this.getDockedItems('toolbar[dock="top"]')[0];
				tb.getComponent("add_item").setDisabled( !node[0] );				
				tb.getComponent("edit_item").setDisabled( !link );
					
                if (node[0] && menu) {
                
                    Ext.getCmp('tb_menu_delete').enable();
                    Ext.getCmp('tb_menu_rename').enable(); 
                    Ext.getCmp('tb_menu_deleteitem').disable(); 
                    
                } else if (node[0] && !menu) {
                
                    Ext.getCmp('tb_menu_delete').disable();
                    Ext.getCmp('tb_menu_rename').disable(); 
                    Ext.getCmp('tb_menu_deleteitem').enable();
                     
                } else {
                    Ext.getCmp('tb_menu_deleteitem').disable();
                    Ext.getCmp('tb_menu_delete').disable();
                    Ext.getCmp('tb_menu_rename').disable();  
                }

            },
            scope:this
        });
        
        //this.menus.on('itemdblclick', this.edit, this);
        
        this.tree = Ext.create('Cetera.catalog.SiteTree', {
            region: 'center',
            padding: '5 5 5 0',
            
            animate: true,
            
            viewConfig: {
                plugins: {
                    ddGroup: 'organizerDD',
                    ptype  : 'treeviewdragdrop',
                    displayField: 'name',
                    enableDrag: true,
                    enableDrop: false
                },
                allowCopy: true,
                copy: true
            },
            
            nolink: 1,
            materials: 1,
            norootselect: 1
        });
        
        this.items = [
            this.menus,
            this.tree
        ];
          
        this.callParent();
        
        this.tree.expandPath('/root/item-0-1', 'id', '/');

    },
    
    prompt: function(title,name,alias,callback,scope,name_title,alias_title) {
    
		if (!name_title) name_title = 'Имя';
		if (!alias_title) alias_title = 'Alias';
			
        var form = Ext.create('Ext.FormPanel', {
				  
			fieldDefaults : {
				labelAlign: 'right', 
				labelWidth: 100
			},

            bodyStyle:'padding:5px 5px 0; background: none',
            defaults   : { anchor: '0' },
            defaultType: 'textfield',
            border: false,
            monitorValid: true,
            			
            items: [
                {
                    fieldLabel: name_title,
                    name: 'name',
                    allowBlank:false,
                    value: name
                },{
                    fieldLabel: alias_title,
                    name: 'alias',
                    allowBlank: false,
                    regex: /^[\:\/\.\-\_A-Z0-9#]+$/i,
                    value: alias
                }
            ],
            
            buttons: [
                {
                    text: 'OK',
                    formBind: true,
                    disabled:true,  
                    handler: function() {
                        var form = this.up('form');
                        if (form.userCallback) form.userCallback(form.getForm().getValues());
                        form.win.close();
                    }
                },{
                    xtype: 'button',
                    text: _('Отмена'),
                    handler: function() { this.up('form').win.close(); }
                }
            ]
            
        }); 
        
        form.userCallback = Ext.Function.bind(callback, scope);
        
        var win = Ext.create('Ext.Window', {
            items: form,
            width: 400,
            title: title,
            modal: true
        }); 
        
        form.win = win;
        win.show();  
    
    },
    
    addMenu: function() {
    
        this.prompt(_('Добавить меню'), '', '', function(values){
            this.call({ 
                action: 'create', 
                name: values.name,
                alias: values.alias
            });
        },this);
    
    },
	
    addItem: function() {
    
        this.prompt(_('Добавить внешнюю ссылку'), '', '', function(values){
			
			var c = Ext.create('Cetera.model.Menu', {
				children: [],
                leaf: true,
                data: 'url-'+this.replaceDash(values.alias)+'-name-'+this.replaceDash(values.name),
                iconCls: '',
                name: values.name + ' ['+values.alias+']',
				link: 1
            });
						
			var n = this.getSelectedMenuNode();
			n.appendChild(c);
			this.saveMenu(n);
			
        },this,_('Заголовок'),'URL');
    
    },	
	
    editItem: function() {
    
		var sn = this.menus.getSelectionModel().getLastSelected();
		var data = sn.get('data').split('-');
	
        this.prompt(_('Изменить внешнюю ссылку'), this.restoreDash(data[3]), this.restoreDash(data[1]), function(values){
			
			sn.set('name', values.name + ' ['+values.alias+']' );
			sn.set('data', 'url-'+this.replaceDash(values.alias)+'-name-'+this.replaceDash(values.name) );			
			this.saveMenu( this.getSelectedMenuNode() );
			
        },this,_('Заголовок'),'URL');
    
    },	
	
	replaceDash: function(value) {
		return value.split('-').join('%DASH%');
	},
	
	restoreDash: function(value) {
		return value.split('%DASH%').join('-');
	},	
    
    renameMenu: function() {
        var sn = this.menus.getSelectionModel().getLastSelected();
        if (!sn) return;    
        this.prompt(_('Переименовать меню'), sn.get('menu_name'), sn.get('menu_alias'), function(values){

            this.call({ 
                action: 'rename', 
                id: this.getSelectedMenuId(),
                name: values.name,
                alias: values.alias
            });

        },this);
    
    },
	
    getSelectedMenuNode: function() {
        var sn = this.menus.getSelectionModel().getLastSelected();
        if (!sn) return 0;
        while (!sn.get('menu'))
		{
			sn = sn.parentNode;
		}
		return sn;
    },	
    
    getSelectedMenuId: function() {
        var sn = this.menus.getSelectionModel().getLastSelected();
        if (!sn) return 0;
        return parseInt(sn.get('menu'));    
    },
    
    deleteMenu: function() {
        Ext.MessageBox.confirm(_('Удалить меню'), _('Вы уверены?'), function(btn) {
            if (btn == 'yes') {
                       
                this.call({ 
                        action: 'delete', 
                        id: this.getSelectedMenuId()
                });
                                                  
            }
        }, this);    
    },
    
    deleteItem: function() {
        var sn = this.menus.getSelectionModel().getLastSelected(); 
        if (!sn || sn.get('menu')) return;
        var parent = sn.parentNode;
        sn.remove();   
        this.saveMenu(parent);
    },
    
    call: function(params, no_reload) {
        Ext.Ajax.request({
            url: '/cms/include/action_menus.php',
            params: params,                  
            success: function(response){
                if (!no_reload) this.store.load();
            },
            scope: this
        });    
    },
    
    saveMenu: function(node) {

        var children = [];
        Ext.Array.each(node.childNodes, function(child) {
            children[children.length] = child.get('data');
        }, this);       
    
        this.call({ 
            action: 'save', 
            'children[]': children,
            id: node.get('menu')
        }, true);    
    }
    
});