Ext.require('Cetera.model.MaterialType');
Ext.require('Cetera.model.ObjectField');

Ext.define('Cetera.panel.MaterialTypes', {

    extend:'Ext.Panel',

    layout: 'fit',
    border: false,
    
    typesStore: Ext.create('Ext.data.Store', {
        sorters:    [{property: 'alias', direction: "ASC"}],
        groupField: 'fixed',
        autoSync:   true,
        batch:      false,
        
        model: 'Cetera.model.MaterialType',
        
        proxy: {
            type: 'ajax',
            simpleSortMode: true,
            api: {
                read: 'include/data_types.php',
                create: 'include/action_types.php?action=type_create',
                update: 'include/action_types.php?action=type_update',
                destroy: 'include/action_types.php?action=type_delete'
            },
            reader: {
                type: 'json',
                successProperty: 'success',
                root: 'rows',
                messageProperty: 'message'
            },
            writer: {
                type: 'json',
                writeAllFields: true,
                root: 'rows',
                encode: true
            }
        }
    }),
	
	destroy : function() {
        this.typesGrid.destroy();
        this.fieldsGrid.destroy();
        this.editWin.destroy();
        this.fieldsWin.destroy();
		this.callParent();
	},
    
    initComponent : function() {
    
        if (!this.compact) this.compact = false;
            
        this.fieldsStore = new Ext.data.JsonStore({
            autoDestroy: true,		
			autoSync:   true,
			batch:      false,
       			
			model: 'Cetera.model.ObjectField',
			
            proxy: {
                type: 'ajax',
                reader: {
                    type: 'json',
                    successProperty: 'success',
                    root: 'rows',
                    messageProperty: 'message'
                },
                writer: {
                    type: 'json',
                    writeAllFields: true,
                    root: 'rows',
                    encode: true
                },
                api: {
                    read:    'include/data_types.php?mode=fields',
                    destroy: 'include/action_types.php?action=field_delete',
					update:  'include/action_types.php?action=field_edit',
                },
                extraParams: {
                    'type_id': 0
                }
            }
        });
    

        this.typesGrid = this.buildTypesGrid();
        this.fieldsGrid = this.buildFieldsGrid();
        this.editWin = this.buildEditWindow();  
        this.fieldsWin = this.buildFieldsWindow();  
        
        this.items = this.typesGrid;
        
        this.typesStore.load();
        
        this.callParent();
    },
   
    // Новый тип материалов
    typeAdd: function() {        
        this.typesStore.insert(0, {'alias': '', 'fixed':0});
        this.typeRowEditing.startEdit(0, 2);
    },
    
    // Изменить тип материалов
    typeEdit: function() {
        if (!this.typesGrid.getSelectionModel().hasSelection()) return;
        var sel = this.typesGrid.getSelectionModel().getSelection()[0];
        this.fieldsStore.proxy.extraParams.type_id = sel.getId();
        this.fieldsStore.load();
        this.fieldsWin.setTitle(sel.get('describ'));
        this.fieldsWin.show();
    },
    
    // Удалить тип материалов
    typeDelete: function() {
        var rec = this.typesGrid.getSelectionModel().getSelection()[0];
        if (!rec) return false;
        if (rec.data.fixed) return false;
        Ext.MessageBox.confirm(Config.Lang.delete, Ext.String.format(Config.Lang.typeDeleteWarning, rec.data.alias), function(btn) {
            if (btn == 'yes') this.typesStore.remove(rec);
        }, this);
    },
     
    fieldCall: function(action) {
        Ext.Ajax.request({
            url: 'include/action_types.php',
            params: { 
                action: action, 
                id: this.fieldsGrid.getSelectionModel().getSelection()[0].getId()
            },
            scope: this,
            success: function(resp) {
                this.fieldsStore.load();
            }
        });
    },
    
    // Добавить поле
    fieldAddClick: function() {
        this.field_action = 'field_create';
        this.editWin.setTitle(Config.Lang.fieldAdd);
        var fp = this.field_props;
        fp.name.setValue('');
		fp.name.setDisabled(0);
        fp.describ.setValue('');
        fp.required.setValue(false);
		fp.required.setDisabled(0);
        fp.hidden.setValue(false);
		fp.hidden.setDisabled(0);
        fp.size.setValue('100');
		fp.size.setDisabled(0);
        fp.variants.setValue('');
		fp.variants.setDisabled(0);
        fp.folder.setValue(0);
        fp.from_cat_l.setValue(0);
        fp.folder.setDisplayValue('');
        fp.folder.setValue('');
		fp.folder.setDisabled(0);
        fp.def_value.setValue('');
        fp.editor_u.setValue('');
        fp.page.setValue('');
        //fp.type.setValue(fp.type.getStore().getAt(0).get('id'));
        fp.datatype.setValue(fp.datatype.getStore().getAt(0).get('id'));
        fp.datatype.fireEvent('select', fp.datatype);
		fp.datatype.setDisabled(0);
		fp.editor.setDisabled(0);
        this.editWin.show();
    },
    
    // Изменить поле
    fieldEditClick: function() {
        this.field_action = 'field_edit';
        this.editWin.setTitle(Config.Lang.fieldEdit);
        var fp = this.field_props;
        var f = this.fieldsGrid.getSelectionModel().getSelection()[0];
        var type = f.get('type');
		var fixed = f.get('fixed');
		
        fp.name.setValue(f.get('name'));
		fp.name.setDisabled(fixed);
		
        fp.describ.setValue(f.get('describ'));
		
        fp.required.setValue(f.get('required')=='1');
		fp.required.setDisabled(fixed);
		
        fp.hidden.setValue(f.get('shw')=='0');
		fp.hidden.setDisabled(fixed);
		
        fp.size.setValue('1000');
		fp.size.setDisabled(fixed);
		
        fp.variants.setValue('');
		fp.variants.setDisabled(fixed);
		
        fp.folder.folderId = 0;
        fp.folder.setValue('');
		fp.folder.setDisabled(fixed);
		
        fp.def_value.setValue(f.get('default_value'));
        fp.editor_u.setValue(f.get('editor_user'));
        fp.page.setValue(f.get('page'));
		
        fp.type.setValue(parseInt(fp.datatype.getStore().getAt(0).get('id')));
		fp.type.setDisabled(fixed);
        
        fp.datatype.setValue(parseInt(type));
        fp.datatype.fireEvent('select', fp.datatype);
		fp.datatype.setDisabled(fixed);
        
        if (f.get('editor') != '0') {
            fp.editor.setValue( parseInt(f.get('editor')) );
            fp.editor.fireEvent('select', fp.editor);
        }
		fp.editor.setDisabled(fixed);

        if (type==Config.fields.FIELD_MATSET || type==Config.fields.FIELD_MATERIAL) fp.type.setValue(parseInt(f.get('len')))
        else if (type==Config.fields.FIELD_LINK || type==Config.fields.FIELD_LINKSET) { 
        
      	  	if (parseInt(f.get('len'))) {
                  fp.folder.setValue(f.get('len'));
                  fp.folder.setDisplayValue(f.get('path'));
      			      fp.from_cat.setValue(true);
      		  } else {
      		        fp.cur_cat.setValue(true); 
      		  }
     
        } else if (type==Config.fields.PSEUDO_FIELD_CATOLOGS) {
        
                fp.from_cat_l.setValue(f.get('len'));
                fp.from_cat_l.setDisplayValue(f.get('path'));
            
        } else if (type==Config.fields.FIELD_ENUM) fp.variants.setValue(f.get('len'))   
        else if (type==Config.fields.FIELD_TEXT) fp.size.setValue(f.get('len'));
              
        this.editWin.show();
    },
    
    // Удалить поле
    fieldDeleteClick: function() {
        Ext.MessageBox.confirm(Config.Lang.delete, Config.Lang.r_u_sure, function(btn) {
            if (btn == 'yes') this.fieldCall('field_delete');
        }, this);      
    },
    
    fieldSubmit: function() {
        this.form.getForm().submit({
            url:'include/action_types.php', 
			timeout: 600,
            params: { 
                action:   this.field_action, 
                type_id:  this.typesGrid.getSelectionModel().getSelection()[0].getId(),
                field_id: this.fieldsGrid.getSelectionModel().hasSelection()?this.fieldsGrid.getSelectionModel().getSelection()[0].getId():0
            },
            waitMsg: Config.Lang.wait,
            scope: this,
            success: function(form, action) {
                this.editWin.hide();
                this.fieldsStore.load();                
                this.fieldsGrid.getSelectionModel().clearSelections();                
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
    },
    
    // Список типов материалов
    buildTypesGrid: function() {
    
        this.typeRowEditing = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 2,
            listeners: {
                cancelEdit: function(rowEditing, context) {
                    // Canceling editing of a locally added, unsaved record: remove it
                    if (context.record.phantom) {
                        this.typesStore.remove(context.record);
                    }
                }
            }
        });
    
        return Ext.create('Ext.grid.Panel', {
            plugins: [this.typeRowEditing],
            columns: [
                {
                    sortable: true, 
                    dataIndex: 'fixed',
                    hidden: this.compact
                },{
                    header: 'ID', 
                    width: 40, 
                    dataIndex: 'id',
                    hidden: this.compact
                },{
                    header: Config.Lang.name, 
                    width: 150, 
                    sortable: true, 
                    dataIndex: 'alias',
                    editor: {allowBlank:false, regex: /([\.\-\_a-zA-Z0123456789]+)/i},
                    hidden: this.compact
                },{
                    header: Config.Lang.description, 
                    width: 150, 
                    sortable: true, 
                    dataIndex: 'describ', 
                    flex: 1,        
					renderer: function(value,m,rec) {
						return rec.get('describDisplay');
					},
                    editor: this.compact ? false : {}
                }
            ],
            hideHeaders: this.compact,
            store: this.typesStore,
            border: false,
            tbar: [{
                tooltip: Config.Lang.refresh,
                iconCls: 'icon-reload',
                handler: function() { this.typesStore.load(); },
                scope: this
            }, '-', {
                tooltip: Config.Lang.typeCreate,
                iconCls: 'icon-new',
                handler: this.typeAdd,
                scope: this,
                hidden: this.compact
            }, {
                id: this.getId()+'tb_typ_edit',
                tooltip: Config.Lang.edit,
                disabled: true,
                iconCls: 'icon-edit',
                handler: this.typeEdit,
                scope: this
            }, {
                id: this.getId()+'tb_typ_delete',
                disabled: true,
                tooltip: Config.Lang.delete,
                iconCls: 'icon-delete',
                handler: this.typeDelete,
                scope: this,
                hidden: this.compact
            }],
            features: [{
                id: 'group',
                ftype: 'grouping',
                groupHeaderTpl: '<tpl if="name == 1">'+Config.Lang.typeFixed+'</tpl><tpl if="name == 0">'+Config.Lang.typeUser+'</tpl>',
                hideGroupedHeader: true,
                enableGroupingMenu: false
            }],
            listeners: {
                beforeedit: function(e,c) {
                    // Запрещаем редактировать встроенные типы
                    if (c.record.get('fixed') && c.field == 'alias') return false;
                }
            },
            selModel: {
                singleSelect:true,
                listeners: {
                    'selectionchange' : {
                        fn: function(sm) {
                            var hs = sm.hasSelection();
                            var sel  = 'x';
                            if (hs) sel = sm.getSelection()[0].getId();
                            Ext.getCmp(this.getId()+'tb_typ_delete').setDisabled(!hs || sm.getSelection()[0].get('fixed') || isNaN(sel));
                            Ext.getCmp(this.getId()+'tb_typ_edit').setDisabled(!hs || isNaN(sel));
                        },
                        scope: this
                    }
                }
            },
            stripeRows: true,
            height: 250   
        });
    },
  
    // Список полей
    buildFieldsGrid: function() {
        var grid = Ext.create('Cetera.grid.Fields', {
            store: this.fieldsStore,
            tbar: [{
                tooltip: Config.Lang.refresh,
                iconCls: 'icon-reload',
                handler: function(){ this.fieldsStore.load(); },
                scope: this
            }, '-',  {
                tooltip: Config.Lang.add,
                iconCls: 'icon-new',
                handler: this.fieldAddClick,
                scope: this
            }, {
                id: this.getId()+'tb_fld_edit',
                tooltip: Config.Lang.edit,
                disabled: true,
                iconCls: 'icon-edit',
                handler: this.fieldEditClick,
                scope: this
            }, '-',  {
                id: this.getId()+'tb_fld_up',
                disabled: true,
                iconCls:'icon-up',
                tooltip: Config.Lang.upper,
                handler: function() { this.fieldCall('field_up'); },
                scope: this
            },{
                id: this.getId()+'tb_fld_down',
                disabled: true,
                iconCls:'icon-down',
                tooltip: Config.Lang.downer,
                handler: function() { this.fieldCall('field_down'); },
                scope: this
            }, '-', {
                id: this.getId()+'tb_fld_delete',
                disabled: true,
                tooltip: Config.Lang.delete,
                iconCls: 'icon-delete',
                handler: this.fieldDeleteClick,
                scope: this
            }],
		
            selModel: {
                singleSelect:true,
                listeners: {
                    'selectionchange' : {
                        fn: function(sm) {
                            var hs = sm.hasSelection();
							
                            Ext.getCmp(this.getId()+'tb_fld_up').setDisabled(!hs);
                            Ext.getCmp(this.getId()+'tb_fld_down').setDisabled(!hs);															
                            Ext.getCmp(this.getId()+'tb_fld_delete').setDisabled(!hs);
                            Ext.getCmp(this.getId()+'tb_fld_edit').setDisabled(!hs);						
							
							if (hs)
							{
								var rec = this.fieldsGrid.getSelectionModel().getSelection()[0];
								if (rec.get('fixed'))
								{
									Ext.getCmp(this.getId()+'tb_fld_delete').setDisabled(1);										
								}
							}
							
							
                        },
                        scope: this
                    }
                }
            },
            listeners: {
                'rowdblclick' : { fn: this.fieldEditClick, scope: this}
            }
        });
        
        return grid; 
    },
    
    // Окно со списком полей
    buildFieldsWindow: function() {
        var win = new Ext.Window({
            closable:true,
            width:1000,
            height:700,
            closeAction: 'hide',
            plain:true,
            layout: 'fit',
            resizable: false,
            modal: true,
            items: [this.fieldsGrid],
            buttons: [{
                text: Config.Lang.close,
                scope: this,
                handler: function() { this.fieldsWin.hide(); }
            }]
        });
        
        return win;
    },
    
    // Окно редактирования поля
    buildEditWindow: function() {
               
        Ext.apply(Ext.form.VTypes, {
            len:  function(v) {
                if (v<1 || v>65535) return;
                return true;
            },
            lenText: Config.Lang.invalidSize,
            lenMask: /[\d]/i
        });

        // Элементы формы редактирования поля
        this.field_props = {
            describ:   new Ext.form.TextField({ fieldLabel: Config.Lang.name , name: 'describ', allowBlank: false }),
            name:      new Ext.form.TextField({ fieldLabel: 'Alias', name: 'name', regex: /^[\_A-Z0-9]+$/i, allowBlank: false }),
            required:  new Ext.form.Checkbox({boxLabel: Config.Lang.requiredField, name: 'required', inputValue: 1}),
            hidden:    new Ext.form.Checkbox({boxLabel: Config.Lang.hiddenField, name: 'hidden', inputValue: 1}),
            size:      new Ext.form.TextField({fieldLabel: Config.Lang.size, name: 'len', vtype: 'len', value: 1000}),
            page:      new Ext.form.TextField({ fieldLabel: Config.Lang.page, name: 'page' }),
            folder:    Ext.create('Cetera.field.Folder', { name: 'catid', value: 0, nolink: 1 }),
            variants:  new Ext.form.TextField({fieldLabel: Config.Lang.variants, name: 'variants'}),
            def_value: new Ext.form.TextField({fieldLabel: Config.Lang.defaultValue, name: 'default_value'}),
            editor_u:  new Ext.form.TextField({fieldLabel: Config.Lang.editor, name: 'editor_user'}),
            from_cat:  new Ext.form.Radio({ boxLabel: Config.Lang.fromCatalog, name: 'cat', inputValue: 1, checked: true }),
            cur_cat:   new Ext.form.Radio({ boxLabel: Config.Lang.fromCurrentCatalog, name: 'cat', inputValue: 0 }),
            from_cat_l:Ext.create('Cetera.field.Folder', {fieldLabel: Config.Lang.fromCatalog2, name: 'catid_l', value: 0, nolink: 1 }),
            // Тип поля
            datatype:  new Ext.form.ComboBox({
                fieldLabel: Config.Lang.dataType,
                allowBlank: false,
                name:'type',
                store: new Ext.data.SimpleStore({
                    fields: ['id', 'name'],
                    data : Config.fieldTypes
                }),
                valueField:'id',
                displayField:'name',
                queryMode: 'local',
                triggerAction: 'all',
                editable: false
            }),
            type: new Ext.form.ComboBox({
                fieldLabel: Config.Lang.tipa,
                name:'types',
                store: new Ext.data.JsonStore({
                    fields: ['id', 'describ'],
                    autoSync: true,  
                    autoLoad: true,                  
                    proxy: {
                        type: 'ajax',
                        url: 'include/data_types.php?linkable=1',
                        reader: {
                            type: 'json',
                            root: 'rows'
                        },
                    }
                }),
                valueField:'id',
                displayField:'describ',
                triggerAction: 'all',
                editable: false
            }),
            editor: new Ext.form.ComboBox({
                fieldLabel: Config.Lang.editor,
                name:'editor',
                allowBlank: false,
                store: new Ext.data.SimpleStore({
                    fields: ['id', 'name'],
                    autoSync: true,
                    autoLoad: true,
                    data : []
                }),
                valueField:'id',
                displayField:'name',
                queryMode: 'local',
                triggerAction: 'all',
                editable: false
            })
        };
        
        this.field_props.type.getStore().load();

        this.type0 = new Ext.Panel({
            layout: 'form', border: false, defaults: {width: 478},
            bodyStyle:'background: none',
            items: [
                this.field_props.name,
                this.field_props.describ,
                this.field_props.datatype,  
                this.field_props.page,            
                {
                    xtype: 'checkboxgroup',
                    items: [
                        this.field_props.required,
                        this.field_props.hidden
                    ]
                },
                this.field_props.editor            
            ]
        });        
        
        this.type1 = this.field_props.size;
        
        this.type2 = new Ext.Panel({
            layout: 'form', bodyStyle:'background: none', border: false,
            hidden: true,
            defaults: {width: 478},
            items: [
                {
                    xtype: 'radiogroup',
                    items: [
                        this.field_props.from_cat,
                        this.field_props.cur_cat
                    ]
                },
                this.field_props.folder
            ]
        });
        
        this.type3 = this.field_props.type;
        this.type4 = this.field_props.variants;
        this.type5 = this.field_props.editor_u;
        this.type6 = this.field_props.from_cat_l;
        this.def_v = this.field_props.def_value;
        
        this.field_props.cur_cat.on('check', function(cb, checked) {
            this.field_props.folder.setDisabled(checked);
        }, this);
        
        this.field_props.editor.on('select', function() {
            this.type5.setVisible(this.field_props.editor.value == Config.editors.EDITOR_USER);    
        }, this);
        
        this.field_props.datatype.on('select', function(combo){
            this.type0.show();
            this.type1.hide();
            this.type2.hide();
            this.type3.hide();
            this.type4.hide();
            this.type6.hide();
            this.def_v.hide();
            switch (combo.value) {
                case Config.fields.FIELD_MATSET:
                case Config.fields.FIELD_MATERIAL:
                    this.type3.show();
                    break;
                case Config.fields.FIELD_LINK:
                case Config.fields.FIELD_LINKSET:
                    this.type2.show();
                    break;
                case Config.fields.FIELD_TEXT:
                    this.type1.show();       
                    break;
                case Config.fields.FIELD_ENUM:
                    this.type4.show();       
                    break;
                case Config.fields.PSEUDO_FIELD_CATOLOGS:
                    this.type6.show();      
                    break;
            }
            
            if (combo.value==Config.fields.FIELD_TEXT||combo.value==Config.fields.FIELD_INTEGER||combo.value==Config.fields.FIELD_DATETIME||combo.value==Config.fields.FIELD_ENUM||combo.value==Config.fields.FIELD_BOOLEAN)
            	this.def_v.show(); 
                
          if (Config.fields_fieldEditors[combo.value]) {
              var data = [];
            	for (i = 0; i < Config.fields_fieldEditors[combo.value].length; i++) {
            	   data[data.length] = [Config.fields_fieldEditors[combo.value][i], Config.fieldEditors[Config.fields_fieldEditors[combo.value][i]]];
            	}
              
            	this.field_props.editor.getStore().loadData(data); 
                this.field_props.editor.setValue(this.field_props.editor.getStore().getAt(0).get('id'));
            	this.field_props.editor.fireEvent('select');
          }
            
        }, this);
        
        this.form = new Ext.form.FormPanel({
            baseCls: 'x-plain',
			fieldDefaults : {
				labelWidth: 145
			},

            defaultType: 'textfield',
            bodyStyle:'padding:5px 5px 0;',
            defaults   : { anchor: '0', hideEmptyLabel: false },
            items: [              
                this.type0,
                this.type1,
                this.type2,
                this.type3,
                this.type4,
                this.type6,
                this.def_v,
                this.type5
            ]
        });
    
        var win = new Ext.Window({
            closable:true,
            width:500,
            height:300,
            closeAction: 'hide',
            plain:true,
            layout: 'fit',
            resizable: false,
            modal: true,
            items: [this.form],
            buttons: [{
                text: Config.Lang.ok,
                scope: this,
                handler: this.fieldSubmit
            },{
                text: Config.Lang.cancel,
                scope: this,
                handler: function() { this.editWin.hide(); }
            }],
            listeners: {
                'show': {
                    fn: function() {
                        // Обновим список доступных типов материалов
                        this.field_props.type.getStore().load({params: {exclude: this.typesGrid.getSelectionModel().getSelection()[0].getId()}});
                    },
                    scope: this
                }
            }
        });
        
        return win;
    }
 
});