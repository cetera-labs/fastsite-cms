Ext.define('Cetera.widget.Container', {

    extend: 'Cetera.widget.Widget',
	
    formfields : [{
		xtype: 'combo',
		fieldLabel: Config.Lang.area,
		valueField: 'widgetId',
		displayField: 'widgetTitle',
		name: 'widgetId',
		store: Ext.create('Ext.data.JsonStore', {
			fields: ['widgetId','widgetTitle'],
			autoSync: true,
			autoLoad: false,
            proxy: {
                type: 'ajax',
                url: '/cms/include/data_widgets.php?containers=1',
				reader: {
					type: 'json',
					rootProperty: 'data'
				},
            }
		}),
		editable: false,
		triggerAction: 'all',
		selectOnFocus:true,
		allowBlank: false      
	},{
        name: 'template',
        fieldLabel: Config.Lang.template,
        allowBlank: true
    }]	

});