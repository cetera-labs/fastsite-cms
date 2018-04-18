Ext.define('Cetera.theme.Install', {

    extend:'Ext.Window',    
          
    initComponent: function(){
		
        this.cellEditing = new Ext.grid.plugin.CellEditing({
            clicksToEdit: 1
        });	

		this.contentsStore = new Ext.data.JsonStore({
			fields: ['id', 'title','locale'],
			root: 'rows',
			autoLoad: true,
			proxy: {
				type: 'ajax',
				url: 'include/data_themes_avail.php?theme='+this.themeName
			}
		});
		
		this.installButton = Ext.create('Ext.Button', {	
			text: _('Установить'),
			handler: function() {
				var formData = this.up('form').getForm().getValues();
				if (!formData.install) {	
					formData.content = '';
				}
				Ext.create('Cetera.theme.Upgrade',{
					themeName: this.up('window').themeName,
					content: formData.content
				});
				this.up('window').close();
			}
		});		
		
		this.formPanel = Ext.create('Ext.form.Panel', {
			region: 'center',
			layout: 'anchor',
			defaults: {
				anchor: '100%',
				labelWidth: 200
			},	
			border: false,
			bodyStyle:'background: none',
			items: [{
				name: 'install',
				boxLabel: _('установить типовой контент'),
				xtype: 'checkbox',
				checked: true
			},{
				name: 'content',
				fieldLabel:  _('Выберите контент'),
				xtype: 'combo',
				triggerAction: 'all',
				editable: false,
				store: this.contentsStore,
				valueField: 'id',
				displayField: 'title'				
			}],		
			buttons: [this.installButton]
		});
    
        Ext.apply(this, {
            title: _('Установка темы') + ' "' + this.themeName + '"',
            autoHeight: true,
            autoShow: true,
            modal: true,
            width: 600,
            closable: true,
            resizable: false,
            bodyPadding: 10,
			items: [this.formPanel]
        });
        
        this.callParent(arguments);
    }
});