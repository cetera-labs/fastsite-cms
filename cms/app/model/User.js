Ext.define('Cetera.model.User', {
    extend: 'Ext.data.Model',
    fields: ['id','login','name','disabled', 'bo', 'checked'],
	
	proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : '/cms/include/data_users.php',		
        },		
        reader: {
			type: 'json',
            root: 'rows'
        }		
	}	
}); 