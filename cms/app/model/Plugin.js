Ext.define('Cetera.model.Plugin', {
    extend: 'Ext.data.Model',
    fields: [
        'title', 
        'description', 
        'version',
        'cms_version_min',
        'cms_version_max',
        'compatible', 
		{name:'compatible_message', persist: false}, 
        'disabled', 
        'installed',
        'upgrade',  
        'author'
    ]
});