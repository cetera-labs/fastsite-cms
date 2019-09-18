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
			name: 'isServer',
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
		},
		{
			name: 'item_id',
			type: 'int'
		},
		{
			name: 'structure_id',
			type: 'int'
		}			
	],
	
	proxy: {
		type: 'ajax',
		url: '/cms/include/data_tree.php'
	}
}); 