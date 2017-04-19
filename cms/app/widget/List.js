// Панелька виджета 
Ext.require('Cetera.field.Folder');
Ext.require('Cetera.field.FileEditable');
Ext.require('Cetera.field.WidgetTemplate');

Ext.define('Cetera.widget.List', {

    extend: 'Cetera.widget.Widget',
    
    initComponent : function() {
        this.formfields = [{
            fieldLabel: Config.Lang.catalog,
            name: 'catalog',
            xtype: 'folderfield'
        },{
            xtype: 'numberfield',
            name: 'limit',
            fieldLabel: Config.Lang.matCount,
            maxValue: 999,
            minValue: 1,
            allowBlank: false
        },{
            name: 'order',
            fieldLabel: Config.Lang.sort,
            allowBlank: false
        },new Ext.form.ComboBox({
            fieldLabel: Config.Lang.order,
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
        }),
		{
            name: 'catalog_link',
            fieldLabel: _('Ссылка на раздел'),
        },
		{
			xtype: 'fieldcontainer',
			cls: 'x-field',
			fieldLabel: _('Страницы'),
			layout: 'hbox',
			items: [{
				xtype:          'checkbox',
				boxLabel:       _('показать навигацию'),
				name:           'paginator',
				inputValue:     1,
				uncheckedValue: 0,
				flex: 1
			}, {
				xtype: 'textfield',
				name: 'page_param',
				labelWidth: 150,
				fieldLabel: _('query параметр'),
				flex: 2
			}]
		},
		{
			xtype: 'fieldcontainer',
			cls: 'x-field',
			layout: 'hbox',
			items: [{
				xtype:          'checkbox',
				boxLabel:       _('AJAX навигация'),
				name:           'ajax',
				inputValue:     1,
				uncheckedValue: 0,
				flex: 1
			}, {
				xtype: 'textfield',
				name: 'paginator_url',
				fieldLabel: _('ссылка на страницу'),
				flex: 2
			}]
		},			
		{
			xtype: 'widgettemplate',
			widget: 'List'
        }];
        this.callParent();
    }

});