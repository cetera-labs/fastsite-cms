Ext.define('Cetera.theme.Copy', {

    extend:'Ext.Window',    
          
	title: _('Копировать тему'),
	autoHeight: true,
	autoShow: false,
	modal: true,
	width:500,
	closable: true,
	resizable: false,
	bodyPadding: 10,	
	closeAction: 'hide',
		
	items: {
		xtype: 'form',
		itemId: 'form',
		bodyStyle:'background:none;',
		border: false,
		layout     : 'anchor',
		defaults   : { anchor: '0', hideEmptyLabel: false },
        url: '/cms/include/action_themes.php?action=copy',		
		items: [
			{
				xtype: 'hidden',
				name: 'theme'
			},        
			{
				xtype: 'textfield',
				fieldLabel: _('Идентификатор'),
				name: 'name',
				regex: /^[\-\_a-z0-9]+$/i,
				allowBlank: false
			},
			{
				xtype: 'textfield',
				fieldLabel: _('Название'),
				name: 'title',
				allowBlank: false
			},		
			{
				xtype: 'textfield',
				fieldLabel: _('Версия темы'),
				regex: /^[0-9]+[\.0-9]+[a-z]*$/i,
				name: 'version',
				allowBlank: false
			},				
			{
				xtype: 'textarea',
				fieldLabel: _('Описание'),
				name: 'description'
			}
		]
	},
	
	buttons: [
		{
			text: _('OK'),
			handler: function() {
				this.up('window').save();
			}			
		},
		{
			text: _('Отмена'),
			handler: function() {
				this.up('window').close();
			}
		},
	],
		  
    initComponent: function(){       
        this.callParent(arguments);
        var f = this.getComponent('form');
		f.loadRecord( this.theme );
        f.getForm().findField('theme').setValue( this.theme.get('name') );
    },
	
	save: function(){  
		var f = this.getComponent('form');
		if (!f.isValid()) return;

        f.getForm().submit({
            scope: this,
            waitMsg: Config.Lang.wait,
            success: function(form, action) {
                this.fireEvent('theme_update');
                this.close();
            }
        }); 

	}
});