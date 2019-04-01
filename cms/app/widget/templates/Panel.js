Ext.define('Cetera.widget.templates.Panel', {

    extend: 'Ext.Panel',
	requires: [
		'Cetera.model.WidgetTemplate',
		'Cetera.model.Theme'
	],
    border: false,
    
    layout: {
        type: 'border'
    },
	
	currentTemplate: null,
	   
    initComponent : function(){
       
        this.editor = Ext.create('Cetera.field.Ace',{
        });
        
        this.tree = Ext.create('Ext.tree.TreePanel', {    
            region: 'west',
			rootVisible:false,
            width: 300,
            lines:true,
            autoScroll:true,
			margins:'5',
			
            store: {
				model: 'Cetera.model.WidgetTemplate',
                root: {
					expanded: true
                }
            },

			tbar: [
				{
					iconCls:'icon-reload',
					tooltip: Config.Lang.reload,
					handler: function () {
						this.tree.getStore().load();
					},
					scope: this
				}
			]		
        });   

        this.tree.getSelectionModel().on({
            'selectionchange' : function(sm, node){
                node = node[0];
				
                if (node && node.get('path'))
				{
					this.edit(node);
				}

            },
            scope:this
        });		
		
		this.statusBar = Ext.create('Ext.ux.StatusBar', {
			itemId: 'status',
			text: '&nbsp;'
		});
		
		this.saveBtn = Ext.create('Ext.Button', {
			iconCls:'icon-save',
			itemId:'btn-save',
			text: Config.Lang.save,
			handler: function () {
				this.save();
			},
			scope: this,
			disabled: true
		});
		
		this.saveAsBtn = Ext.create('Ext.Button', {
			iconCls:'icon-save-as',
			text: Config.Lang.saveAs,
			handler: function () {
				var wnd = this.getSaveAsWindow();
				wnd.on('selected',this.saveAsCheck,this);
				wnd.show();
			},
			scope: this,
			disabled: true
		});		
		
		this.deleteBtn = Ext.create('Ext.Button', {
			iconCls:'icon-delete',
			text: Config.Lang.delete,
			handler: function () {
				Ext.Msg.show({
					 title: '',
					 msg: Config.Lang.r_u_sure,
					 buttons: Ext.Msg.YESNO,
					 icon: Ext.Msg.QUESTION,
					 scope: this,
					 fn: function(btn) {
						 if (btn == 'yes')
						 {
							this.deleteTemplate();
						 }
					 }
				});	
			},
			scope: this,
			disabled: true
		});			
        
        this.items = [
			this.tree, 
			{
				region: 'center',
				margins:'5 5 5 0',
				layout: 'fit',
				tbar: [
                    // Запрещаем изменять шаблоны из админки 
                    // https://pm.cetera.ru/browse/CCD-1335
					//this.saveBtn,
					//this.saveAsBtn,
					//this.deleteBtn
				],	
				bbar: this.statusBar,			
				items: [this.editor]
			}
		];
              
        this.callParent();

    },
	
	clearEditor: function() {
		this.saveBtn.setDisabled( true );
		this.deleteBtn.setDisabled( true );
		this.saveAsBtn.setDisabled( true );		
		this.editor.setValue( '' );	
		this.statusBar.setStatus({
			'text': ''
		});		
	},
	
	deleteTemplate: function() {
		var me = this;
		me.clearEditor();
		me.editor.setLoading( Config.Lang.wait );
        Ext.Ajax.request({
            url: 'include/action_files.php',
            params: {
				action: 'delete',
				path: me.currentTemplate.get('path')
			},
            scope: this,
            success: function(resp) {
				me.editor.setLoading( false );				
				me.tree.getStore().load({
					node: me.currentTemplate.parentNode
				});					
				
            }
        });			
	},
	
	save: function() {
		
		var me = this;
		
		me.editor.setLoading( Config.Lang.wait );
        Ext.Ajax.request({
            url: '/cms/include/action_files.php',
            params: {
				action: 'save_file',
				file: me.currentTemplate.get('path'),
				data: me.editor.getValue()
			},
            scope: this,
            success: function(resp) {
				me.editor.setLoading( false );
                var obj = Ext.decode( resp.responseText );
                if (!obj.success)
				{					
					Ext.Msg.alert(Config.Lang.error, obj.message);
				}
            }
        });			
		
	},	
	
	saveAsCheck: function(wnd, values)
	{
		wnd.setLoading( Config.Lang.wait );
		var me = this;
		var file = '/themes/' + values.theme + me.currentTemplate.get('folder') + '/' + values.name;
        Ext.Ajax.request({
            url: '/cms/include/action_files.php',
            params: {
				action: 'file_info',
				file: file
			},
            scope: this,
            success: function(resp) {
				wnd.setLoading( false );
				me.editor.setLoading( false );
                var obj = Ext.decode( resp.responseText );
                
				if (obj.exists)
				{
					Ext.Msg.show({
						 title: '',
						 msg: Config.Lang.templateExistsReplace,
						 buttons: Ext.Msg.YESNO,
						 icon: Ext.Msg.QUESTION,
						 scope: this,
						 fn: function(btn) {
							 if (btn == 'yes')
							 {
								wnd.close();
								this.saveAs(file, values);
							 }
						 }
					});					
				}
				else
				{
					wnd.close();
					this.saveAs(file, values);
				}
            }
        });			
	},
	
	saveAs: function(file, values)
	{
		var me = this;
		
		me.editor.setLoading( Config.Lang.wait );
        Ext.Ajax.request({
            url: '/cms/include/action_files.php',
            params: {
				action: 'save_file',
				file: file,
				data: me.editor.getValue()
			},
            scope: this,
            success: function(resp) {
				me.editor.setLoading( false );
                var obj = Ext.decode( resp.responseText );
                if (!obj.success)
				{		
					if (obj.deny) obj.message = Config.Lang.accessDenied;
					Ext.Msg.alert(Config.Lang.error, obj.message);
				}
				else
				{
					var p = me.currentTemplate.parentNode;
					var path = p.getPath() + '/' + p.getId() + '\\' + values.name + '[' + values.theme + ']';
					var tree = me.tree;
					me.tree.getStore().load({
						node: p,
						callback: function() {							
							tree.selectPath(path, 'id', '/');
						}
					});					
					
				}
            }
        });			
	},
	
	edit: function(template)
	{		
		var me = this;		
		me.currentTemplate = template;
		me.clearEditor();
		
		this.statusBar.setStatus({
			'text': template.get('path')
		});
		
		me.editor.setLoading( true );
        Ext.Ajax.request({
            url: '/cms/include/action_files.php',
            params: {
				action: 'get_file',
				file: template.get('path')
			},
            scope: this,
            success: function(resp) {
				me.editor.setLoading( false );
                var obj = Ext.decode( resp.responseText );
                if (obj.success)
				{			
					me.editor.setMode(obj.extension);					
					me.editor.setValue( obj.data );	
					
					this.saveBtn.setDisabled( !template.get('writable') );
					this.deleteBtn.setDisabled( !template.get('writable') );
					this.saveAsBtn.setDisabled( false );
				}
				else
				{					
					console.log(obj);					
				}
            }
        });		
		
	},
	
	getSaveAsWindow: function() {
		
		var wnd = Ext.create('Ext.Window',{
			title: Config.Lang.saveAs,
			xtype: 'window',
			width: 400,
			modal: true,
			resizable: false,
			layout: 'fit',
			items: {
			
				itemId: 'form',
				xtype: 'form',
				layout: 'form',
				border: false,
				bodyPadding: 10,
			
				items: [
					{	
						xtype: 'combobox',
						itemId: 'theme',
						fieldLabel: Config.Lang.theme,
						name:'theme',
						allowBlank: false,
						displayField: 'title',
						valueField: 'id',
						editable: false,
						store: {
							model: 'Cetera.model.Theme',
							autoLoad: true
						},
						value: this.currentTemplate.get('theme')
					},
					{
						xtype: 'textfield',
						itemId: 'name',
						fieldLabel: Config.Lang.template,
						allowBlank: false,
						name:'name',
						value: this.currentTemplate.get('name')
					}
				],
				
				buttons: [
					{
						text: Config.Lang.ok,
						handler: function(btn) {
							var form = btn.up('form');
							if (!form.isValid()) return;
							var wnd = btn.up('window');
							wnd.fireEvent('selected', wnd, form.getValues());
						}						
					},
					{
						text: Config.Lang.cancel,
						handler: function(btn) {
							btn.up('window').close();
						}
					}
				]
				
			}
		});
		return wnd;
		
	}

});