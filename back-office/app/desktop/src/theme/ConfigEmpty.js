Ext.define('Cetera.theme.ConfigEmpty', {

    extend:'Cetera.theme.Config', 
    
    items: [{
		xtype: 'panel',
		padding: 10,
		border: false,
		bodyStyle: 'background: none',
		html: _('Тема не имеет настроек')
	}]
         
});