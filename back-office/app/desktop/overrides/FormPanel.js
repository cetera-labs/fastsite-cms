Ext.define('Cetera.override.FormPanel', {
    
    override: 'Ext.form.Panel',
    
	requires: [
        'Ext.Responsive'
    ],  
       
    labelAlign: 'left',

    responsiveConfig: {
        small: {
            fieldDefaults: {
                labelAlign: 'top',
            },
        },
        large: {
            fieldDefaults: {
                labelAlign: 'left',
            },
        }
    },       
    
});