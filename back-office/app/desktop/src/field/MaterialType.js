Ext.define('Cetera.field.MaterialType', {

    extend: 'Ext.form.field.ComboBox',
    
    alias: 'widget.materialtypefield',
    
    valueField: 'id',
    displayField: 'describDisplay',
								
    triggerAction: 'all',
    editable: false,
	
    initComponent: function(){
		
		if (!this.empty) this.empty = 0;
		if (!this.linkable) this.linkable = 0;
		
        Ext.apply(this, {
			
			store: new Ext.data.JsonStore({
				fields: ['id', 'describ', 'describDisplay'],
                autoLoad: true,
                proxy: {
                    type: 'ajax',
					url: '/cms/include/data_types.php?linkable=' + this.linkable + '&empty=' + this.empty,
					 reader: {
						 type: 'json',
						 rootProperty: 'rows'
					 }					
                }
			}),					
                        
        });              
        
        this.callParent(arguments);

    }	
});