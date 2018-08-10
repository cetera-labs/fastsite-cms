Ext.require('Cetera.field.WidgetTemplate');

// Панелька виджета
Ext.define('Cetera.widget.MenuUser', {
    extend: 'Cetera.widget.Widget',
    
    formfields: [
        {
            xtype: 'combo',
            fieldLabel: Config.Lang.menu,
            valueField: 'menu',
            displayField: 'menu_name',
            name: 'menu',
            store: new Ext.data.JsonStore({
                fields: ['menu','menu_name'],
                proxy: {
                    type: 'ajax',
                    url: '/cms/include/data_menus.php'
                },
                autoSync: true,
                autoLoad: true
            }),
            editable: false,
            triggerAction: 'all',
            selectOnFocus:true,
            allowBlank: false       
        },{
            name: 'css_class',
            fieldLabel: 'CSS класс',
            allowBlank: true
        },	{
			xtype: 'widgettemplate',
			widget: 'MenuUser'
		},
    ]

});