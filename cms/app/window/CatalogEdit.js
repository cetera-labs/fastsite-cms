Ext.define('Cetera.window.CatalogEdit', {

    extend:'Ext.Window',
	
	requires: [
		'Cetera.model.Catalog',
		'Cetera.field.MaterialType',
		'Cetera.field.Aliases',
		'Cetera.field.Folder',
		'Cetera.field.MaterialType',
		'Cetera.field.SectionController',
		'Cetera.model.Theme',
        'Cetera.field.VisualTemplate'
	],

	closable:true,
	width:850,
	height: 500,
	minHeight: 300,
	plain:true,
	layout: 'fit',
	resizable: true,
	modal: true, 
	autoShow: true,
	
	catalog_id: 0,
	catalog: null,
	formPanel: null,
	
	initComponent : function() {
	
		this.tabs = Ext.create('Ext.tab.Panel',{
			plain:true,
			bodyStyle:'background: none;',
			border    : false,
			defaults:{bodyStyle:'background:none; padding:5px'}			
		});
		
		this.form = Ext.create('Ext.form.Panel',{
			border: false,
			bodyBorder: false,
			bodyStyle:'background: none',
			margin: '2 0 0 0',
			waitMsgTarget: true,
			layout: 'fit',
			monitorValid: true,
			fieldDefaults: {
				labelAlign: 'right',
				labelWidth: 110
			},
			items: this.tabs
		});		
		
		this.items = [this.form]; 
		
		if ( Config.user.permissions.admin )
		{
			Ext.apply(this, {
				tools:[{
					type:'gear',
					tooltip: Config.Lang.setup,
					scope: this,
					handler: function(){
						Ext.create('Cetera.window.CatalogPrefs',{
							catalog: this.catalog
						});
					},
				}]
			});
		}
		
		Ext.apply(this, {
			buttons: [
				{
                    xtype: 'button',
                    text: Config.Lang.ok,
					scope: this,
                    handler: function() { this.save(true); }
                },
				{
                    xtype: 'button',
                    text: Config.Lang.cancel,
					scope: this,
                    handler: function() { this.close(); }
                },
				{
                    xtype: 'button',
                    text: _('Применить'),
					scope: this,
                    handler: function() { this.save(false); }
                }				
			]
		});
		
		this.callParent();	
	},

	beforeShow: function() {
		this.setLoading( true );
		Cetera.model.Catalog.load(this.catalog_id, {
			success: function(catalog) {
				this.catalog = catalog.getData();
				this.setup();
			},
			failure: function(record, operation) {
				this.close();
			},			
			callback: function(record, operation) {
				this.setLoading( false );	
			},
			scope: this			
		});		
		this.callParent();
	},
	
	setup: function() {
		if (!this.catalog) return;
		this.setTitle(Config.Lang.catProps + ' "'+ this.catalog.name2 +'" (ID='+this.catalog.id+')');
		
		this.variables = this.catalog.is_root || this.catalog.is_server;
		this.permissions = !this.catalog.is_link && Config.user.permissions.admin;
				
		if (!this.catalog.is_root)
		{
			var fields = [				
				{
					fieldLabel: Config.Lang.name,
					name: 'name',
					allowBlank:false,
					value: this.catalog.name
				}, 
				{
					fieldLabel: this.catalog.is_server?Config.Lang.domainName:'Alias',
					name: 'alias',
					allowBlank:false,
					regex: /^[\.\-\_A-Z0-9А-Я]+$/i,
					value: this.catalog.alias
				}
			];
			
			if (this.catalog.is_server)
			{
				Ext.Array.push(fields,{
					xtype: 'aliasesfield',
					fieldLabel: Config.Lang.synonym,
					name: 'server_aliases',
					value: this.catalog.aliases
				});
				
				Ext.Array.push(fields,{
					fieldLabel: _('Тема'),
					name: 'templateDir',
					xtype: 'combobox',
					value: this.catalog.templateDir,
					displayField: 'title',
					valueField: 'url',
					editable: false,
					store: Ext.create('Ext.data.Store', {
						model: 'Cetera.model.Theme',
						autoLoad: true
					}),
					trigger2Cls: 'icon-setup',
					onTrigger2Click: function() {
						var rec = this.findRecordByValue(this.getValue());
						if (!rec) return;
						Ext.create('Cetera.theme.Activate',{
							theme: rec,
							serverId: this.up('window').catalog.id
						});
					}
				});			
			}
			
			Ext.Array.push(fields, {
				xtype: 'section_controller',
				section: this.catalog
			});	

			if (!this.catalog.is_server)
			{
				Ext.Array.push(fields, {
					xtype: 'folderfield',
					name: 'parentid',
					allowBlank: true,
					fieldLabel: _('Размещение'),
					displayValue: this.catalog.parent.path,
					path: this.catalog.parent.treePath,
					value: this.catalog.parent.id,
					exclude: this.catalog.id,
					nolink: 1,
					rule: Config.permissions.PERM_CAT_ADMIN
				});			
			}
			
			if (!this.catalog.is_link)
			{
				Ext.Array.push(fields, {
					xtype: 'materialtypefield',
					fieldLabel: _('Тип материалов'),
					name: 'typ',
					empty: 1,
					linkable: 1,
					allowBlank: false,
					disabled: this.catalog.materialsCount > 0,
					value: this.catalog.materialsType  
				});	
				
				Ext.Array.push(fields, {
					xtype: 'checkbox',
					boxLabel: _('автоматически заполнять alias материалов'),
					name: 'autoalias',
					labelSeparator: '',
					checked: this.catalog.autoalias,
					listeners: {
						check: {
							fn: function(el, checked) {
								this.ownerCt.getComponent("auto1").setDisabled(!checked);
								this.ownerCt.getComponent("auto2").setDisabled(!checked);
							}
						}
					},
					inputValue: 1
				});	

				Ext.Array.push(fields, {
					xtype: 'radio',
					itemId: 'auto1',
					boxLabel: _('на основании даты'),
					name: 'autoalias_type',
					labelSeparator: '',
					disabled: 1-this.catalog.autoalias,
					checked: 1-this.catalog.autoaliasTranslit && 1-this.catalog.autoaliasId,
					inputValue: 1
				});
				
				Ext.Array.push(fields, {
					xtype: 'radio',
					itemId: 'auto2',
					boxLabel: _('транслит заголовка'),
					name: 'autoalias_type',
					labelSeparator: '',
					disabled: 1-this.catalog.autoalias,
					checked: this.catalog.autoaliasTranslit,
					inputValue: 2
				});
				
				Ext.Array.push(fields, {
					xtype: 'radio',
					itemId: 'auto3',
					boxLabel: _('ID материала'),
					name: 'autoalias_type',
					labelSeparator: '',
					disabled: 1-this.catalog.autoalias,
					checked: this.catalog.autoaliasId,
					inputValue: 3
				});				
				
				Ext.Array.push(fields, {
					xtype: 'checkbox',
					boxLabel: _('скрытый'),
					name: 'hidden',
					labelSeparator: '',
					checked: this.catalog.hidden,
					inputValue: 1
				});
				
			}
			
			if (this.catalog.is_link)
			{
				Ext.Array.push(fields, {
					xtype: 'folderfield',
					name: 'typ',
					fieldLabel: _('Связан с'),
					displayValue: this.catalog.prototype.path,
					path: this.catalog.prototype.treePath,
					value: this.catalog.prototype.id,
					exclude: this.catalog.id,
					nolink: 1,
					allowBlank: false,
					rule: 7
				});			
			}

			this.tabs.add({
				title      : Config.Lang.propsMain,
				layout     : 'anchor',
				border     : false,
				bodyBorder : false,
				defaults   : { anchor: '0', hideEmptyLabel: false },
				defaultType: 'textfield',
				items: fields			
			});
		}
        
        // Construсtor
		if (this.catalog.template=='[visual_constructor]' && Config.user.permissions.admin)
		{
			this.tabs.add({
				title  : _('Конструктор'),
				border    : false,
				bodyBorder: false,
				layout     : 'anchor',
				defaults   : { anchor: '100% 100%' },	                
				items  : [
                    {
                        fieldLabel: _('Шаблон'),
                        xtype: 'visualtemplate',
                        name: 'visual_constructor',
                        hideLabel: true,
                        value: this.catalog.visual_constructor
                    }	
				]		
			});	
		}        
		
		// robots.txt
		if (this.catalog.is_server && Config.user.permissions.admin)
		{
			this.tabs.add({
				title  : 'robots.txt',
				border    : false,
				bodyBorder: false,
				layout     : 'anchor',
				defaults   : { anchor: '100% 100%' },						
				items  : [
					{
						xtype: 'textarea',
						name: '_robots_txt',
						value: this.catalog.robots
					},
				]		
			});	
		}
		
		// permissions
		if (this.permissions)
		{
            this.selectedGroup = 0;
            
            this.hiddenInherit = new Ext.form.Hidden({
                name:  'cat_inherit',
                value: this.catalog.inheritPermissions
            });		

            // список разрешений
            this.permGrid = new Ext.grid.GridPanel({
                margin: '5 0 0 0',
				anchor:   '100% 45%',
                store: new Ext.data.SimpleStore({
                    fields: ['id', 'name', 'checked'], data: []
                }),
                columns: [
              		{
                      header: _('Разрешения'), 
                      flex: 1,
                      dataIndex: 'name'
                  },{
                      xtype: 'checkcolumn',
                      header: _('Разрешить'),
                      dataIndex: 'checked',
                      width: 65,
                      listeners: {
                          checkchange: function (c, rowIndex, checked, eOpts) {
                              var record = this.permGrid.store.getAt( rowIndex );
                              if (checked) {
								  Ext.Array.push( this.catalog.permissions[record.getId()].groups, this.selectedGroup );
                              } else {
								  Ext.Array.remove( this.catalog.permissions[record.getId()].groups, this.selectedGroup );
                              }
                          }, 
                          scope: this
                      }
                  }
              	],
                selModel: new Ext.selection.RowModel({
                    listeners: { 'beforeselect' : function() { return false; } }
                })
            });
            
            // список групп
            this.groupsGrid = Ext.create('Ext.grid.Panel', {
                loadMask: true,
				anchor:   '100% 45%',
                store : new Ext.data.JsonStore({
                    fields: ['id', 'name'],                  				
					proxy: {
						type: 'ajax',
						url: 'include/data_groups.php',
						reader: {
							type: 'json',
							root: 'rows'
						}
					},					
              
                    listeners: {
                        'load': {
                            fn: function() {
                                this.groupsGrid.getSelectionModel().select(0);
                            },
                            scope: this
                        }
                    }
                }),
                selModel: new Ext.selection.RowModel({
                    listeners: {
                        'select' : {
                            fn: function(sm,r,i) {
                                this.selectedGroup = parseInt(r.data['id']);
                                var a = [];
                                Ext.Object.each(this.catalog.permissions, function(index, item) {
                                    if (item) a.push([index, item.name, this.selectedGroup==Config.groupAdmin || item.groups.indexOf(this.selectedGroup)>=0]);
                                }, this);
                                this.permGrid.getStore().loadData(a);
                                this.checkPermGrid();
                            },
                            scope: this
                        },
                        'deselect' : {
                            fn: function() {
                                this.checkPermGrid();
                            },
                            scope: this
                        }
                    }
                }),
                columns: [
            		{width: 20, renderer: function(v, m) { m.css = 'icon-users'; } },
            		{dataIndex: 'name', flex: 1}
            	],
                listeners: {
                    viewready: {
                        fn: function(grid) {
                            grid.store.load();
                        }
                    }
                },
                hideHeaders      : true
            });
                        
			this.tabs.add({
				title  : _('Разрешения'),
				border    : false,
				bodyBorder: false,
				layout: 'anchor',
				items  : [
					{
						xtype: 'checkbox',
						boxLabel: _('наследовать разрешения родительского раздела'),
						submitValue: false,
						labelSeparator: '',
						disabled: this.catalog.id == 0,
						checked: this.catalog.inheritPermissions,
						listeners: {
							'change': {
								fn: function(el) { 
									this.hiddenInherit.setValue(el.checked?2:0);
									this.checkPermGrid();
								},
								scope: this
							}
						}
					},
					this.hiddenInherit, 
					{
						xtype : 'label',
						html  : _('Группы')+':',
						style: 'padding: 3px;'
					}, 
					this.groupsGrid, 
					this.permGrid
				]	
			});				
		}
		
		// Переменные 
		if (this.variables)
		{
            this.varsStore = new Ext.data.JsonStore({
                proxy: {
                    type: 'ajax',
                    url: 'include/data_vars.php',
                    reader: {
                        type: 'json',
                        root: 'rows',
                        idProperty: 'name'
                    },
                    extraParams: {
						id: this.catalog.id
					}
                },
                fields: ['id','name','value','value_orig','describ']
            });	

            this.varDescrib = new Ext.Panel({
				anchor: '100% 50%',
                bodyStyle: 'padding: 2px',
                height: 50
            });

            this.varsGrid = new Ext.grid.GridPanel({
                anchor: '100% 50%',
                margin: '0 0 5 0',
                store: this.varsStore,
                loadMask: true,
                plugins: [Ext.create('Ext.grid.plugin.RowEditing', {
                    clicksToMoveEditor: 1,
                    autoCancel: true
                })],
                columns: [
            		{
                        header: _('Имя'), 
                        dataIndex: 'name', 
                        sortable: true,
                        width: 200
                    },{
                        header: _('Значение'), 
                        dataIndex: 'value',
                        sortable: true,
                        renderer: this.varValue,
                        flex: 1
                    }
            	],
            	tbar: [
                    {
                        iconCls: 'icon-reload',
                        tooltip: _('Обновить'),
                        handler: function () { this.varsStore.reload(); },
                        scope: this
                    },'-',{
                        iconCls:'icon-new',
                        tooltip:_('Новая переменная'),
						hidden: !this.catalog.is_root,
                        handler: function () {
                            var win = this.getVarEditWindow();
                            this.varNameField.setValue(''); 
                            this.varValueField.setValue(''); 
                            this.varDescribField.setValue(''); 
                            win.show();
                        },
                        scope: this
                    },{
                        iconCls:'icon-edit',
                        tooltip:_('Редактировать'),
						hidden: !this.catalog.is_root,
                        handler: function () {
                            if (this.selectedVar === false) return;
                            var win = this.getVarEditWindow();
                            var data = this.varsStore.getAt(this.selectedVar).data;
                            this.varNameField.setValue(data.name); 
                            this.varValueField.setValue(data.value); 
                            this.varDescribField.setValue(data.describ); 
                            win.show();
                        },
                        scope: this
                    },{
                        iconCls:'icon-delete',
                        tooltip:_('Удалить'),
						hidden: !this.catalog.is_root,
                        handler: function () {
                            if (this.selectedVar !== false)
                                this.varsStore.removeAt(this.selectedVar);
                            this.selectedVar = false;
                        },
                        scope: this
                    },{
                        iconCls:'icon-edit',
                        tooltip:_('Переопределить'),
						hidden: this.catalog.is_root,
                        handler: function () {
                            if (this.selectedVar === false) return;
                            var win = this.getVarEditWindow();
                            var data = this.varsStore.getAt(this.selectedVar).data;
                            this.varNameField.setValue(data.name); 
                            this.varValueField.setValue(data.value); 
                            this.varDescribField.setValue(data.describ); 
                            win.show();
                        },
                        scope: this
                    },{
                        iconCls:'icon-delete',
                        tooltip:_('Удалить переопределение'),
						hidden: this.catalog.is_root,
                        handler: function () {
                            if (this.selectedVar === false) return;
                            var rec = this.varsStore.getAt(this.selectedVar);
                            rec.set('value',rec.get('value_orig'));
                        },
                        scope: this
                    }
                ],
                selModel: new Ext.selection.RowModel({
                    listeners: {
                        'select': {
                            fn: function(sm, r, rowIdx) {
                                this.selectedVar = rowIdx;
                                this.varDescrib.update(r.get('describ'));
                            },
                            scope: this
                        },
                        'deselect': {
                            fn: function(sm, r, rowIdx) {
                                this.selectedVar = false;
                                this.varDescrib.update('');
                            },
                            scope: this
                        }
                    }
                }),
                listeners: {
                    viewready: {
                        fn: function(grid) {
                            grid.store.load();
                        }
                    }
                }
            });	

            this.selectedVar = false;
            this.varEditWindow = null;
            
            this.getVarEditWindow = function() {
                if (!this.varEditWindow) {
                
                    this.varNameField = new Ext.form.TextField({fieldLabel: _('Имя')});
                    this.varValueField = new Ext.form.TextField({fieldLabel: _('Значение')});
                    this.varDescribField = new Ext.form.TextField({fieldLabel: _('Описание')});
                
                    if (!this.catalog.is_root) {
                        this.varNameField.disabled = true;
                        this.varDescribField.disabled = true;
                    }
                
                    this.varEditWindow = new Ext.Window({
                        modal: true,
                        closeAction: 'hide',
                        width: 400,
                        height: 150,
                        layout: 'anchor',
                        bodyStyle: 'padding:5px',
                        items: [this.varNameField, this.varValueField, this.varDescribField],
                        defaults: { anchor: '0' },
                        buttons: [{
                            text: _('ОК'),
                            handler: function() { 
                                if (this.selectedVar !== false) {
                                    var rec = this.varsStore.getAt(this.selectedVar);
                                    rec.set('name',this.varNameField.getValue());
                                    rec.set('value',this.varValueField.getValue());
                                    rec.set('describ',this.varDescribField.getValue());
                                } else {
                                    var n = this.varsStore.getCount();
                                    var p = {
                                        id: 0,
                                        name: this.varNameField.getValue(),
                                        value: this.varValueField.getValue(),
                                        describ: this.varDescribField.getValue()
                                    };
                                    this.varsStore.insert(n, p);
                                }
                                this.varEditWindow.hide(); 
                            },
                            scope: this
                        },{
                            text: _('Отмена'),
                            handler: function() { this.varEditWindow.hide(); },
                            scope: this
                        }]
                    });
                }
                return this.varEditWindow;
                
            }
            
			this.tabs.add({
				title  : _('Переменные'),
				border    : false,
				bodyBorder: false,
				layout: 'anchor',
				items  : [
					this.varsGrid,
					this.varDescrib
				]	
			});	
			
		}
		
		if (this.catalog.user_fields)
		{
			eval(this.catalog.user_fields.init);
			eval('this.tabs.add(['+this.catalog.user_fields.tabs+']);');
		}
		
		this.tabs.setActiveTab(0);		
		
	},
	
	save: function(close) {
		
		if (this.catalog.user_fields)
		{
			eval(this.catalog.user_fields.save);
		}		
					
		var form = this.form.getForm();
		var me   =  this;
		var tree = Ext.getCmp('main_tree');              
		
		var params = {
			action: 'cat_save', 
			id: this.catalog.id
		};

		if (me.permissions && !me.hiddenInherit.value) 
		{
			Ext.Object.each(me.catalog.permissions, function(index, item) {
				if (item) {
				  params['permissions['+index+'][]'] = item.groups;
				}
			}, me);
		}
		
		if (me.variables)
		{
			var i = 0;
			me.varsStore.each(function(rec) {
				params['vars['+i+'][id]'] = rec.get('id');
				params['vars['+i+'][name]'] = rec.get('name');
				params['vars['+i+'][value]'] = rec.get('value');
				params['vars['+i+'][describ]'] = rec.get('describ');
				i++;
			},me);
		}
		
		form.submit({
			url:'include/action_catalog.php', 
			params: params,
			waitMsg: _('Подождите ...'),
			scope: this,
			success: function(form, action) {
				tree.reload(action.result.path); 
				if (close) this.close();
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
						
	},

	varValue: function(val,m,rec) {
		if(val != rec.get('value_orig')) {
			return '<b>' + val + '</b>';
		}
		return val;
	},

	checkPermGrid: function() {
		this.permGrid.setDisabled(this.groupsGrid.getSelectionModel().getCount()==0 || this.hiddenInherit.value>0 || this.selectedGroup==Config.groupAdmin);
	}	

});