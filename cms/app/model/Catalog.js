Ext.define('Cetera.model.Catalog', {
    extend: 'Ext.data.Model',
    fields: [
		{
			name: 'name',
			type: 'string'
		},
		{
			name: 'alias',
			type: 'string'
		},
		{
			name: 'is_link',
			type: 'boolean'
		},
		{
			name: 'is_root',
			type: 'boolean'
		},		
		{
			name: 'is_server',
			type: 'boolean'
		},
		{
			name: 'aliases',
			persist: false
		},

		'materialsType',
		'parent',
		'template',
		'templateDir',
		'materialsCount',
		'autoalias',
		'autoaliasTranslit',
		'autoaliasId',
		'hidden',
		'prototype',
		'robots',
		'inheritPermissions',
		'inheritFields',
		'permissions',
		'user_fields'
	],
	
    proxy: {
		type: 'ajax',
		simpleSortMode: true,
        api: {
            read    : 'include/data_catalog.php',
            update  : 'include/data_catalog.php?action=update',
            create  : 'include/data_catalog.php?action=create',
            destroy : 'include/data_catalog.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            root: 'rows'
        }
    }	
}); 