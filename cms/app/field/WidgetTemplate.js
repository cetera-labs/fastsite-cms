Ext.define('Cetera.field.WidgetTemplate', {

    extend: 'Ext.form.field.ComboBox',
	
	alias : 'widget.widgettemplate',
	
	fieldLabel: Config.Lang.template,
	name: 'template',
    valueField: 'name',
    displayField: 'display',	
	
    initComponent : function() {

        this.store = Ext.create('Ext.data.JsonStore', {
			fields: ['name','display'],
			autoLoad: true,
			proxy: {
				type: 'ajax',
				url: '/cms/include/data_widget_templates.php',
                reader: {
                    type: 'json',
                    root: 'rows'
                },				
				extraParams: {
					'widget'    : this.widget
				}    				
			}                                
         }); 	
	
        this.callParent();
    }	

});
