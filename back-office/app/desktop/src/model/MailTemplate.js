Ext.define('Cetera.model.MailTemplate', {
    extend: 'Ext.data.Model',

    fields: [
		{name:'id', type: 'int'},
        {name:'active', type: 'boolean'},
        {name:'event', type: 'string'},
		{name:'content_type', type: 'string', defaultValue: 'text/plain'},
		{name:'mail_subject', type: 'string'},
		{name:'mail_from_name', type: 'string'},
		{name:'mail_from_email', type: 'string'},
		{name:'mail_to', type: 'string'},
		{name:'mail_body', type: 'string'}
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : 'include/data_mail_templates.php',
            update  : 'include/data_mail_templates.php?action=update',
            create  : 'include/data_mail_templates.php?action=create',
            destroy : 'include/data_mail_templates.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            rootProperty: 'rows'
        }
    }	
	
});