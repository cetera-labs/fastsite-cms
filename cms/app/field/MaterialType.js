Ext.define('Cetera.field.MaterialType', {

    extend: 'Ext.form.field.ComboBox',
    
    alias: 'widget.materialtypefield',
    
    valueField: 'id',
    displayField: 'describ',
								
    triggerAction: 'all',
    editable: false,
	
    initComponent: function(){
		
		if (!this.empty) this.empty = 0;
		if (!this.linkable) this.linkable = 0;
		
        Ext.apply(this, {
			
			store: new Ext.data.JsonStore({
				fields: ['id', 'describ'],
                autoLoad: true,
                proxy: {
                    type: 'ajax',
					url: 'include/data_types.php?linkable=' + this.linkable + '&empty=' + this.empty,
					 reader: {
						 type: 'json',
						 root: 'rows'
					 }					
                }
			}),					
                        
        });              
        
        this.callParent(arguments);

    }	
});