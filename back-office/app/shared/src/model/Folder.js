Ext.define('Cetera.model.Folder', {
    extend: 'Ext.data.Model',
    fields: [
		{
			name: 'text',
			type: 'string'
		},
		{
			name: 'readOnly',
			type: 'boolean'
		}		
	]
}); 