Ext.define('Cetera.model.SiteTree', {
    extend: 'Ext.data.Model',
    fields: [
		{
			name: 'text',
			type: 'string'
		},{
			name: 'name',
			type: 'string'
		}, {
			name: 'alias',
			type: 'string'
		}, {
			name: 'disabled',
			type: 'boolean'
		}, {
			name: 'link',
			type: 'integer'
		}, {
			name: 'mtype',
			type: 'integer'
		},
		{
			name: 'date',
			type: 'date'
		},
		{
			name: 'mtype_name',
			type: 'string'
		}
	],
	
	proxy: {
		type: 'ajax',
		url: '/cms/include/data_tree.php'
	}
}); 