// Панелька виджета 
Ext.require('Cetera.field.Folder');
Ext.require('Cetera.field.FileEditable');
Ext.require('Cetera.field.WidgetTemplate');
Ext.require('Cetera.field.LinkSet2');
Ext.require('Cetera.field.MatSet');

Ext.define('Cetera.widget.List', {

    extend: 'Cetera.widget.Widget',
    
    initComponent : function() {
        this.formfields = [
		
		{
			xtype: 'fieldcontainer',
			cls: 'x-field',
			layout: 'hbox',
			hideLabel: true,
			items: [
				{
					fieldLabel: Config.Lang.catalog,
					name: 'catalog',
					xtype: 'folderfield',
					flex: 1
				},	
				{
					xtype:          'checkbox',
					boxLabel:       _('подразделы'),
					name:           'subfolders',
					inputValue:     1,
					uncheckedValue: 0,
					margin: '0 5'						
				}				
			]
			
		},		
		
		{
            xtype: 'numberfield',
            name: 'limit',
            fieldLabel: Config.Lang.matCount,
            maxValue: 999,
            minValue: 1,
            allowBlank: false
        },
		
		{
			xtype: 'fieldcontainer',
			cls: 'x-field',
			layout: 'hbox',
			fieldLabel: Config.Lang.sort,
			defaults: {
				flex:1,
				xtype: 'textfield',
				hideLabel: true
			},
			items: [{
				name: 'order',
				allowBlank: false,
				margin: '0 5 0 0'
			},
			new Ext.form.ComboBox({
				name:'sort',
				store: new Ext.data.SimpleStore({
					fields: ['name', 'value'],
					data : [
						[_('Возрастанию'), 'ASC'],
						[_('Убыванию'), 'DESC']              
					]
				}),
				valueField:'value',
				displayField:'name',
				queryMode: 'local',
				triggerAction: 'all',
				editable: false,
			})]
			
		},		
		
		{
            name: 'catalog_link',
            fieldLabel: _('Ссылка на раздел'),
        },
		
		{
			xtype: 'fieldcontainer',
			cls: 'x-field',
			fieldLabel: _('Страницы'),
			layout: 'hbox',
			defaults: {
				inputValue:     1,
				uncheckedValue: 0,
				margin: '0 5 0 0'				
			},			
			items: [{
				xtype:          'checkbox',
				boxLabel:       _('показать навигацию'),
				name:           'paginator'
			}, {
				xtype:          'checkbox',
				boxLabel:       _('AJAX навигация'),
				name:           'ajax'
			}, {
				flex:1,
				xtype:          'checkbox',
				boxLabel:       _('бесконечная лента'),
				name:           'infinite'
			}]
		},
		{
			xtype: 'fieldcontainer',
			cls: 'x-field',
			layout: 'hbox',
			defaults: {
				flex:1
			},			
			items: [{
				xtype: 'textfield',
				name: 'page_param',
				labelWidth: 150,
				fieldLabel: _('query параметр'),
				margin: '0 5 0 0'
			}, {
				xtype: 'textfield',
				name: 'paginator_url',
				fieldLabel: _('ссылка на страницу')
			}]
		},	
		{
			xtype: 'fieldcontainer',
			cls: 'x-field',
			layout: 'hbox',
			items: [{
                flex: 1,
				xtype:          'checkbox',
				boxLabel:       _('текст, если нет материалов'),
				name:           'not_found_block'
			}, {
                flex: 2,
				xtype: 'textfield',
				name: 'not_found_text'
			}]
		},		
		{
			xtype: 'widgettemplate',
			widget: 'List'
        },
		{
            name: 'where',
            fieldLabel: _('Where'),
        },        
		{
            name: 'filter',
            fieldLabel: _('Доп. фильтр'),
        },
		{
            xtype: 'linkset2',
            name: 'materials',
            fieldLabel: _('Ручной выбор материалов'),
            height: 100
        }      
		];
        this.callParent();
    }

});