Ext.define('Cetera.model.WidgetTemplate', {
	
    extend: 'Ext.data.Model',
	
    fields: [{
        name: 'text',
        type: 'string'
    },{
        name: 'path',
        type: 'string'
    },{
        name: 'writable',
        type: 'boolean'
    },{
        name: 'theme',
        type: 'string'
    },{
        name: 'name',
        type: 'string'
    },{
        name: 'folder',
        type: 'string'
    }],
	
    proxy: {
		type: 'ajax',
        url: '/cms/include/data_widgets_tree.php'
    }		
}); 