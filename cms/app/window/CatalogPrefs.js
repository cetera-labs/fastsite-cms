Ext.define('Cetera.window.CatalogPrefs', {
	extend:'Ext.Window',
	closable:true,
	width:500,
	height: 400,
	title: _('Настройки раздела'),
	plain:true,
	layout: 'fit',
	resizable: true,
	autoShow: true,
	modal: true,
	
	catalog: null,
	
	initComponent : function() {
				
		this.okButton = Ext.create('Ext.Button',{
			text: _('OK'), 
			scope: this,
			handler: this.save
		}); 
		
		this.cancelButton = Ext.create('Ext.Button',{
			text: _('Отмена'),
			scope: this,
			handler: function() { this.close(); }
		});             
		
		this.buttons = [this.okButton, this.cancelButton]; 
		
		// список полей материалов
		this.fieldsGrid = new Ext.grid.GridPanel({
			enableHdMenu     : false,
			enableColumnMove : false,
			enableColumnResize: false,
			region: 'center',
			disabled: this.catalog.inheritFields,
			store: Ext.create('Ext.data.JsonStore', {
				autoLoad: true,
				fields: ['id', 'name', 'describ', 'force_show', 'force_hide', 'shw', 'fixed'],
				proxy: {
					type: 'ajax',
					url: 'include/data_types.php?mode=fields&type_id='+this.catalog.materialsType+'&catalog='+this.catalog.id,
					reader: {
						type: 'json',
						root: 'rows'
					}
				},	
				filters: [
					function(item) {
						return item.get('fixed') != 1;
					}
				]				
			}),
			columns: [
				{
				  header: _('Поле'), 
				  flex: 1,
				  renderer: this.fieldName,
				  dataIndex: 'name'
			  },{
				  xtype: 'checkcolumn',
				  header: _('Показать'),
				  dataIndex: 'force_show',
				  width: 65,
				  renderer: function (value, metaData, record) {
					  if (record.get('shw')) return;
					  return (new Ext.grid.column.CheckColumn()).renderer(value);
				  }
			  },{
				  xtype: 'checkcolumn',
				  header: _('Скрыть'),
				  dataIndex: 'force_hide',
				  width: 65,
				  renderer: function (value, metaData, record) {
					  if (!record.get('shw')) return;
					  return (new Ext.grid.column.CheckColumn()).renderer(value);
				  }
			  }
			],
			selModel: new Ext.selection.RowModel({
				listeners: { 'beforeselect' : function() { return false; } }
			}),
			height: 145
		});            
		
		this.items = [{
			xtype:'tabpanel',
			plain:true,
			bodyStyle:'background: none;',
			border    : false,
			defaults:{bodyStyle:'background:none; padding:5px'},
			items:[
				{ 
					title      : _('Видимость полей'),
					border     : false,
					bodyBorder : false,
					layout: 'border',
					items: [
						{
							xtype: 'checkbox',
							height: 30,
							region: 'north',
							boxLabel: _('наследовать настройки родительского раздела'),
							submitValue: false,
							labelSeparator: '',
							checked: this.catalog.inheritFields,
							listeners: {
								'change': {
									fn: function(el) { 
										this.fieldsGrid.setDisabled(el.checked);
									},
									scope: this
								}
							}
						},                        
						this.fieldsGrid
					]
				}
			]
		}]; 
		
		this.callParent();
		
	},
	 
	save: function() {
	
		var params = {
			action: 'cat_prefs',
			id: this.catalog.id,
			inheritFields: this.fieldsGrid.isDisabled()?1:0,
		};
		
		params['fields'] = {}
		this.fieldsGrid.store.each(function(rec) {
			params['fields['+rec.get('id')+'][force_show]'] = rec.get('force_show')?1:0;
			params['fields['+rec.get('id')+'][force_hide]'] = rec.get('force_hide')?1:0;
		}, this);            
		
		this.setLoading(true);
	
		Ext.Ajax.request({
			url: '/cms/include/action_catalog.php',
			params: params,
			scope: this,
			success: function(resp) {
				this.setLoading(false);
				this.close();
			}
		});
		
	},

	fieldName: function(val,m,rec) {
		return '<b>' + rec.get('describ') + '</b> ('+val+')';
	},	
				   
});
