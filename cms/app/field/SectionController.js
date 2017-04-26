Ext.define('Cetera.field.SectionController', {

    extend: 'Ext.form.field.ComboBox',
    
    alias: 'widget.section_controller',
    
	fieldLabel: _('Контроллер'),
	valueField: 'name',
	displayField: 'name',
	name: 'template',
	store: this.controllerLookupStore,
	triggerAction: 'all',
	selectOnFocus:true,
	
    initComponent: function(){
		
		this.trigger2Cls = 'icon-edit';
				
        Ext.apply(this, {
			
			value: this.section.template,
			
			store: Ext.create('Ext.data.JsonStore', {
				fields: ['name'],
				proxy: {
					type: 'ajax',
					url: 'include/data_templates.php',
					reader: {
						type: 'json',
						root: 'rows'
					},					
					extraParams: {
						'catalog_id': this.section.id
					}  
				}                                  
			}),					
                        
        });  
		
		this.fileEditWindow = Ext.create('Cetera.window.FileEdit', {
			width: 700,
			height: 500,
			modal: true
		}); 		
        
        this.callParent(arguments);

    },

    onTrigger2Click: function() {
		var ext = this.getValue().split('.').pop();
		if (ext == 'twig') {
			this.setLoading(true);
			Ext.Ajax.request({
				url: 'include/action_files.php',
				params: { 
					action: 'get_twig_template_file',
					section_id: this.section.id,
					name: this.getValue()
				},
				scope: this,
				success: function(resp) {
					var obj = Ext.decode(resp.responseText);
					if (obj.filename) {
						this.fileEditWindow.editFile( obj.filename );
					}
				},
				callback: function(response){
					this.setLoading(false);
				},
				
			});			
		}
    },
	
  	onDestroy: function(){
        this.fileEditWindow.destroy();
    	this.callParent(arguments);
  	}	
});