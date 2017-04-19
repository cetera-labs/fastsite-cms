Ext.define('Cetera.model.SiteTree', {
    extend: 'Ext.data.Model',
    fields: [{
        name: 'text',
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
    }]
}); 