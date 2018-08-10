// Панелька виджета
Ext.require('Cetera.field.Folder');
Ext.require('Cetera.field.FileEditable');
Ext.require('Cetera.field.WidgetTemplate');

Ext.define('Cetera.widget.Menu', {
    extend: 'Cetera.widget.Widget',
    
    saveButton: true,
    
    formfields : [{
        fieldLabel: Config.Lang.rootFolder,
        name: 'catalog',
        xtype: 'folderfield'
    },{
        xtype: 'numberfield',
        name: 'depth',
        fieldLabel: Config.Lang.depth,
        maxValue: 99,
        minValue: 0,
        allowBlank: false
    },{
        name: 'css_class',
        fieldLabel: 'CSS класс',
        allowBlank: true
    },
	{
		xtype: 'widgettemplate',
		widget: 'Menu'
	},	
	{
        xtype: 'panel',
        bodyStyle:'background: none',
        border: false,
        html: '<p style="font-size:90%; margin: 0 0 0 140px">' + _('Если корневой раздел не указан, будет использован раздел той страницы, на которой показан виджет.')
        + '<br>' + _('Для вывода всех подразделов установите глубину = 0') + '</p>'
    }]

});