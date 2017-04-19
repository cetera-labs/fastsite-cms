Ext.define('Cetera.model.MailEvent', {
    extend: 'Ext.data.Model',

    fields: [
		{name:'id', type: 'string'},
        {name:'name', type: 'string'},
		{name:'parameters'}
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        url : 'include/data_mail_events.php',		
        reader: {
			type: 'json',
            root: 'rows'
        }
    }	
	
});