Ext.define('Cetera.override.Window', {
    
    override: 'Ext.Window',
    
	requires: [
        'Ext.Responsive'
    ],  
    
    maximizable: false,

    responsiveConfig: {
        small: {
            maximized: true,
        },
        large: {
            maximized: false,
        }
    },    
    
});