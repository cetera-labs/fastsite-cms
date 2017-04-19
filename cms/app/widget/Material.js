// Панелька виджета 
Ext.require('Cetera.field.Folder');
Ext.require('Cetera.field.FileEditable');

Ext.define('Cetera.widget.Material', {

    extend: 'Cetera.widget.Widget',
    
    initComponent : function() {
        this.formfields = [
			{
				fieldLabel: Config.Lang.material,
				name: 'material_id',
				xtype: 'folderfield',
				nocatselect: 1,
				materials: 1,
				listeners: {
					select: {
						scope:this,
						fn: function(data)
						{ 
							this.form.getForm().setValues({
								material_type: data.type
							});
						}
					}
				}			
			},
			{
				name: 'material_type',
				xtype: 'hidden'
			},
			{
				xtype:          'checkbox',
				boxLabel:       _('показать кнопки шаринга в соцсетях'),
				name:           'share_buttons',
				inputValue:     1,
				uncheckedValue: 0
			},
			{
				xtype:         'checkbox',
				boxLabel:      _('показать иллюстрацию'),
				name:          'show_pic',
				inputValue:     1,
				uncheckedValue: 0
			},
			{
				name: 'template',
				fieldLabel: Config.Lang.template,
				allowBlank: true
			}		
		];
        this.callParent();
    },
	
	setParams : function(params) {
		this.callParent([params]);
		if (params.material_id && params.material_type)
		{			
            Ext.Ajax.request({
                url: 'include/action_materials.php?action=get_path_info&mat_id='+params.material_id+'&type='+params.material_type,
                success: function(response){
                    var obj = Ext.decode( response.responseText );
                    this.form.getForm().findField( 'material_id' ).setDisplayValue( obj.displayPath );
                },
                scope: this
            }); 			
			
		}		
	}	

});