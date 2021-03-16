Ext.define('Cetera.model.Event', {
    extend: 'Ext.data.Model',

    fields: [
		{name:'id', type: 'string'},
        {name:'name', type: 'string'},
		{name:'parameters'}
    ],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        url : '/cms/include/data_events.php',		
        reader: {
			type: 'json',
            rootProperty: 'rows'
        }
    }	
	
});