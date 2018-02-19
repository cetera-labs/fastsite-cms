Ext.define('Cetera.theme.Settings', {

    extend:'Ext.Window',    
          
	title: _('Конфигурация'),
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
		items: [
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
				fieldLabel: _('Языковая доступность'),
				xtype: 'combo',
				name: 'locale',
				store: ['','ru','en']
			},
			{
				xtype: 'textfield',
				fieldLabel: _('Автор'),
				name: 'author',
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
			},
			{
				xtype: 'checkbox',
				boxLabel: _('запретить обновление темы'),
				name: 'disableUpgrade'
			},
			{
				xtype: 'checkbox',
				boxLabel: _('режим разработчика'),
				name: 'developerMode'
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
		this.getComponent('form').loadRecord( this.theme );
    },
	
	save: function(){  
		var f = this.getComponent('form');
		if (!f.isValid()) return;
		f.getForm().updateRecord( this.theme );
		this.setLoading(true);
		this.theme.save({
			scope: this,
			callback: function(r,o) {
				this.setLoading(false);
				if (o.success) {
					if (this.theme.get('name') == this.theme.get('id')) {
						this.fireEvent('theme_update');
						this.close();
					} else {
						Cetera.getApplication().reload();
					}
				}
			}
		});
	}
});