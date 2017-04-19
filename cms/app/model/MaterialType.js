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
        'plugin',
        'handler'
    ],
    validations: [
        {type: 'length',    field: 'alias', min: 1}
    ]
    
});