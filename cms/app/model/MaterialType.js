Ext.define('Cetera.model.MaterialType', {

    extend: 'Ext.data.Model',
    fields: [
        {
            name: 'id',
            type: 'int',
            useNull: true
        },{
            name: 'fixed',
            type: 'int'
        }, 
        'alias', 
        'describ', 
		'describDisplay'
    ],
    validations: [
        {type: 'length',    field: 'alias', min: 1}
    ]
    
});