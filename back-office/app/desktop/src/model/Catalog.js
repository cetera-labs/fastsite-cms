Ext.define('Cetera.model.Catalog', {
    extend: 'Ext.data.Model',
    fields: [
		{
			name: 'name',
			type: 'string'
		},
		{
			name: 'name2',
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
        'visual_constructor',
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
            read    : '/cms/include/data_catalog.php',
            update  : '/cms/include/data_catalog.php?action=update',
            create  : '/cms/include/data_catalog.php?action=create',
            destroy : '/cms/include/data_catalog.php?action=destroy'			
        },		
        reader: {
			type: 'json',
            rootProperty: 'rows'
        }
    }	
}); 