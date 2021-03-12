Ext.define('Cetera.field.WidgetTemplate', {

    extend: 'Ext.form.field.ComboBox',
	
	alias : 'widget.widgettemplate',
	
	fieldLabel: Config.Lang.template,
	name: 'template',
    valueField: 'name',

	// Template for the dropdown menu.
    // Note the use of "x-boundlist-item" class,
    // this is required to make the items selectable.
    tpl: Ext.create('Ext.XTemplate',
        '<tpl for=".">',
            '<div class="x-boundlist-item"><b>{name}</b>{display}</div>',
        '</tpl>'
    ),
    // template for the content inside text field
    displayTpl: Ext.create('Ext.XTemplate',
        '<tpl for=".">',
            '{name}',
        '</tpl>'
    ),
	
	
    initComponent : function() {

        this.store = Ext.create('Ext.data.JsonStore', {
			fields: ['name','display'],
			autoLoad: true,
			proxy: {
				type: 'ajax',
				url: '/cms/include/data_widget_templates.php',
                reader: {
                    type: 'json',
                    rootProperty: 'rows'
                },				
				extraParams: {
					'widget'    : this.widget
				}    				
			}                                
         }); 	
	
        this.callParent();
    }	

});
