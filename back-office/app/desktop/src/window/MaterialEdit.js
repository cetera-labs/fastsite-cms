Ext.define('Cetera.window.MaterialEdit', {

    extend:'Ext.Window',
    
	requires: [
        'Ext.Responsive'
    ],     

    maximizable: true, 
    closable:    true,
    resizable:   true,
    plain:       true,
    modal:       true,
    minHeight:   450,
    minWidth:    450,
    layout:     'fit',
    title:      Config.Lang.material,
    width:      Config.maxWindowWidth,
    height:     Config.maxWindowHeight,
	constrainHeader: true,
    
    materialForm: false,
    
    responsiveConfig: {
        small: {
            maximized: true,
            maximizable: false,
            minHeight:   50,
            minWidth:    50,   
            resizable:   false,
        },
        large: {
            maximized: false,
            maximizable: true,
            minHeight:   450,
            minWidth:    450,  
            resizable:   true,
        }
    },     
    
});