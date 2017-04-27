Ext.define('Cetera.widget.Container', {

    extend: 'Cetera.widget.Widget',
	
    formfields : [{
		xtype: 'combo',
		fieldLabel: Config.Lang.area,
		valueField: 'widgetId',
		displayField: 'widgetTitle',
		name: 'widgetId',
		store: new Ext.data.JsonStore({
			fields: ['widgetId','widgetTitle'],
			url: '/cms/include/data_widgets.php?containers=1',
			root: 'data',
			autoSync: true,
			autoLoad: true
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