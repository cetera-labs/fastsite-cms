Ext.define('Cetera.model.Material', {

    extend: 'Ext.data.Model',

	fields: [
		'icon','tag','name','alias',
		{name: 'dat', type: 'date', dateFormat: 'timestamp'},
		'autor','disabled','catalog','locked','locked_login'
	]
    
});