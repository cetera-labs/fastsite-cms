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
		
		if (ext == 'widget') {
			
			this.setLoading(true);
			var widgetAlias = this.getValue().replace('.'+ext, '');
			Ext.Ajax.request({
				url: '/cms/include/action_get_widget.php',
				params: {
					widgetName:	widgetAlias
				},
				callback: function(response){
					this.setLoading(false);
				},				
				success: function(response) {
					this.setLoading(false);
					var obj = Ext.decode(response.responseText);
					
					if (!obj.success) return;
					
					if (!obj.widgetId) {
						this.newWidget( widgetAlias );
						return;
					}
					
					obj.closable = false;

					var window = Ext.create('Ext.Window', {
						width: '80%',
						height: '80%',
						modal: true,
						layout: 'fit',
						border: false,
						buttons: [{
							text: 'OK',
							handler: function(){
								this.up('window').close();
							}
						}]
					}); 	
					window.setLoading(true);
					window.show();
					window.add(Ext.create('Cetera.widget.ContainerMain', obj));
					window.setLoading(false);
				},
				failure: function() {
					this.setLoading(false);
					this.newWidget( widgetAlias );
				},
				scope: this
			}); 			
			
		}
		
    },
	
	// создание нового виджета - области
	newWidget: function( widgetAlias ) {
		
        Ext.MessageBox.confirm(_('Виджет не найден'), _('Создать виджет?'), function(btn) {
            if (btn == 'yes') {

				this.setLoading(true);
				Ext.Ajax.request({
					url: '/cms/include/action_set_widget.php',
					params: {
						id: 0,
						container_id: 0,
						widgetAlias: widgetAlias,
						template: 'page.twig',
						widgetName: 'Container',
						widgetTitle: _('Область')
					},
					success: function(response) {
						this.setLoading( false ); 
						var obj = Ext.decode(response.responseText);
						this.setValue(obj.widgetAlias + '.widget');
						this.onTrigger2Click();
					},
					failure: function() {
						this.setLoading( false );
					},
					scope: this
				});  

			}
        }, this);
		
	},
	
  	onDestroy: function(){
        this.fileEditWindow.destroy();
    	this.callParent(arguments);
  	}	
});