Ext.define('Cetera.theme.ContentSettings', {

    extend:'Ext.Window',    
          
	title: _('Контент'),
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
				name: 'id',
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
				fieldLabel: _('Язык'),
				xtype: 'combo',
				name: 'locale',
				store: ['ru','en'],
				allowBlank: false
			},
			{
				xtype: 'textfield',
				fieldLabel: _('Автор'),
				name: 'author',
				allowBlank: false
			},		
			{
				xtype: 'textfield',
				fieldLabel: _('Версия'),
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
			text: _('Выгрузить контент'),
			handler: function() {
				this.up('window').save(true);
			}			
		},	
		{
			text: _('Сохранить'),
			handler: function() {
				this.up('window').save(false);
			}			
		},
		{
			text: _('Закрыть'),
			handler: function() {
				this.up('window').close();
			}
		},
	],
		  
    initComponent: function(){       
        this.callParent(arguments);
		this.getComponent('form').loadRecord( this.theme.getContent() );
    },
	
	save: function(upload){  
	
		var f = this.getComponent('form');
		if (!f.isValid()) return;
		this.setLoading(true);
		f.getForm().updateRecord();
		f.getForm().getRecord().save({
			scope: this,
			callback: function(r,o) {
				this.setLoading(false);
				if (o.success) {
					this.fireEvent('content_update');
					if (upload) {
						this.upload();
					}
				}
			}
		});
	},
	
	upload: function(){ 
	
		this.setLoading(true);
    
        Ext.Ajax.request({
            url: 'include/action_themes.php',
            params: { 
                action: 'upload_content', 
                'theme': this.theme.get('id')
            },
            scope: this,
            success: function(resp) {
				Ext.MessageBox.alert(_('Успешное завершение'), _('Контент выгружен в MarketPlace'));			
            },
            callback: function(response){
                this.setLoading(false);
            },
			
        });	
	
	}
});