// Панелька виджета 
Ext.require('Cetera.field.Folder');
Ext.require('Cetera.field.WidgetTemplate');

Ext.define('Cetera.widget.Section.Info', {

    extend: 'Cetera.widget.Widget',
    
    initComponent : function() {
        this.formfields = [
			{
				fieldLabel: Config.Lang.catalog,
				name: 'catalog',
				xtype: 'folderfield',
			},	
			{
				xtype:         'checkbox',
				boxLabel:      _('выводить META-теги'),
				name:          'show_meta',
				inputValue:     1,
				uncheckedValue: 0
			},				
			{
				xtype: 'widgettemplate',
				widget: 'Section.Info'
			}	
		];
        this.callParent();
    }	

});